<?php

namespace Database\Factories;

use App\Models\Guide;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuideFactory extends Factory
{
    protected $model = Guide::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'kode_guide' => 'GD-' . fake()->unique()->numerify('###'),
            'nama' => fake()->name(),
            'nomor_telepon' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'alamat' => fake()->address(),
            'bahasa' => 'Indonesia, English',
            'spesialisasi' => 'Culture, Trekking',
            'queue_number' => fake()->numberBetween(1, 100),
            'status' => 'AVAILABLE',
            'tanggal_gabung' => fake()->date(),
            'tipe_fee' => 'PER_GUEST',
            'fee_per_guest' => 50000,
            'fee_per_booking' => 300000,
            'fee_minimum' => 0,
        ];
    }
}
