@extends('layouts.app')

@section('title', 'Laporan Absensi Guide')
@section('header', 'Laporan Absensi & Jam Kerja Guide')

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

    <div class="clay-card p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
        <div>
            <h2 class="text-base font-bold text-[#29283D]">Rekap Jam Kerja & Kehadiran Guide</h2>
            <p class="text-xs text-[#77768C] mt-1">Mencatat jam masuk, jam pulang, serta akumulasi jam kerja guide</p>
        </div>

        <form action="{{ route('reports.attendance') }}" method="GET" class="flex flex-wrap items-center gap-2 text-xs">
            <input type="date" name="start_date" value="{{ $startDate }}" class="clay-input text-xs py-1.5 px-3">
            <span class="text-[#77768C] font-bold">s/d</span>
            <input type="date" name="end_date" value="{{ $endDate }}" class="clay-input text-xs py-1.5 px-3">
            <button type="submit" class="clay-btn-primary px-3.5 py-1.5 text-xs font-bold">Filter</button>
            <a href="{{ route('reports.attendance', ['start_date' => $startDate, 'end_date' => $endDate, 'export' => 'csv']) }}" 
               class="clay-btn-turquoise px-3.5 py-1.5 text-xs font-bold inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-[#29283D]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Ekspor Excel / CSV</span>
            </a>
            <button type="button" onclick="window.print()" class="clay-btn-secondary px-3.5 py-1.5 text-xs font-bold">
                Cetak Laporan
            </button>
        </form>
    </div>

    <div class="clay-card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F4F3FA] text-[#77768C] font-bold border-b border-[#E8E6F0]">
                    <tr>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Kode & Nama Guide</th>
                        <th class="px-5 py-3.5">Jam Check-in</th>
                        <th class="px-5 py-3.5">Jam Check-out</th>
                        <th class="px-5 py-3.5">Total Jam Kerja</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($attendances as $att)
                    <tr class="hover:bg-purple-50/30 transition-colors">
                        <td class="px-5 py-3.5 font-bold text-[#29283D]">{{ $att->date }}</td>
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-[#29283D]">{{ $att->guide_nama }}</div>
                            <div class="text-[10px] text-[#77768C] font-mono">{{ $att->kode_guide }}</div>
                        </td>
                        <td class="px-5 py-3.5 text-[#29283D] font-medium">{{ $att->check_in }}</td>
                        <td class="px-5 py-3.5 text-[#29283D] font-medium">{{ $att->check_out }}</td>
                        <td class="px-5 py-3.5 font-bold text-[#6C63FF]">{{ $att->working_hours }}</td>
                        <td class="px-5 py-3.5">
                            <span class="clay-badge {{ $att->status === 'CHECKED_IN' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($att->status === 'CHECKED_OUT' ? 'bg-[#F4F3FA] text-[#77768C] border-[#E8E6F0]' : 'bg-rose-50 text-rose-700 border-rose-200') }}">
                                {{ $att->status === 'CHECKED_IN' ? 'Check-in' : ($att->status === 'CHECKED_OUT' ? 'Check-out' : 'Libur (OFF)') }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-[#77768C]">{{ $att->notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-[#77768C]">Tidak ada riwayat absensi pada rentang tanggal ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
