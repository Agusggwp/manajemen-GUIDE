@extends('layouts.app')

@section('title', 'Laporan Kinerja Guide')
@section('header', 'Laporan Kinerja Guide')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
        <div>
            <h2 class="text-base font-bold text-slate-900">Statistik Kinerja & Penugasan Tour Guide</h2>
            <p class="text-xs text-slate-500">Rekapitulasi total booking, tamu yang dipandu, dan akumulasi fee</p>
        </div>

        <form action="{{ route('reports.guides') }}" method="GET" class="flex flex-wrap items-center gap-2 text-xs">
            <input type="date" name="start_date" value="{{ $startDate }}" class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-md">
            <span class="text-slate-400">s/d</span>
            <input type="date" name="end_date" value="{{ $endDate }}" class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-md">
            <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded-md font-semibold">Filter</button>
            <a href="{{ route('reports.guides', ['start_date' => $startDate, 'end_date' => $endDate, 'export' => 'csv']) }}" 
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
                        <th class="px-4 py-3.5 text-center">No Antrean</th>
                        <th class="px-4 py-3.5">Kode & Nama Guide</th>
                        <th class="px-4 py-3.5 text-center">Total Booking Ditangani</th>
                        <th class="px-4 py-3.5 text-center">Total Tamu Dipandu</th>
                        <th class="px-4 py-3.5 text-right">Akumulasi Hak Fee</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($guides as $g)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-4 py-3.5 text-center font-bold text-slate-800">
                            #{{ $g->queue_number }}
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900 text-sm">{{ $g->nama }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $g->kode }}</div>
                        </td>
                        <td class="px-4 py-3.5 text-center font-semibold text-slate-700">
                            {{ $g->total_bookings }} booking
                        </td>
                        <td class="px-4 py-3.5 text-center font-extrabold text-slate-900">
                            {{ $g->total_guests }} orang
                        </td>
                        <td class="px-4 py-3.5 text-right font-extrabold text-emerald-700 text-sm">
                            Rp {{ number_format($g->total_fee, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400">Tidak ada data untuk periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
