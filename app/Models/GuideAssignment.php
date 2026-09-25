<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuideAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'guide_id',
        'guest_count',
        'fee_type',
        'fee_rate',
        'fee_amount',
        'queue_position',
        'assigned_at',
        'started_at',
        'completed_at',
        'status',
        'assigned_by',
    ];

    protected function casts(): array
    {
        return [
            'guest_count' => 'integer',
            'fee_rate' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'queue_position' => 'integer',
            'assigned_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function guide()
    {
        return $this->belongsTo(Guide::class, 'guide_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
