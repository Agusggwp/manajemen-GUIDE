@extends('layouts.guide')

@section('title', 'Papan Antrean & Guide ON')

@section('content')
<div class="space-y-4" x-data="{ filter: 'all' }">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-bold text-slate-900">Papan Antrean Guide</h2>
            <p class="text-xs text-slate-500">Pantau posisi antrean Anda & status rekan kerja yang sedang ON</p>
        </div>
        <div class="text-right">
            <span class="text-[10px] text-slate-400 block">Tanggal:</span>
            <span class="text-xs font-semibold text-slate-700">{{ \Carbon\Carbon::parse($today)->format('d M Y') }}</span>
        </div>
    </div>

    <!-- My Realtime Queue Status Card -->
    <div class="bg-gradient-to-br from-[#14433b] to-[#1c5d52] rounded-2xl p-5 text-white shadow-md relative overflow-hidden">
        <div class="absolute -right-6 -bottom-6 w-32 h-32 rounded-full bg-white/5 pointer-events-none"></div>

        <div class="flex items-start justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-300">STATUS ANTREAN ANDA</span>
                <div class="text-2xl font-extrabold mt-0.5 flex items-center gap-2">
                    <span>#{{ $guide->queue_number }}</span>
                    <span class="text-sm font-semibold text-emerald-100">({{ $guide->nama }})</span>
                </div>
            </div>

            @if($isMyTurnNext)
                <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-[#21c9a4] text-[#14433b] shadow-sm flex items-center gap-1 animate-bounce">
                    <span>⭐</span> GILIRAN BERIKUTNYA!
                </span>
            @elseif($myInfo && $myInfo->is_on_tour)
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-400/20 text-blue-200 border border-blue-300/30">
                    Sedang On Tour
                </span>
            @elseif($myInfo && $myInfo->is_available)
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-400/20 text-emerald-200 border border-emerald-400/30">
                    Standby (Antrean #{{ $guidesAheadCount + 1 }})
                </span>
            @elseif($myInfo && $myInfo->is_checked_out)
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-400/20 text-slate-300">
                    Sudah Check-out
                </span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-400/20 text-amber-200 border border-amber-300/30">
                    Belum Check-In
                </span>
            @endif
        </div>

        <div class="mt-4 pt-4 border-t border-white/10 grid grid-cols-2 gap-3 text-xs">
            <div class="bg-white/10 rounded-xl p-3 backdrop-blur-xs">
                <span class="text-[10px] text-emerald-200 block">Penugasan Terakhir:</span>
                <span class="font-bold text-sm text-white">Posisi #{{ $lastPosition }}</span>
            </div>

            <div class="bg-white/10 rounded-xl p-3 backdrop-blur-xs">
                <span class="text-[10px] text-emerald-200 block">Giliran Terdepan Saat Ini:</span>
                <span class="font-bold text-sm text-[#21c9a4]">
                    @if($nextGuide)
                        #{{ $nextGuide->queue_number }} {{ $nextGuide->nama }}
                    @else
                        Belum ada guide siap
                    @endif
                </span>
            </div>
        </div>

        @if($isMyTurnNext)
            <div class="mt-3.5 p-2.5 rounded-xl bg-[#21c9a4]/20 border border-[#21c9a4]/40 text-emerald-100 text-xs flex items-center gap-2">
                <span class="text-base">📢</span>
                <span><strong>Siap-siap!</strong> Anda berada di posisi terdepan untuk menerima penugasan tour berikutnya.</span>
            </div>
        @elseif($myInfo && $myInfo->is_available && $guidesAheadCount > 0)
            <div class="mt-3.5 p-2.5 rounded-xl bg-white/10 text-emerald-100 text-xs flex items-center gap-2">
                <span class="text-base">⏳</span>
                <span>Ada <strong>{{ $guidesAheadCount }} guide</strong> lagi sebelum giliran Anda tiba.</span>
            </div>
        @elseif(!($todayAttendance ?? null) || ($todayAttendance->status ?? null) !== 'CHECKED_IN')
            <div class="mt-3.5 p-2.5 rounded-xl bg-amber-400/20 border border-amber-300/30 text-amber-100 text-xs flex items-center justify-between">
                <span>Check-in sekarang agar masuk ke dalam antrean harian.</span>
                <a href="{{ route('guide.dashboard') }}" class="px-2.5 py-1 bg-amber-400 text-slate-900 rounded-lg font-bold text-[11px]">
                    Check-in
                </a>
            </div>
        @endif
    </div>

    <!-- Live Guide Status Counters -->
    <div class="grid grid-cols-3 gap-2 text-center text-xs">
        <button type="button" @click="filter = 'all'"
                :class="filter === 'all' ? 'border-[#14433b] bg-emerald-50 text-[#14433b] ring-1 ring-[#14433b]' : 'bg-white text-slate-600 border-slate-200'"
                class="p-2.5 rounded-xl border transition shadow-2xs">
            <span class="text-[10px] text-slate-400 block font-medium">Semua ON</span>
            <span class="text-base font-extrabold">{{ $onGuides->count() }}</span>
        </button>

        <button type="button" @click="filter = 'standby'"
                :class="filter === 'standby' ? 'border-[#14433b] bg-emerald-50 text-[#14433b] ring-1 ring-[#14433b]' : 'bg-white text-slate-600 border-slate-200'"
                class="p-2.5 rounded-xl border transition shadow-2xs">
            <span class="text-[10px] text-slate-400 block font-medium">Standby (Siap)</span>
            <span class="text-base font-extrabold text-emerald-700">{{ $standbyCount }}</span>
        </button>

        <button type="button" @click="filter = 'ontour'"
                :class="filter === 'ontour' ? 'border-[#14433b] bg-emerald-50 text-[#14433b] ring-1 ring-[#14433b]' : 'bg-white text-slate-600 border-slate-200'"
                class="p-2.5 rounded-xl border transition shadow-2xs">
            <span class="text-[10px] text-slate-400 block font-medium">Sedang Tour</span>
            <span class="text-base font-extrabold text-blue-700">{{ $onTourCount }}</span>
        </button>
    </div>

    <!-- Active Guides List -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Daftar Rekan Guide Hari Ini</h3>
            <span class="text-[10px] text-slate-400">Total: {{ $guideCards->count() }} Terdaftar</span>
        </div>

        <div class="space-y-2">
            @forelse($guideCards as $row)
            <div x-show="filter === 'all' || (filter === 'standby' && {{ $row->is_available ? 'true' : 'false' }}) || (filter === 'ontour' && {{ $row->is_on_tour ? 'true' : 'false' }})"
                 class="bg-white rounded-xl border {{ $row->is_me ? 'border-[#21c9a4] ring-2 ring-[#21c9a4]/40 bg-[#f4fbf9]' : 'border-slate-200' }} p-3.5 shadow-2xs transition">
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $row->is_next ? 'bg-[#21c9a4] text-[#14433b]' : ($row->is_checked_in ? 'bg-[#14433b] text-white' : 'bg-slate-100 text-slate-400') }} flex items-center justify-center font-bold text-sm shadow-xs shrink-0">
                            #{{ $row->queue_number }}
                        </div>

                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-sm text-slate-900">{{ $row->nama }}</span>
                                @if($row->is_me)
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-[#14433b] text-white">Anda</span>
                                @endif
                                @if($row->is_next)
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">⭐ Giliran Berikutnya</span>
                                @endif
                            </div>

                            <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                <span>{{ $row->kode_guide }}</span>
                                @if($row->is_checked_in)
                                    <span>•</span>
                                    <span class="text-emerald-700 font-medium">Masuk: {{ $row->check_in_time }} WITA</span>
                                @elseif($row->is_checked_out)
                                    <span>•</span>
                                    <span class="text-slate-500">Keluar: {{ $row->check_out_time }} WITA</span>
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
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                Sedang Tour
                            </span>
                        @elseif($row->is_available)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Standby (Siap)
                            </span>
                        @elseif($row->is_checked_out)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600">
                                Selesai
                            </span>
                        @elseif($row->status === 'OFF')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                Libur (OFF)
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                Belum Hadir
                            </span>
                        @endif

                        <div class="text-[10px] text-slate-400 mt-1">
                            {{ $row->today_jobs }} tur • {{ $row->today_guests }} pax
                        </div>
                    </div>
                </div>

                @if($row->nomor_telepon && !$row->is_me)
                <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span class="text-slate-500">Kontak Koordinasi:</span>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $row->nomor_telepon) }}" target="_blank"
                       class="text-emerald-700 hover:text-emerald-800 font-semibold flex items-center gap-1">
                        <span>WhatsApp ({{ $row->nomor_telepon }})</span>
                    </a>
                </div>
                @endif
            </div>
            @empty
            <div class="bg-white rounded-xl border border-slate-200 p-6 text-center text-slate-400 text-xs shadow-2xs">
                Belum ada data guide.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
