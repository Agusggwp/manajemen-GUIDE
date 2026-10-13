@extends('layouts.guide')

@section('title', 'Guide Dashboard')

@section('content')
<div class="space-y-4" x-data="{
    gpsLatitude: null,
    gpsLongitude: null,
    gpsLoading: false,
    gpsCaptured: false,
    walkInModalOpen: false,

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
    <div class="clay-card p-4 border border-rose-300 bg-rose-50/90 text-rose-900">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-2xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-md">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
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

    <!-- Check-in & Attendance Status Card (Requirement 35 & 36) -->
    <div class="clay-card p-5">
        <div class="flex items-center justify-between pb-3.5 border-b border-[#E8E6F0]">
            <div>
                <span class="text-[10px] text-[#77768C] block font-bold uppercase tracking-wider">STATUS KEHADIRAN HARI INI</span>
                <h3 class="text-base font-extrabold text-[#29283D] font-display">{{ \Carbon\Carbon::parse($today)->format('l, d F Y') }}</h3>
            </div>

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
            @elseif($todayAttendance && $todayAttendance->status === 'CHECKED_OUT')
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-[#77768C] border border-[#E8E6F0]">
                    SUDAH CHECK-OUT
                </span>
            @elseif($guide->status === 'OFF')
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                    LIBUR (OFF)
                </span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#FFD166]/20 text-[#8a6800] border border-[#FFD166]/60">
                    BELUM CHECK-IN
                </span>
            @endif
        </div>

        <div class="mt-3.5 grid grid-cols-2 gap-2.5 text-xs">
            <div class="p-3 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0]/80">
                <span class="text-[10px] text-[#77768C] block font-medium">Nomor Antrean Anda:</span>
                <span class="text-lg font-black text-[#6C63FF] font-display">Posisi #{{ $guide->queue_number }}</span>
            </div>

            <div class="p-3 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0]/80">
                <span class="text-[10px] text-[#77768C] block font-medium">Status Giliran Saat Ini:</span>
                @if($guide->isSuspended())
                    <span class="text-xs font-bold text-rose-700">Ditangguhkan</span>
                @elseif($isMyTurnNext)
                    <span class="text-xs font-extrabold text-[#6C63FF] flex items-center gap-1">
                        <span>⭐</span> Giliran Anda!
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

            <div class="p-3 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0]/80">
                <span class="text-[10px] text-[#77768C] block font-medium">Jam Masuk (Check-in):</span>
                <span class="text-xs font-bold text-[#29283D]">
                    {{ $todayAttendance?->check_in_at ? $todayAttendance->check_in_at->format('H:i') . ' WITA' : '-' }}
                </span>
            </div>

            <div class="p-3 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0]/80">
                <span class="text-[10px] text-[#77768C] block font-medium">Giliran Terdepan:</span>
                <span class="text-xs font-bold text-[#6C63FF] truncate block">
                    {{ $nextGuide ? '#' . $nextGuide->queue_number . ' ' . $nextGuide->nama : 'Belum Ada' }}
                </span>
            </div>
        </div>

        <!-- Check-in / Checkout Action Buttons -->
        <div class="mt-4 pt-3.5 border-t border-[#E8E6F0]">
            @if($guide->isSuspended())
                <div class="text-center p-3 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-800">
                    <span class="font-bold block mb-0.5">Absen Masuk Dinonaktifkan</span>
                    Akun Anda sedang ditangguhkan. Silakan hubungi admin operasional untuk info lebih lanjut.
                </div>
            @elseif(!$todayAttendance || $todayAttendance->status === 'NOT_CHECKED_IN')
                <form action="{{ route('guide.check-in') }}" method="POST">
                    @csrf
                    <input type="hidden" name="latitude" x-model="gpsLatitude">
                    <input type="hidden" name="longitude" x-model="gpsLongitude">
                    <button type="submit" 
                            class="clay-btn clay-btn-primary w-full py-3.5 px-4 text-xs font-bold tracking-wide flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 text-[#FFD166]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>ABSEN MASUK (CHECK-IN)</span>
                    </button>
                    <span class="text-[10px] text-[#77768C] text-center block mt-2">
                        Setelah check-in, Anda aktif menerima antrean penugasan tour.
                    </span>
                </form>
            @elseif($todayAttendance->status === 'CHECKED_IN')
                @if($activeTour)
                <!-- Warning per Requirement 6 & 36 -->
                <div class="p-3 mb-3 rounded-2xl bg-[#FFD166]/20 border border-[#FFD166]/50 text-[#8a6800] text-xs">
                    <div class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#FF8A65]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <span>Peringatan Tour Aktif</span>
                    </div>
                    <p class="text-[11px] mt-0.5">Anda masih memiliki tour aktif. Selesaikan tour terlebih dahulu sebelum checkout.</p>
                </div>
                @endif

                <form action="{{ route('guide.check-out') }}" method="POST" data-confirm="Apakah Anda yakin ingin checkout (selesai bertugas) sekarang?" data-confirm-title="Konfirmasi Check-out" data-confirm-icon="question" data-confirm-btn="Ya, Check-out!">
                    @csrf
                    <input type="hidden" name="latitude" x-model="gpsLatitude">
                    <input type="hidden" name="longitude" x-model="gpsLongitude">
                    <button type="submit" {{ $activeTour ? 'disabled' : '' }}
                            class="clay-btn w-full py-3.5 px-4 {{ $activeTour ? 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none' : 'clay-btn-secondary text-[#29283D]' }} text-xs font-bold tracking-wide">
                        ABSEN KELUAR (CHECK-OUT)
                    </button>
                </form>
            @elseif($todayAttendance->status === 'CHECKED_OUT')
                <!-- Guide has checked out, but can check in again anytime (no daily limits) -->
                <div class="space-y-3">
                    <div class="p-3 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0] text-xs text-[#29283D] flex items-center justify-between">
                        <div>
                            <span class="font-bold text-[#29283D] block">Selesai Checkout</span>
                            <span class="text-[11px] text-[#77768C]">Checkout terakhir pukul {{ $todayAttendance->check_out_at?->format('H:i') }} WITA</span>
                        </div>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-slate-200 text-[#77768C]">Nonaktif Sementara</span>
                    </div>

                    <form action="{{ route('guide.check-in') }}" method="POST">
                        @csrf
                        <input type="hidden" name="latitude" x-model="gpsLatitude">
                        <input type="hidden" name="longitude" x-model="gpsLongitude">
                        <button type="submit" 
                                class="clay-btn clay-btn-primary w-full py-3.5 px-4 text-xs font-bold tracking-wide flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-[#FFD166]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>ABSEN MASUK KEMBALI (CHECK-IN LAGI)</span>
                        </button>
                        <span class="text-[10px] text-[#77768C] text-center block mt-2">
                            Anda dapat check-in kembali kapan saja tanpa batasan per hari untuk menerima penugasan tour baru.
                        </span>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <!-- Summary of Work Today (Requirement 36) -->
    <div class="grid grid-cols-3 gap-2.5">
        <div class="clay-card p-3.5 text-center">
            <span class="text-[10px] text-[#77768C] block font-bold uppercase tracking-wider">Job Hari Ini</span>
            <span class="text-xl font-extrabold text-[#29283D] font-display mt-0.5 block">{{ $todayJobsCount }}</span>
        </div>
        <div class="clay-card p-3.5 text-center">
            <span class="text-[10px] text-[#77768C] block font-bold uppercase tracking-wider">Tamu Ditangani</span>
            <span class="text-xl font-extrabold text-[#6C63FF] font-display mt-0.5 block">{{ $todayGuestsCount }} pax</span>
        </div>
        <div class="clay-card p-3.5 text-center">
            <span class="text-[10px] text-[#77768C] block font-bold uppercase tracking-wider">Estimasi Fee</span>
            <span class="text-xs font-extrabold text-[#FF8A65] block mt-1">Rp {{ number_format($todayEarnings, 0, ',', '.') }}</span>
        </div>
    </div>
 
    @if(!$guide->isSuspended() && $todayAttendance && $todayAttendance->status === 'CHECKED_IN' && !$activeTour)
    <!-- Walk-in Quick Tour Launch for Guides -->
    <div class="clay-card p-4 bg-gradient-to-r from-white to-[#FFD166]/10 border border-[#FFD166]/40">
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-1.5 font-bold text-xs text-[#29283D]">
                    <span class="w-6 h-6 rounded-lg bg-[#FFD166]/30 text-[#8a6800] flex items-center justify-center text-xs">⚡</span>
                    <span>Ada Tamu Langsung (Walk-In)?</span>
                </div>
                <p class="text-[11px] text-[#77768C] mt-0.5">Mulai tour seketika tanpa perlu reservasi atau booking admin.</p>
            </div>
            <button type="button" @click="walkInModalOpen = true" 
                    class="clay-btn clay-btn-coral py-2 px-3.5 text-xs font-bold shrink-0 flex items-center gap-1">
                <span>⚡ Mulai Tour</span>
            </button>
        </div>
    </div>
    @endif

    <!-- Papan Antrean & Rekan Guide yang ON Hari Ini -->
    <div class="clay-card p-4 space-y-3">
        <div class="flex items-center justify-between pb-2.5 border-b border-[#E8E6F0]">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#6C63FF] to-[#554cd8] text-white flex items-center justify-center font-bold text-xs shadow-md shadow-[#6C63FF]/20">
                    #
                </div>
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#29283D]">Antrean & Rekan Guide ON</h3>
                    <p class="text-[10px] text-[#77768C]">{{ $onGuides->count() }} Hadir ({{ $standbyCount }} Standby, {{ $onTourCount }} On Tour)</p>
                </div>
            </div>
            <a href="{{ route('guide.queue') }}" class="text-[11px] font-bold text-[#6C63FF] hover:text-[#554cd8] flex items-center gap-1 transition">
                <span>Papan Lengkap</span>
                <span>&rarr;</span>
            </a>
        </div>

        <!-- Preview list of on-duty guides -->
        <div class="space-y-2">
            @forelse($onGuides->take(4) as $og)
            <div class="flex items-center justify-between p-2.5 rounded-2xl {{ $og->is_me ? 'bg-[#6C63FF]/8 border border-[#6C63FF]/30 ring-2 ring-[#6C63FF]/15' : 'bg-[#F4F3FA] border border-[#E8E6F0]/80' }} text-xs transition">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl {{ $og->is_next ? 'bg-[#FFD166] text-[#29283D] shadow-sm' : 'bg-[#6C63FF] text-white' }} font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                        #{{ $og->queue_number }}
                    </span>
                    <div>
                        <div class="font-bold text-[#29283D] flex items-center gap-1.5">
                            <span>{{ $og->nama }}</span>
                            @if($og->is_me)
                                <span class="px-1.5 py-0.2 rounded-md text-[9px] font-bold bg-[#6C63FF] text-white">Anda</span>
                            @endif
                            @if($og->is_next)
                                <span class="px-1.5 py-0.2 rounded-md text-[9px] font-extrabold bg-[#FFD166] text-[#29283D]">⭐ Berikutnya</span>
                            @endif
                        </div>
                        <span class="text-[10px] text-[#77768C]">Masuk: {{ $og->check_in_time }} WITA</span>
                    </div>
                </div>

                <div>
                    @if($og->is_on_tour)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">On Tour</span>
                    @elseif($og->is_available)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#72D6C9]/20 text-[#147a6f] border border-[#72D6C9]/40">Standby</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-[#77768C]">Selesai</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-4 text-center text-[#77768C] text-xs bg-[#F4F3FA] rounded-2xl border border-dashed border-[#E8E6F0]">
                Belum ada guide yang check-in hari ini.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Today's Job List (Requirement 34) -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#29283D]">Jadwal Tugas Hari Ini</h3>
            <span class="text-[10px] text-[#77768C] font-semibold">{{ $todayAssignments->count() }} Tugas</span>
        </div>

        @forelse($todayAssignments as $job)
        <div class="clay-card p-4 space-y-3 {{ $job->status === 'ON_GOING' ? 'ring-2 ring-[#6C63FF]/40 border-[#6C63FF]/30' : '' }}">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-mono font-bold text-[#6C63FF] bg-[#6C63FF]/10 px-2 py-0.5 rounded-md">
                        {{ $job->booking->booking_code }}
                    </span>
                    <h4 class="text-sm font-bold text-[#29283D] mt-1">{{ $job->booking->customer_name }}</h4>
                    <p class="text-[11px] text-[#77768C]">{{ $job->booking->tourPackage?->name ?? 'Custom Tour' }}</p>
                </div>

                <div class="text-right">
                    @if($job->status === 'COMPLETED')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-[#77768C]">Selesai</span>
                    @elseif($job->status === 'ON_GOING')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 animate-pulse">Sedang Berlangsung</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#6C63FF]/10 text-[#6C63FF] border border-[#6C63FF]/20">Siap Mulai</span>
                    @endif
                </div>
            </div>

            <!-- Job Details -->
            <div class="grid grid-cols-2 gap-2 text-xs bg-[#F4F3FA] p-3 rounded-2xl border border-[#E8E6F0]/80">
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
                        <span class="text-[10px] text-[#77768C] block">Lokasi & Pickup:</span>
                        <span class="font-medium text-[#29283D] text-[11px]">{{ $job->booking->pickup_location ?? 'Lobby' }} &bull; {{ $job->booking->location }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-[#77768C] block">Hak Fee Anda:</span>
                        <span class="font-bold text-[#FF8A65] text-xs">Rp {{ number_format($job->fee_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Start / Complete Tour Action Buttons (Requirement 34) -->
            <div class="flex items-center gap-2 pt-1">
                @if($job->status === 'ASSIGNED')
                <form action="{{ route('guide.start-tour', $job) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" 
                            class="clay-btn clay-btn-primary w-full py-2.5 px-3 text-xs font-bold">
                        ▶ MULAI TUR (START TOUR)
                    </button>
                </form>
                @elseif($job->status === 'ON_GOING')
                <form action="{{ route('guide.complete-tour', $job) }}" method="POST" class="flex-1" data-confirm="Apakah Anda yakin seluruh kegiatan tour ini telah selesai?" data-confirm-title="Selesaikan Tur" data-confirm-icon="question" data-confirm-btn="Ya, Selesaikan!">
                    @csrf
                    <button type="submit" 
                            class="clay-btn clay-btn-turquoise w-full py-2.5 px-3 text-xs font-bold text-[#147a6f]">
                        ✓ SELESAIKAN TUR (COMPLETE)
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="clay-card p-6 text-center text-[#77768C] text-xs">
            Belum ada jadwal penugasan tour untuk Anda hari ini.
        </div>
        @endforelse
    </div>

    <!-- Walk-in Tour Modal for Guide -->
    <div x-show="walkInModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-[#29283D]/40 backdrop-blur-sm transition-opacity" @click="walkInModalOpen = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white rounded-3xl p-6 clay-card space-y-4" @click.stop>
                <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3.5">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-2xl bg-[#FFD166]/30 text-[#8a6800] flex items-center justify-center font-bold text-sm shadow-xs">
                            ⚡
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-[#29283D] font-display">Mulai Tour Langsung</h3>
                            <p class="text-[11px] text-[#77768C]">Input tamu walk-in dan langsung mulai bertugas</p>
                        </div>
                    </div>
                    <button type="button" @click="walkInModalOpen = false" class="w-8 h-8 rounded-full bg-[#F4F3FA] text-[#77768C] hover:text-[#29283D] flex items-center justify-center text-lg font-bold">
                        &times;
                    </button>
                </div>

                <form action="{{ route('guide.direct-tour.store') }}" method="POST" class="space-y-3.5 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-[#29283D] mb-1">Pilih Paket Wisata <span class="text-[#FF8A65]">*</span></label>
                        <select name="tour_package_id" required
                                class="clay-input text-xs font-semibold">
                            @foreach($tourPackages as $pkg)
                                <option value="{{ $pkg->id }}">
                                    {{ $pkg->name }} (Rp {{ number_format($pkg->default_price, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-[#29283D] mb-1">Jumlah Tamu <span class="text-[#FF8A65]">*</span></label>
                            <input type="number" name="guest_count" value="2" min="1" max="99" required
                                   class="clay-input text-xs font-bold text-[#29283D]">
                        </div>
                        <div>
                            <label class="block font-bold text-[#29283D] mb-1">Nama Tamu / Grup</label>
                            <input type="text" name="customer_name" value="Tamu Walk-in" placeholder="Contoh: Tamu Walk-in"
                                   class="clay-input text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-[#29283D] mb-1">No. WhatsApp Tamu (Opsional)</label>
                        <input type="text" name="customer_phone" placeholder="08xxxxxxxxxx"
                               class="clay-input text-xs">
                    </div>

                    <div>
                        <label class="block font-bold text-[#29283D] mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" rows="2" placeholder="Catatan lokasi/rombongan"
                                  class="clay-input text-xs"></textarea>
                    </div>

                    <div class="p-3 bg-[#72D6C9]/15 border border-[#72D6C9]/40 rounded-2xl text-[11px] text-[#147a6f] space-y-1">
                        <div class="font-bold flex items-center gap-1">
                            <span>✓</span> Status Anda Langsung ON TOUR
                        </div>
                        <p class="text-[#147a6f]">Fee tour akan dihitung otomatis sesuai jumlah tamu dan masuk ke catatan pendapatan Anda.</p>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button type="button" @click="walkInModalOpen = false" 
                                class="clay-btn clay-btn-secondary flex-1 py-3 text-[#77768C] font-bold">
                            Batal
                        </button>
                        <button type="submit" 
                                class="clay-btn clay-btn-coral flex-1 py-3 font-bold flex items-center justify-center gap-1">
                            <span>⚡ Mulai Tour Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
