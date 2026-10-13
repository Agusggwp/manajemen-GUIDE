@extends('layouts.guide')

@section('title', 'Penghasilan Saya')

@section('content')
<div class="space-y-4">
    <!-- Header Summary -->
    <div class="clay-card p-5 space-y-3.5">
        <h2 class="text-sm font-extrabold text-[#29283D] font-display">Rekapitulasi Pendapatan Saya</h2>
        <p class="text-[11px] text-[#77768C]">Rincian pendapatan tour guide dan histori pembayaran yang telah diterima</p>

        <div class="grid grid-cols-3 gap-2.5 text-center pt-3 border-t border-[#E8E6F0]">
            <div class="p-3 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0]/80">
                <span class="text-[10px] text-[#77768C] block font-bold uppercase tracking-wider">Total Hak Fee</span>
                <span class="text-xs font-black text-[#29283D] block mt-1">Rp {{ number_format($totalFee, 0, ',', '.') }}</span>
            </div>
            <div class="p-3 rounded-2xl bg-[#72D6C9]/15 border border-[#72D6C9]/40">
                <span class="text-[10px] text-[#147a6f] block font-bold uppercase tracking-wider">Telah Diterima</span>
                <span class="text-xs font-black text-[#147a6f] block mt-1">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
            </div>
            <div class="p-3 rounded-2xl bg-[#FF8A65]/15 border border-[#FF8A65]/40">
                <span class="text-[10px] text-[#c9522e] block font-bold uppercase tracking-wider">Sisa Fee</span>
                <span class="text-xs font-black text-[#c9522e] block mt-1">Rp {{ number_format($remaining, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Payments Received List -->
    <div class="space-y-2.5">
        <h3 class="text-xs font-bold uppercase tracking-wider text-[#29283D] px-1">Riwayat Penerimaan Transfer</h3>
        
        @forelse($payments as $pmt)
        <div class="clay-card p-4 flex items-center justify-between transition">
            <div>
                <span class="text-[10px] font-mono font-bold text-[#6C63FF] block">{{ $pmt->payment_code }}</span>
                <span class="text-xs font-bold text-[#29283D] block mt-0.5">{{ $pmt->payment_date->format('d F Y') }}</span>
                <span class="text-[10px] text-[#77768C]">{{ $pmt->payment_method }} &bull; {{ $pmt->notes ?? 'Fee Tour' }}</span>
            </div>
            <div class="text-right">
                <span class="text-sm font-black text-[#147a6f] font-display">+ Rp {{ number_format($pmt->amount, 0, ',', '.') }}</span>
                <span class="text-[9px] text-[#147a6f] font-extrabold block bg-[#72D6C9]/20 border border-[#72D6C9]/40 px-2 py-0.5 rounded-full mt-1">DITERIMA</span>
            </div>
        </div>
        @empty
        <div class="clay-card p-6 text-center text-[#77768C] text-xs">
            Belum ada catatan transfer pembayaran fee.
        </div>
        @endforelse
    </div>
</div>
@endsection

