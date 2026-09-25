@extends('layouts.app')

@section('title', 'Dashboard Operasional Harian')
@section('header', 'Dashboard Operasional Harian')

@section('content')
<div class="space-y-6">
    <!-- Next Guide Banner (Continuous Rotating Queue Indicator) -->
    <div class="bg-gradient-to-r from-[#14433b] to-[#1b584d] rounded-xl p-5 text-white shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 border border-[#21c9a4]/30">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-[#21c9a4] text-[#14433b] flex items-center justify-center font-bold text-xl shadow-xs">
                #{{ $nextGuide ? $nextGuide->queue_number : '-' }}
            </div>
            <div>
                <div class="text-xs uppercase tracking-wider text-[#21c9a4] font-semibold">Giliran Antrean Berikutnya</div>
                <div class="text-xl font-bold">
                    @if($nextGuide)
                        {{ $nextGuide->nama }} ({{ $nextGuide->kode_guide }})
                    @else
                        <span class="text-slate-300">Belum ada guide yang tersedia</span>
                    @endif
                </div>
                <div class="text-xs text-slate-200 mt-0.5">
                    Sistem antrean memutar melingkar (Circular Continuous Queue). Tidak reset kembali ke #1 setiap hari.
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('direct-tours.create') }}" class="px-3.5 py-2 rounded-lg bg-amber-400 hover:bg-amber-300 text-slate-900 text-xs font-bold tracking-wide transition shadow-sm flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                <span>⚡ Tour Langsung</span>
            </a>
            <a href="{{ route('queue.board') }}" class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-semibold tracking-wide transition-colors border border-white/20">
                Papan Antrean
            </a>
            <a href="{{ route('bookings.create') }}" class="px-3 py-2 rounded-lg bg-[#21c9a4] hover:bg-[#1bb392] text-[#14433b] text-xs font-bold tracking-wide transition-colors shadow-xs">
                + Booking Baru
            </a>
        </div>
    </div>

    @php
        $pendingUsersCount = \App\Models\User::where('approval_status', 'PENDING')->count();
    @endphp
    @if($pendingUsersCount > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="p-2 rounded-lg bg-amber-100 text-amber-700 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </span>
            <div>
                <h4 class="text-sm font-bold text-amber-900">Ada {{ $pendingUsersCount }} Pendaftaran User Menunggu Persetujuan (ACC)</h4>
                <p class="text-xs text-amber-700">Calon staf atau admin telah mendaftar dan menunggu verifikasi serta izin akses Anda.</p>
            </div>
        </div>
        <a href="{{ route('admin.user-approvals.index') }}" class="px-3.5 py-1.5 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-semibold shrink-0 transition-colors shadow-xs inline-flex items-center justify-center">
            Tinjau & ACC Sekarang &rarr;
        </a>
    </div>
    @endif

    <!-- Period Filter & Export Toolbar -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-3.5 sm:p-4 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mr-1">Filter Periode:</span>
            
            <!-- Hari Ini Tab -->
            <a href="{{ route('dashboard', ['period' => 'today']) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ ($period ?? 'today') === 'today' ? 'bg-[#14433b] text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                📅 Hari Ini
            </a>

            <!-- Form Filter Per Bulan -->
            <form action="{{ route('dashboard') }}" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="period" value="month">
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
                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ ($period ?? 'today') === 'month' ? 'bg-[#14433b] text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                    Terapkan
                </button>
            </form>
        </div>

        <div class="flex items-center gap-3">
            <div class="text-xs text-slate-500 hidden sm:block">
                Menampilkan: <span class="font-bold text-slate-800">{{ $periodLabel ?? 'Hari Ini' }}</span>
            </div>

            <!-- Export CSV Button -->
            <a href="{{ route('dashboard.export-csv', request()->query()) }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Operations Metrics (Row 1) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- Period Booking -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
            <div class="text-xs font-medium text-slate-500">Booking {{ ($period ?? 'today') === 'month' ? 'Bulan Ini' : 'Hari Ini' }}</div>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $totalBookings ?? $totalBookingsToday ?? 0 }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">{{ $periodSubtitle ?? 'Hari ini' }}</div>
        </div>

        <!-- Period Guests -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
            <div class="text-xs font-medium text-slate-500">Total Tamu {{ ($period ?? 'today') === 'month' ? 'Bulan Ini' : 'Hari Ini' }}</div>
            <div class="text-2xl font-bold text-[#14433b] mt-1">{{ $totalGuests ?? $totalGuestsToday ?? 0 }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Orang / pax</div>
        </div>

        <!-- Waiting Assignment -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
            <div class="text-xs font-medium text-slate-500">Menunggu Guide</div>
            <div class="text-2xl font-bold text-amber-600 mt-1">{{ $waitingAssignmentCount ?? 0 }}</div>
            <div class="text-[11px] text-amber-600/80 mt-0.5">Perlu alokasi hari ini</div>
        </div>

        <!-- Guide Checked In -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
            <div class="text-xs font-medium text-slate-500">Guide Check-in</div>
            <div class="text-2xl font-bold text-[#21c9a4] mt-1">{{ $checkedInCount ?? 0 }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Hadir di lokasi hari ini</div>
        </div>

        <!-- Guide Available -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
            <div class="text-xs font-medium text-slate-500">Guide Siap</div>
            <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $guidesAvailable ?? 0 }}</div>
            <div class="text-[11px] text-emerald-600/80 mt-0.5">Siap terima tugas</div>
        </div>

        <!-- Guide On Tour -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
            <div class="text-xs font-medium text-slate-500">Guide Sedang Tur</div>
            <div class="text-2xl font-bold text-blue-600 mt-1">{{ $guidesOnTour ?? 0 }}</div>
            <div class="text-[11px] text-blue-600/80 mt-0.5">Sedang bertugas</div>
        </div>
    </div>

    <!-- Financial Metrics (Row 2) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Revenue -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium mb-1">
                <span>Revenue {{ ($period ?? 'today') === 'month' ? 'Bulan Ini' : 'Hari Ini' }}</span>
                <span class="p-1 rounded bg-[#e6faf5] text-[#14433b]">Pendapatan</span>
            </div>
            <div class="text-xl font-bold text-slate-900">Rp {{ number_format($revenue ?? $revenueToday ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Total tagihan {{ ($period ?? 'today') === 'month' ? 'bulan terpilih' : 'hari ini' }}</div>
        </div>

        <!-- Guide Fee -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium mb-1">
                <span>Fee Guide {{ ($period ?? 'today') === 'month' ? 'Bulan Ini' : 'Hari Ini' }}</span>
                <span class="p-1 rounded bg-amber-50 text-amber-700">Fee</span>
            </div>
            <div class="text-xl font-bold text-amber-700">Rp {{ number_format($guideFee ?? $guideFeeToday ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Hak guide yang dialokasikan</div>
        </div>

        <!-- Operational Cost -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium mb-1">
                <span>Biaya Operasional</span>
                <span class="p-1 rounded bg-rose-50 text-rose-700">Biaya</span>
            </div>
            <div class="text-xl font-bold text-rose-700">Rp {{ number_format($operationalCost ?? $operationalCostToday ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Tiket, bensin & sewa kendaraan</div>
        </div>

        <!-- Estimated Profit -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium mb-1">
                <span>Estimasi Keuntungan</span>
                <span class="p-1 rounded bg-emerald-50 text-emerald-700 font-bold">Laba</span>
            </div>
            <div class="text-xl font-bold text-emerald-700">Rp {{ number_format($estimatedProfit ?? $estimatedProfitToday ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Revenue - Fee Guide - Operasional</div>
        </div>
    </div>

    <!-- Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Bookings (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Booking Terbaru</h3>
                    <p class="text-xs text-slate-400">Daftar booking yang baru didaftarkan ke sistem</p>
                </div>
                <a href="{{ route('bookings.index') }}" class="text-xs font-medium text-[#14433b] hover:text-[#21c9a4] transition-colors">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3">Kode Booking</th>
                            <th class="px-4 py-3">Pelanggan</th>
                            <th class="px-4 py-3">Tanggal / Waktu</th>
                            <th class="px-4 py-3 text-center">Tamu</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentBookings as $b)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                <div class="flex items-center gap-1.5">
                                    @if($b->is_walkin)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300">⚡ Walk-in</span>
                                    @endif
                                    <a href="{{ route('bookings.show', $b) }}" class="hover:text-[#14433b]">
                                        {{ $b->booking_code }}
                                    </a>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">{{ $b->customer_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $b->nationality }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ $b->booking_date->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ substr($b->start_time, 0, 5) }} - {{ substr($b->end_time, 0, 5) }}</div>
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-slate-700">
                                {{ $b->guest_count }}
                            </td>
                            <td class="px-4 py-3">
                                @if($b->status === 'FULLY_ASSIGNED')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Guide Lengkap</span>
                                @elseif($b->status === 'PARTIALLY_ASSIGNED')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Guide Sebagian</span>
                                @elseif($b->status === 'ON_GOING')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Sedang Tur</span>
                                @elseif($b->status === 'COMPLETED')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">Selesai</span>
                                @elseif($b->status === 'CANCELLED')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">Dibatalkan</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Menunggu Guide</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('bookings.show', $b) }}" class="inline-flex items-center px-2 py-1 rounded text-[11px] font-medium bg-[#14433b]/10 text-[#14433b] hover:bg-[#14433b] hover:text-white transition-colors">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-400">Belum ada booking terdaftar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Queue Activity (1 Col) -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Aktivitas Antrean</h3>
                    <p class="text-xs text-slate-400">Riwayat rotasi auto & manual</p>
                </div>
                <a href="{{ route('queue.history') }}" class="text-xs font-medium text-[#14433b] hover:text-[#21c9a4] transition-colors">
                    Semua &rarr;
                </a>
            </div>

            <div class="p-4 flex-1 divide-y divide-slate-100 space-y-3">
                @forelse($recentQueueHistory as $qh)
                <div class="pt-3 first:pt-0">
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-bold text-slate-800">
                            {{ $qh->selectedGuide?->nama ?? 'Guide' }}
                            <span class="text-[10px] text-[#21c9a4] font-semibold bg-[#14433b] px-1.5 py-0.2 rounded-full">#{{ $qh->next_position }}</span>
                        </span>
                        <span class="text-[10px] text-slate-400">{{ $qh->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="text-xs text-slate-600">
                        {{ $qh->notes ?? 'Penugasan guide' }}
                    </div>
                    <div class="text-[10px] text-slate-400 mt-0.5">
                        Rotasi: #{{ $qh->previous_position }} &rarr; #{{ $qh->next_position }} | Booking: {{ $qh->booking?->booking_code ?? '-' }}
                    </div>
                </div>
                @empty
                <div class="text-center text-slate-400 text-xs py-8">Belum ada aktivitas antrean.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
