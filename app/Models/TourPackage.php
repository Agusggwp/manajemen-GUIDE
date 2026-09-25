<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'duration',
        'default_price',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'default_price' => 'decimal:2',
        ];
    }

    public function prices()
    {
        return $this->hasMany(TourPackagePrice::class, 'tour_package_id')->orderBy('min_guests');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'tour_package_id');
    }

    public function getPriceForGuestCount(int $guestCount): float
    {
        $tier = $this->prices()
            ->where('active', true)
            ->where('min_guests', '<=', $guestCount)
            ->where('max_guests', '>=', $guestCount)
            ->first();

        if ($tier) {
            return (float) $tier->price_per_guest;
        }

        return (float) ($this->default_price > 0 ? $this->default_price : 150000);
    }

    public function getPriceForGuests(int $guestCount): float
    {
        return $this->getPriceForGuestCount($guestCount);
    }
}
