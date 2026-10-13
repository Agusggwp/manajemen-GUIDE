@extends('layouts.app')

@section('title', 'Laporan Rotasi Antrean')
@section('header', 'Laporan Rotasi Antrean')

@section('content')
<div class="space-y-6">
    <div class="clay-card p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
        <div>
            <h2 class="text-base font-bold text-[#29283D]">Rekapitulasi Rotasi Antrean Guide</h2>
            <p class="text-xs text-[#77768C] mt-1">Membuktikan keadilan rotasi continuous queue tanpa reset harian</p>
        </div>

        <form action="{{ route('reports.queue') }}" method="GET" class="flex flex-wrap items-center gap-2 text-xs">
            <input type="date" name="start_date" value="{{ $startDate }}" class="clay-input text-xs py-1.5 px-3">
            <span class="text-[#77768C] font-bold">s/d</span>
            <input type="date" name="end_date" value="{{ $endDate }}" class="clay-input text-xs py-1.5 px-3">
            <button type="submit" class="clay-btn-primary px-3.5 py-1.5 text-xs font-bold">Filter</button>
            <a href="{{ route('reports.queue', ['start_date' => $startDate, 'end_date' => $endDate, 'export' => 'csv']) }}" 
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
                        <th class="px-5 py-3.5">Waktu Penugasan</th>
                        <th class="px-5 py-3.5 text-center">Posisi Antrean</th>
                        <th class="px-5 py-3.5">Guide Terpilih</th>
                        <th class="px-5 py-3.5">Kode Booking</th>
                        <th class="px-5 py-3.5 text-center">Jumlah Tamu</th>
                        <th class="px-5 py-3.5">Tipe Penugasan</th>
                        <th class="px-5 py-3.5">Catatan Rotasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($histories as $qh)
                    <tr class="hover:bg-purple-50/30 transition-colors">
                        <td class="px-5 py-3.5 font-bold text-[#29283D]">
                            {{ $qh->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-5 py-3.5 text-center font-bold">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-[#6C63FF] text-white text-xs font-black shadow-xs">
                                #{{ $qh->next_position }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 font-bold text-[#29283D]">
                            {{ $qh->selectedGuide?->nama ?? '-' }}
                        </td>
                        <td class="px-5 py-3.5 text-[#29283D] font-mono font-bold">
                            {{ $qh->booking?->booking_code ?? '-' }}
                        </td>
                        <td class="px-5 py-3.5 text-center font-black text-[#29283D]">
                            {{ $qh->booking?->guest_count ?? '-' }} pax
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="clay-badge {{ $qh->type === 'AUTO' ? 'bg-purple-50 text-[#6C63FF] border-purple-200' : 'bg-[#F4F3FA] text-[#77768C] border-[#E8E6F0]' }}">
                                {{ $qh->type === 'AUTO' ? 'Otomatis' : 'Manual' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-[#77768C] font-medium">
                            {{ $qh->notes ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-[#77768C]">Tidak ada riwayat rotasi antrean pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
