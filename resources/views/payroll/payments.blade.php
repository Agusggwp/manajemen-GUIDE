@extends('layouts.app')

@section('title', 'Riwayat Pembayaran Guide')
@section('header', 'Riwayat Pembayaran Gaji / Fee Guide')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Histori Pembayaran Guide</h2>
            <p class="text-xs text-slate-500">Semua transaksi pengeluaran fee dan gaji yang sudah diproses</p>
        </div>

        <form action="{{ route('payroll.payments') }}" method="GET" class="flex items-center space-x-2 text-xs">
            <select name="guide_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg">
                <option value="">Semua Guide</option>
                @foreach($guides as $g)
                    <option value="{{ $g->id }}" {{ request('guide_id') == $g->id ? 'selected' : '' }}>{{ $g->nama }} (#{{ $g->queue_number }})</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
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
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $p)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-4 py-3.5 font-bold text-slate-900 font-mono">{{ $p->payment_code }}</td>
                        <td class="px-4 py-3.5">
                            <span class="font-bold text-slate-900">{{ $p->guide->nama }}</span>
                            <span class="text-[10px] text-slate-400 block font-mono">#{{ $p->guide->queue_number }} - {{ $p->guide->kode_guide }}</span>
                        </td>
                        <td class="px-4 py-3.5 font-medium text-slate-800">{{ $p->payment_date->format('d M Y') }}</td>
                        <td class="px-4 py-3.5 text-slate-700">{{ $p->payment_method }}</td>
                        <td class="px-4 py-3.5 text-right font-extrabold text-emerald-700 text-sm">
                            Rp {{ number_format($p->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-slate-600">{{ $p->notes ?? '-' }}</td>
                        <td class="px-4 py-3.5 text-slate-500">{{ $p->creator?->name ?? 'Admin' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada riwayat pembayaran yang tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
