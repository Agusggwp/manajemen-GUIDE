@extends('layouts.app')

@section('title', 'Laporan Pendapatan & Laba')
@section('header', 'Laporan Pendapatan & Profitabilitas')

@section('content')
<div class="space-y-6">
    <!-- Filter & Export Controls -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs space-y-4 no-print">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Laporan Pendapatan, Fee Guide, Gaji Staff & Laba Bersih</h2>
                <p class="text-xs text-slate-500">
                    Menampilkan data periode: <span class="font-bold text-slate-800">{{ $periodLabel }}</span> 
                    &bull; Kalkulasi: Laba Bersih = Pendapatan - Fee Guide - Ops Tour - Gaji Staff
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('reports.revenue', array_merge(request()->query(), ['export' => 'csv'])) }}" 
                   class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 transition shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Excel / CSV</span>
                </a>
                <button type="button" onclick="window.print()" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                    Cetak (Print)
                </button>
                <a href="{{ route('staff-payroll.index') }}" class="px-3.5 py-1.5 bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-semibold rounded-lg transition shadow-xs">
                    Kelola Gaji Staff &rarr;
                </a>
            </div>
        </div>

        <!-- Filter Per Bulan Bar (Primary) -->
        <div class="pt-3 border-t border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Filter Periode:</span>

                <!-- Form Filter Per Bulan -->
                <form action="{{ route('reports.revenue') }}" method="GET" class="flex flex-wrap items-center gap-2 text-xs">
                    <input type="hidden" name="filter_type" value="month">
                    <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1">
                        <span class="text-xs text-slate-500">🗓️ Bulan:</span>
                        <select name="month" class="bg-transparent text-xs font-medium text-slate-800 focus:outline-none cursor-pointer py-0.5">
                            @foreach(($months ?? []) as $num => $name)
                                <option value="{{ $num }}" {{ ($selectedMonth ?? date('n')) == $num ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        <select name="year" class="bg-transparent text-xs font-medium text-slate-800 focus:outline-none cursor-pointer py-0.5 border-l border-slate-200 pl-1.5">
                            @for($y = \Carbon\Carbon::today()->year - 2; $y <= \Carbon\Carbon::today()->year + 1; $y++)
                                <option value="{{ $y }}" {{ ($selectedYear ?? date('Y')) == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit" class="px-3 py-1.5 bg-[#14433b] hover:bg-[#1b584d] text-white rounded-lg text-xs font-semibold transition shadow-xs">
                        Terapkan Bulan
                    </button>
                </form>

                <!-- Quick Bulan Ini / Bulan Lalu buttons -->
                <a href="{{ route('reports.revenue', ['filter_type' => 'month', 'month' => \Carbon\Carbon::today()->month, 'year' => \Carbon\Carbon::today()->year]) }}"
                   class="px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ ($filterType ?? 'month') === 'month' && ($selectedMonth ?? 0) == \Carbon\Carbon::today()->month && ($selectedYear ?? 0) == \Carbon\Carbon::today()->year ? 'bg-slate-800 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                    Bulan Ini
                </a>
                @php
                    $lastMonthCarbon = \Carbon\Carbon::today()->subMonth();
                @endphp
                <a href="{{ route('reports.revenue', ['filter_type' => 'month', 'month' => $lastMonthCarbon->month, 'year' => $lastMonthCarbon->year]) }}"
                   class="px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ ($filterType ?? 'month') === 'month' && ($selectedMonth ?? 0) == $lastMonthCarbon->month && ($selectedYear ?? 0) == $lastMonthCarbon->year ? 'bg-slate-800 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                    Bulan Lalu
                </a>
            </div>

            <!-- Custom Date Range Toggle / Form -->
            <div x-data="{ openCustom: {{ ($filterType ?? 'month') === 'custom' ? 'true' : 'false' }} }" class="text-xs">
                <button type="button" @click="openCustom = !openCustom" class="text-xs text-slate-500 hover:text-slate-800 font-medium inline-flex items-center gap-1">
                    <span>Rentang Tanggal Custom (s/d)</span>
                    <svg class="w-3.5 h-3.5 transition-transform" :class="openCustom ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openCustom" x-cloak class="mt-2 flex items-center gap-1.5 bg-slate-50 border border-slate-200 p-1.5 rounded-lg">
                    <form action="{{ route('reports.revenue') }}" method="GET" class="flex flex-wrap items-center gap-1.5">
                        <input type="hidden" name="filter_type" value="custom">
                        <input type="date" name="start_date" value="{{ $startDate }}" class="px-2 py-1 bg-white border border-slate-300 rounded text-xs">
                        <span class="text-slate-400">s/d</span>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="px-2 py-1 bg-white border border-slate-300 rounded text-xs">
                        <button type="submit" class="px-2.5 py-1 bg-slate-700 hover:bg-slate-800 text-white rounded text-xs font-semibold">
                            Terapkan Range
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Metrics Cards (5 Cards) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <!-- 1. Total Tamu -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-xs text-slate-500 font-medium block">Total Tamu (Pax)</span>
            <span class="text-xl font-bold text-slate-900 block mt-1">{{ $totalGuests }} orang</span>
            <span class="text-[10px] text-slate-400 mt-0.5 block">{{ $bookings->count() }} booking</span>
        </div>

        <!-- 2. Gross Revenue -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-xs text-slate-500 font-medium block">Total Pendapatan</span>
            <span class="text-xl font-bold text-[#14433b] block mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
            <span class="text-[10px] text-slate-400 mt-0.5 block">Pendapatan kotor tour</span>
        </div>

        <!-- 3. Fee Guide & Tour Ops -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-xs text-slate-500 font-medium block">Fee Guide + Biaya Tour</span>
            <span class="text-xl font-bold text-amber-700 block mt-1">Rp {{ number_format($totalGuideFee + $totalOperational, 0, ',', '.') }}</span>
            <span class="text-[10px] text-slate-400 mt-0.5 block">Fee: {{ number_format($totalGuideFee, 0, ',', '.') }} &bull; Ops: {{ number_format($totalOperational, 0, ',', '.') }}</span>
        </div>

        <!-- 4. Gaji Staff -->
        <div class="bg-white p-4 rounded-xl border border-purple-200 bg-purple-50/20 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs text-purple-900 font-semibold block">Gaji Staff (Payroll)</span>
                <span class="text-[10px] px-1.5 py-0.5 bg-purple-100 text-purple-800 rounded font-bold">Staf</span>
            </div>
            <span class="text-xl font-bold text-purple-700 block mt-1">Rp {{ number_format($totalStaffSalary, 0, ',', '.') }}</span>
            <span class="text-[10px] text-purple-600/80 mt-0.5 block">
                {{ $staffPayrolls->where('payment_status', 'PAID')->count() }} slip lunas
                @if($totalStaffSalaryPending > 0)
                    &bull; Rp {{ number_format($totalStaffSalaryPending, 0, ',', '.') }} pending
                @endif
            </span>
        </div>

        <!-- 5. Net Profit -->
        <div class="bg-white p-4 rounded-xl border border-emerald-200 bg-emerald-50/20 shadow-2xs col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-xs text-emerald-900 font-bold block">Laba Bersih (Net Profit)</span>
                <span class="text-[10px] px-1.5 py-0.5 bg-emerald-100 text-emerald-800 rounded font-bold">Laba</span>
            </div>
            <span class="text-xl font-extrabold text-emerald-700 block mt-1">Rp {{ number_format($totalProfit, 0, ',', '.') }}</span>
            <span class="text-[10px] text-emerald-700/80 mt-0.5 block">Revenue - Fee - Ops - Gaji Staff</span>
        </div>
    </div>

    <!-- Section 1: Bookings Report Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Rincian Booking Tour</h3>
                <p class="text-xs text-slate-400">Daftar transaksi booking dan laba kotor per perjalanan</p>
            </div>
            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                {{ $bookings->count() }} Data
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Kode Booking</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Paket Tour</th>
                        <th class="px-4 py-3 text-center">Tamu</th>
                        <th class="px-4 py-3 text-right">Revenue</th>
                        <th class="px-4 py-3 text-right">Fee Guide</th>
                        <th class="px-4 py-3 text-right">Biaya Operasional</th>
                        <th class="px-4 py-3 text-right">Laba Tour</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bookings as $b)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-4 py-3 font-bold text-slate-900 font-mono">{{ $b->booking_code }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $b->booking_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $b->customer_name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $b->tourPackage?->name ?? 'Custom Tour' }}</td>
                        <td class="px-4 py-3 text-center font-bold text-slate-800">{{ $b->guest_count }}</td>
                        <td class="px-4 py-3 text-right font-bold text-slate-900">Rp {{ number_format($b->total_price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-medium text-amber-700">Rp {{ number_format($b->guide_fee_total, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-medium text-rose-700">Rp {{ number_format($b->operational_cost, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-bold text-slate-800">Rp {{ number_format($b->company_profit, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-slate-400">Tidak ada data booking pada rentang tanggal ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-right">SUBTOTAL TOUR:</td>
                        <td class="px-4 py-3 text-center">{{ $totalGuests }}</td>
                        <td class="px-4 py-3 text-right text-[#14433b]">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-amber-700">Rp {{ number_format($totalGuideFee, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-rose-700">Rp {{ number_format($totalOperational, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-slate-900">Rp {{ number_format($totalRevenue - $totalGuideFee - $totalOperational, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Section 2: Staff Payroll Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-purple-100 text-purple-700">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Beban Gaji Staff (Staff Payroll) Periode Ini</h3>
                    <p class="text-xs text-slate-400">Rincian slip gaji staff yang menjadi komponen pengurang laba bersih perusahaan</p>
                </div>
            </div>
            <a href="{{ route('staff-payroll.create') }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100 transition">
                + Buat Slip Gaji
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Kode Slip</th>
                        <th class="px-4 py-3">Nama Staff</th>
                        <th class="px-4 py-3">Jabatan</th>
                        <th class="px-4 py-3">Periode</th>
                        <th class="px-4 py-3 text-right">Gaji Pokok</th>
                        <th class="px-4 py-3 text-right">Tunjangan & Bonus</th>
                        <th class="px-4 py-3 text-right">Potongan</th>
                        <th class="px-4 py-3 text-right">Gaji Bersih (Net)</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($staffPayrolls as $sp)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-4 py-3 font-bold text-purple-900 font-mono">{{ $sp->payroll_code }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $sp->user?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $sp->position ?? 'Staff' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $sp->period_name }}</td>
                        <td class="px-4 py-3 text-right text-slate-700">Rp {{ number_format($sp->base_salary, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-emerald-700">+Rp {{ number_format($sp->allowance + $sp->bonus, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-rose-700">-Rp {{ number_format($sp->deduction, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-extrabold text-purple-700">Rp {{ number_format($sp->net_salary, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($sp->payment_status === 'PAID')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    LUNAS ({{ $sp->payment_date ? $sp->payment_date->format('d/m/Y') : '-' }})
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                    MENUNGGU
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('staff-payroll.show', $sp) }}" class="font-medium text-[#14433b] hover:text-[#21c9a4] transition-colors">
                                Rincian &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-slate-400">
                            Belum ada data slip gaji staff pada periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-purple-50/50 font-bold border-t border-purple-100">
                    <tr>
                        <td colspan="7" class="px-4 py-3 text-right text-purple-950">TOTAL BEBAN GAJI STAFF:</td>
                        <td class="px-4 py-3 text-right text-purple-800 font-extrabold">Rp {{ number_format($totalStaffSalary, 0, ',', '.') }}</td>
                        <td colspan="2" class="px-4 py-3 text-left text-[11px] text-purple-700">
                            ({{ $staffPayrolls->where('payment_status', 'PAID')->count() }} Lunas, {{ $staffPayrolls->where('payment_status', 'PENDING')->count() }} Pending)
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Section 3: Grand Financial Reconciliation Card -->
    <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-xl p-6 text-white shadow-sm border border-slate-700">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-1">
                <div class="text-xs uppercase tracking-wider text-[#21c9a4] font-bold">Rekapitulasi Keuangan & Laba Bersih Perusahaan</div>
                <h3 class="text-lg font-bold text-white">Ringkasan Margin Bersih Periode {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</h3>
                <p class="text-xs text-slate-300">Seluruh komponen pendapatan telah dikurangi total fee guide, biaya operasional tour, dan beban gaji staff.</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div class="bg-white/10 rounded-lg p-3 border border-white/10">
                    <span class="text-slate-300 block text-[11px]">Gross Revenue</span>
                    <span class="font-bold text-emerald-400 text-sm block mt-0.5">+Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                </div>
                <div class="bg-white/10 rounded-lg p-3 border border-white/10">
                    <span class="text-slate-300 block text-[11px]">Fee Guide</span>
                    <span class="font-bold text-amber-400 text-sm block mt-0.5">-Rp {{ number_format($totalGuideFee, 0, ',', '.') }}</span>
                </div>
                <div class="bg-white/10 rounded-lg p-3 border border-white/10">
                    <span class="text-slate-300 block text-[11px]">Biaya Ops Tour</span>
                    <span class="font-bold text-rose-400 text-sm block mt-0.5">-Rp {{ number_format($totalOperational, 0, ',', '.') }}</span>
                </div>
                <div class="bg-white/10 rounded-lg p-3 border border-white/10">
                    <span class="text-slate-300 block text-[11px]">Gaji Staff</span>
                    <span class="font-bold text-purple-300 text-sm block mt-0.5">-Rp {{ number_format($totalStaffSalary, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="mt-5 pt-4 border-t border-slate-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="text-xs text-slate-300">
                Formula: <span class="font-mono text-slate-200">Revenue ({{ number_format($totalRevenue, 0, ',', '.') }}) - Fee Guide ({{ number_format($totalGuideFee, 0, ',', '.') }}) - Ops Tour ({{ number_format($totalOperational, 0, ',', '.') }}) - Gaji Staff ({{ number_format($totalStaffSalary, 0, ',', '.') }})</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-300 uppercase tracking-wider font-semibold">Total Laba Bersih:</span>
                <span class="text-2xl font-black text-[#21c9a4]">
                    Rp {{ number_format($totalProfit, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
