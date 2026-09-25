@extends('layouts.guide')

@section('title', 'Penghasilan Saya')

@section('content')
<div class="space-y-4">
    <!-- Header Summary -->
    <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs space-y-3">
        <h2 class="text-sm font-bold text-slate-900">Rekapitulasi Pendapatan Saya</h2>
        <p class="text-[11px] text-slate-500">Rincian pendapatan tour guide dan histori pembayaran yang telah diterima</p>

        <div class="grid grid-cols-3 gap-2 text-center pt-2 border-t border-slate-100">
            <div class="p-2 rounded-lg bg-slate-50 border border-slate-100">
                <span class="text-[10px] text-slate-400 block">Total Hak Fee</span>
                <span class="text-xs font-bold text-slate-900 block mt-0.5">Rp {{ number_format($totalFee, 0, ',', '.') }}</span>
            </div>
            <div class="p-2 rounded-lg bg-emerald-50 border border-emerald-100">
                <span class="text-[10px] text-emerald-600 block">Telah Diterima</span>
                <span class="text-xs font-bold text-emerald-700 block mt-0.5">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
            </div>
            <div class="p-2 rounded-lg bg-amber-50 border border-amber-100">
                <span class="text-[10px] text-amber-600 block">Sisa Fee</span>
                <span class="text-xs font-bold text-amber-700 block mt-0.5">Rp {{ number_format($remaining, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Payments Received List -->
    <div class="space-y-2">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 px-1">Riwayat Penerimaan Transfer</h3>
        
        @forelse($payments as $pmt)
        <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-mono font-bold text-slate-400 block">{{ $pmt->payment_code }}</span>
                <span class="text-xs font-bold text-slate-900 block mt-0.5">{{ $pmt->payment_date->format('d F Y') }}</span>
                <span class="text-[10px] text-slate-500">{{ $pmt->payment_method }} &bull; {{ $pmt->notes ?? 'Fee Tour' }}</span>
            </div>
            <div class="text-right">
                <span class="text-sm font-extrabold text-emerald-700">+ Rp {{ number_format($pmt->amount, 0, ',', '.') }}</span>
                <span class="text-[9px] text-emerald-600 font-bold block bg-emerald-50 px-1.5 py-0.2 rounded mt-0.5">DITERIMA</span>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center text-slate-400 text-xs shadow-2xs">
            Belum ada catatan transfer pembayaran fee.
        </div>
        @endforelse
    </div>
</div>
@endsection
