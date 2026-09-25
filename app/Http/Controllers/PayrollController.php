<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use App\Models\GuidePayment;
use App\Services\PayrollService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request, PayrollService $payrollService)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $payrolls = $payrollService->getPayrollSummary($startDate, $endDate);

        $totalFeeAll = $payrolls->sum('total_fee');
        $totalPaidAll = $payrolls->sum('total_paid');
        $totalRemainingAll = $payrolls->sum('remaining');

        return view('payroll.index', compact('payrolls', 'startDate', 'endDate', 'totalFeeAll', 'totalPaidAll', 'totalRemainingAll'));
    }

    public function show(Guide $guide, PayrollService $payrollService)
    {
        $payroll = $payrollService->getGuidePayroll($guide);
        return view('payroll.show', compact('guide', 'payroll'));
    }

    public function storePayment(Request $request, Guide $guide, PayrollService $payrollService)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1000',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'admin_override' => 'nullable|boolean',
        ]);

        try {
            $payment = $payrollService->makePayment(
                $guide,
                (float) $validated['amount'],
                $validated['payment_date'],
                $validated['payment_method'],
                $validated['notes'] ?? null,
                $request->boolean('admin_override')
            );

            return back()->with('success', "Pembayaran {$payment->payment_code} sebesar Rp " . number_format($payment->amount, 0, ',', '.') . " berhasil dicatat.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function paymentList(Request $request)
    {
        $query = GuidePayment::with(['guide', 'creator'])->orderBy('payment_date', 'desc');

        if ($request->filled('guide_id')) {
            $query->where('guide_id', $request->guide_id);
        }

        $payments = $query->paginate(20)->withQueryString();
        $guides = Guide::orderBy('nama')->get();

        return view('payroll.payments', compact('payments', 'guides'));
    }
}
