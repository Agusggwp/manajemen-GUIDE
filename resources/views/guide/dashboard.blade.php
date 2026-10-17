@extends('layouts.guide')

@section('title', 'Beranda')

@section('content')
<div class="space-y-6" x-data="{
    gpsLatitude: null,
    gpsLongitude: null,
    gpsLoading: false,
    gpsCaptured: false,

    captureGps() {
        if ('geolocation' in navigator) {
            this.gpsLoading = true;
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.gpsLatitude = pos.coords.latitude;
                    this.gpsLongitude = pos.coords.longitude;
                    this.gpsLoading = false;
                    this.gpsCaptured = true;
                },
                (err) => {
                    this.gpsLoading = false;
                    console.log('GPS error:', err.message);
                },
                { enableHighAccuracy: true, timeout: 7000 }
            );
        }
    }
}" x-init="captureGps()">

    @if($guide->isSuspended())
    <!-- Suspension Notice Card -->
    <div class="clay-card p-5 border border-rose-300 bg-rose-50/90 text-rose-900 shadow-sm">
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-md">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <h3 class="text-sm font-extrabold text-rose-950 flex items-center gap-2">
                        <span>AKUN ANDA SEDANG DITANGGUHKAN (SUSPENDED)</span>
                        <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-rose-200 text-rose-800 uppercase">Nonaktif Sementara</span>
                    </h3>
                    @if($guide->suspended_at)
                    <span class="text-[11px] text-rose-700 font-medium">
                        Sejak: {{ $guide->suspended_at->format('d M Y, H:i') }} WITA
                    </span>
                    @endif
                </div>

                <div class="mt-2 text-xs bg-white/80 rounded-xl p-3 border border-rose-200">
                    <span class="font-bold text-rose-900 block text-[11px]">Alasan Penangguhan:</span>
                    <p class="text-rose-800 mt-0.5 leading-relaxed whitespace-pre-line">{{ $guide->suspend_reason ?: 'Tidak ada keterangan alasan yang dicantumkan.' }}</p>
                </div>

                <p class="text-[11px] text-rose-700 mt-2">
                    Selama masa penangguhan, Anda <strong>tidak dapat melakukan absen masuk (check-in)</strong> dan <strong>dikecualikan dari perputaran antrean penugasan tour</strong>. Silakan hubungi admin atau koordinator operasional.
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Status Kehadiran Hari Ini -->
    <div class="clay-card p-5 sm:p-6 space-y-4 border border-[#E8E6F0]">
            <div class="flex items-center justify-between pb-3 border-b border-[#E8E6F0] flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#ECEBFC] text-[#6C63FF] flex items-center justify-center shrink-0 shadow-2xs">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] text-[#77768C] block font-extrabold uppercase tracking-wider">STATUS KEHADIRAN HARI INI</span>
                        <h3 class="text-sm sm:text-base font-extrabold text-[#29283D] font-display">{{ \Carbon\Carbon::parse($today)->format('l, d F Y') }}</h3>
                    </div>
                </div>

                <!-- Status Badge -->
                <div>
                    @if($guide->isSuspended())
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 flex items-center gap-1.5 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                        DITANGGUHKAN
                    </span>
                    @elseif($todayAttendance && $todayAttendance->status === 'CHECKED_IN')
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-[#72D6C9]/20 text-[#147a6f] border border-[#72D6C9]/50 flex items-center gap-1.5 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-[#72D6C9] animate-pulse"></span>
                        HADIR (CHECK-IN)
                    </span>
                    @elseif(!$isOperationalOpen && $todayAttendance && $todayAttendance->status === 'CHECKED_OUT')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-[#77768C] border border-[#E8E6F0] flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                        CHECK-OUT (LOKET TUTUP)
                    </span>
                    @elseif(!$isOperationalOpen)
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                        LOKET TUTUP
                    </span>
                    @elseif($todayAttendance && $todayAttendance->status === 'CHECKED_OUT')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-[#77768C] border border-[#E8E6F0]">
                        SUDAH CHECK-OUT
                    </span>
                    @elseif($guide->status === 'OFF')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        LIBUR (OFF)
                    </span>
                    @else
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#FFD166]/25 text-[#8a6800] border border-[#FFD166]/60 shadow-2xs">
                        BELUM CHECK-IN
                    </span>
                    @endif
                </div>
            </div>

            <!-- 4 Metrics Grid on Desktop -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <!-- Box 1: Nomor Antrean Anda -->
                <div class="p-3 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0]/80 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-white text-[#6C63FF] flex items-center justify-center shrink-0 shadow-2xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] text-[#77768C] block font-medium">Nomor Antrean Anda:</span>
                        <span class="text-sm font-black text-[#6C63FF] font-display">Posisi #{{ $guide->queue_number }}</span>
                    </div>
                </div>

                <!-- Box 2: Status Giliran Saat Ini -->
                <div class="p-3 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0]/80 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-white text-[#6C63FF] flex items-center justify-center shrink-0 shadow-2xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] text-[#77768C] block font-medium">Status Giliran Saat Ini:</span>
                        @if($guide->isSuspended())
                        <span class="text-xs font-bold text-rose-700">Ditangguhkan</span>
                        @elseif($isMyTurnNext)
                        <span class="text-xs font-extrabold text-[#6C63FF] flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 fill-[#FFD166] inline-block -mt-0.5" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <span>Giliran Anda!</span>
                        </span>
                        @elseif($myInfo && $myInfo->is_on_tour)
                        <span class="text-xs font-bold text-blue-700">Sedang On Tour</span>
                        @elseif($myInfo && $myInfo->is_available)
                        <span class="text-xs font-bold text-[#29283D]">
                            {{ $guidesAheadCount > 0 ? $guidesAheadCount . ' Guide Lagi' : 'Siap Bertugas' }}
                        </span>
                        @elseif($myInfo && $myInfo->is_checked_out)
                        <span class="text-xs font-medium text-[#77768C]">Sudah Selesai</span>
                        @else
                        <span class="text-xs font-medium text-[#FF8A65]">Belum Check-In</span>
                        @endif
                    </div>
                </div>

                <!-- Box 3: Jam Masuk (Check-in) -->
                <div class="p-3 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0]/80 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-white text-[#6C63FF] flex items-center justify-center shrink-0 shadow-2xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] text-[#77768C] block font-medium">Jam Masuk (Check-in):</span>
                        <span class="text-xs font-bold text-[#29283D]">{{ $todayAttendance?->check_in_at ? $todayAttendance->check_in_at->format('H:i') . ' WITA' : '-' }}</span>
                    </div>
                </div>

                <!-- Box 4: Giliran Terdepan -->
                <div class="p-3 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0]/80 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-white text-[#6C63FF] flex items-center justify-center shrink-0 shadow-2xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] text-[#77768C] block font-medium">Giliran Terdepan:</span>
                        <span class="text-xs font-bold text-[#6C63FF] truncate block">
                            {{ $nextGuide ? '#' . $nextGuide->queue_number . ' ' . $nextGuide->nama : 'Belum Ada' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

            <!-- Check-in Action Banner (Clay Banner matching Screenshot 2) -->
            @if($guide->isSuspended())
            <div class="clay-card p-4 rounded-2xl bg-rose-50 border border-rose-200 text-center text-xs text-rose-800">
                <span class="font-bold block mb-0.5">Absen Masuk Dinonaktifkan</span>
                Akun Anda sedang ditangguhkan. Silakan hubungi admin operasional untuk info lebih lanjut.
            </div>
            @elseif(!$isOperationalOpen && (!$todayAttendance || $todayAttendance->status === 'NOT_CHECKED_IN'))
            <div class="clay-card p-5 rounded-2xl bg-amber-50/80 border border-amber-200 text-xs text-amber-900 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold shrink-0 shadow-2xs">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <span class="font-extrabold text-sm block text-[#29283D]">Loket Operasional Sedang TUTUP</span>
                        <p class="text-[11px] text-[#77768C] mt-0.5">Loket kasir operasional belum dibuka oleh staf/admin. Pemandu baru dapat check-in setelah loket dibuka.</p>
                    </div>
                </div>
                <button type="button" disabled class="clay-btn bg-slate-200 text-slate-400 border-slate-300 py-2.5 px-4 text-xs font-bold cursor-not-allowed shrink-0">
                    LOKET TUTUP
                </button>
            </div>
            @elseif(!$todayAttendance || $todayAttendance->status === 'NOT_CHECKED_IN')
            <form action="{{ route('guide.check-in') }}" method="POST">
                @csrf
                <input type="hidden" name="latitude" x-model="gpsLatitude">
                <input type="hidden" name="longitude" x-model="gpsLongitude">
                <div class="clay-card p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-[#6C63FF]/30 bg-gradient-to-r from-white via-[#F4F3FA] to-[#ECEBFC]/60 transition-all hover:shadow-md hover:border-[#6C63FF]/50 group">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-[#6C63FF] text-white flex items-center justify-center shrink-0 shadow-md shadow-[#6C63FF]/30 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-sm font-black text-[#29283D] flex items-center gap-1.5 group-hover:text-[#6C63FF] transition-colors">
                                <span>ABSEN MASUK (CHECK-IN)</span>
                                <span class="text-base">&rarr;</span>
                            </span>
                            <p class="text-[11px] text-[#77768C] mt-0.5">Setelah check-in, Anda aktif menerima antrean penugasan tour.</p>
                        </div>
                    </div>

                    <button type="submit"
                        class="clay-btn clay-btn-primary py-2.5 px-5 text-xs font-black tracking-wide shrink-0 shadow-md">
                        Check-in Sekarang
                    </button>
                </div>
            </form>
            @elseif($todayAttendance->status === 'CHECKED_IN')
            <div class="space-y-3">
                @if($activeTour)
                <div class="p-3.5 rounded-2xl bg-[#FFD166]/20 border border-[#FFD166]/50 text-[#8a6800] text-xs flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-[#FF8A65] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <span class="font-bold block text-[#29283D]">Peringatan Tour Sedang Aktif</span>
                        <span class="text-[11px]">Anda masih memiliki tour aktif. Selesaikan tugas tour terlebih dahulu sebelum checkout.</span>
                    </div>
                </div>
                @endif

                <form action="{{ route('guide.check-out') }}" method="POST" data-confirm="Apakah Anda yakin ingin checkout (selesai bertugas) sekarang?" data-confirm-title="Konfirmasi Check-out" data-confirm-icon="question" data-confirm-btn="Ya, Check-out!">
                    @csrf
                    <input type="hidden" name="latitude" x-model="gpsLatitude">
                    <input type="hidden" name="longitude" x-model="gpsLongitude">
                    <div class="clay-card p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-[#72D6C9]/40 bg-gradient-to-r from-white via-[#EBFBF8]/50 to-[#EBFBF8]">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-[#72D6C9] text-white flex items-center justify-center shrink-0 shadow-md shadow-[#72D6C9]/30">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-sm font-black text-[#147a6f] flex items-center gap-1.5">
                                    <span>STATUS: AKTIF BERTUGAS (CHECKED-IN)</span>
                                </span>
                                <p class="text-[11px] text-[#77768C] mt-0.5">Klik tombol checkout jika Anda sudah selesai seluruh penugasan hari ini.</p>
                            </div>
                        </div>

                        <button type="submit" {{ $activeTour ? 'disabled' : '' }}
                            class="clay-btn py-2.5 px-5 {{ $activeTour ? 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none' : 'clay-btn-secondary text-[#29283D]' }} text-xs font-bold tracking-wide shrink-0">
                            ABSEN KELUAR (CHECK-OUT) &rarr;
                        </button>
                    </div>
                </form>
            </div>
            @elseif($todayAttendance->status === 'CHECKED_OUT')
            <div class="clay-card p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-[#E8E6F0]">
                <div>
                    <span class="font-extrabold text-sm text-[#29283D] block">Status: Selesai Checkout</span>
                    <span class="text-[11px] text-[#77768C]">Checkout terakhir tercatat pukul {{ $todayAttendance->check_out_at?->format('H:i') }} WITA. Anda dapat check-in kembali kapan saja.</span>
                </div>

                @if($isOperationalOpen)
                <form action="{{ route('guide.check-in') }}" method="POST" class="shrink-0">
                    @csrf
                    <input type="hidden" name="latitude" x-model="gpsLatitude">
                    <input type="hidden" name="longitude" x-model="gpsLongitude">
                    <button type="submit" class="clay-btn clay-btn-primary py-2.5 px-4 text-xs font-bold flex items-center gap-1.5 shadow-md">
                        <span>Check-in Masuk Lagi &rarr;</span>
                    </button>
                </form>
                @else
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-[#77768C] shrink-0">Loket Tutup</span>
                @endif
            </div>
            @endif

            <!-- 3 Stat Cards Row (Matching Screenshot 2) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Job Hari Ini -->
                <div class="clay-card p-4 sm:p-5 flex items-center justify-between border border-[#E8E6F0] hover:border-[#6C63FF]/30 transition-all">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-[#ECEBFC] text-[#6C63FF] flex items-center justify-center shrink-0 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-[10px] text-[#77768C] block font-extrabold uppercase tracking-wider">JOB HARI INI</span>
                            <span class="text-2xl font-black text-[#29283D] font-display leading-tight block mt-0.5">{{ $todayJobsCount }}</span>
                        </div>
                    </div>
                    <a href="{{ route('guide.jobs') }}" class="text-[#77768C] hover:text-[#6C63FF] text-xs font-bold transition">
                        &rarr;
                    </a>
                </div>

                <!-- Tamu Ditangani -->
                <div class="clay-card p-4 sm:p-5 flex items-center justify-between border border-[#E8E6F0] hover:border-[#72D6C9]/40 transition-all">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-[#EBFBF8] text-[#147a6f] flex items-center justify-center shrink-0 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-[10px] text-[#77768C] block font-extrabold uppercase tracking-wider">TAMU DITANGANI</span>
                            <span class="text-2xl font-black text-[#147a6f] font-display leading-tight block mt-0.5">{{ $todayGuestsCount }} pax</span>
                        </div>
                    </div>
                    <a href="{{ route('guide.jobs') }}" class="text-[#77768C] hover:text-[#147a6f] text-xs font-bold transition">
                        &rarr;
                    </a>
                </div>

                <!-- Estimasi Fee -->
                <div class="clay-card p-4 sm:p-5 flex items-center justify-between border border-[#E8E6F0] hover:border-[#FF8A65]/40 transition-all">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-[#FFF0EB] text-[#FF8A65] flex items-center justify-center shrink-0 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-[10px] text-[#77768C] block font-extrabold uppercase tracking-wider">ESTIMASI FEE</span>
                            <span class="text-lg font-black text-[#FF8A65] font-display leading-tight block mt-0.5">Rp {{ number_format($todayEarnings, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <a href="{{ route('guide.earnings') }}" class="text-[#77768C] hover:text-[#FF8A65] text-xs font-bold transition">
                        &rarr;
                    </a>
                </div>
            </div>

            <!-- Papan Antrean & Rekan Guide ON Hari Ini -->
            <div class="clay-card p-5 space-y-4 border border-[#E8E6F0]">
                <div class="flex items-center justify-between pb-3 border-b border-[#E8E6F0]">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-2xl bg-[#6C63FF] text-white flex items-center justify-center font-black text-sm shadow-md shadow-[#6C63FF]/25">
                            #
                        </div>
                        <div>
                            <h3 class="text-xs font-extrabold uppercase tracking-wider text-[#29283D]">ANTREAN & REKAN GUIDE ON</h3>
                            <p class="text-[11px] text-[#77768C] mt-0.5">{{ $onGuides->count() }} Hadir ({{ $standbyCount }} Standby, {{ $onTourCount }} On Tour)</p>
                        </div>
                    </div>
                    <a href="{{ route('guide.queue') }}" class="text-xs font-bold text-[#6C63FF] hover:text-[#554cd8] flex items-center gap-1 transition">
                        <span>Papan Lengkap</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <!-- Preview list of on-duty guides -->
                <div class="space-y-2.5">
                    @forelse($onGuides->take(5) as $og)
                    <div class="flex items-center justify-between p-3 rounded-2xl {{ $og->is_me ? 'bg-[#6C63FF]/8 border border-[#6C63FF]/30 ring-2 ring-[#6C63FF]/15' : 'bg-[#F4F3FA] border border-[#E8E6F0]/80' }} text-xs transition">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-2xl {{ $og->is_next ? 'bg-[#FFD166] text-[#29283D] shadow-sm' : 'bg-[#6C63FF] text-white' }} font-black text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                #{{ $og->queue_number }}
                            </span>
                            <div>
                                <div class="font-extrabold text-[#29283D] flex items-center gap-1.5">
                                    <span>{{ $og->nama }}</span>
                                    @if($og->is_me)
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-black bg-[#6C63FF] text-white">Anda</span>
                                    @endif
                                    @if($og->is_next)
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-black bg-[#FFD166] text-[#29283D]">Berikutnya</span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-[#77768C]">Masuk: {{ $og->check_in_time }} WITA</span>
                            </div>
                        </div>

                        <div>
                            @if($og->is_on_tour)
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">On Tour</span>
                            @elseif($og->is_available)
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-[#72D6C9]/20 text-[#147a6f] border border-[#72D6C9]/40">Standby</span>
                            @else
                            <span class="px-3 py-1 rounded-full text-[10px] font-medium bg-slate-100 text-[#77768C]">Selesai</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-[#77768C] text-xs bg-[#F4F3FA] rounded-2xl border border-dashed border-[#E8E6F0] flex flex-col items-center justify-center gap-2">
                        <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span>Belum ada guide yang check-in hari ini.</span>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Jadwal Tugas Hari Ini -->
            <div class="space-y-3">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-[#29283D]">Jadwal Tugas Hari Ini</h3>
                    <span class="text-[11px] text-[#77768C] font-semibold">
                        {{ $todayAssignments->count() }} Tugas
                        @php
                        $pendingCount = $todayAssignments->whereIn('status', ['ON_GOING', 'ASSIGNED'])->count();
                        @endphp
                        @if($pendingCount > 0)
                        &bull; <span class="text-[#6C63FF] font-bold">{{ $pendingCount }} Belum Selesai</span>
                        @endif
                    </span>
                </div>

                @forelse($todayAssignments as $job)
                <div class="clay-card p-5 space-y-3.5 border border-[#E8E6F0] {{ $job->status === 'ON_GOING' ? 'ring-2 ring-[#6C63FF]/40 border-[#6C63FF]/30' : ($job->status === 'COMPLETED' ? 'opacity-85 bg-white/75' : '') }}">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] font-mono font-bold text-[#6C63FF] bg-[#ECEBFC] px-2.5 py-1 rounded-lg">
                                {{ $job->booking->booking_code }}
                            </span>
                            <h4 class="text-sm font-extrabold text-[#29283D] mt-1.5">{{ $job->booking->customer_name }}</h4>
                            <p class="text-xs text-[#77768C] mt-0.5">{{ $job->booking->tourPackage?->name ?? 'Custom Tour' }}</p>
                        </div>

                        <div class="text-right">
                            @if($job->status === 'COMPLETED')
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-[#77768C]">Selesai</span>
                            @elseif($job->status === 'ON_GOING')
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 animate-pulse">Sedang Berlangsung</span>
                            @else
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-[#ECEBFC] text-[#6C63FF] border border-[#6C63FF]/20">Siap Mulai</span>
                            @endif
                        </div>
                    </div>

                    <!-- Job Details -->
                    <div class="grid grid-cols-2 gap-2.5 text-xs bg-[#F4F3FA] p-3.5 rounded-2xl border border-[#E8E6F0]/80">
                        <div>
                            <span class="text-[10px] text-[#77768C] block">Jumlah Tamu:</span>
                            <span class="font-extrabold text-[#29283D]">{{ $job->guest_count }} orang</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-[#77768C] block">Jam Tour:</span>
                            <span class="font-semibold text-[#29283D]">{{ substr($job->booking->start_time, 0, 5) }} - {{ substr($job->booking->end_time, 0, 5) }} WITA</span>
                        </div>
                        <div class="col-span-2 pt-2 border-t border-[#E8E6F0] flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-[#77768C] block">Lokasi:</span>
                                <span class="font-medium text-[#29283D] text-[11px]">{{ $job->booking->pickup_location ?? 'Lobby' }} &bull; {{ $job->booking->location }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-[#77768C] block">Hak Fee Anda:</span>
                                <span class="font-bold text-[#FF8A65] text-xs">Rp {{ number_format($job->fee_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 pt-1">
                        @if($job->status === 'ASSIGNED')
                        <form action="{{ route('guide.start-tour', $job) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit"
                                class="clay-btn clay-btn-primary w-full py-2.5 px-3 text-xs font-bold">
                                <svg class="w-3.5 h-3.5 inline-block mr-1 -mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4.5 3.5l11 6.5-11 6.5V3.5z" />
                                </svg>MULAI TUR (START TOUR)
                            </button>
                        </form>
                        @elseif($job->status === 'ON_GOING')
                        <form action="{{ route('guide.complete-tour', $job) }}" method="POST" class="flex-1" data-confirm="Apakah Anda yakin seluruh kegiatan tour ini telah selesai?" data-confirm-title="Selesaikan Tur" data-confirm-icon="question" data-confirm-btn="Ya, Selesaikan!">
                            @csrf
                            <button type="submit"
                                class="clay-btn clay-btn-turquoise w-full py-2.5 px-3 text-xs font-bold text-[#147a6f]">
                                <svg class="w-3.5 h-3.5 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>SELESAIKAN TUR (COMPLETE)
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <div class="clay-card p-6 text-center text-[#77768C] text-xs border border-[#E8E6F0]">
                    Belum ada jadwal penugasan tour untuk Anda hari ini.
                </div>
                @endforelse
            </div>
</div>
@endsection