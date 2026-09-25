<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'previous_position',
        'next_position',
        'selected_guide_id',
        'skipped_guides',
        'booking_id',
        'assigned_by',
        'type',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'previous_position' => 'integer',
            'next_position' => 'integer',
            'skipped_guides' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function selectedGuide()
    {
        return $this->belongsTo(Guide::class, 'selected_guide_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
