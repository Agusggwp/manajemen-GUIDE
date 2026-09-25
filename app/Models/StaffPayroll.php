<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffPayroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_code',
        'user_id',
        'period_month',
        'period_year',
        'position',
        'base_salary',
        'allowance',
        'bonus',
        'deduction',
        'net_salary',
        'payment_status',
        'payment_date',
        'payment_method',
        'payment_reference',
        'notes',
        'paid_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'period_month' => 'integer',
            'period_year' => 'integer',
            'base_salary' => 'decimal:2',
            'allowance' => 'decimal:2',
            'bonus' => 'decimal:2',
            'deduction' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'PAID';
    }

    public function getPeriodNameAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $monthName = $months[$this->period_month] ?? 'Bulan ' . $this->period_month;
        return "{$monthName} {$this->period_year}";
    }

    /**
     * Generate unique payroll slip code.
     */
    public static function generatePayrollCode(int $year, int $month): string
    {
        $prefix = sprintf('STF-PAY-%04d%02d-', $year, $month);
        $lastRecord = self::where('payroll_code', 'LIKE', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastRecord && preg_match('/-(\d+)$/', $lastRecord->payroll_code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        }

        return $prefix . sprintf('%03d', $nextNumber);
    }

    /**
     * Spell out amount in Indonesian words (Terbilang).
     */
    public function getTerbilangAttribute(): string
    {
        return self::numberToWords((int) round($this->net_salary)) . ' Rupiah';
    }

    public static function numberToWords(int $number): string
    {
        $words = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($number < 12) {
            return $words[$number];
        } elseif ($number < 20) {
            return self::numberToWords($number - 10) . ' Belas';
        } elseif ($number < 100) {
            return self::numberToWords(intdiv($number, 10)) . ' Puluh ' . self::numberToWords($number % 10);
        } elseif ($number < 200) {
            return 'Seratus ' . self::numberToWords($number - 100);
        } elseif ($number < 1000) {
            return self::numberToWords(intdiv($number, 100)) . ' Ratus ' . self::numberToWords($number % 100);
        } elseif ($number < 2000) {
            return 'Seribu ' . self::numberToWords($number - 1000);
        } elseif ($number < 1000000) {
            return self::numberToWords(intdiv($number, 1000)) . ' Ribu ' . self::numberToWords($number % 1000);
        } elseif ($number < 1000000000) {
            return self::numberToWords(intdiv($number, 1000000)) . ' Juta ' . self::numberToWords($number % 1000000);
        } elseif ($number < 1000000000000) {
            return self::numberToWords(intdiv($number, 1000000000)) . ' Miliar ' . self::numberToWords($number % 1000000000);
        }

        return (string) $number;
    }
}
