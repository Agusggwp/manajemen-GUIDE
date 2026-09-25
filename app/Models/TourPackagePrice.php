<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourPackagePrice extends Model
{
    protected $fillable = [
        'tour_package_id',
        'min_guests',
        'max_guests',
        'price_per_guest',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'min_guests' => 'integer',
            'max_guests' => 'integer',
            'price_per_guest' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function tourPackage()
    {
        return $this->belongsTo(TourPackage::class, 'tour_package_id');
    }
}
