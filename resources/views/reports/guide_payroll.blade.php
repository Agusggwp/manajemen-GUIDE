@extends('layouts.app')

@section('title', 'Laporan Gaji & Fee Guide')
@section('header', 'Laporan Gaji & Fee Guide (Payroll Report)')

@section('content')
<div class="space-y-6">

    <!-- Top Report Navigation Bar (Tabs) -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-print border-b border-[#E8E6F0]">
        <a href="{{ route('reports.revenue') }}" 
           class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ request()->routeIs('reports.revenue') ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30' : 'bg-white hover:bg-[#F4F3FA] text-[#77768C] border border-[#E8E6F0]' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span>Laporan Omset & Laba</span>
        </a>

        <a href="{{ route('reports.guide-payroll') }}" 
           class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ request()->routeIs('reports.guide-payroll') ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30' : 'bg-white hover:bg-[#F4F3FA] text-[#77768C] border border-[#E8E6F0]' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>Laporan Gaji Guide</span>
            <span class="px-1.5 py-0.2 rounded-md text-[9px] font-black uppercase {{ request()->routeIs('reports.guide-payroll') ? 'bg-white/20 text-white' : 'bg-[#6C63FF]/15 text-[#6C63FF]' }}">Aktif</span>
        </a>

        <a href="{{ route('reports.guides') }}" 
           class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ request()->routeIs('reports.guides') ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30' : 'bg-white hover:bg-[#F4F3FA] text-[#77768C] border border-[#E8E6F0]' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>Kinerja Penugasan</span>
        </a>

        <a href="{{ route('reports.attendance') }}" 
           class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ request()->routeIs('reports.attendance') ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30' : 'bg-white hover:bg-[#F4F3FA] text-[#77768C] border border-[#E8E6F0]' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Absensi & Jam Kerja</span>
        </a>

        <a href="{{ route('reports.queue') }}" 
           class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ request()->routeIs('reports.queue') ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30' : 'bg-white hover:bg-[#F4F3FA] text-[#77768C] border border-[#E8E6F0]' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Riwayat Antrean</span>
        </a>
    </div>

    <!-- Filter & Action Controls -->
    <div class="clay-card p-6 space-y-4 no-print">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-[#29283D] font-display flex items-center gap-2">
                    <span class="w-7 h-7 rounded-xl bg-[#6C63FF]/15 text-[#6C63FF] flex items-center justify-center font-bold text-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </span>
                    <span>Rekapitulasi Gaji & Hak Fee Seluruh Guide</span>
                </h2>
                <p class="text-xs text-[#77768C] mt-1">
                    Periode laporan: <strong class="text-[#29283D]">{{ $periodLabel }}</strong> &bull; Total pemandu terdata: <strong>{{ $guidesData->count() }} orang</strong>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('reports.guide-payroll', array_merge(request()->query(), ['export' => 'csv'])) }}" 
                   class="clay-btn-turquoise px-3.5 py-2 text-xs font-bold inline-flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4 text-[#29283D]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Excel / CSV</span>
                </a>
                <button type="button" onclick="window.print()" class="clay-btn-secondary px-3.5 py-2 text-xs font-bold shadow-sm">
                    Cetak (Print)
                </button>
                <a href="{{ route('payroll.index') }}" class="clay-btn-primary px-3.5 py-2 text-xs font-bold inline-flex items-center gap-1 shadow-sm">
                    <span>Proses Bayar Gaji &rarr;</span>
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="pt-4 border-t border-[#E8E6F0] space-y-3">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold text-[#77768C] uppercase tracking-wider">Periode:</span>

                    <!-- Form Filter Per Bulan -->
                    <form action="{{ route('reports.guide-payroll') }}" method="GET" class="flex flex-wrap items-center gap-2 text-xs">
                        <input type="hidden" name="filter_type" value="month">
                        @if($selectedGuideId) <input type="hidden" name="guide_id" value="{{ $selectedGuideId }}"> @endif
                        @if($selectedStatus !== 'all') <input type="hidden" name="payment_status" value="{{ $selectedStatus }}"> @endif

                        <div class="flex items-center gap-1.5 bg-[#F4F3FA] border border-[#E8E6F0] rounded-xl px-3 py-1.5 shadow-2xs">
                            <span class="text-xs text-[#77768C] flex items-center gap-1 font-bold">
                                <svg class="w-3.5 h-3.5 text-[#77768C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Bulan:</span>
                            </span>
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
                        <button type="submit" class="clay-btn-primary px-3.5 py-1.5 text-xs font-bold shadow-sm">
                            Terapkan Bulan
                        </button>
                    </form>

                    <!-- Quick buttons -->
                    <a href="{{ route('reports.guide-payroll', ['filter_type' => 'month', 'month' => \Carbon\Carbon::today()->month, 'year' => \Carbon\Carbon::today()->year, 'guide_id' => $selectedGuideId, 'payment_status' => $selectedStatus]) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ ($filterType ?? 'month') === 'month' && ($selectedMonth ?? 0) == \Carbon\Carbon::today()->month && ($selectedYear ?? 0) == \Carbon\Carbon::today()->year ? 'bg-[#6C63FF] text-white shadow-xs' : 'clay-btn-secondary' }}">
                        Bulan Ini
                    </a>
                    @php $lastMonth = \Carbon\Carbon::today()->subMonth(); @endphp
                    <a href="{{ route('reports.guide-payroll', ['filter_type' => 'month', 'month' => $lastMonth->month, 'year' => $lastMonth->year, 'guide_id' => $selectedGuideId, 'payment_status' => $selectedStatus]) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ ($filterType ?? 'month') === 'month' && ($selectedMonth ?? 0) == $lastMonth->month && ($selectedYear ?? 0) == $lastMonth->year ? 'bg-[#6C63FF] text-white shadow-xs' : 'clay-btn-secondary' }}">
                        Bulan Lalu
                    </a>
                    <a href="{{ route('reports.guide-payroll', ['filter_type' => 'all', 'guide_id' => $selectedGuideId, 'payment_status' => $selectedStatus]) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ ($filterType ?? '') === 'all' ? 'bg-[#6C63FF] text-white shadow-xs' : 'clay-btn-secondary' }}">
                        Semua Waktu
                    </a>
                </div>

                <!-- Custom Range Dropdown / Form -->
                <div x-data="{ openCustom: {{ ($filterType ?? 'month') === 'custom' ? 'true' : 'false' }} }" class="text-xs">
                    <button type="button" @click="openCustom = !openCustom" class="text-xs text-[#77768C] hover:text-[#6C63FF] font-bold inline-flex items-center gap-1 transition">
                        <span>Rentang Tanggal Custom</span>
                        <svg class="w-3.5 h-3.5 transition-transform" :class="openCustom ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openCustom" x-cloak class="mt-2 flex items-center gap-1.5 bg-[#F4F3FA] border border-[#E8E6F0] p-2 rounded-2xl">
                        <form action="{{ route('reports.guide-payroll') }}" method="GET" class="flex flex-wrap items-center gap-2">
                            <input type="hidden" name="filter_type" value="custom">
                            @if($selectedGuideId) <input type="hidden" name="guide_id" value="{{ $selectedGuideId }}"> @endif
                            @if($selectedStatus !== 'all') <input type="hidden" name="payment_status" value="{{ $selectedStatus }}"> @endif
                            <input type="date" name="start_date" value="{{ $startDate }}" class="clay-input text-xs py-1 px-2.5">
                            <span class="text-[#77768C] font-bold">s/d</span>
                            <input type="date" name="end_date" value="{{ $endDate }}" class="clay-input text-xs py-1 px-2.5">
                            <button type="submit" class="clay-btn-secondary px-3 py-1 text-xs font-bold shadow-xs">
                                Terapkan Range
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Filter Guide & Status Pembayaran -->
            <div class="pt-3 border-t border-[#E8E6F0]/80 flex flex-wrap items-center gap-3 text-xs">
                <form action="{{ route('reports.guide-payroll') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <input type="hidden" name="filter_type" value="{{ $filterType }}">
                    @if($filterType === 'month')
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                        <input type="hidden" name="year" value="{{ $selectedYear }}">
                    @elseif($filterType === 'custom')
                        <input type="hidden" name="start_date" value="{{ $startDate }}">
                        <input type="hidden" name="end_date" value="{{ $endDate }}">
                    @endif

                    <div class="flex items-center gap-2">
                        <label class="font-bold text-[#77768C]">Pilih Guide:</label>
                        <select name="guide_id" onchange="this.form.submit()" class="clay-input text-xs py-1 px-2.5 font-bold text-[#29283D] cursor-pointer">
                            <option value="">-- Semua Guide ({{ $allGuidesList->count() }} Orang) --</option>
                            @foreach($allGuidesList as $g)
                                <option value="{{ $g->id }}" {{ $selectedGuideId == $g->id ? 'selected' : '' }}>
                                    #{{ $g->queue_number }} &bull; {{ $g->nama }} ({{ $g->kode_guide }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="font-bold text-[#77768C]">Status Pembayaran:</label>
                        <select name="payment_status" onchange="this.form.submit()" class="clay-input text-xs py-1 px-2.5 font-bold text-[#29283D] cursor-pointer">
                            <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="PAID" {{ $selectedStatus === 'PAID' ? 'selected' : '' }}>LUNAS (Paid)</option>
                            <option value="PARTIAL" {{ $selectedStatus === 'PARTIAL' ? 'selected' : '' }}>SEBAGIAN (Partial)</option>
                            <option value="UNPAID" {{ $selectedStatus === 'UNPAID' ? 'selected' : '' }}>BELUM DIBAYAR (Unpaid)</option>
                        </select>
                    </div>

                    @if($selectedGuideId || $selectedStatus !== 'all')
                        <a href="{{ route('reports.guide-payroll', ['filter_type' => $filterType, 'month' => $selectedMonth, 'year' => $selectedYear, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                           class="text-xs text-rose-600 hover:underline font-bold">
                            &times; Reset Filter Tambahan
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- 4 KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Hak Fee Guide -->
        <div class="clay-card p-5 border-l-4 border-l-[#6C63FF]">
            <span class="text-xs font-bold text-[#6C63FF] block uppercase tracking-wider">Total Hak Fee / Gaji</span>
            <span class="text-2xl font-black text-[#29283D] block mt-1 font-display">
                Rp {{ number_format($totalFeeAll, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-[#77768C] mt-0.5 block font-medium">
                Hak pendapatan seluruh guide
            </span>
        </div>

        <!-- 2. Total Terbayar (Paid) -->
        <div class="clay-card p-5 border-l-4 border-l-[#72D6C9]">
            <span class="text-xs font-bold text-[#0f766e] block uppercase tracking-wider">Gaji Terbayar (Paid)</span>
            <span class="text-2xl font-black text-[#0f766e] block mt-1 font-display">
                Rp {{ number_format($totalPaidAll, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-[#77768C] mt-0.5 block font-medium">
                Telah ditransfer ke rekening guide
            </span>
        </div>

        <!-- 3. Sisa Belum Dibayar (Remaining) -->
        <div class="clay-card p-5 border-l-4 border-l-[#FF8A65]">
            <span class="text-xs font-bold text-[#b45309] block uppercase tracking-wider">Sisa Belum Dibayar</span>
            <span class="text-2xl font-black text-[#c0392b] block mt-1 font-display">
                Rp {{ number_format($totalRemainingAll, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-[#77768C] mt-0.5 block font-medium">
                Kewajiban payroll tertunda
            </span>
        </div>

        <!-- 4. Total Penugasan & Tamu -->
        <div class="clay-card p-5 border-l-4 border-l-[#FFD166]">
            <span class="text-xs font-bold text-[#8a6800] block uppercase tracking-wider">Tour & Tamu Dipandu</span>
            <span class="text-2xl font-black text-[#29283D] block mt-1 font-display">
                {{ $totalBookingsAll }} <span class="text-xs font-bold text-[#77768C]">Tour</span> / {{ $totalGuestsAll }} <span class="text-xs font-bold text-[#77768C]">Pax</span>
            </span>
            <span class="text-[11px] text-[#77768C] mt-0.5 block font-medium">
                Volume pemanduan periode ini
            </span>
        </div>
    </div>

    <!-- Main Table: Rekapitulasi Gaji Guide -->
    <div class="clay-table-wrapper" x-data="{ expandedGuide: null }">
        <div class="p-5 md:p-6 border-b border-[#E8E6F0] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-extrabold text-[#29283D] flex items-center gap-2 font-display">
                    <span>Tabel Rincian Gaji & Pembayaran per Pemandu</span>
                </h3>
                <p class="text-xs text-[#77768C] mt-0.5">
                    Klik tombol <strong>Rincian</strong> untuk melihat riwayat booking tour dan bukti pembayaran masing-masing guide
                </p>
            </div>
            <div class="text-xs font-bold text-[#77768C]">
                Menampilkan: <span class="text-[#6C63FF] font-black">{{ $guidesData->count() }} Guide</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F4F3FA] text-[#77768C] font-bold border-b border-[#E8E6F0]">
                    <tr>
                        <th class="px-4 py-3.5 text-center">No Antrean</th>
                        <th class="px-4 py-3.5">Pemandu Wisata (Guide)</th>
                        <th class="px-4 py-3.5 text-center">Total Tour</th>
                        <th class="px-4 py-3.5 text-center">Total Tamu</th>
                        <th class="px-4 py-3.5 text-right">Total Hak Fee</th>
                        <th class="px-4 py-3.5 text-right">Telah Dibayar</th>
                        <th class="px-4 py-3.5 text-right">Sisa Pembayaran</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center no-print">Rincian & Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($guidesData as $row)
                    <tr class="hover:bg-[#F4F3FA]/70 transition-colors cursor-pointer"
                        :class="expandedGuide === {{ $row->guide->id }} ? 'bg-[#ECEBFC]/50' : ''">
                        
                        <!-- Queue Number -->
                        <td class="px-4 py-3.5 text-center font-extrabold" @click="expandedGuide = (expandedGuide === {{ $row->guide->id }} ? null : {{ $row->guide->id }})">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-[#6C63FF]/15 text-[#6C63FF] font-extrabold shadow-2xs">
                                #{{ $row->guide->queue_number }}
                            </span>
                        </td>

                        <!-- Guide Info -->
                        <td class="px-4 py-3.5" @click="expandedGuide = (expandedGuide === {{ $row->guide->id }} ? null : {{ $row->guide->id }})">
                            <div class="font-extrabold text-[#29283D] text-sm">{{ $row->guide->nama }}</div>
                            <div class="text-[11px] text-[#77768C] flex items-center gap-2 mt-0.5">
                                <span class="font-mono bg-[#E8E6F0] px-1.5 py-0.2 rounded text-[10px]">{{ $row->guide->kode_guide }}</span>
                                <span>&bull; {{ $row->guide->nomor_telepon ?? 'No HP -' }}</span>
                                @if($row->guide->status === 'AVAILABLE')
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">Ready</span>
                                @elseif($row->guide->status === 'ON_TOUR')
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-blue-100 text-blue-800">On Tour</span>
                                @elseif($row->guide->status === 'SUSPENDED')
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-100 text-rose-800">Suspended</span>
                                @else
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-100 text-slate-700">{{ $row->guide->status }}</span>
                                @endif
                            </div>
                        </td>

                        <!-- Total Bookings -->
                        <td class="px-4 py-3.5 text-center font-bold text-[#29283D]" @click="expandedGuide = (expandedGuide === {{ $row->guide->id }} ? null : {{ $row->guide->id }})">
                            <span class="px-2 py-1 rounded-lg bg-[#F4F3FA] font-bold">{{ $row->total_bookings }} tour</span>
                        </td>

                        <!-- Total Guests -->
                        <td class="px-4 py-3.5 text-center font-black text-[#6C63FF]" @click="expandedGuide = (expandedGuide === {{ $row->guide->id }} ? null : {{ $row->guide->id }})">
                            {{ $row->total_guests }} pax
                        </td>

                        <!-- Total Fee -->
                        <td class="px-4 py-3.5 text-right font-black text-sm text-[#29283D]" @click="expandedGuide = (expandedGuide === {{ $row->guide->id }} ? null : {{ $row->guide->id }})">
                            Rp {{ number_format($row->total_fee, 0, ',', '.') }}
                        </td>

                        <!-- Total Paid -->
                        <td class="px-4 py-3.5 text-right font-bold text-emerald-700" @click="expandedGuide = (expandedGuide === {{ $row->guide->id }} ? null : {{ $row->guide->id }})">
                            Rp {{ number_format($row->total_paid, 0, ',', '.') }}
                        </td>

                        <!-- Remaining -->
                        <td class="px-4 py-3.5 text-right font-black {{ $row->remaining > 0 ? 'text-[#c0392b]' : 'text-slate-400' }}" @click="expandedGuide = (expandedGuide === {{ $row->guide->id }} ? null : {{ $row->guide->id }})">
                            Rp {{ number_format($row->remaining, 0, ',', '.') }}
                        </td>

                        <!-- Status Badge -->
                        <td class="px-4 py-3.5 text-center" @click="expandedGuide = (expandedGuide === {{ $row->guide->id }} ? null : {{ $row->guide->id }})">
                            @if($row->status === 'PAID')
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-[#72D6C9]/20 text-[#0f766e] border border-[#72D6C9]/40">
                                    LUNAS
                                </span>
                            @elseif($row->status === 'PARTIAL')
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-[#FFD166]/25 text-[#b45309] border border-[#FFD166]/50">
                                    SEBAGIAN
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-rose-50 text-rose-700 border border-rose-200">
                                    BELUM DIBAYAR
                                </span>
                            @endif
                        </td>

                        <!-- Action / Detail Toggle -->
                        <td class="px-4 py-3.5 text-center no-print">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" 
                                        @click="expandedGuide = (expandedGuide === {{ $row->guide->id }} ? null : {{ $row->guide->id }})"
                                        class="px-2.5 py-1.5 rounded-xl font-bold text-xs transition border flex items-center gap-1"
                                        :class="expandedGuide === {{ $row->guide->id }} ? 'bg-[#6C63FF] text-white border-[#6C63FF]' : 'bg-white hover:bg-[#F4F3FA] text-[#6C63FF] border-[#E8E6F0]'">
                                    <span x-text="expandedGuide === {{ $row->guide->id }} ? 'Tutup' : 'Rincian'"></span>
                                    <svg class="w-3.5 h-3.5 transition-transform" :class="expandedGuide === {{ $row->guide->id }} ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <a href="{{ route('payroll.show', $row->guide) }}" 
                                   title="Buka Lembar Pembayaran Guide"
                                   class="p-1.5 rounded-xl bg-[#F4F3FA] hover:bg-[#6C63FF] hover:text-white text-[#77768C] transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>

                    <!-- Expandable Detail Row for Assignments & Payments -->
                    <tr x-show="expandedGuide === {{ $row->guide->id }}" x-cloak class="bg-[#F8F7FC] no-print">
                        <td colspan="9" class="p-4 sm:p-6 border-b border-[#E8E6F0]">
                            <div class="space-y-4 max-w-5xl mx-auto" x-data="{ subTab: 'tours' }">
                                <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-extrabold text-[#29283D] text-xs font-display">Rincian Finansial: {{ $row->guide->nama }}</span>
                                        <span class="text-[11px] text-[#77768C] font-mono">({{ $row->guide->kode_guide }})</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="subTab = 'tours'" 
                                                class="px-3 py-1 rounded-lg text-xs font-bold transition"
                                                :class="subTab === 'tours' ? 'bg-[#6C63FF] text-white shadow-2xs' : 'bg-white text-[#77768C] hover:text-[#29283D]'">
                                            Riwayat Tour ({{ $row->assignments->count() }})
                                        </button>
                                        <button type="button" @click="subTab = 'payments'" 
                                                class="px-3 py-1 rounded-lg text-xs font-bold transition"
                                                :class="subTab === 'payments' ? 'bg-[#6C63FF] text-white shadow-2xs' : 'bg-white text-[#77768C] hover:text-[#29283D]'">
                                            Riwayat Pembayaran ({{ $row->payments->count() }})
                                        </button>
                                        <a href="{{ route('payroll.show', $row->guide) }}" 
                                           class="px-3 py-1 rounded-lg text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-2xs">
                                            + Catat Pembayaran Gaji
                                        </a>
                                    </div>
                                </div>

                                <!-- SubTab 1: Riwayat Tour & Fee -->
                                <div x-show="subTab === 'tours'" class="space-y-2">
                                    @if($row->assignments->count() > 0)
                                        <div class="rounded-xl border border-[#E8E6F0] overflow-hidden bg-white shadow-2xs">
                                            <table class="w-full text-left text-xs">
                                                <thead class="bg-[#F4F3FA] text-[#77768C] font-bold border-b border-[#E8E6F0]">
                                                    <tr>
                                                        <th class="px-3.5 py-2.5">Tanggal Penugasan</th>
                                                        <th class="px-3.5 py-2.5">Kode Booking</th>
                                                        <th class="px-3.5 py-2.5">Paket Wisata & Tamu</th>
                                                        <th class="px-3.5 py-2.5 text-center">Jumlah Tamu</th>
                                                        <th class="px-3.5 py-2.5 text-right">Fee Guide (Rp)</th>
                                                        <th class="px-3.5 py-2.5 text-center">Status Tour</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-[#E8E6F0]">
                                                    @foreach($row->assignments as $a)
                                                    <tr class="hover:bg-slate-50/50">
                                                        <td class="px-3.5 py-2 text-[#77768C] font-medium">
                                                            {{ $a->assigned_at ? $a->assigned_at->format('d M Y, H:i') : '-' }} WITA
                                                        </td>
                                                        <td class="px-3.5 py-2 font-mono font-bold text-[#6C63FF]">
                                                            @if($a->booking)
                                                                <a href="{{ route('bookings.show', $a->booking) }}" class="hover:underline">
                                                                    {{ $a->booking->booking_code }}
                                                                </a>
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td class="px-3.5 py-2">
                                                            <div class="font-bold text-[#29283D]">{{ $a->booking?->tourPackage?->name ?? 'Tour' }}</div>
                                                            <div class="text-[10px] text-[#77768C]">{{ $a->booking?->customer_name }}</div>
                                                        </td>
                                                        <td class="px-3.5 py-2 text-center font-bold text-[#29283D]">
                                                            {{ $a->guest_count }} orang
                                                        </td>
                                                        <td class="px-3.5 py-2 text-right font-extrabold text-[#29283D]">
                                                            Rp {{ number_format($a->fee_amount, 0, ',', '.') }}
                                                        </td>
                                                        <td class="px-3.5 py-2 text-center">
                                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $a->status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                                                {{ $a->status }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="p-4 text-center text-xs text-[#77768C] bg-white rounded-xl border border-dashed border-[#E8E6F0]">
                                            Tidak ada penugasan tour yang tercatat untuk guide ini dalam rentang periode yang dipilih.
                                        </div>
                                    @endif
                                </div>

                                <!-- SubTab 2: Riwayat Pembayaran -->
                                <div x-show="subTab === 'payments'" class="space-y-2" x-cloak>
                                    @if($row->payments->count() > 0)
                                        <div class="rounded-xl border border-[#E8E6F0] overflow-hidden bg-white shadow-2xs">
                                            <table class="w-full text-left text-xs">
                                                <thead class="bg-[#F4F3FA] text-[#77768C] font-bold border-b border-[#E8E6F0]">
                                                    <tr>
                                                        <th class="px-3.5 py-2.5">Tanggal Transfer</th>
                                                        <th class="px-3.5 py-2.5">Kode Transaksi</th>
                                                        <th class="px-3.5 py-2.5">Metode Bayar</th>
                                                        <th class="px-3.5 py-2.5 text-right">Nominal Dibayarkan</th>
                                                        <th class="px-3.5 py-2.5">Catatan</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-[#E8E6F0]">
                                                    @foreach($row->payments as $p)
                                                    <tr class="hover:bg-slate-50/50">
                                                        <td class="px-3.5 py-2 text-[#29283D] font-bold">
                                                            {{ $p->payment_date ? $p->payment_date->format('d M Y') : '-' }}
                                                        </td>
                                                        <td class="px-3.5 py-2 font-mono font-bold text-[#6C63FF]">
                                                            {{ $p->payment_code }}
                                                        </td>
                                                        <td class="px-3.5 py-2 text-[#77768C]">
                                                            {{ $p->payment_method }}
                                                        </td>
                                                        <td class="px-3.5 py-2 text-right font-black text-emerald-700">
                                                            Rp {{ number_format($p->amount, 0, ',', '.') }}
                                                        </td>
                                                        <td class="px-3.5 py-2 text-[#77768C] text-[11px]">
                                                            {{ $p->notes ?? '-' }}
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="p-4 text-center text-xs text-[#77768C] bg-white rounded-xl border border-dashed border-[#E8E6F0]">
                                            Belum ada catatan transfer/pembayaran fee untuk guide ini dalam rentang periode yang dipilih.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-[#77768C]">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <svg class="w-10 h-10 text-[#77768C]/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="font-bold text-sm text-[#29283D]">Tidak ada data gaji guide ditemukan</span>
                                <span class="text-xs text-[#77768C]">Silakan ubah filter periode tanggal, status pembayaran, atau nama guide di atas.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
