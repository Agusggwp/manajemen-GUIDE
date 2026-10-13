@extends('layouts.app')

@section('title', 'Laporan Pendapatan & Laba')
@section('header', 'Laporan Pendapatan & Profitabilitas')

@section('content')
<div class="space-y-6">
    <!-- Filter & Export Controls -->
    <div class="clay-card p-6 space-y-4 no-print">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-[#29283D]">Laporan Pendapatan, Fee Guide, Gaji Staff & Laba Bersih</h2>
                <p class="text-xs text-[#77768C] mt-1">
                    Menampilkan data periode: <span class="font-bold text-[#29283D]">{{ $periodLabel }}</span> 
                    &bull; Kalkulasi: Laba Bersih = Pendapatan - Fee Guide - Ops Tour - Gaji Staff
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('reports.revenue', array_merge(request()->query(), ['export' => 'csv'])) }}" 
                   class="clay-btn-turquoise px-3.5 py-2 text-xs font-bold inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-[#29283D]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Excel / CSV</span>
                </a>
                <button type="button" onclick="window.print()" class="clay-btn-secondary px-3.5 py-2 text-xs font-bold">
                    Cetak (Print)
                </button>
                <a href="{{ route('staff-payroll.index') }}" class="clay-btn-primary px-3.5 py-2 text-xs font-bold inline-flex items-center gap-1">
                    <span>Kelola Gaji Staff &rarr;</span>
                </a>
            </div>
        </div>

        <!-- Filter Per Bulan Bar (Primary) -->
        <div class="pt-3 border-t border-[#E8E6F0] flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-[#77768C] uppercase tracking-wider">Filter Periode:</span>

                <!-- Form Filter Per Bulan -->
                <form action="{{ route('reports.revenue') }}" method="GET" class="flex flex-wrap items-center gap-2 text-xs">
                    <input type="hidden" name="filter_type" value="month">
                    <div class="flex items-center gap-1.5 bg-[#F4F3FA] border border-[#E8E6F0] rounded-xl px-3 py-1.5 shadow-inner">
                        <span class="text-xs text-[#77768C] flex items-center gap-1"><svg class="w-3.5 h-3.5 text-[#77768C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg><span>Bulan:</span></span>
                        <select name="month" class="bg-transparent text-xs font-bold text-[#29283D] focus:outline-none cursor-pointer py-0.5">
                            @foreach(($months ?? []) as $num => $name)
                                <option value="{{ $num }}" {{ ($selectedMonth ?? date('n')) == $num ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        <select name="year" class="bg-transparent text-xs font-bold text-[#29283D] focus:outline-none cursor-pointer py-0.5 border-l border-[#E8E6F0] pl-2">
                            @for($y = \Carbon\Carbon::today()->year - 2; $y <= \Carbon\Carbon::today()->year + 1; $y++)
                                <option value="{{ $y }}" {{ ($selectedYear ?? date('Y')) == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit" class="clay-btn-primary px-3.5 py-1.5 text-xs font-bold">
                        Terapkan Bulan
                    </button>
                </form>

                <!-- Quick Bulan Ini / Bulan Lalu buttons -->
                <a href="{{ route('reports.revenue', ['filter_type' => 'month', 'month' => \Carbon\Carbon::today()->month, 'year' => \Carbon\Carbon::today()->year]) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ ($filterType ?? 'month') === 'month' && ($selectedMonth ?? 0) == \Carbon\Carbon::today()->month && ($selectedYear ?? 0) == \Carbon\Carbon::today()->year ? 'bg-[#6C63FF] text-white shadow-xs' : 'clay-btn-secondary' }}">
                    Bulan Ini
                </a>
                @php
                    $lastMonthCarbon = \Carbon\Carbon::today()->subMonth();
                @endphp
                <a href="{{ route('reports.revenue', ['filter_type' => 'month', 'month' => $lastMonthCarbon->month, 'year' => $lastMonthCarbon->year]) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ ($filterType ?? 'month') === 'month' && ($selectedMonth ?? 0) == $lastMonthCarbon->month && ($selectedYear ?? 0) == $lastMonthCarbon->year ? 'bg-[#6C63FF] text-white shadow-xs' : 'clay-btn-secondary' }}">
                    Bulan Lalu
                </a>
            </div>

            <!-- Custom Date Range Toggle / Form -->
            <div x-data="{ openCustom: {{ ($filterType ?? 'month') === 'custom' ? 'true' : 'false' }} }" class="text-xs">
                <button type="button" @click="openCustom = !openCustom" class="text-xs text-[#77768C] hover:text-[#6C63FF] font-bold inline-flex items-center gap-1 transition">
                    <span>Rentang Tanggal Custom (s/d)</span>
                    <svg class="w-3.5 h-3.5 transition-transform" :class="openCustom ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openCustom" x-cloak class="mt-2 flex items-center gap-1.5 bg-[#F4F3FA] border border-[#E8E6F0] p-2 rounded-2xl">
                    <form action="{{ route('reports.revenue') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        <input type="hidden" name="filter_type" value="custom">
                        <input type="date" name="start_date" value="{{ $startDate }}" class="clay-input text-xs py-1 px-2.5">
                        <span class="text-[#77768C] font-bold">s/d</span>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="clay-input text-xs py-1 px-2.5">
                        <button type="submit" class="clay-btn-secondary px-3 py-1 text-xs font-bold">
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
        <div class="clay-card p-5">
            <span class="text-xs text-[#77768C] font-bold block uppercase tracking-wider">Total Tamu (Pax)</span>
            <span class="text-2xl font-black text-[#29283D] block mt-1">{{ $totalGuests }} orang</span>
            <span class="text-[11px] text-[#77768C] mt-0.5 block font-medium">{{ $bookings->count() }} booking</span>
        </div>

        <!-- 2. Gross Revenue -->
        <div class="clay-card p-5 border-l-4 border-l-[#6C63FF]">
            <span class="text-xs text-[#6C63FF] font-bold block uppercase tracking-wider">Total Pendapatan</span>
            <span class="text-2xl font-black text-[#6C63FF] block mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
            <span class="text-[11px] text-[#77768C] mt-0.5 block font-medium">Pendapatan kotor tour</span>
        </div>

        <!-- 3. Fee Guide & Tour Ops -->
        <div class="clay-card p-5 border-l-4 border-l-[#FF8A65]">
            <span class="text-xs text-[#FF8A65] font-bold block uppercase tracking-wider">Fee Guide + Biaya Tour</span>
            <span class="text-2xl font-black text-[#FF8A65] block mt-1">Rp {{ number_format($totalGuideFee + $totalOperational, 0, ',', '.') }}</span>
            <span class="text-[11px] text-[#77768C] mt-0.5 block font-medium">Fee: {{ number_format($totalGuideFee, 0, ',', '.') }} &bull; Ops: {{ number_format($totalOperational, 0, ',', '.') }}</span>
        </div>

        <!-- 4. Gaji Staff -->
        <div class="clay-card p-5 border-l-4 border-l-purple-500 bg-purple-50/30">
            <div class="flex items-center justify-between">
                <span class="text-xs text-purple-900 font-bold block uppercase tracking-wider">Gaji Staff (Payroll)</span>
                <span class="text-[10px] px-2 py-0.5 bg-purple-100 text-purple-800 rounded-full font-bold">Staf</span>
            </div>
            <span class="text-2xl font-black text-purple-700 block mt-1">Rp {{ number_format($totalStaffSalary, 0, ',', '.') }}</span>
            <span class="text-[11px] text-purple-600/90 mt-0.5 block font-medium">
                {{ $staffPayrolls->where('payment_status', 'PAID')->count() }} slip lunas
                @if($totalStaffSalaryPending > 0)
                    &bull; Rp {{ number_format($totalStaffSalaryPending, 0, ',', '.') }} pending
                @endif
            </span>
        </div>

        <!-- 5. Net Profit -->
        <div class="clay-card p-5 border-l-4 border-l-emerald-500 bg-emerald-50/30 col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-xs text-emerald-900 font-bold block uppercase tracking-wider">Laba Bersih (Net Profit)</span>
                <span class="text-[10px] px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full font-bold">Laba</span>
            </div>
            <span class="text-2xl font-black text-emerald-600 block mt-1">Rp {{ number_format($totalProfit, 0, ',', '.') }}</span>
            <span class="text-[11px] text-emerald-700/90 mt-0.5 block font-medium">Revenue - Fee - Ops - Gaji Staff</span>
        </div>
    </div>

    <!-- Section 1: Bookings Report Table -->
    <div class="clay-card p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-[#E8E6F0] flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-[#29283D]">Rincian Booking Tour</h3>
                <p class="text-xs text-[#77768C]">Daftar transaksi booking dan laba kotor per perjalanan</p>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-[#F4F3FA] text-[#29283D]">
                {{ $bookings->count() }} Data
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F4F3FA] text-[#77768C] font-bold border-b border-[#E8E6F0]">
                    <tr>
                        <th class="px-5 py-3.5">Kode Booking</th>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Pelanggan</th>
                        <th class="px-5 py-3.5">Paket Tour</th>
                        <th class="px-5 py-3.5 text-center">Tamu</th>
                        <th class="px-5 py-3.5 text-right">Revenue</th>
                        <th class="px-5 py-3.5 text-right">Fee Guide</th>
                        <th class="px-5 py-3.5 text-right">Biaya Operasional</th>
                        <th class="px-5 py-3.5 text-right">Laba Tour</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($bookings as $b)
                    <tr class="hover:bg-purple-50/30 transition-colors">
                        <td class="px-5 py-3.5 font-bold text-[#29283D] font-mono">{{ $b->booking_code }}</td>
                        <td class="px-5 py-3.5 text-[#29283D]">{{ $b->booking_date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3.5 font-bold text-[#29283D]">{{ $b->customer_name }}</td>
                        <td class="px-5 py-3.5 text-[#77768C]">{{ $b->tourPackage?->name ?? 'Custom Tour' }}</td>
                        <td class="px-5 py-3.5 text-center font-bold text-[#29283D]">{{ $b->guest_count }}</td>
                        <td class="px-5 py-3.5 text-right font-bold text-[#29283D]">Rp {{ number_format($b->total_price, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-right font-bold text-[#FF8A65]">Rp {{ number_format($b->guide_fee_total, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-right font-bold text-rose-600">Rp {{ number_format($b->operational_cost, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-right font-black text-[#29283D]">Rp {{ number_format($b->company_profit, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-[#77768C]">Tidak ada data booking pada rentang tanggal ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-[#F4F3FA] font-bold border-t border-[#E8E6F0]">
                    <tr>
                        <td colspan="4" class="px-5 py-3.5 text-right text-[#29283D]">SUBTOTAL TOUR:</td>
                        <td class="px-5 py-3.5 text-center text-[#29283D]">{{ $totalGuests }}</td>
                        <td class="px-5 py-3.5 text-right text-[#6C63FF] font-black">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-right text-[#FF8A65] font-black">Rp {{ number_format($totalGuideFee, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-right text-rose-600 font-black">Rp {{ number_format($totalOperational, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-right text-[#29283D] font-black">Rp {{ number_format($totalRevenue - $totalGuideFee - $totalOperational, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Section 2: Staff Payroll Table -->
    <div class="clay-card p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-[#E8E6F0] flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-purple-100 text-[#6C63FF] flex items-center justify-center font-bold shadow-inner">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-[#29283D]">Beban Gaji Staff (Staff Payroll) Periode Ini</h3>
                    <p class="text-xs text-[#77768C]">Rincian slip gaji staff yang menjadi komponen pengurang laba bersih perusahaan</p>
                </div>
            </div>
            <a href="{{ route('staff-payroll.create') }}" class="clay-btn-secondary px-3.5 py-1.5 text-xs font-bold inline-flex items-center gap-1">
                + Buat Slip Gaji
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F4F3FA] text-[#77768C] font-bold border-b border-[#E8E6F0]">
                    <tr>
                        <th class="px-5 py-3.5">Kode Slip</th>
                        <th class="px-5 py-3.5">Nama Staff</th>
                        <th class="px-5 py-3.5">Jabatan</th>
                        <th class="px-5 py-3.5">Periode</th>
                        <th class="px-5 py-3.5 text-right">Gaji Pokok</th>
                        <th class="px-5 py-3.5 text-right">Tunjangan & Bonus</th>
                        <th class="px-5 py-3.5 text-right">Potongan</th>
                        <th class="px-5 py-3.5 text-right">Gaji Bersih (Net)</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($staffPayrolls as $sp)
                    <tr class="hover:bg-purple-50/30 transition-colors">
                        <td class="px-5 py-3.5 font-bold text-[#6C63FF] font-mono">{{ $sp->payroll_code }}</td>
                        <td class="px-5 py-3.5 font-bold text-[#29283D]">{{ $sp->user?->name ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-[#77768C]">{{ $sp->position ?? 'Staff' }}</td>
                        <td class="px-5 py-3.5 text-[#29283D]">{{ $sp->period_name }}</td>
                        <td class="px-5 py-3.5 text-right text-[#29283D] font-medium">Rp {{ number_format($sp->base_salary, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-right text-[#6C63FF] font-bold">+Rp {{ number_format($sp->allowance + $sp->bonus, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-right text-rose-600 font-bold">-Rp {{ number_format($sp->deduction, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-right font-black text-purple-700">Rp {{ number_format($sp->net_salary, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-center">
                            @if($sp->payment_status === 'PAID')
                                <span class="clay-badge bg-emerald-50 text-emerald-700 border-emerald-200">
                                    LUNAS ({{ $sp->payment_date ? $sp->payment_date->format('d/m/Y') : '-' }})
                                </span>
                            @else
                                <span class="clay-badge bg-amber-50 text-amber-700 border-amber-200">
                                    MENUNGGU
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('staff-payroll.show', $sp) }}" class="font-bold text-[#6C63FF] hover:underline transition-colors">
                                Rincian &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-5 py-12 text-center text-[#77768C]">
                            Belum ada data slip gaji staff pada periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-purple-50/40 font-bold border-t border-purple-100">
                    <tr>
                        <td colspan="7" class="px-5 py-3.5 text-right text-purple-950 font-bold">TOTAL BEBAN GAJI STAFF:</td>
                        <td class="px-5 py-3.5 text-right text-purple-800 font-black">Rp {{ number_format($totalStaffSalary, 0, ',', '.') }}</td>
                        <td colspan="2" class="px-5 py-3.5 text-left text-[11px] text-purple-700">
                            ({{ $staffPayrolls->where('payment_status', 'PAID')->count() }} Lunas, {{ $staffPayrolls->where('payment_status', 'PENDING')->count() }} Pending)
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Section 3: Grand Financial Reconciliation Card -->
    <div class="clay-card bg-gradient-to-br from-[#29283D] to-[#1F1E30] p-6 text-white border-0 shadow-lg">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-1">
                <div class="text-xs uppercase tracking-wider text-[#FFD166] font-extrabold">Rekapitulasi Keuangan & Laba Bersih Perusahaan</div>
                <h3 class="text-lg font-bold text-white">Ringkasan Margin Bersih Periode {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</h3>
                <p class="text-xs text-white/70">Seluruh komponen pendapatan telah dikurangi total fee guide, biaya operasional tour, dan beban gaji staff.</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div class="bg-white/10 rounded-2xl p-3.5 border border-white/10 backdrop-blur-xs">
                    <span class="text-white/70 block text-[11px] font-semibold">Gross Revenue</span>
                    <span class="font-black text-[#72D6C9] text-sm block mt-1">+Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                </div>
                <div class="bg-white/10 rounded-2xl p-3.5 border border-white/10 backdrop-blur-xs">
                    <span class="text-white/70 block text-[11px] font-semibold">Fee Guide</span>
                    <span class="font-black text-[#FFD166] text-sm block mt-1">-Rp {{ number_format($totalGuideFee, 0, ',', '.') }}</span>
                </div>
                <div class="bg-white/10 rounded-2xl p-3.5 border border-white/10 backdrop-blur-xs">
                    <span class="text-white/70 block text-[11px] font-semibold">Biaya Ops Tour</span>
                    <span class="font-black text-[#FF8A65] text-sm block mt-1">-Rp {{ number_format($totalOperational, 0, ',', '.') }}</span>
                </div>
                <div class="bg-white/10 rounded-2xl p-3.5 border border-white/10 backdrop-blur-xs">
                    <span class="text-white/70 block text-[11px] font-semibold">Gaji Staff</span>
                    <span class="font-black text-purple-200 text-sm block mt-1">-Rp {{ number_format($totalStaffSalary, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="mt-5 pt-4 border-t border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="text-xs text-white/70">
                Formula: <span class="font-mono text-white/90">Revenue ({{ number_format($totalRevenue, 0, ',', '.') }}) - Fee Guide ({{ number_format($totalGuideFee, 0, ',', '.') }}) - Ops Tour ({{ number_format($totalOperational, 0, ',', '.') }}) - Gaji Staff ({{ number_format($totalStaffSalary, 0, ',', '.') }})</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-white/80 uppercase tracking-wider font-bold">Total Laba Bersih:</span>
                <span class="text-2xl font-black text-[#FFD166]">
                    Rp {{ number_format($totalProfit, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
