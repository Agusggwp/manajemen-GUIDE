<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'nama',
        'jenis_kelamin',
        'umur',
        'negara',
        'nomor_telepon',
        'email',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'umur' => 'integer',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
