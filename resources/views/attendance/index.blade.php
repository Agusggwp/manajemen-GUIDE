@extends('layouts.app')

@section('title', 'Absensi Guide')
@section('header', 'Absensi & Kehadiran Guide')

@section('content')
<div class="space-y-6">
    <!-- Top Filter Bar -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Lembar Absensi Harian Guide</h2>
            <p class="text-xs text-slate-500">Absensi di-reset harian, namun urutan rotasi antrean tetap berlanjut (Continuous).</p>
        </div>

        <form action="{{ route('attendance.index') }}" method="GET" class="flex items-center space-x-2 text-xs">
            <label class="font-semibold text-slate-600">Pilih Tanggal:</label>
            <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"
                   class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-xs font-semibold focus:ring-1 focus:ring-[#21c9a4]">
        </form>
    </div>

    <!-- Attendance Quick Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 font-medium">Hadir & Check-in</span>
                <span class="text-2xl font-bold text-emerald-600 block mt-0.5">{{ $checkedInCount }}</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">✓</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 font-medium">Selesai / Checked Out</span>
                <span class="text-2xl font-bold text-slate-700 block mt-0.5">{{ $checkedOutCount }}</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center font-bold">⏱</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 font-medium">Belum Check-in / OFF</span>
                <span class="text-2xl font-bold text-amber-600 block mt-0.5">{{ $notCheckedInCount }}</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">⚠</div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
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
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $row)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg font-bold text-xs bg-slate-100 text-slate-800">
                                #{{ $row->guide->queue_number }}
                            </span>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900 text-sm">{{ $row->guide->nama }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $row->guide->kode_guide }} &bull; {{ $row->guide->nomor_telepon }}</div>
                        </td>

                        <td class="px-4 py-3.5">
                            @if($row->status === 'CHECKED_IN')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    HADIR (CHECK-IN)
                                </span>
                            @elseif($row->status === 'CHECKED_OUT')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                    SELESAI (CHECK-OUT)
                                </span>
                            @elseif($row->status === 'OFF')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-600 border border-rose-200">
                                    LIBUR (OFF)
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-600 border border-amber-200">
                                    BELUM CHECK-IN
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 font-medium text-slate-800">
                            {{ $row->check_in_at ? $row->check_in_at->format('H:i') . ' WITA' : '-' }}
                        </td>

                        <td class="px-4 py-3.5 font-medium text-slate-800">
                            {{ $row->check_out_at ? $row->check_out_at->format('H:i') . ' WITA' : '-' }}
                        </td>

                        <td class="px-4 py-3.5 text-slate-500">
                            @if($row->latitude && $row->longitude)
                                <a href="https://maps.google.com/?q={{ $row->latitude }},{{ $row->longitude }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-[#14433b] hover:text-[#21c9a4] underline font-medium">
                                    <span>📍 Peta ({{ round($row->latitude, 4) }}, {{ round($row->longitude, 4) }})</span>
                                </a>
                            @else
                                <span class="text-slate-400 italic">Tanpa GPS</span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 text-slate-600">
                            {{ $row->notes ?? '-' }}
                        </td>

                        <td class="px-4 py-3.5 text-right space-x-1">
                            @if(auth()->user()->isAdmin())
                                @if($row->status !== 'CHECKED_IN')
                                <form action="{{ route('attendance.force-checkin', $row->guide) }}" method="POST" class="inline-block">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                                    <button type="submit" class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[10px] font-semibold">
                                        Paksa Check-in
                                    </button>
                                </form>
                                @endif

                                @if($row->status === 'CHECKED_IN')
                                <form action="{{ route('attendance.force-checkout', $row->guide) }}" method="POST" class="inline-block" data-confirm="Paksa checkout guide {{ $row->guide->nama }} sekarang?" data-confirm-title="Paksa Checkout" data-confirm-danger="true" data-confirm-btn="Ya, Checkout!">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                                    <button type="submit" class="px-2 py-1 bg-slate-600 hover:bg-slate-700 text-white rounded text-[10px] font-semibold">
                                        Paksa Check-out
                                    </button>
                                </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-400">Tidak ada data absensi untuk tanggal ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
