<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuideAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'guide_id',
        'attendance_date',
        'check_in_at',
        'check_out_at',
        'status',
        'notes',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date:Y-m-d',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'check_in_latitude' => 'decimal:7',
            'check_in_longitude' => 'decimal:7',
            'check_out_latitude' => 'decimal:7',
            'check_out_longitude' => 'decimal:7',
        ];
    }

    public function setAttendanceDateAttribute($value)
    {
        $this->attributes['attendance_date'] = $value instanceof \DateTimeInterface
            ? $value->format('Y-m-d')
            : substr((string)$value, 0, 10);
    }

    public function guide()
    {
        return $this->belongsTo(Guide::class, 'guide_id');
    }
}
