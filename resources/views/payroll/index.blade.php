@extends('layouts.app')

@section('title', 'Penggajian Guide')
@section('header', 'Penggajian & Fee Guide (Payroll)')

@section('content')
<div class="space-y-6">
    <!-- Top Summary Metrics (Requirement 31) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Total Hak Fee Seluruh Guide</span>
            <span class="text-2xl font-bold text-slate-900 block mt-1">Rp {{ number_format($totalFeeAll, 0, ',', '.') }}</span>
            <span class="text-[11px] text-slate-400 mt-1 block">Akumulasi seluruh tour guide</span>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Total Fee Terbayar (Paid)</span>
            <span class="text-2xl font-bold text-emerald-600 block mt-1">Rp {{ number_format($totalPaidAll, 0, ',', '.') }}</span>
            <span class="text-[11px] text-emerald-600/70 mt-1 block">Telah ditransfer ke guide</span>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Sisa Fee Belum Dibayar (Remaining)</span>
            <span class="text-2xl font-bold text-amber-600 block mt-1">Rp {{ number_format($totalRemainingAll, 0, ',', '.') }}</span>
            <span class="text-[11px] text-amber-600/70 mt-1 block">Kewajiban outstanding</span>
        </div>
    </div>

    <!-- Payroll Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Rekapitulasi Fee per Guide</h3>
                <p class="text-xs text-slate-500">Daftar akumulasi pekerjaan dan status pembayaran gaji guide</p>
            </div>
            <a href="{{ route('payroll.payments') }}" class="text-xs font-semibold text-[#14433b] hover:text-[#21c9a4]">
                Lihat Riwayat Pembayaran &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
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
                <tbody class="divide-y divide-slate-100">
                    @forelse($payrolls as $row)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-4 py-3.5 text-center font-bold text-slate-800">
                            #{{ $row->guide->queue_number }}
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900 text-sm">{{ $row->guide->nama }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $row->guide->kode_guide }} &bull; {{ $row->guide->nomor_telepon }}</div>
                        </td>

                        <td class="px-4 py-3.5 text-center font-semibold text-slate-700">
                            {{ $row->total_bookings }} booking
                        </td>

                        <td class="px-4 py-3.5 text-center font-bold text-slate-800">
                            {{ $row->total_guests }} pax
                        </td>

                        <td class="px-4 py-3.5 text-right font-bold text-slate-900">
                            Rp {{ number_format($row->total_fee, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3.5 text-right font-medium text-emerald-700">
                            Rp {{ number_format($row->total_paid, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3.5 text-right font-bold text-amber-700">
                            Rp {{ number_format($row->remaining, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            @if($row->status === 'PAID')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    LUNAS
                                </span>
                            @elseif($row->status === 'PARTIAL')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                    SEBAGIAN
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                    BELUM DIBAYAR
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 text-right">
                            <a href="{{ route('payroll.show', $row->guide) }}" 
                               class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold bg-[#14433b] hover:bg-[#1b584d] text-white transition-colors">
                                Rincian & Bayar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-slate-400">Belum ada data payroll guide.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
