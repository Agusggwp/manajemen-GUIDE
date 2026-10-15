<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Guide;
use App\Models\GuideAssignment;
use App\Models\GuidePayment;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Get summary payroll metrics for all guides.
     */
    public function getPayrollSummary(?string $startDate = null, ?string $endDate = null): Collection
    {
        $guides = Guide::with(['assignments' => function ($q) use ($startDate, $endDate) {
            $q->where('status', '!=', 'CANCELLED');
            if ($startDate && $endDate) {
                $q->whereBetween('assigned_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }
        }, 'payments' => function ($q) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $q->whereBetween('payment_date', [$startDate, $endDate]);
            }
        }])->get();

        return $guides->map(function (Guide $guide) {
            $totalBookings = $guide->assignments->count();
            $totalGuests = $guide->assignments->sum('guest_count');
            $totalFee = (float) $guide->assignments->sum('fee_amount');
            $totalPaid = (float) $guide->payments->sum('amount');
            $remaining = max(0, $totalFee - $totalPaid);

            $status = 'UNPAID';
            if ($totalPaid >= $totalFee && $totalFee > 0) {
                $status = 'PAID';
            } elseif ($totalPaid > 0) {
                $status = 'PARTIAL';
            }

            return (object) [
                'guide' => $guide,
                'total_bookings' => $totalBookings,
                'total_guests' => $totalGuests,
                'total_fee' => $totalFee,
                'total_paid' => $totalPaid,
                'remaining' => $remaining,
                'status' => $status,
            ];
        });
    }

    /**
     * Get payroll details for a specific guide.
     */
    public function getGuidePayroll(Guide $guide): object
    {
        $assignments = $guide->assignments()
            ->with('booking')
            ->where('status', '!=', 'CANCELLED')
            ->orderBy('assigned_at', 'desc')
            ->get();

        $payments = $guide->payments()
            ->orderBy('payment_date', 'desc')
            ->get();

        $totalFee = (float) $assignments->sum('fee_amount');
        $totalPaid = (float) $payments->sum('amount');
        $remaining = max(0, $totalFee - $totalPaid);

        $status = 'UNPAID';
        if ($totalPaid >= $totalFee && $totalFee > 0) {
            $status = 'PAID';
        } elseif ($totalPaid > 0) {
            $status = 'PARTIAL';
        }

        return (object) [
            'guide' => $guide,
            'assignments' => $assignments,
            'payments' => $payments,
            'total_bookings' => $assignments->count(),
            'total_guests' => $assignments->sum('guest_count'),
            'total_fee' => $totalFee,
            'total_paid' => $totalPaid,
            'remaining' => $remaining,
            'status' => $status,
        ];
    }

    /**
     * Record a payment for a guide with balance validation.
     */
    public function makePayment(
        Guide $guide,
        float $amount,
        string $paymentDate,
        string $paymentMethod = 'Bank Transfer',
        ?string $notes = null,
        bool $adminOverride = false,
        ?int $createdById = null
    ): GuidePayment {
        return DB::transaction(function () use ($guide, $amount, $paymentDate, $paymentMethod, $notes, $adminOverride, $createdById) {
            if ($amount <= 0) {
                throw new Exception("Nominal pembayaran harus lebih besar dari 0.");
            }

            // Lock guide record to serialize concurrent payments for this guide
            $guideModel = Guide::where('id', $guide->id)->lockForUpdate()->firstOrFail();

            // Lock and calculate current valid payments and fees under pessimistic lock
            $totalPaid = (float) GuidePayment::where('guide_id', $guideModel->id)->lockForUpdate()->sum('amount');
            $totalFee = (float) GuideAssignment::where('guide_id', $guideModel->id)->where('status', '!=', 'CANCELLED')->sum('fee_amount');
            $remaining = max(0, $totalFee - $totalPaid);

            if ($amount > $remaining && !$adminOverride) {
                throw new Exception("Nominal pembayaran (Rp " . number_format($amount, 0, ',', '.') . ") melebihi sisa fee yang belum dibayar (Rp " . number_format($remaining, 0, ',', '.') . ").");
            }

            $prefix = 'PAY-' . Carbon::parse($paymentDate)->format('ymd');
            $maxCode = GuidePayment::where('payment_code', 'like', $prefix . '-%')
                ->lockForUpdate()
                ->orderBy('payment_code', 'desc')
                ->value('payment_code');

            $seq = 1;
            if ($maxCode && preg_match('/-(\d+)$/', $maxCode, $matches)) {
                $seq = ((int) $matches[1]) + 1;
            }
            $paymentCode = sprintf('%s-%03d', $prefix, $seq);

            $payment = GuidePayment::create([
                'guide_id' => $guideModel->id,
                'payment_code' => $paymentCode,
                'amount' => $amount,
                'payment_date' => $paymentDate,
                'payment_method' => $paymentMethod,
                'notes' => $notes,
                'created_by' => $createdById ?? auth()->id(),
            ]);

            AuditLog::log('PAYMENT_GUIDE', GuidePayment::class, $payment->id, null, [
                'guide_id' => $guideModel->id,
                'payment_code' => $paymentCode,
                'amount' => $amount,
                'payment_date' => $paymentDate,
            ]);

            return $payment;
        });
    }
}
