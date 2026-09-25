<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public static function getExpenseCategories(): array
    {
        $val = static::get('expense_categories');
        if (!empty($val)) {
            $decoded = json_decode($val, true);
            if (is_array($decoded) && count($decoded) > 0) {
                return array_values(array_filter(array_map('trim', $decoded)));
            }
            if (is_string($val)) {
                $lines = preg_split('/[\r\n,]+/', $val);
                $filtered = array_values(array_filter(array_map('trim', $lines)));
                if (!empty($filtered)) {
                    return $filtered;
                }
            }
        }

        return [
            'Tiket Masuk & Parkir',
            'Bahan Bakar & Tol',
            'Sewa Mobil / Bus Pariwisata',
            'Konsumsi & Air Mineral',
            'Lain-lain',
        ];
    }
}
