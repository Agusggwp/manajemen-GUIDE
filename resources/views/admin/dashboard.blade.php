@extends('layouts.app')

@section('title', 'Dashboard Operasional Harian')
@section('header', 'Dashboard Operasional Harian')

@section('content')
<div class="space-y-6">
    <!-- Next Guide Banner (Continuous Rotating Queue Indicator - Claymorphism Hero) -->
    <div class="clay-card-primary p-6 sm:p-7 relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-5">
        <!-- Decorative Organic Clay Blob Background -->
        <div class="absolute -right-8 -bottom-8 w-44 h-44 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-36 h-36 rounded-full bg-[#FFD166]/15 blur-lg pointer-events-none"></div>

        <div class="relative z-10 flex items-center space-x-4 sm:space-x-5">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-[#FFD166] text-[#29283D] flex items-center justify-center font-black text-2xl sm:text-3xl shadow-[4px_8px_20px_rgba(255,209,102,0.45),inset_0_2px_2px_rgba(255,255,255,0.7)] shrink-0">
                #{{ $nextGuide ? $nextGuide->queue_number : '-' }}
            </div>
            <div>
                <div class="text-[11px] sm:text-xs uppercase tracking-wider text-[#FFD166] font-extrabold flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <span>Giliran Antrean Berikutnya</span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-white mt-0.5">
                    @if($nextGuide)
                        {{ $nextGuide->nama }} <span class="text-sm font-semibold text-white/80">({{ $nextGuide->kode_guide }})</span>
                    @else
                        <span class="text-white/70">Belum ada guide yang tersedia</span>
                    @endif
                </div>
                <div class="text-xs text-white/80 mt-1">
                    Sistem antrean memutar melingkar (Circular Continuous Queue). Tidak reset kembali ke #1 setiap hari.
                </div>
            </div>
        </div>

        <div class="relative z-10 flex flex-wrap items-center gap-2.5">
            <a href="{{ route('direct-tours.create') }}" class="clay-btn clay-btn-coral px-4 py-2.5 text-xs font-bold tracking-wide">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                <span>Tour Langsung</span>
            </a>
            <a href="{{ route('queue.board') }}" class="clay-btn clay-btn-secondary px-4 py-2.5 text-xs font-semibold">
                Papan Antrean
            </a>
            <a href="{{ route('bookings.create') }}" class="clay-btn clay-btn-turquoise px-4 py-2.5 text-xs font-bold">
                + Booking Baru
            </a>
        </div>
    </div>

    @php
        $pendingUsersCount = \App\Models\User::where('approval_status', 'PENDING')->count();
    @endphp
    @if($pendingUsersCount > 0)
    <div class="clay-card p-4 sm:p-5 border-l-4 border-l-[#FFD166] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <span class="w-10 h-10 rounded-2xl bg-[#FFF9E6] text-[#9C7800] flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </span>
            <div>
                <h4 class="text-sm font-bold text-[#29283D]">Ada {{ $pendingUsersCount }} Pendaftaran User Menunggu Persetujuan (ACC)</h4>
                <p class="text-xs text-[#77768C]">Calon staf atau admin telah mendaftar dan menunggu verifikasi serta izin akses Anda.</p>
            </div>
        </div>
        <a href="{{ route('admin.user-approvals.index') }}" class="clay-btn clay-btn-primary px-4 py-2 text-xs font-semibold shrink-0">
            Tinjau & ACC Sekarang &rarr;
        </a>
    </div>
    @endif

    <!-- Period Filter & Export Toolbar (Clay Card) -->
    <div class="clay-card p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2.5">
            <span class="text-xs font-bold text-[#77768C] uppercase tracking-wider mr-1">Filter Periode:</span>
            
            <!-- Hari Ini Tab -->
            <a href="{{ route('dashboard', ['period' => 'today']) }}"
               class="clay-btn {{ ($period ?? 'today') === 'today' ? 'clay-btn-primary' : 'clay-btn-secondary' }} px-3.5 py-1.5 text-xs font-bold">
                <svg class="w-3.5 h-3.5 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Hari Ini
            </a>

            <!-- Form Filter Per Bulan -->
            <form action="{{ route('dashboard') }}" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="period" value="month">
                <div class="flex items-center gap-1.5 bg-white border border-[#E8E6F0] rounded-xl px-3 py-1 shadow-inner">
                    <span class="text-xs text-[#77768C] flex items-center gap-1"><svg class="w-3.5 h-3.5 text-[#77768C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg><span>Bulan:</span></span>
                    <select name="month" class="bg-transparent text-xs font-semibold text-[#29283D] focus:outline-none cursor-pointer py-0.5">
                        @foreach(($months ?? []) as $num => $name)
                            <option value="{{ $num }}" {{ ($selectedMonth ?? date('n')) == $num ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="year" class="bg-transparent text-xs font-semibold text-[#29283D] focus:outline-none cursor-pointer py-0.5 border-l border-[#E8E6F0] pl-2">
                        @for($y = \Carbon\Carbon::today()->year - 2; $y <= \Carbon\Carbon::today()->year + 1; $y++)
                            <option value="{{ $y }}" {{ ($selectedYear ?? date('Y')) == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="clay-btn {{ ($period ?? 'today') === 'month' ? 'clay-btn-primary' : 'clay-btn-secondary' }} px-3.5 py-1.5 text-xs font-bold">
                    Terapkan
                </button>
            </form>
        </div>

        <div class="flex items-center gap-3">
            <div class="text-xs text-[#77768C] hidden sm:block">
                Menampilkan: <span class="font-bold text-[#29283D]">{{ $periodLabel ?? 'Hari Ini' }}</span>
            </div>

            <!-- Export CSV Button -->
            <a href="{{ route('dashboard.export-csv', request()->query()) }}" 
               class="clay-btn clay-btn-turquoise px-4 py-1.5 text-xs font-bold shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Operations Metrics (Row 1 - Clay Cards) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- Period Booking -->
        <div class="clay-card clay-card-interactive p-4 flex flex-col justify-between">
            <div>
                <div class="w-8 h-8 rounded-xl bg-[#ECEBFC] text-[#6C63FF] flex items-center justify-center mb-2 shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                </div>
                <div class="text-xs font-medium text-[#77768C]">Booking {{ ($period ?? 'today') === 'month' ? 'Bulan Ini' : 'Hari Ini' }}</div>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black text-[#29283D]">{{ $totalBookings ?? $totalBookingsToday ?? 0 }}</div>
                <div class="text-[11px] text-[#77768C] mt-0.5">{{ $periodSubtitle ?? 'Hari ini' }}</div>
            </div>
        </div>

        <!-- Period Guests -->
        <div class="clay-card clay-card-interactive p-4 flex flex-col justify-between">
            <div>
                <div class="w-8 h-8 rounded-xl bg-[#FFF0EB] text-[#FF8A65] flex items-center justify-center mb-2 shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <div class="text-xs font-medium text-[#77768C]">Total Tamu {{ ($period ?? 'today') === 'month' ? 'Bulan Ini' : 'Hari Ini' }}</div>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black text-[#FF8A65]">{{ $totalGuests ?? $totalGuestsToday ?? 0 }}</div>
                <div class="text-[11px] text-[#77768C] mt-0.5">Orang / pax</div>
            </div>
        </div>

        <!-- Waiting Assignment -->
        <div class="clay-card clay-card-interactive p-4 flex flex-col justify-between">
            <div>
                <div class="w-8 h-8 rounded-xl bg-[#FFF9E6] text-[#9C7800] flex items-center justify-center mb-2 shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div class="text-xs font-medium text-[#77768C]">Menunggu Guide</div>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black text-[#D4A017]">{{ $waitingAssignmentCount ?? 0 }}</div>
                <div class="text-[11px] text-[#9C7800] mt-0.5 font-medium">Perlu alokasi hari ini</div>
            </div>
        </div>

        <!-- Guide Checked In -->
        <div class="clay-card clay-card-interactive p-4 flex flex-col justify-between">
            <div>
                <div class="w-8 h-8 rounded-xl bg-[#EBFBF8] text-[#1F7368] flex items-center justify-center mb-2 shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </div>
                <div class="text-xs font-medium text-[#77768C]">Guide Check-in</div>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black text-[#2B9688]">{{ $checkedInCount ?? 0 }}</div>
                <div class="text-[11px] text-[#77768C] mt-0.5">Hadir di lokasi hari ini</div>
            </div>
        </div>

        <!-- Guide Available -->
        <div class="clay-card clay-card-interactive p-4 flex flex-col justify-between">
            <div>
                <div class="w-8 h-8 rounded-xl bg-[#ECEBFC] text-[#6C63FF] flex items-center justify-center mb-2 shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div class="text-xs font-medium text-[#77768C]">Guide Siap</div>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black text-[#6C63FF]">{{ $guidesAvailable ?? 0 }}</div>
                <div class="text-[11px] text-[#6C63FF] mt-0.5 font-medium">Siap terima tugas</div>
            </div>
        </div>

        <!-- Guide On Tour -->
        <div class="clay-card clay-card-interactive p-4 flex flex-col justify-between">
            <div>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-2 shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
                <div class="text-xs font-medium text-[#77768C]">Guide Sedang Tur</div>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-black text-blue-600">{{ $guidesOnTour ?? 0 }}</div>
                <div class="text-[11px] text-blue-600 mt-0.5 font-medium">Sedang bertugas</div>
            </div>
        </div>
    </div>

    <!-- Financial Metrics (Row 2 - Soft Clay Elevation) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Revenue -->
        <div class="clay-card clay-card-interactive p-5">
            <div class="flex items-center justify-between text-xs font-medium mb-1.5">
                <span class="text-[#77768C]">Revenue {{ ($period ?? 'today') === 'month' ? 'Bulan Ini' : 'Hari Ini' }}</span>
                <span class="clay-badge clay-badge-primary">Pendapatan</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-[#29283D] mt-1">Rp {{ number_format($revenue ?? $revenueToday ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-[#77768C] mt-1.5">Total tagihan {{ ($period ?? 'today') === 'month' ? 'bulan terpilih' : 'hari ini' }}</div>
        </div>

        <!-- Guide Fee -->
        <div class="clay-card clay-card-interactive p-5">
            <div class="flex items-center justify-between text-xs font-medium mb-1.5">
                <span class="text-[#77768C]">Fee Guide {{ ($period ?? 'today') === 'month' ? 'Bulan Ini' : 'Hari Ini' }}</span>
                <span class="clay-badge clay-badge-accent">Fee</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-[#FF8A65] mt-1">Rp {{ number_format($guideFee ?? $guideFeeToday ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-[#77768C] mt-1.5">Hak guide yang dialokasikan</div>
        </div>

        <!-- Operational Cost -->
        <div class="clay-card clay-card-interactive p-5">
            <div class="flex items-center justify-between text-xs font-medium mb-1.5">
                <span class="text-[#77768C]">Biaya Operasional</span>
                <span class="clay-badge bg-rose-50 text-rose-700 border border-rose-200">Biaya</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-rose-600 mt-1">Rp {{ number_format($operationalCost ?? $operationalCostToday ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-[#77768C] mt-1.5">Tiket, bensin & sewa kendaraan</div>
        </div>

        <!-- Estimated Profit -->
        <div class="clay-card clay-card-interactive p-5">
            <div class="flex items-center justify-between text-xs font-medium mb-1.5">
                <span class="text-[#77768C]">Estimasi Keuntungan</span>
                <span class="clay-badge clay-badge-supporting">Laba</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-[#1F7368] mt-1">Rp {{ number_format($estimatedProfit ?? $estimatedProfitToday ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-[#77768C] mt-1.5">Revenue - Fee Guide - Operasional</div>
        </div>
    </div>

    <!-- Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Bookings (2 Cols - Clay Table) -->
        <div class="lg:col-span-2 clay-table-wrapper flex flex-col justify-between">
            <div>
                <div class="px-6 py-4.5 border-b border-[#E8E6F0] flex items-center justify-between bg-white">
                    <div>
                        <h3 class="text-sm font-bold text-[#29283D]">Booking Terbaru</h3>
                        <p class="text-xs text-[#77768C]">Daftar booking yang baru didaftarkan ke sistem</p>
                    </div>
                    <a href="{{ route('bookings.index') }}" class="text-xs font-bold text-[#6C63FF] hover:text-[#5850E6] transition-colors">
                        Lihat Semua &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#F4F3FA] text-[#77768C] font-bold border-b border-[#E8E6F0]">
                            <tr>
                                <th class="px-5 py-3.5">Kode Booking</th>
                                <th class="px-5 py-3.5">Pelanggan</th>
                                <th class="px-5 py-3.5">Tanggal / Waktu</th>
                                <th class="px-5 py-3.5 text-center">Tamu</th>
                                <th class="px-5 py-3.5">Status</th>
                                <th class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E8E6F0]">
                            @forelse($recentBookings as $b)
                            <tr class="hover:bg-[#F4F3FA]/60 transition-colors">
                                <td class="px-5 py-3.5 font-bold text-[#29283D]">
                                    <div class="flex items-center gap-1.5">
                                        @if($b->is_walkin)
                                            <span class="px-1.5 py-0.5 rounded-md text-[9px] font-extrabold bg-[#FFF9E6] text-[#9C7800] border border-[#FFE9A8]">Walk-in</span>
                                        @endif
                                        <a href="{{ route('bookings.show', $b) }}" class="hover:text-[#6C63FF]">
                                            {{ $b->booking_code }}
                                        </a>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-semibold text-[#29283D]">{{ $b->customer_name }}</div>
                                    <div class="text-[10px] text-[#77768C]">{{ $b->nationality }}</div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-medium text-[#29283D]">{{ $b->booking_date->format('d/m/Y') }}</div>
                                    <div class="text-[10px] text-[#77768C]">{{ substr($b->start_time, 0, 5) }} - {{ substr($b->end_time, 0, 5) }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-center font-bold text-[#29283D]">
                                    <span class="px-2 py-0.5 rounded-full bg-[#ECEBFC] text-[#6C63FF] text-[11px] font-bold">
                                        {{ $b->guest_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($b->status === 'FULLY_ASSIGNED')
                                        <span class="clay-badge clay-badge-supporting text-[10px]">Guide Lengkap</span>
                                    @elseif($b->status === 'PARTIALLY_ASSIGNED')
                                        <span class="clay-badge clay-badge-accent text-[10px]">Guide Sebagian</span>
                                    @elseif($b->status === 'ON_GOING')
                                        <span class="clay-badge bg-blue-50 text-blue-700 border border-blue-200 text-[10px]">Sedang Tur</span>
                                    @elseif($b->status === 'COMPLETED')
                                        <span class="clay-badge bg-slate-100 text-slate-700 border border-slate-200 text-[10px]">Selesai</span>
                                    @elseif($b->status === 'CANCELLED')
                                        <span class="clay-badge bg-rose-50 text-rose-700 border border-rose-200 text-[10px]">Dibatalkan</span>
                                    @else
                                        <span class="clay-badge clay-badge-secondary text-[10px]">Menunggu Guide</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('bookings.show', $b) }}" class="clay-btn clay-btn-secondary px-2.5 py-1 text-[11px] font-bold">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-[#77768C]">Belum ada booking terdaftar.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Queue Activity (1 Col - Clay Card) -->
        <div class="clay-card p-5 flex flex-col justify-between">
            <div>
                <div class="pb-4 border-b border-[#E8E6F0] flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-[#29283D]">Aktivitas Antrean</h3>
                        <p class="text-xs text-[#77768C]">Riwayat rotasi auto & manual</p>
                    </div>
                    <a href="{{ route('queue.history') }}" class="text-xs font-bold text-[#6C63FF] hover:text-[#5850E6] transition-colors">
                        Semua &rarr;
                    </a>
                </div>

                <div class="divide-y divide-[#E8E6F0] space-y-3 pt-3">
                    @forelse($recentQueueHistory as $qh)
                    <div class="pt-3 first:pt-0">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-bold text-[#29283D] flex items-center gap-1.5">
                                <span>{{ $qh->selectedGuide?->nama ?? 'Guide' }}</span>
                                <span class="clay-badge clay-badge-primary text-[10px] py-0 px-1.5">#{{ $qh->next_position }}</span>
                            </span>
                            <span class="text-[10px] text-[#77768C]">{{ $qh->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-xs text-[#77768C]">
                            {{ $qh->notes ?? 'Penugasan guide' }}
                        </div>
                        <div class="text-[10px] text-[#77768C]/80 mt-0.5">
                            Rotasi: #{{ $qh->previous_position }} &rarr; #{{ $qh->next_position }} | Booking: {{ $qh->booking?->booking_code ?? '-' }}
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-[#77768C] text-xs py-8">Belum ada aktivitas antrean.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

