<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'booking_date',
        'start_time',
        'end_time',
        'customer_name',
        'customer_phone',
        'nationality',
        'guest_count',
        'tour_package_id',
        'location',
        'pickup_location',
        'notes',
        'price_per_guest',
        'total_price',
        'guide_fee_total',
        'operational_cost',
        'company_profit',
        'status',
        'is_walkin',
        'payment_method',
        'payment_status',
        'amount_paid',
        'change_amount',
        'payment_reference',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'guest_count' => 'integer',
            'price_per_guest' => 'decimal:2',
            'total_price' => 'decimal:2',
            'guide_fee_total' => 'decimal:2',
            'operational_cost' => 'decimal:2',
            'company_profit' => 'decimal:2',
            'is_walkin' => 'boolean',
            'amount_paid' => 'decimal:2',
            'change_amount' => 'decimal:2',
        ];
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        $map = [
            'CASH' => 'Tunai (Cash)',
            'QRIS' => 'QRIS Scan',
            'TRANSFER' => 'Transfer Bank',
            'CARD' => 'Kartu Debit / EDC',
            'CREDIT_CARD' => 'Kartu Kredit',
        ];

        return $map[strtoupper($this->payment_method ?? '')] ?? ($this->payment_method ?: 'Tunai (Cash)');
    }

    public function tourPackage()
    {
        return $this->belongsTo(TourPackage::class, 'tour_package_id');
    }

    public function guests()
    {
        return $this->hasMany(Guest::class, 'booking_id');
    }

    public function assignments()
    {
        return $this->hasMany(GuideAssignment::class, 'booking_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'booking_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedGuestsCount(): int
    {
        return (int) $this->assignments()
            ->where('status', '!=', 'CANCELLED')
            ->sum('guest_count');
    }

    public function remainingGuestsCount(): int
    {
        return max(0, $this->guest_count - $this->assignedGuestsCount());
    }

    public function recalculateTotals(): void
    {
        $this->total_price = $this->guest_count * $this->price_per_guest;
        $this->guide_fee_total = (float) $this->assignments()
            ->where('status', '!=', 'CANCELLED')
            ->sum('fee_amount');
            
        $this->operational_cost = (float) $this->expenses()->sum('amount');
        $this->company_profit = $this->total_price - $this->guide_fee_total - $this->operational_cost;

        $assignedCount = $this->assignedGuestsCount();
        if ($this->status !== 'CANCELLED' && $this->status !== 'COMPLETED') {
            $hasOnGoing = $this->assignments()->where('status', 'ON_GOING')->exists();
            if ($hasOnGoing) {
                $this->status = 'ON_GOING';
            } elseif ($assignedCount >= $this->guest_count) {
                $this->status = 'FULLY_ASSIGNED';
            } elseif ($assignedCount > 0) {
                $this->status = 'PARTIALLY_ASSIGNED';
            } else {
                $this->status = 'WAITING';
            }
        }

        $this->save();
    }
}
