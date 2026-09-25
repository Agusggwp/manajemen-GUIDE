@extends('layouts.app')

@section('title', 'Laporan Absensi Guide')
@section('header', 'Laporan Absensi & Jam Kerja Guide')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
        <div>
            <h2 class="text-base font-bold text-slate-900">Rekap Jam Kerja & Kehadiran Guide</h2>
            <p class="text-xs text-slate-500">Mencatat jam masuk, jam pulang, serta akumulasi jam kerja guide</p>
        </div>

        <form action="{{ route('reports.attendance') }}" method="GET" class="flex flex-wrap items-center gap-2 text-xs">
            <input type="date" name="start_date" value="{{ $startDate }}" class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-md">
            <span class="text-slate-400">s/d</span>
            <input type="date" name="end_date" value="{{ $endDate }}" class="px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-md">
            <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded-md font-semibold">Filter</button>
            <a href="{{ route('reports.attendance', ['start_date' => $startDate, 'end_date' => $endDate, 'export' => 'csv']) }}" 
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
                        <th class="px-4 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5">Kode & Nama Guide</th>
                        <th class="px-4 py-3.5">Jam Check-in</th>
                        <th class="px-4 py-3.5">Jam Check-out</th>
                        <th class="px-4 py-3.5">Total Jam Kerja</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $att)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-4 py-3.5 font-medium text-slate-900">{{ $att->date }}</td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900">{{ $att->guide_nama }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $att->kode_guide }}</div>
                        </td>
                        <td class="px-4 py-3.5 text-slate-700 font-medium">{{ $att->check_in }}</td>
                        <td class="px-4 py-3.5 text-slate-700 font-medium">{{ $att->check_out }}</td>
                        <td class="px-4 py-3.5 font-bold text-[#14433b]">{{ $att->working_hours }}</td>
                        <td class="px-4 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $att->status === 'CHECKED_IN' ? 'bg-emerald-50 text-emerald-700' : ($att->status === 'CHECKED_OUT' ? 'bg-slate-100 text-slate-700' : 'bg-rose-50 text-rose-700') }}">
                                {{ $att->status === 'CHECKED_IN' ? 'Check-in' : ($att->status === 'CHECKED_OUT' ? 'Check-out' : 'Libur (OFF)') }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-slate-500">{{ $att->notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">Tidak ada riwayat absensi pada rentang tanggal ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
