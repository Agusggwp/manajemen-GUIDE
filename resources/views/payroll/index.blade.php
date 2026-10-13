@extends('layouts.app')

@section('title', 'Penggajian Guide')
@section('header', 'Penggajian & Fee Guide (Payroll)')

@section('content')
<div class="space-y-6">
    <!-- Top Summary Metrics (Requirement 31) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="clay-card p-6">
            <span class="text-xs font-bold text-[#77768C] block uppercase tracking-wider">Total Hak Fee Seluruh Guide</span>
            <span class="text-2xl font-extrabold text-[#29283D] block mt-1">Rp {{ number_format($totalFeeAll, 0, ',', '.') }}</span>
            <span class="text-[11px] text-[#77768C] mt-1 block">Akumulasi seluruh tour guide</span>
        </div>

        <div class="clay-card p-6">
            <span class="text-xs font-bold text-[#0f766e] block uppercase tracking-wider">Total Fee Terbayar (Paid)</span>
            <span class="text-2xl font-extrabold text-[#72D6C9] block mt-1">Rp {{ number_format($totalPaidAll, 0, ',', '.') }}</span>
            <span class="text-[11px] text-[#77768C] mt-1 block">Telah ditransfer ke guide</span>
        </div>

        <div class="clay-card p-6">
            <span class="text-xs font-bold text-[#b45309] block uppercase tracking-wider">Sisa Fee Belum Dibayar (Remaining)</span>
            <span class="text-2xl font-extrabold text-[#FFD166] block mt-1">Rp {{ number_format($totalRemainingAll, 0, ',', '.') }}</span>
            <span class="text-[11px] text-[#77768C] mt-1 block">Kewajiban outstanding</span>
        </div>
    </div>

    <!-- Payroll Table -->
    <div class="clay-table-wrapper">
        <div class="p-5 md:p-6 border-b border-[#E8E6F0] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-extrabold text-[#29283D] flex items-center gap-2">
                    <span class="w-7 h-7 rounded-xl bg-[#6C63FF]/10 text-[#6C63FF] flex items-center justify-center"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
                    <span>Rekapitulasi Fee per Guide</span>
                </h3>
                <p class="text-xs text-[#77768C] mt-0.5">Daftar akumulasi pekerjaan dan status pembayaran gaji guide</p>
            </div>
            <a href="{{ route('payroll.payments') }}" class="clay-btn clay-btn-secondary text-xs px-3.5 py-2 font-bold inline-flex items-center gap-1.5 self-start sm:self-auto">
                Lihat Riwayat Pembayaran &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F4F3FA] text-[#77768C] font-bold border-b border-[#E8E6F0]">
                    <tr>
                        <th class="px-4 py-3.5 text-center">No Antrean</th>
                        <th class="px-4 py-3.5">Nama & Kode Guide</th>
                        <th class="px-4 py-3.5 text-center">Total Booking</th>
                        <th class="px-4 py-3.5 text-center">Total Tamu Ditangani</th>
                        <th class="px-4 py-3.5 text-right">Total Hak Fee</th>
                        <th class="px-4 py-3.5 text-right">Telah Dibayar</th>
                        <th class="px-4 py-3.5 text-right">Sisa Pembayaran</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($payrolls as $row)
                    <tr class="hover:bg-[#F4F3FA]/70 transition-colors">
                        <td class="px-4 py-3.5 text-center font-extrabold text-[#6C63FF]">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-[#6C63FF]/15 text-[#6C63FF] font-extrabold">
                                #{{ $row->guide->queue_number }}
                            </span>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="font-extrabold text-[#29283D] text-sm">{{ $row->guide->nama }}</div>
                            <div class="text-[10px] text-[#77768C] font-mono mt-0.5">{{ $row->guide->kode_guide }} &bull; {{ $row->guide->nomor_telepon }}</div>
                        </td>

                        <td class="px-4 py-3.5 text-center font-bold text-[#29283D]">
                            {{ $row->total_bookings }} booking
                        </td>

                        <td class="px-4 py-3.5 text-center font-bold text-[#6C63FF]">
                            {{ $row->total_guests }} pax
                        </td>

                        <td class="px-4 py-3.5 text-right font-extrabold text-[#29283D]">
                            Rp {{ number_format($row->total_fee, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3.5 text-right font-bold text-[#0f766e]">
                            Rp {{ number_format($row->total_paid, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3.5 text-right font-extrabold text-[#b45309]">
                            Rp {{ number_format($row->remaining, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            @if($row->status === 'PAID')
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-[#72D6C9]/20 text-[#0f766e] border border-[#72D6C9]/40">
                                    LUNAS
                                </span>
                            @elseif($row->status === 'PARTIAL')
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-[#FFD166]/25 text-[#b45309] border border-[#FFD166]/50">
                                    SEBAGIAN
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-[#FF8A65]/15 text-[#FF8A65] border border-[#FF8A65]/30">
                                    BELUM DIBAYAR
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 text-right">
                            <a href="{{ route('payroll.show', $row->guide) }}" 
                               class="clay-btn clay-btn-primary px-3 py-1 text-xs font-bold inline-flex items-center">
                                Rincian & Bayar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-[#77768C]">
                            <div class="w-12 h-12 mx-auto mb-2 text-[#6C63FF]/40"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
                            <p class="font-bold">Belum ada data payroll guide.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
