<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guide;
use App\Models\GuideAssignment;
use App\Models\GuideAttendance;
use App\Models\QueueHistory;
use App\Models\StaffPayroll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function revenue(Request $request)
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $currentYear = (int) Carbon::today()->format('Y');
        $currentMonth = (int) Carbon::today()->format('n');

        $filterType = $request->query('filter_type');
        if (!$filterType) {
            $filterType = ($request->filled('start_date') && $request->filled('end_date') && !$request->filled('month')) ? 'custom' : 'month';
        }

        $selectedMonth = $request->filled('month') ? (int) $request->month : $currentMonth;
        $selectedYear = $request->filled('year') ? (int) $request->year : $currentYear;

        if ($filterType === 'month') {
            $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth()->toDateString();
            $endDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth()->toDateString();
            $periodLabel = "Bulan {$months[$selectedMonth]} {$selectedYear}";
        } else {
            $startDate = $request->query('start_date', Carbon::today()->startOfMonth()->toDateString());
            $endDate = $request->query('end_date', Carbon::today()->toDateString());
            $periodLabel = Carbon::parse($startDate)->format('d M Y') . ' s/d ' . Carbon::parse($endDate)->format('d M Y');
        }

        $startBound = Carbon::parse($startDate)->startOfDay()->toDateTimeString();
        $endBound = Carbon::parse($endDate)->endOfDay()->toDateTimeString();

        $bookings = Booking::with(['tourPackage', 'assignments'])
            ->where(function ($q) use ($startDate, $endDate, $startBound, $endBound) {
                $q->whereBetween('booking_date', [$startDate, $endDate])
                  ->orWhereBetween('booking_date', [$startBound, $endBound]);
            })
            ->where('status', '!=', 'CANCELLED')
            ->orderBy('booking_date', 'asc')
            ->get();

        $totalRevenue = (float) $bookings->sum('total_price');
        $totalGuideFee = (float) $bookings->sum('guide_fee_total');
        $totalOperational = (float) $bookings->sum('operational_cost');
        $totalGuests = (int) $bookings->sum('guest_count');

        // Query Staff Payrolls in this date range
        $startCarbon = Carbon::parse($startDate);
        $endCarbon = Carbon::parse($endDate);

        $staffPayrolls = StaffPayroll::with(['user', 'paidBy'])
            ->where(function ($q) use ($startDate, $endDate, $startBound, $endBound, $startCarbon, $endCarbon) {
                $q->whereBetween('payment_date', [$startDate, $endDate])
                  ->orWhereBetween('payment_date', [$startBound, $endBound])
                  ->orWhere(function ($sub) use ($startCarbon, $endCarbon) {
                      $sub->whereBetween('period_year', [$startCarbon->year, $endCarbon->year])
                          ->whereBetween('period_month', [$startCarbon->month, $endCarbon->month]);
                  });
            })
            ->orderBy('period_year', 'desc')
            ->orderBy('period_month', 'desc')
            ->get();

        $totalStaffSalaryPaid = (float) $staffPayrolls->where('payment_status', 'PAID')->sum('net_salary');
        $totalStaffSalaryPending = (float) $staffPayrolls->where('payment_status', 'PENDING')->sum('net_salary');
        // Total beban gaji staff yang dihitung ke profit (prioritas yang sudah lunas/terbayar atau seluruh slip gaji periode ini)
        $totalStaffSalary = $totalStaffSalaryPaid > 0 ? $totalStaffSalaryPaid : (float) $staffPayrolls->sum('net_salary');

        // Total Laba Bersih = Pendapatan - Fee Guide - Biaya Operasional Tour - Beban Gaji Staff
        $totalProfit = $totalRevenue - $totalGuideFee - $totalOperational - $totalStaffSalary;

        if ($request->query('export') === 'csv') {
            return $this->exportRevenueCsv($bookings, $staffPayrolls, $startDate, $endDate, $totalRevenue, $totalGuideFee, $totalOperational, $totalStaffSalary, $totalProfit, $periodLabel);
        }

        return view('reports.revenue', compact(
            'bookings',
            'staffPayrolls',
            'startDate',
            'endDate',
            'filterType',
            'selectedMonth',
            'selectedYear',
            'months',
            'periodLabel',
            'totalRevenue',
            'totalGuideFee',
            'totalOperational',
            'totalStaffSalary',
            'totalStaffSalaryPaid',
            'totalStaffSalaryPending',
            'totalProfit',
            'totalGuests'
        ));
    }

    public function guideReport(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::today()->toDateString());

        $guides = Guide::with(['assignments' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('assigned_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
              ->where('status', '!=', 'CANCELLED');
        }])->get()->map(function (Guide $guide) {
            return (object) [
                'kode' => $guide->kode_guide,
                'nama' => $guide->nama,
                'queue_number' => $guide->queue_number,
                'total_bookings' => $guide->assignments->count(),
                'total_guests' => $guide->assignments->sum('guest_count'),
                'total_fee' => $guide->assignments->sum('fee_amount'),
            ];
        });

        if ($request->query('export') === 'csv') {
            return $this->exportGuideCsv($guides, $startDate, $endDate);
        }

        return view('reports.guide', compact('guides', 'startDate', 'endDate'));
    }

    public function attendance(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::today()->startOfWeek()->toDateString());
        $endDate = $request->query('end_date', Carbon::today()->toDateString());

        $attendances = GuideAttendance::with('guide')
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->orderBy('attendance_date', 'desc')
            ->orderBy('check_in_at', 'asc')
            ->get()
            ->map(function ($att) {
                $hours = 0;
                $formattedHours = '-';
                if ($att->check_in_at && $att->check_out_at) {
                    $diffMinutes = $att->check_in_at->diffInMinutes($att->check_out_at);
                    $h = floor($diffMinutes / 60);
                    $m = $diffMinutes % 60;
                    $formattedHours = "{$h} jam {$m} menit";
                    $hours = round($diffMinutes / 60, 2);
                }

                return (object) [
                    'guide_nama' => $att->guide->nama,
                    'kode_guide' => $att->guide->kode_guide,
                    'date' => $att->attendance_date->format('d/m/Y'),
                    'check_in' => $att->check_in_at ? $att->check_in_at->format('H:i') : '-',
                    'check_out' => $att->check_out_at ? $att->check_out_at->format('H:i') : '-',
                    'status' => $att->status,
                    'working_hours' => $formattedHours,
                    'notes' => $att->notes,
                ];
            });

        if ($request->query('export') === 'csv') {
            return $this->exportAttendanceCsv($attendances, $startDate, $endDate);
        }

        return view('reports.attendance', compact('attendances', 'startDate', 'endDate'));
    }

    public function queueReport(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::today()->startOfWeek()->toDateString());
        $endDate = $request->query('end_date', Carbon::today()->toDateString());

        $histories = QueueHistory::with(['selectedGuide', 'booking', 'assigner'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->query('export') === 'csv') {
            return $this->exportQueueCsv($histories, $startDate, $endDate);
        }

        return view('reports.queue', compact('histories', 'startDate', 'endDate'));
    }

    private function exportRevenueCsv($bookings, $staffPayrolls, $startDate, $endDate, $totalRevenue, $totalGuideFee, $totalOperational, $totalStaffSalary, $totalProfit, $periodLabel = null): StreamedResponse
    {
        $cleanLabel = $periodLabel ? \Illuminate\Support\Str::slug($periodLabel) : "{$startDate}-to-{$endDate}";
        $filename = "laporan-revenue-profit-{$cleanLabel}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($bookings, $staffPayrolls, $startDate, $endDate, $totalRevenue, $totalGuideFee, $totalOperational, $totalStaffSalary, $totalProfit, $periodLabel) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header summary
            fputcsv($handle, ['LAPORAN PENDAPATAN & PROFITABILITAS (LABA BERSIH)']);
            fputcsv($handle, ['Periode', $periodLabel ?? "{$startDate} s/d {$endDate}"]);
            fputcsv($handle, ['Waktu Export', now()->format('d/m/Y H:i:s') . ' WITA']);
            fputcsv($handle, []);

            // KPI Summary Section
            fputcsv($handle, ['RINGKASAN KEUANGAN & LABA']);
            fputcsv($handle, ['Komponen', 'Nominal (Rp)']);
            fputcsv($handle, ['Total Pendapatan (Gross Revenue)', number_format($totalRevenue, 2, '.', '')]);
            fputcsv($handle, ['Total Hak Fee Guide', number_format($totalGuideFee, 2, '.', '')]);
            fputcsv($handle, ['Total Biaya Operasional Tour', number_format($totalOperational, 2, '.', '')]);
            fputcsv($handle, ['Total Beban Gaji Staff (Payroll)', number_format($totalStaffSalary, 2, '.', '')]);
            fputcsv($handle, ['LABA BERSIH AKHIR (PROFIT)', number_format($totalProfit, 2, '.', '')]);
            fputcsv($handle, []);

            // Section 1: Bookings
            fputcsv($handle, ['RINCIAN BOOKING PERIODE INI']);
            fputcsv($handle, ['No', 'Kode Booking', 'Tanggal', 'Pelanggan', 'Paket', 'Tamu', 'Harga/Tamu', 'Total Pendapatan', 'Fee Guide', 'Biaya Operasional', 'Laba Bersih Booking']);

            $no = 1;
            foreach ($bookings as $b) {
                fputcsv($handle, [
                    $no++,
                    $b->booking_code,
                    $b->booking_date->format('Y-m-d'),
                    $b->customer_name,
                    $b->tourPackage?->name ?? '-',
                    $b->guest_count,
                    $b->price_per_guest,
                    $b->total_price,
                    $b->guide_fee_total,
                    $b->operational_cost,
                    $b->company_profit,
                ]);
            }

            fputcsv($handle, []);

            // Section 2: Staff Payroll
            fputcsv($handle, ['RINCIAN BEBAN GAJI STAFF (STAFF PAYROLL)']);
            fputcsv($handle, ['No', 'Kode Slip', 'Nama Staff', 'Posisi/Jabatan', 'Bulan', 'Tahun', 'Gaji Pokok', 'Tunjangan+Bonus', 'Potongan', 'Gaji Bersih (Take Home)', 'Status Pembayaran', 'Tanggal Bayar']);

            $noStaff = 1;
            foreach ($staffPayrolls as $sp) {
                fputcsv($handle, [
                    $noStaff++,
                    $sp->payroll_code,
                    $sp->user?->name ?? '-',
                    $sp->position ?? '-',
                    $sp->period_month,
                    $sp->period_year,
                    number_format($sp->base_salary, 2, '.', ''),
                    number_format($sp->allowance + $sp->bonus, 2, '.', ''),
                    number_format($sp->deduction, 2, '.', ''),
                    number_format($sp->net_salary, 2, '.', ''),
                    $sp->payment_status === 'PAID' ? 'LUNAS (PAID)' : 'MENUNGGU (PENDING)',
                    $sp->payment_date ? $sp->payment_date->format('Y-m-d') : '-',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function exportGuideCsv($guides, $startDate, $endDate): StreamedResponse
    {
        $filename = "laporan-kinerja-guide-{$startDate}-to-{$endDate}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($guides) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Kode', 'Nama Guide', 'No Antrean', 'Total Booking', 'Total Tamu Ditangani', 'Total Fee']);

            foreach ($guides as $g) {
                fputcsv($handle, [
                    $g->kode,
                    $g->nama,
                    $g->queue_number,
                    $g->total_bookings,
                    $g->total_guests,
                    $g->total_fee,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    private function exportAttendanceCsv($attendances, $startDate, $endDate): StreamedResponse
    {
        $filename = "laporan-absensi-guide-{$startDate}-to-{$endDate}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($attendances) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Kode Guide', 'Nama Guide', 'Tanggal', 'Jam Masuk', 'Jam Pulang', 'Status', 'Jam Kerja', 'Catatan']);

            foreach ($attendances as $a) {
                fputcsv($handle, [
                    $a->kode_guide,
                    $a->guide_nama,
                    $a->date,
                    $a->check_in,
                    $a->check_out,
                    $a->status,
                    $a->working_hours,
                    $a->notes,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    private function exportQueueCsv($histories, $startDate, $endDate): StreamedResponse
    {
        $filename = "laporan-rotasi-antrean-{$startDate}-to-{$endDate}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($histories) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Waktu', 'Posisi Sebelum', 'Posisi Sesudah', 'Guide Terpilih', 'Booking', 'Tamu', 'Tipe', 'Catatan']);

            foreach ($histories as $h) {
                fputcsv($handle, [
                    $h->created_at->format('Y-m-d H:i:s'),
                    '#' . $h->previous_position,
                    '#' . $h->next_position,
                    $h->selectedGuide?->nama ?? '-',
                    $h->booking?->booking_code ?? '-',
                    $h->booking?->guest_count ?? '-',
                    $h->type,
                    $h->notes,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
