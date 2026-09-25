<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuidePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'guide_id',
        'payment_code',
        'amount',
        'payment_date',
        'payment_method',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function guide()
    {
        return $this->belongsTo(Guide::class, 'guide_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
