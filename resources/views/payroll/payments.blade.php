@extends('layouts.app')

@section('title', 'Riwayat Pembayaran Guide')
@section('header', 'Riwayat Pembayaran Gaji / Fee Guide')

@section('content')
<div class="space-y-6">
    <div class="clay-card p-5 md:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-[#29283D] flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-[#6C63FF]/10 text-[#6C63FF] flex items-center justify-center"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></span>
                <span>Histori Pembayaran Guide</span>
            </h2>
            <p class="text-xs text-[#77768C] mt-1">Semua transaksi pengeluaran fee dan gaji yang sudah diproses</p>
        </div>

        <div class="flex items-center space-x-2.5">
            <form action="{{ route('payroll.payments') }}" method="GET" class="flex items-center space-x-2 text-xs">
                <select name="guide_id" onchange="this.form.submit()" class="clay-input text-xs py-1.5 px-3">
                    <option value="">Semua Guide</option>
                    @foreach($guides as $g)
                        <option value="{{ $g->id }}" {{ request('guide_id') == $g->id ? 'selected' : '' }}>{{ $g->nama }} (#{{ $g->queue_number }})</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('payroll.index') }}" class="clay-btn clay-btn-secondary text-xs px-3.5 py-1.5 font-bold inline-flex items-center gap-1">
                <span>&larr;</span> Kembali
            </a>
        </div>
    </div>

    <div class="clay-table-wrapper">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F4F3FA] text-[#77768C] font-bold border-b border-[#E8E6F0]">
                    <tr>
                        <th class="px-4 py-3.5">Kode Pembayaran</th>
                        <th class="px-4 py-3.5">Guide Penerima</th>
                        <th class="px-4 py-3.5">Tanggal Pembayaran</th>
                        <th class="px-4 py-3.5">Metode Bayar</th>
                        <th class="px-4 py-3.5 text-right">Nominal Ditransfer</th>
                        <th class="px-4 py-3.5">Catatan / Bukti</th>
                        <th class="px-4 py-3.5">Diproses Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($payments as $p)
                    <tr class="hover:bg-[#F4F3FA]/70 transition-colors">
                        <td class="px-4 py-3.5 font-bold text-[#6C63FF] font-mono">{{ $p->payment_code }}</td>
                        <td class="px-4 py-3.5">
                            <span class="font-extrabold text-[#29283D]">{{ $p->guide->nama }}</span>
                            <span class="text-[10px] text-[#77768C] block font-mono">#{{ $p->guide->queue_number }} - {{ $p->guide->kode_guide }}</span>
                        </td>
                        <td class="px-4 py-3.5 font-medium text-[#29283D]">{{ $p->payment_date->format('d M Y') }}</td>
                        <td class="px-4 py-3.5 text-[#77768C]">{{ $p->payment_method }}</td>
                        <td class="px-4 py-3.5 text-right font-extrabold text-[#72D6C9] text-sm">
                            Rp {{ number_format($p->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-[#77768C]">{{ $p->notes ?? '-' }}</td>
                        <td class="px-4 py-3.5 text-[#77768C]">{{ $p->creator?->name ?? 'Admin' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-[#77768C]">
                            <div class="w-12 h-12 mx-auto mb-2 text-[#6C63FF]/40"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
                            <p class="font-bold">Belum ada riwayat pembayaran yang tercatat.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
        <div class="px-4 py-3.5 border-t border-[#E8E6F0] bg-[#F4F3FA]">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
