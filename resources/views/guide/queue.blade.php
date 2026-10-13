@extends('layouts.guide')

@section('title', 'Papan Antrean & Guide ON')

@section('content')
<div class="space-y-4" x-data="{ filter: 'all' }">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-extrabold text-[#29283D] font-display">Papan Antrean Guide</h2>
            <p class="text-xs text-[#77768C]">Pantau posisi antrean Anda & status rekan kerja yang sedang ON</p>
        </div>
        <div class="text-right">
            <span class="text-[10px] text-[#77768C] block font-medium">Tanggal:</span>
            <span class="text-xs font-bold text-[#29283D]">{{ \Carbon\Carbon::parse($today)->format('d M Y') }}</span>
        </div>
    </div>

    <!-- My Realtime Queue Status Card -->
    <div class="clay-card-primary p-5 relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-36 h-36 rounded-full bg-white/10 pointer-events-none blur-xl"></div>
        <div class="absolute -left-10 -top-10 w-28 h-28 rounded-full bg-[#FFD166]/15 pointer-events-none blur-lg"></div>

        <div class="flex items-start justify-between relative z-10">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-white/80">STATUS ANTREAN ANDA</span>
                <div class="text-2xl font-black mt-0.5 flex items-center gap-2 font-display text-white">
                    <span>#{{ $guide->queue_number }}</span>
                    <span class="text-sm font-semibold text-white/90">({{ $guide->nama }})</span>
                </div>
            </div>

            @if($isMyTurnNext)
                <span class="px-3 py-1 rounded-full text-xs font-black bg-[#FFD166] text-[#29283D] shadow-md flex items-center gap-1 animate-bounce">
                    <svg class="w-3.5 h-3.5 fill-current inline-block mr-1 -mt-0.5" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg><span>GILIRAN BERIKUTNYA!</span>
                </span>
            @elseif($myInfo && $myInfo->is_on_tour)
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/20 text-white border border-white/30 backdrop-blur-xs">
                    Sedang On Tour
                </span>
            @elseif($myInfo && $myInfo->is_available)
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#72D6C9]/25 text-[#72D6C9] border border-[#72D6C9]/40 backdrop-blur-xs">
                    Standby (Antrean #{{ $guidesAheadCount + 1 }})
                </span>
            @elseif($myInfo && $myInfo->is_checked_out)
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/15 text-white/70">
                    Sudah Check-out
                </span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#FF8A65]/30 text-white border border-[#FF8A65]/50">
                    Belum Check-In
                </span>
            @endif
        </div>

        <div class="mt-4 pt-4 border-t border-white/15 grid grid-cols-2 gap-3 text-xs relative z-10">
            <div class="bg-white/15 rounded-2xl p-3 backdrop-blur-xs border border-white/10 shadow-inner">
                <span class="text-[10px] text-white/80 block font-medium">Penugasan Terakhir:</span>
                <span class="font-extrabold text-sm text-white">Posisi #{{ $lastPosition }}</span>
            </div>

            <div class="bg-white/15 rounded-2xl p-3 backdrop-blur-xs border border-white/10 shadow-inner">
                <span class="text-[10px] text-white/80 block font-medium">Giliran Terdepan Saat Ini:</span>
                <span class="font-extrabold text-sm text-[#FFD166]">
                    @if($nextGuide)
                        #{{ $nextGuide->queue_number }} {{ $nextGuide->nama }}
                    @else
                        Belum ada guide siap
                    @endif
                </span>
            </div>
        </div>

        @if($isMyTurnNext)
            <div class="mt-3.5 p-3 rounded-2xl bg-[#FFD166]/25 border border-[#FFD166]/50 text-white text-xs flex items-center gap-2 backdrop-blur-xs">
                <svg class="w-4 h-4 shrink-0 text-[#FFD166]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span><strong>Siap-siap!</strong> Anda berada di posisi terdepan untuk menerima penugasan tour berikutnya.</span>
            </div>
        @elseif($myInfo && $myInfo->is_available && $guidesAheadCount > 0)
            <div class="mt-3.5 p-3 rounded-2xl bg-white/15 text-white text-xs flex items-center gap-2 backdrop-blur-xs border border-white/10">
                <svg class="w-4 h-4 shrink-0 text-white/80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Ada <strong>{{ $guidesAheadCount }} guide</strong> lagi sebelum giliran Anda tiba.</span>
            </div>
        @elseif(!($todayAttendance ?? null) || ($todayAttendance->status ?? null) !== 'CHECKED_IN')
            <div class="mt-3.5 p-3 rounded-2xl bg-white/20 border border-white/30 text-white text-xs flex items-center justify-between backdrop-blur-xs">
                <span class="font-medium">Check-in sekarang agar masuk ke dalam antrean harian.</span>
                <a href="{{ route('guide.dashboard') }}" class="px-3 py-1 bg-[#FFD166] text-[#29283D] rounded-xl font-bold text-[11px] shadow-sm hover:scale-105 transition">
                    Check-in
                </a>
            </div>
        @endif
    </div>

    <!-- Live Guide Status Counters -->
    <div class="grid grid-cols-3 gap-2.5 text-center text-xs">
        <button type="button" @click="filter = 'all'"
                :class="filter === 'all' ? 'border-[#6C63FF] bg-[#6C63FF]/10 text-[#6C63FF] shadow-sm ring-2 ring-[#6C63FF]/20' : 'bg-white text-[#77768C] border-[#E8E6F0] shadow-2xs'"
                class="p-3 rounded-2xl border transition-all duration-200">
            <span class="text-[10px] text-[#77768C] block font-bold uppercase tracking-wider">Semua ON</span>
            <span class="text-lg font-extrabold font-display block mt-0.5">{{ $onGuides->count() }}</span>
        </button>

        <button type="button" @click="filter = 'standby'"
                :class="filter === 'standby' ? 'border-[#72D6C9] bg-[#72D6C9]/15 text-[#147a6f] shadow-sm ring-2 ring-[#72D6C9]/25' : 'bg-white text-[#77768C] border-[#E8E6F0] shadow-2xs'"
                class="p-3 rounded-2xl border transition-all duration-200">
            <span class="text-[10px] text-[#77768C] block font-bold uppercase tracking-wider">Standby (Siap)</span>
            <span class="text-lg font-extrabold text-[#147a6f] font-display block mt-0.5">{{ $standbyCount }}</span>
        </button>

        <button type="button" @click="filter = 'ontour'"
                :class="filter === 'ontour' ? 'border-blue-400 bg-blue-50 text-blue-700 shadow-sm ring-2 ring-blue-300/30' : 'bg-white text-[#77768C] border-[#E8E6F0] shadow-2xs'"
                class="p-3 rounded-2xl border transition-all duration-200">
            <span class="text-[10px] text-[#77768C] block font-bold uppercase tracking-wider">Sedang Tour</span>
            <span class="text-lg font-extrabold text-blue-700 font-display block mt-0.5">{{ $onTourCount }}</span>
        </button>
    </div>

    <!-- Active Guides List -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#29283D]">Daftar Rekan Guide Hari Ini</h3>
            <span class="text-[10px] text-[#77768C] font-semibold">Total: {{ $guideCards->count() }} Terdaftar</span>
        </div>

        <div class="space-y-2.5">
            @forelse($guideCards as $row)
            <div x-show="filter === 'all' || (filter === 'standby' && {{ $row->is_available ? 'true' : 'false' }}) || (filter === 'ontour' && {{ $row->is_on_tour ? 'true' : 'false' }})"
                 class="clay-card p-4 transition-all duration-200 {{ $row->is_me ? 'ring-2 ring-[#6C63FF]/30 border-[#6C63FF]/40 bg-[#6C63FF]/5' : '' }}">
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl {{ $row->is_next ? 'bg-[#FFD166] text-[#29283D] shadow-sm' : ($row->is_checked_in ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/20' : 'bg-slate-100 text-slate-400') }} flex items-center justify-center font-extrabold text-sm shrink-0">
                            #{{ $row->queue_number }}
                        </div>

                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-sm text-[#29283D] font-display">{{ $row->nama }}</span>
                                @if($row->is_me)
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-[#6C63FF] text-white">Anda</span>
                                @endif
                                @if($row->is_next)
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-[#FFD166] text-[#29283D] border border-[#FFD166]/60">Giliran Berikutnya</span>
                                @endif
                            </div>

                            <div class="text-[11px] text-[#77768C] flex items-center gap-2 mt-0.5">
                                <span class="font-mono text-[10px]">{{ $row->kode_guide }}</span>
                                @if($row->is_checked_in)
                                    <span>•</span>
                                    <span class="text-[#147a6f] font-semibold">Masuk: {{ $row->check_in_time }} WITA</span>
                                @elseif($row->is_checked_out)
                                    <span>•</span>
                                    <span class="text-[#77768C]">Keluar: {{ $row->check_out_time }} WITA</span>
                                @else
                                    <span>•</span>
                                    <span class="text-slate-400">Belum Check-In</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div class="text-right">
                        @if($row->is_on_tour)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                Sedang Tour
                            </span>
                        @elseif($row->is_available)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#72D6C9]/20 text-[#147a6f] border border-[#72D6C9]/40">
                                Standby (Siap)
                            </span>
                        @elseif($row->is_checked_out)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-[#77768C]">
                                Selesai
                            </span>
                        @elseif($row->status === 'OFF')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                Libur (OFF)
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-[#FFD166]/20 text-[#8a6800] border border-[#FFD166]/40">
                                Belum Hadir
                            </span>
                        @endif

                        <div class="text-[10px] text-[#77768C] mt-1 font-medium">
                            {{ $row->today_jobs }} tur • {{ $row->today_guests }} pax
                        </div>
                    </div>
                </div>

                @if($row->nomor_telepon && !$row->is_me)
                <div class="mt-3 pt-2.5 border-t border-[#E8E6F0] flex items-center justify-between text-[11px]">
                    <span class="text-[#77768C]">Kontak Koordinasi:</span>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $row->nomor_telepon) }}" target="_blank"
                       class="text-[#6C63FF] hover:text-[#554cd8] font-bold flex items-center gap-1 transition">
                        <span>WhatsApp ({{ $row->nomor_telepon }})</span>
                    </a>
                </div>
                @endif
            </div>
            @empty
            <div class="clay-card p-6 text-center text-[#77768C] text-xs">
                Belum ada data guide.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

