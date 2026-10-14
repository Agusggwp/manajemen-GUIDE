@extends('layouts.app')

@section('title', 'Laporan Kinerja Guide')
@section('header', 'Laporan Kinerja Guide')

@section('content')
<div class="space-y-6">

    <!-- Top Report Navigation Bar (Tabs) -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-print border-b border-[#E8E6F0]">
        <a href="{{ route('reports.revenue') }}" 
           class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ request()->routeIs('reports.revenue') ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30' : 'bg-white hover:bg-[#F4F3FA] text-[#77768C] border border-[#E8E6F0]' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span>Laporan Omset & Laba</span>
        </a>

        <a href="{{ route('reports.guide-payroll') }}" 
           class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ request()->routeIs('reports.guide-payroll') ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30' : 'bg-white hover:bg-[#F4F3FA] text-[#77768C] border border-[#E8E6F0]' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>Laporan Gaji Guide</span>
        </a>

        <a href="{{ route('reports.guides') }}" 
           class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ request()->routeIs('reports.guides') ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30' : 'bg-white hover:bg-[#F4F3FA] text-[#77768C] border border-[#E8E6F0]' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>Kinerja Penugasan</span>
        </a>

        <a href="{{ route('reports.attendance') }}" 
           class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ request()->routeIs('reports.attendance') ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30' : 'bg-white hover:bg-[#F4F3FA] text-[#77768C] border border-[#E8E6F0]' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Absensi & Jam Kerja</span>
        </a>

        <a href="{{ route('reports.queue') }}" 
           class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 {{ request()->routeIs('reports.queue') ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30' : 'bg-white hover:bg-[#F4F3FA] text-[#77768C] border border-[#E8E6F0]' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            <span>Siklus & Antrean</span>
        </a>
    </div>

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
