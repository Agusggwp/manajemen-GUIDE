@extends('layouts.app')

@section('title', 'Rincian Payroll ' . $guide->nama)
@section('header', 'Payroll Guide: ' . $guide->nama)

@section('content')
<div class="space-y-6" x-data="{ paymentModalOpen: false }">
    <!-- Guide Statement Header -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg font-bold text-sm bg-[#14433b] text-[#21c9a4]">
                    #{{ $guide->queue_number }}
                </span>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ $guide->nama }} ({{ $guide->kode_guide }})</h2>
                    <p class="text-xs text-slate-500">{{ $guide->nomor_telepon }} &bull; {{ $guide->email }} &bull; Skema: {{ $guide->tipe_fee }}</p>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            @if(auth()->user()->isAdmin())
            <button type="button" @click="paymentModalOpen = true"
                    class="px-4 py-2 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-bold transition-colors shadow-xs">
                + Catat Pembayaran Gaji / Fee
            </button>
            @endif
            <a href="{{ route('payroll.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-slate-600 text-xs font-medium hover:bg-slate-50">
                &larr; Kembali ke Payroll
            </a>
        </div>
    </div>

    <!-- Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Total Booking Ditangani</span>
            <span class="text-xl font-bold text-slate-900 block mt-1">{{ $payroll->total_bookings }} Tour</span>
            <span class="text-[11px] text-slate-400 mt-0.5 block">{{ $payroll->total_guests }} orang tamu</span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Total Hak Fee</span>
            <span class="text-xl font-bold text-slate-900 block mt-1">Rp {{ number_format($payroll->total_fee, 0, ',', '.') }}</span>
            <span class="text-[11px] text-slate-400 mt-0.5 block">Dari seluruh penugasan</span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Telah Dibayar (Paid)</span>
            <span class="text-xl font-bold text-emerald-600 block mt-1">Rp {{ number_format($payroll->total_paid, 0, ',', '.') }}</span>
            <span class="text-[11px] text-emerald-600/70 mt-0.5 block">Transfer sukses</span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Sisa Belum Dibayar</span>
            <span class="text-xl font-bold text-amber-600 block mt-1">Rp {{ number_format($payroll->remaining, 0, ',', '.') }}</span>
            <span class="text-[11px] text-amber-600/70 mt-0.5 block">Status: 
                @if($payroll->status === 'PAID') LUNAS
                @elseif($payroll->status === 'PARTIAL') SEBAGIAN
                @else BELUM DIBAYAR
                @endif
            </span>
        </div>
    </div>

    <!-- Assignment Breakdown Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Rincian Penugasan & Snapshot Fee</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Tanggal & Waktu</th>
                        <th class="px-4 py-3">Kode Booking</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3 text-center">Tamu</th>
                        <th class="px-4 py-3">Tipe Fee</th>
                        <th class="px-4 py-3 text-right">Fee Rate Snapshot</th>
                        <th class="px-4 py-3 text-right">Total Fee</th>
                        <th class="px-4 py-3">Status Tour</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payroll->assignments as $asg)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $asg->assigned_at->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-slate-400">{{ $asg->assigned_at->format('H:i') }} WITA</div>
                        </td>
                        <td class="px-4 py-3 font-bold text-slate-900">
                            {{ $asg->booking?->booking_code }}
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-800">
                            {{ $asg->booking?->customer_name }}
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-slate-800">
                            {{ $asg->guest_count }} pax
                        </td>
                        <td class="px-4 py-3 text-slate-600 font-medium">
                            {{ $asg->fee_type }}
                        </td>
                        <td class="px-4 py-3 text-right text-slate-600">
                            Rp {{ number_format($asg->fee_rate, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-amber-700">
                            Rp {{ number_format($asg->fee_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">
                                @if($asg->status === 'COMPLETED') Selesai
                                @elseif($asg->status === 'ON_GOING') Sedang Tur
                                @elseif($asg->status === 'ASSIGNED') Ditugaskan
                                @else {{ $asg->status }}
                                @endif
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-slate-400">Belum ada riwayat penugasan tour.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payments Made Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Riwayat Pembayaran yang Telah Ditransfer</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Kode Pembayaran</th>
                        <th class="px-4 py-3">Tanggal Bayar</th>
                        <th class="px-4 py-3">Metode Pembayaran</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3">Catatan</th>
                        <th class="px-4 py-3">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payroll->payments as $pmt)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-4 py-3 font-bold text-slate-900 font-mono">{{ $pmt->payment_code }}</td>
                        <td class="px-4 py-3 text-slate-800 font-medium">{{ $pmt->payment_date->format('d F Y') }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $pmt->payment_method }}</td>
                        <td class="px-4 py-3 text-right font-extrabold text-emerald-700">
                            Rp {{ number_format($pmt->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $pmt->notes ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $pmt->creator?->name ?? 'Admin' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-400">Belum ada riwayat pembayaran untuk guide ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Record Payment Modal (Requirement 32) -->
    <div x-show="paymentModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="paymentModalOpen = false" class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Catat Pembayaran Gaji / Fee Guide</h3>
                <button type="button" @click="paymentModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <div class="p-3 bg-[#e6faf5] rounded-lg border border-[#21c9a4]/30 text-xs text-[#14433b]">
                Sisa hak fee yang belum dibayar: <strong>Rp {{ number_format($payroll->remaining, 0, ',', '.') }}</strong>
            </div>

            <form action="{{ route('payroll.payment', $guide) }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nominal Pembayaran (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" value="{{ $payroll->remaining }}" max="{{ $payroll->remaining }}" min="1000" step="1000" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md font-bold text-sm text-slate-900">
                    <span class="text-[10px] text-slate-500 block mt-0.5">Sistem memvalidasi nominal agar tidak melebihi sisa fee yang berhak diterima.</span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tanggal Pembayaran <span class="text-rose-500">*</span></label>
                    <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Metode Pembayaran</label>
                    <select name="payment_method" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
                        <option value="Bank Transfer BCA">Bank Transfer BCA</option>
                        <option value="Bank Transfer Mandiri">Bank Transfer Mandiri</option>
                        <option value="Bank Transfer BNI / BRI">Bank Transfer BNI / BRI</option>
                        <option value="Cash / Tunai">Cash / Tunai</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Catatan / Bukti Transfer</label>
                    <input type="text" name="notes" placeholder="Contoh: No Ref Transfer #998822"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
                </div>

                <div class="flex items-center">
                    <label class="flex items-center text-[11px] text-slate-600">
                        <input type="checkbox" name="admin_override" value="1" class="rounded text-[#14433b]">
                        <span class="ml-2">Admin Override (izinkan pembayaran melebihi sisa balance jika ada penyesuaian khusus)</span>
                    </label>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                    <button type="button" @click="paymentModalOpen = false" class="px-3 py-2 border border-slate-300 rounded-lg text-slate-600 font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white font-bold transition-colors shadow-xs">
                        Konfirmasi Pembayaran &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
