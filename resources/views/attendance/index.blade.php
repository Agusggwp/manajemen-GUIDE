@extends('layouts.app')

@section('title', 'Absensi Guide')
@section('header', 'Absensi & Kehadiran Guide')

@section('content')
<div class="space-y-6">
    <!-- Top Filter Bar -->
    <div class="clay-card p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-extrabold text-[#29283D] font-display">Lembar Absensi Harian Guide</h2>
            <p class="text-xs text-[#77768C]">Absensi di-reset harian, namun urutan rotasi antrean tetap berlanjut (Continuous).</p>
        </div>

        <form action="{{ route('attendance.index') }}" method="GET" class="flex items-center space-x-2 text-xs">
            <label class="font-bold text-[#77768C]">Pilih Tanggal:</label>
            <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"
                   class="clay-input text-xs font-bold w-auto">
        </form>
    </div>

    <!-- Attendance Quick Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="clay-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs text-[#77768C] font-bold uppercase tracking-wider">Hadir & Check-in</span>
                <span class="text-2xl font-black text-[#6C63FF] block mt-1 font-display">{{ $checkedInCount }}</span>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-[#6C63FF]/15 text-[#6C63FF] flex items-center justify-center shadow-xs"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
        </div>

        <div class="clay-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs text-[#77768C] font-bold uppercase tracking-wider">Selesai / Checked Out</span>
                <span class="text-2xl font-black text-[#29283D] block mt-1 font-display">{{ $checkedOutCount }}</span>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-slate-100 text-[#77768C] flex items-center justify-center shadow-xs"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>

        <div class="clay-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs text-[#77768C] font-bold uppercase tracking-wider">Belum Check-in / OFF</span>
                <span class="text-2xl font-black text-[#FF8A65] block mt-1 font-display">{{ $notCheckedInCount }}</span>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-[#FF8A65]/15 text-[#FF8A65] flex items-center justify-center shadow-xs"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="clay-card p-6 overflow-hidden">
        <div class="clay-table-wrapper">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr>
                        <th class="px-4 py-3.5 text-center">No Antrean</th>
                        <th class="px-4 py-3.5">Nama & Kode Guide</th>
                        <th class="px-4 py-3.5">Status Kehadiran</th>
                        <th class="px-4 py-3.5">Jam Masuk (Check-in)</th>
                        <th class="px-4 py-3.5">Jam Pulang (Check-out)</th>
                        <th class="px-4 py-3.5">Lokasi GPS</th>
                        <th class="px-4 py-3.5">Catatan</th>
                        <th class="px-4 py-3.5 text-right">Aksi Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($attendances as $row)
                    <tr class="hover:bg-[#F4F3FA]/50 transition-colors">
                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl font-black text-xs bg-[#6C63FF] text-white shadow-xs">
                                #{{ $row->guide->queue_number }}
                            </span>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="font-bold text-[#29283D] text-sm font-display">{{ $row->guide->nama }}</div>
                            <div class="text-[10px] text-[#77768C] font-mono">{{ $row->guide->kode_guide }} &bull; {{ $row->guide->nomor_telepon }}</div>
                        </td>

                        <td class="px-4 py-3.5">
                            @if($row->status === 'CHECKED_IN')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#72D6C9]/20 text-[#147a6f] border border-[#72D6C9]/50">
                                    HADIR (CHECK-IN)
                                </span>
                            @elseif($row->status === 'CHECKED_OUT')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-[#77768C] border border-[#E8E6F0]">
                                    SELESAI (CHECK-OUT)
                                </span>
                            @elseif($row->status === 'OFF')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-200">
                                    LIBUR (OFF)
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FFD166]/20 text-[#8a6800] border border-[#FFD166]/50">
                                    BELUM CHECK-IN
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 font-bold text-[#29283D]">
                            {{ $row->check_in_at ? $row->check_in_at->format('H:i') . ' WITA' : '-' }}
                        </td>

                        <td class="px-4 py-3.5 font-bold text-[#29283D]">
                            {{ $row->check_out_at ? $row->check_out_at->format('H:i') . ' WITA' : '-' }}
                        </td>

                        <td class="px-4 py-3.5 text-[#77768C]">
                            @if($row->latitude && $row->longitude)
                                <a href="https://maps.google.com/?q={{ $row->latitude }},{{ $row->longitude }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-[#6C63FF] hover:underline font-bold">
                                    <svg class="w-3.5 h-3.5 inline-block mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Peta ({{ round($row->latitude, 4) }}, {{ round($row->longitude, 4) }})</span>
                                </a>
                            @else
                                <span class="text-slate-400 italic">Tanpa GPS</span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 text-[#77768C]">
                            {{ $row->notes ?? '-' }}
                        </td>

                        <td class="px-4 py-3.5 text-right space-x-1.5">
                            @if(auth()->user()->isAdmin())
                                @if($row->status !== 'CHECKED_IN')
                                <form action="{{ route('attendance.force-checkin', $row->guide) }}" method="POST" class="inline-block">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                                    <button type="submit" class="clay-btn clay-btn-primary py-1 px-2.5 text-[10px] font-bold">
                                        Paksa Check-in
                                    </button>
                                </form>
                                @endif

                                @if($row->status === 'CHECKED_IN')
                                <form action="{{ route('attendance.force-checkout', $row->guide) }}" method="POST" class="inline-block" data-confirm="Paksa checkout guide {{ $row->guide->nama }} sekarang?" data-confirm-title="Paksa Checkout" data-confirm-danger="true" data-confirm-btn="Ya, Checkout!">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                                    <button type="submit" class="clay-btn clay-btn-secondary py-1 px-2.5 text-[10px] font-bold text-[#77768C]">
                                        Paksa Check-out
                                    </button>
                                </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-[#77768C]">Tidak ada data absensi untuk tanggal ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

