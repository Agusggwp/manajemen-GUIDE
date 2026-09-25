<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guide extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kode_guide',
        'nama',
        'nomor_telepon',
        'email',
        'alamat',
        'foto',
        'bahasa',
        'spesialisasi',
        'queue_number',
        'status',
        'tanggal_gabung',
        'tipe_fee',
        'fee_per_guest',
        'fee_per_booking',
        'fee_minimum',
        'catatan',
        'suspend_reason',
        'suspended_at',
        'suspended_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_gabung' => 'date',
            'queue_number' => 'integer',
            'fee_per_guest' => 'decimal:2',
            'fee_per_booking' => 'decimal:2',
            'fee_minimum' => 'decimal:2',
            'suspended_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function suspendedBy()
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function isSuspended(): bool
    {
        return $this->status === 'SUSPENDED';
    }

    public function suspend(string $reason, ?int $adminId = null): void
    {
        $this->update([
            'status' => 'SUSPENDED',
            'suspend_reason' => $reason,
            'suspended_at' => now(),
            'suspended_by' => $adminId ?? auth()->id(),
        ]);
    }

    public function unsuspend(): void
    {
        $this->update([
            'status' => 'AVAILABLE',
            'suspend_reason' => null,
            'suspended_at' => null,
            'suspended_by' => null,
        ]);
    }

    public function attendances()
    {
        return $this->hasMany(GuideAttendance::class, 'guide_id');
    }

    public function todayAttendance(?string $date = null)
    {
        $targetDate = $date ?? Carbon::today()->toDateString();
        return $this->hasOne(GuideAttendance::class, 'guide_id')
            ->whereDate('attendance_date', $targetDate);
    }

    public function assignments()
    {
        return $this->hasMany(GuideAssignment::class, 'guide_id');
    }

    public function payments()
    {
        return $this->hasMany(GuidePayment::class, 'guide_id');
    }

    public function calculateFee(int $guestCount): array
    {
        $type = $this->tipe_fee;
        $rate = 0;
        $amount = 0;

        if ($type === 'PER_BOOKING') {
            $rate = (float) $this->fee_per_booking;
            $amount = $rate;
        } elseif ($type === 'TIERED') {
            if ($guestCount <= 5) {
                $amount = 250000;
                $rate = 250000;
            } elseif ($guestCount <= 10) {
                $amount = 400000;
                $rate = 400000;
            } elseif ($guestCount <= 20) {
                $amount = 600000;
                $rate = 600000;
            } else {
                $amount = 800000;
                $rate = 800000;
            }
        } else {
            // Default: PER_GUEST
            $type = 'PER_GUEST';
            $rate = (float) ($this->fee_per_guest > 0 ? $this->fee_per_guest : 50000);
            $amount = $rate * max(1, $guestCount);
        }

        if ($this->fee_minimum > 0 && $amount < $this->fee_minimum) {
            $amount = (float) $this->fee_minimum;
        }

        return [
            'type' => $type,
            'rate' => $rate,
            'amount' => $amount,
        ];
    }

    public function isCheckedInOn(string $date): bool
    {
        return $this->attendances()
            ->whereDate('attendance_date', $date)
            ->where('status', 'CHECKED_IN')
            ->exists();
    }
}
