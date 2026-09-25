@extends('layouts.app')

@section('title', 'Laporan Rotasi Antrean')
@section('header', 'Laporan Rotasi Antrean')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
        <div>
            <h2 class="text-base font-bold text-slate-900">Rekapitulasi Rotasi Antrean Guide</h2>
            <p class="text-xs text-slate-500">Membuktikan keadilan rotasi continuous queue tanpa reset harian</p>
        </div>

        <form action="{{ route('reports.queue') }}" method="GET" class="flex flex-wrap items-center gap-2 text-xs">
            <input type="date" name="start_date" value="{{ $startDate }}" class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-md">
            <span class="text-slate-400">s/d</span>
            <input type="date" name="end_date" value="{{ $endDate }}" class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-md">
            <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded-md font-semibold">Filter</button>
            <a href="{{ route('reports.queue', ['start_date' => $startDate, 'end_date' => $endDate, 'export' => 'csv']) }}" 
               class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-semibold">
                Ekspor Excel / CSV
            </a>
            <button type="button" onclick="window.print()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-md font-semibold">
                Cetak Laporan
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5">Waktu Penugasan</th>
                        <th class="px-4 py-3.5 text-center">Posisi Antrean</th>
                        <th class="px-4 py-3.5">Guide Terpilih</th>
                        <th class="px-4 py-3.5">Kode Booking</th>
                        <th class="px-4 py-3.5 text-center">Jumlah Tamu</th>
                        <th class="px-4 py-3.5">Tipe Penugasan</th>
                        <th class="px-4 py-3.5">Catatan Rotasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($histories as $qh)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-4 py-3.5 font-medium text-slate-900">
                            {{ $qh->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-4 py-3.5 text-center font-bold">
                            <span class="px-2 py-0.5 rounded bg-[#14433b] text-[#21c9a4]">
                                #{{ $qh->next_position }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 font-bold text-slate-900">
                            {{ $qh->selectedGuide?->nama ?? '-' }}
                        </td>
                        <td class="px-4 py-3.5 text-slate-700 font-mono font-semibold">
                            {{ $qh->booking?->booking_code ?? '-' }}
                        </td>
                        <td class="px-4 py-3.5 text-center font-extrabold text-slate-800">
                            {{ $qh->booking?->guest_count ?? '-' }} pax
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $qh->type === 'AUTO' ? 'bg-[#e6faf5] text-[#14433b]' : 'bg-slate-100 text-slate-700' }}">
                                {{ $qh->type === 'AUTO' ? 'Otomatis' : 'Manual' }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-slate-500">
                            {{ $qh->notes ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">Tidak ada riwayat rotasi antrean pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
