@extends('layouts.app')

@section('title', 'Rincian Payroll ' . $guide->nama)
@section('header', 'Payroll Guide: ' . $guide->nama)

@section('content')
<div class="space-y-6" x-data="{ paymentModalOpen: false }">
    <!-- Guide Statement Header -->
    <div class="clay-card p-5 md:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3.5">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl font-extrabold text-sm bg-[#6C63FF]/15 text-[#6C63FF] border border-[#6C63FF]/30 shadow-xs">
                    #{{ $guide->queue_number }}
                </span>
                <div>
                    <h2 class="text-xl font-extrabold text-[#29283D]">{{ $guide->nama }} ({{ $guide->kode_guide }})</h2>
                    <p class="text-xs text-[#77768C] mt-0.5">{{ $guide->nomor_telepon }} &bull; {{ $guide->email }} &bull; Skema: {{ $guide->tipe_fee }}</p>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-2.5">
            @if(auth()->user()->isAdmin())
            <button type="button" @click="paymentModalOpen = true"
                    class="clay-btn clay-btn-primary px-4 py-2 text-xs font-bold inline-flex items-center gap-1.5">
                <span>+</span> Catat Pembayaran Gaji / Fee
            </button>
            @endif
            <a href="{{ route('payroll.index') }}" class="clay-btn clay-btn-secondary px-3.5 py-2 text-xs font-bold inline-flex items-center gap-1">
                <span>&larr;</span> Kembali ke Payroll
            </a>
        </div>
    </div>

    <!-- Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="clay-card p-5">
            <span class="text-xs font-bold text-[#77768C] block uppercase tracking-wider">Total Booking Ditangani</span>
            <span class="text-xl font-extrabold text-[#29283D] block mt-1">{{ $payroll->total_bookings }} Tour</span>
            <span class="text-[11px] text-[#77768C] mt-0.5 block">{{ $payroll->total_guests }} orang tamu</span>
        </div>

        <div class="clay-card p-5">
            <span class="text-xs font-bold text-[#77768C] block uppercase tracking-wider">Total Hak Fee</span>
            <span class="text-xl font-extrabold text-[#6C63FF] block mt-1">Rp {{ number_format($payroll->total_fee, 0, ',', '.') }}</span>
            <span class="text-[11px] text-[#77768C] mt-0.5 block">Dari seluruh penugasan</span>
        </div>

        <div class="clay-card p-5">
            <span class="text-xs font-bold text-[#0f766e] block uppercase tracking-wider">Telah Dibayar (Paid)</span>
            <span class="text-xl font-extrabold text-[#72D6C9] block mt-1">Rp {{ number_format($payroll->total_paid, 0, ',', '.') }}</span>
            <span class="text-[11px] text-[#77768C] mt-0.5 block">Transfer sukses</span>
        </div>

        <div class="clay-card p-5">
            <span class="text-xs font-bold text-[#b45309] block uppercase tracking-wider">Sisa Belum Dibayar</span>
            <span class="text-xl font-extrabold text-[#FF8A65] block mt-1">Rp {{ number_format($payroll->remaining, 0, ',', '.') }}</span>
            <span class="text-[11px] text-[#77768C] mt-0.5 block">Status: 
                @if($payroll->status === 'PAID') LUNAS
                @elseif($payroll->status === 'PARTIAL') SEBAGIAN
                @else BELUM DIBAYAR
                @endif
            </span>
        </div>
    </div>

    <!-- Assignment Breakdown Table -->
    <div class="clay-table-wrapper">
        <div class="px-5 py-4 border-b border-[#E8E6F0]">
            <h3 class="text-sm font-extrabold text-[#29283D]">Rincian Penugasan & Snapshot Fee</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F4F3FA] text-[#77768C] font-bold border-b border-[#E8E6F0]">
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
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($payroll->assignments as $asg)
                    <tr class="hover:bg-[#F4F3FA]/70 transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-bold text-[#29283D]">{{ $asg->assigned_at->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-[#77768C]">{{ $asg->assigned_at->format('H:i') }} WITA</div>
                        </td>
                        <td class="px-4 py-3 font-bold text-[#6C63FF]">
                            {{ $asg->booking?->booking_code }}
                        </td>
                        <td class="px-4 py-3 font-medium text-[#29283D]">
                            {{ $asg->booking?->customer_name }}
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-[#29283D]">
                            {{ $asg->guest_count }} pax
                        </td>
                        <td class="px-4 py-3 text-[#77768C] font-medium">
                            {{ $asg->fee_type }}
                        </td>
                        <td class="px-4 py-3 text-right text-[#77768C]">
                            Rp {{ number_format($asg->fee_rate, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-extrabold text-[#FF8A65]">
                            Rp {{ number_format($asg->fee_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2.5 py-0.5 rounded-xl text-[10px] font-bold bg-[#F4F3FA] text-[#77768C] border border-[#E8E6F0]">
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
                        <td colspan="8" class="px-4 py-8 text-center text-[#77768C]">Belum ada riwayat penugasan tour.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payments Made Table -->
    <div class="clay-table-wrapper">
        <div class="px-5 py-4 border-b border-[#E8E6F0]">
            <h3 class="text-sm font-extrabold text-[#29283D]">Riwayat Pembayaran yang Telah Ditransfer</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F4F3FA] text-[#77768C] font-bold border-b border-[#E8E6F0]">
                    <tr>
                        <th class="px-4 py-3">Kode Pembayaran</th>
                        <th class="px-4 py-3">Tanggal Bayar</th>
                        <th class="px-4 py-3">Metode Pembayaran</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3">Catatan</th>
                        <th class="px-4 py-3">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($payroll->payments as $pmt)
                    <tr class="hover:bg-[#F4F3FA]/70 transition-colors">
                        <td class="px-4 py-3 font-bold text-[#6C63FF] font-mono">{{ $pmt->payment_code }}</td>
                        <td class="px-4 py-3 text-[#29283D] font-medium">{{ $pmt->payment_date->format('d F Y') }}</td>
                        <td class="px-4 py-3 text-[#77768C]">{{ $pmt->payment_method }}</td>
                        <td class="px-4 py-3 text-right font-extrabold text-[#72D6C9]">
                            Rp {{ number_format($pmt->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-[#77768C]">{{ $pmt->notes ?? '-' }}</td>
                        <td class="px-4 py-3 text-[#77768C]">{{ $pmt->creator?->name ?? 'Admin' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-[#77768C]">Belum ada riwayat pembayaran untuk guide ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Record Payment Modal (Requirement 32) -->
    <div x-show="paymentModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#29283D]/50 backdrop-blur-xs">
        <div @click.away="paymentModalOpen = false" class="clay-card max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3">
                <h3 class="text-sm font-extrabold text-[#29283D] flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-[#6C63FF]/10 text-[#6C63FF] flex items-center justify-center"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></span>
                    Catat Pembayaran Gaji / Fee Guide
                </h3>
                <button type="button" @click="paymentModalOpen = false" class="text-[#77768C] hover:text-[#29283D] text-lg font-bold">&times;</button>
            </div>

            <div class="p-3.5 bg-[#6C63FF]/10 rounded-2xl border border-[#6C63FF]/20 text-xs text-[#29283D]">
                Sisa hak fee yang belum dibayar: <strong class="text-[#6C63FF]">Rp {{ number_format($payroll->remaining, 0, ',', '.') }}</strong>
            </div>

            <form action="{{ route('payroll.payment', $guide) }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-[#29283D] mb-1.5">Nominal Pembayaran (Rp) <span class="text-[#FF8A65]">*</span></label>
                    <input type="number" name="amount" value="{{ $payroll->remaining }}" max="{{ $payroll->remaining }}" min="1000" step="1000" required
                           class="w-full clay-input font-extrabold text-sm text-[#6C63FF]">
                    <span class="text-[10px] text-[#77768C] block mt-1">Sistem memvalidasi nominal agar tidak melebihi sisa fee yang berhak diterima.</span>
                </div>

                <div>
                    <label class="block font-bold text-[#29283D] mb-1.5">Tanggal Pembayaran <span class="text-[#FF8A65]">*</span></label>
                    <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required
                           class="w-full clay-input">
                </div>

                <div>
                    <label class="block font-bold text-[#29283D] mb-1.5">Metode Pembayaran</label>
                    <select name="payment_method" class="w-full clay-input font-bold">
                        <option value="Bank Transfer BCA">Bank Transfer BCA</option>
                        <option value="Bank Transfer Mandiri">Bank Transfer Mandiri</option>
                        <option value="Bank Transfer BNI / BRI">Bank Transfer BNI / BRI</option>
                        <option value="Cash / Tunai">Cash / Tunai</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#29283D] mb-1.5">Catatan / Bukti Transfer</label>
                    <input type="text" name="notes" placeholder="Contoh: No Ref Transfer #998822"
                           class="w-full clay-input">
                </div>

                <div class="flex items-center">
                    <label class="flex items-center text-[11px] text-[#77768C] cursor-pointer">
                        <input type="checkbox" name="admin_override" value="1" class="rounded-lg text-[#6C63FF] focus:ring-[#6C63FF] w-4 h-4">
                        <span class="ml-2.5">Admin Override (izinkan pembayaran melebihi sisa balance jika ada penyesuaian khusus)</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-[#E8E6F0] flex items-center justify-end space-x-2.5">
                    <button type="button" @click="paymentModalOpen = false" class="clay-btn clay-btn-secondary px-4 py-2 font-bold">
                        Batal
                    </button>
                    <button type="submit" class="clay-btn clay-btn-primary px-5 py-2 font-bold">
                        Konfirmasi Pembayaran &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
