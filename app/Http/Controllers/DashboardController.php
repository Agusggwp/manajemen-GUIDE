<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Expense;
use App\Models\Guide;
use App\Models\GuideAssignment;
use App\Models\GuideAttendance;
use App\Models\QueueHistory;
use App\Services\GuideQueueService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index(Request $request, GuideQueueService $queueService)
    {
        $today = Carbon::today()->toDateString();
        $currentYear = (int) Carbon::today()->format('Y');
        $currentMonth = (int) Carbon::today()->format('n');

        $period = $request->query('period', 'today'); // 'today' or 'month'
        $selectedMonth = $request->filled('month') ? (int) $request->month : $currentMonth;
        $selectedYear = $request->filled('year') ? (int) $request->year : $currentYear;

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        if ($period === 'month') {
            $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth()->toDateString();
            $endDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth()->toDateString();
            $periodLabel = "Bulan {$months[$selectedMonth]} {$selectedYear}";
            $periodSubtitle = "1 - " . Carbon::parse($endDate)->format('d') . " " . $months[$selectedMonth] . " {$selectedYear}";
        } else {
            $period = 'today';
            $startDate = $today;
            $endDate = $today;
            $periodLabel = "Hari Ini (" . Carbon::parse($today)->format('d M Y') . ")";
            $periodSubtitle = "Hari ini (" . Carbon::parse($today)->format('d M') . ")";
        }

        $startBound = Carbon::parse($startDate)->startOfDay()->toDateTimeString();
        $endBound = Carbon::parse($endDate)->endOfDay()->toDateTimeString();

        // Bookings in the selected period
        $periodBookings = Booking::where(function ($q) use ($startDate, $endDate, $startBound, $endBound) {
            $q->whereBetween('booking_date', [$startDate, $endDate])
              ->orWhereBetween('booking_date', [$startBound, $endBound]);
        })->get();
        $totalBookings = $periodBookings->count();
        $totalGuests = $periodBookings->sum('guest_count');

        // Financials in the selected period
        $revenue = (float) $periodBookings->sum('total_price');
        $guideFee = (float) GuideAssignment::whereHas('booking', function ($q) use ($startDate, $endDate, $startBound, $endBound) {
            $q->where(function ($sub) use ($startDate, $endDate, $startBound, $endBound) {
                $sub->whereBetween('booking_date', [$startDate, $endDate])
                    ->orWhereBetween('booking_date', [$startBound, $endBound]);
            });
        })->where('status', '!=', 'CANCELLED')->sum('fee_amount');

        $operationalCost = (float) Expense::where(function ($q) use ($startDate, $endDate, $startBound, $endBound) {
            $q->whereBetween('expense_date', [$startDate, $endDate])
              ->orWhereBetween('expense_date', [$startBound, $endBound]);
        })->sum('amount');
        $estimatedProfit = $revenue - $guideFee - $operationalCost;

        // Today's real-time queue & operations status
        $todayAttendances = GuideAttendance::where('attendance_date', $today)->get();
        $checkedInCount = $todayAttendances->where('status', 'CHECKED_IN')->count();
        $checkedOutCount = $todayAttendances->where('status', 'CHECKED_OUT')->count();

        $allGuides = Guide::where('status', '!=', 'INACTIVE')->get();
        $checkedInGuideIds = $todayAttendances->where('status', 'CHECKED_IN')->pluck('guide_id')->flip();
        $guidesAvailable = $allGuides->where('status', 'AVAILABLE')->filter(function ($g) use ($checkedInGuideIds) {
            return isset($checkedInGuideIds[$g->id]);
        })->count();
        $guidesOnTour = $allGuides->where('status', 'ON_TOUR')->count();

        $waitingAssignmentCount = Booking::where('booking_date', $today)
            ->whereIn('status', ['WAITING', 'PARTIALLY_ASSIGNED'])
            ->count();

        // Next candidate guide according to circular queue
        $nextGuideResult = $queueService->findNextAvailableGuide(null, $today);
        $nextGuide = $nextGuideResult['guide'];

        // Recent bookings (for table)
        $recentBookings = Booking::with(['tourPackage', 'assignments.guide'])
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->take(8)
            ->get();

        // If no bookings in period, fallback to overall recent bookings
        if ($recentBookings->isEmpty()) {
            $recentBookings = Booking::with(['tourPackage', 'assignments.guide'])
                ->orderBy('created_at', 'desc')
                ->take(8)
                ->get();
        }

        // Recent Queue Activity
        $recentQueueHistory = QueueHistory::with(['selectedGuide', 'booking', 'assigner'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'today',
            'period',
            'selectedMonth',
            'selectedYear',
            'months',
            'periodLabel',
            'periodSubtitle',
            'startDate',
            'endDate',
            'totalBookings',
            'totalGuests',
            'revenue',
            'guideFee',
            'operationalCost',
            'estimatedProfit',
            'checkedInCount',
            'checkedOutCount',
            'guidesAvailable',
            'guidesOnTour',
            'waitingAssignmentCount',
            'nextGuide',
            'nextGuideResult',
            'recentBookings',
            'recentQueueHistory'
        ));
    }

    /**
     * Export dashboard operational and financial report to CSV.
     */
    public function exportCsv(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $currentYear = (int) Carbon::today()->format('Y');
        $currentMonth = (int) Carbon::today()->format('n');

        $period = $request->query('period', 'today');
        $selectedMonth = $request->filled('month') ? (int) $request->month : $currentMonth;
        $selectedYear = $request->filled('year') ? (int) $request->year : $currentYear;

        \App\Models\AuditLog::log('EXPORT_DASHBOARD', 'Dashboard', null, null, [
            'period' => $period,
            'month' => $selectedMonth,
            'year' => $selectedYear,
        ]);

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        if ($period === 'month') {
            $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth()->toDateString();
            $endDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->endOfMonth()->toDateString();
            $periodLabel = "Bulan {$months[$selectedMonth]} {$selectedYear}";
            $filename = "Laporan-Dashboard-{$months[$selectedMonth]}-{$selectedYear}.csv";
        } else {
            $startDate = $today;
            $endDate = $today;
            $periodLabel = "Hari Ini ({$today})";
            $filename = "Laporan-Dashboard-{$today}.csv";
        }

        $startBound = Carbon::parse($startDate)->startOfDay()->toDateTimeString();
        $endBound = Carbon::parse($endDate)->endOfDay()->toDateTimeString();

        // Data gathering
        $bookings = Booking::with(['tourPackage', 'assignments.guide'])
            ->where(function ($q) use ($startDate, $endDate, $startBound, $endBound) {
                $q->whereBetween('booking_date', [$startDate, $endDate])
                  ->orWhereBetween('booking_date', [$startBound, $endBound]);
            })
            ->orderBy('booking_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        $totalBookings = $bookings->count();
        $totalGuests = $bookings->sum('guest_count');
        $revenue = (float) $bookings->sum('total_price');

        $guideFee = (float) GuideAssignment::whereHas('booking', function ($q) use ($startDate, $endDate, $startBound, $endBound) {
            $q->where(function ($sub) use ($startDate, $endDate, $startBound, $endBound) {
                $sub->whereBetween('booking_date', [$startDate, $endDate])
                    ->orWhereBetween('booking_date', [$startBound, $endBound]);
            });
        })->where('status', '!=', 'CANCELLED')->sum('fee_amount');

        $expenses = Expense::where(function ($q) use ($startDate, $endDate, $startBound, $endBound) {
            $q->whereBetween('expense_date', [$startDate, $endDate])
              ->orWhereBetween('expense_date', [$startBound, $endBound]);
        })
        ->orderBy('expense_date', 'asc')
        ->get();
        $operationalCost = (float) $expenses->sum('amount');
        $estimatedProfit = $revenue - $guideFee - $operationalCost;

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return new StreamedResponse(function () use (
            $periodLabel,
            $totalBookings,
            $totalGuests,
            $revenue,
            $guideFee,
            $operationalCost,
            $estimatedProfit,
            $bookings,
            $expenses
        ) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Section 1: Title & Period
            fputcsv($handle, ['LAPORAN OPERASIONAL & KEUANGAN DASHBOARD']);
            fputcsv($handle, ['Periode', $periodLabel]);
            fputcsv($handle, ['Waktu Export', Carbon::now()->format('d/m/Y H:i:s') . ' WITA']);
            fputcsv($handle, []);

            // Section 2: Executive Summary
            fputcsv($handle, ['RINGKASAN METRIK']);
            fputcsv($handle, ['Metrik', 'Nilai']);
            fputcsv($handle, ['Total Booking', $totalBookings]);
            fputcsv($handle, ['Total Tamu (Pax)', $totalGuests]);
            fputcsv($handle, ['Total Revenue / Pendapatan', number_format($revenue, 2, '.', '')]);
            fputcsv($handle, ['Total Hak Fee Guide', number_format($guideFee, 2, '.', '')]);
            fputcsv($handle, ['Total Biaya Operasional', number_format($operationalCost, 2, '.', '')]);
            fputcsv($handle, ['Estimasi Keuntungan Bersih (Laba)', number_format($estimatedProfit, 2, '.', '')]);
            fputcsv($handle, []);

            // Section 3: Rincian Booking
            fputcsv($handle, ['RINCIAN BOOKING PERIODE INI']);
            fputcsv($handle, [
                'No',
                'Kode Booking',
                'Tanggal Tour',
                'Waktu Mulai',
                'Nama Tamu',
                'Kontak Tamu',
                'Paket Tour',
                'Jumlah Tamu (Pax)',
                'Total Tagihan (Rp)',
                'Guide Ditugaskan',
                'Status Booking',
                'Jenis'
            ]);

            foreach ($bookings as $idx => $b) {
                $guides = $b->assignments->map(fn($a) => $a->guide ? "#{$a->guide->queue_number} {$a->guide->nama}" : '-')->join('; ');
                fputcsv($handle, [
                    $idx + 1,
                    $b->booking_code,
                    is_string($b->booking_date) ? $b->booking_date : $b->booking_date->format('Y-m-d'),
                    $b->start_time ?? '-',
                    $b->customer_name ?? '-',
                    $b->customer_phone ?? '-',
                    $b->tourPackage?->name ?? 'Custom',
                    $b->guest_count,
                    number_format($b->total_price, 2, '.', ''),
                    $guides ?: 'Belum Ada',
                    $b->status,
                    $b->is_walkin ? 'Walk-in' : 'Booking Terjadwal',
                ]);
            }
            fputcsv($handle, []);

            // Section 4: Rincian Pengeluaran Operasional
            fputcsv($handle, ['RINCIAN PENGELUARAN BIAYA OPERASIONAL']);
            fputcsv($handle, [
                'No',
                'Tanggal',
                'Kategori',
                'Keterangan',
                'Jumlah Biaya (Rp)',
                'Catatan'
            ]);

            foreach ($expenses as $idx => $exp) {
                fputcsv($handle, [
                    $idx + 1,
                    is_string($exp->expense_date) ? $exp->expense_date : $exp->expense_date->format('Y-m-d'),
                    $exp->category,
                    $exp->description,
                    number_format($exp->amount, 2, '.', ''),
                    $exp->notes ?? '-',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
