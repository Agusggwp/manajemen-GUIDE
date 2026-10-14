@extends('layouts.app')

@php
    // Clear flash success to prevent SweetAlert modal from interfering with thermal receipt
    session()->forget('success');
@endphp

@section('title', 'Nota Pembayaran - ' . $booking->booking_code)
@section('header', 'Nota Thermal Tamu')

@section('content')
<div class="space-y-6" x-data="{ paperWidth: '80mm' }">
    <!-- Action Controls (Hidden when printed) -->
    <div class="no-print clay-card p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-[#72D6C9]/20 text-[#147a6f] border border-[#72D6C9]/50 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                    Tour Berhasil Dibuat
                </span>
                <span class="text-xs font-mono font-bold text-[#77768C]">{{ $booking->booking_code }}</span>
            </div>
            <p class="text-xs text-[#77768C] mt-1 font-medium">
                Nota siap dicetak untuk tamu. Format ini khusus untuk printer thermal (58mm / 80mm) dan hanya menampilkan total tagihan tamu.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Paper Width Selector Preview -->
            <div class="inline-flex rounded-xl bg-[#F4F3FA] p-1 border border-[#E8E6F0] text-xs font-bold text-[#77768C]">
                <button type="button" @click="paperWidth = '80mm'"
                        :class="paperWidth === '80mm' ? 'bg-white text-[#29283D] shadow-xs' : 'hover:text-[#29283D]'"
                        class="px-2.5 py-1 rounded-lg transition-all">
                    80mm (Standar)
                </button>
                <button type="button" @click="paperWidth = '58mm'"
                        :class="paperWidth === '58mm' ? 'bg-white text-[#29283D] shadow-xs' : 'hover:text-[#29283D]'"
                        class="px-2.5 py-1 rounded-lg transition-all">
                    58mm (Kecil)
                </button>
            </div>

            <button type="button" onclick="window.print()"
                    class="clay-btn-primary px-4 py-2 text-xs font-bold inline-flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4 text-[#FFD166]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Nota Thermal</span>
            </button>

            <a href="{{ route('direct-tours.create') }}" class="clay-btn-coral px-3.5 py-2 text-xs font-bold inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                <span>+ Tour Baru</span>
            </a>

            <a href="{{ route('bookings.show', $booking) }}" class="clay-btn-secondary px-3.5 py-2 text-xs font-bold text-[#77768C] inline-flex items-center gap-1.5">
                <span>Detail Booking</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </a>
        </div>
    </div>

    <!-- Thermal Receipt Paper Display -->
    <div class="flex justify-center py-4">
        <div :style="`max-width: ${paperWidth === '58mm' ? '240px' : '340px'}`"
             class="receipt-container w-full bg-white text-black p-5 sm:p-6 shadow-xl border border-gray-200 transition-all duration-200 font-mono text-[12px] leading-relaxed relative rounded-sm">
            
            <!-- Top Jagged Paper Edge Visual Decoration (on screen only) -->
            <div class="no-print absolute -top-1.5 left-0 right-0 h-2 bg-repeat-x opacity-30"
                 style="background-image: radial-gradient(circle, #cbd5e1 35%, transparent 40%); background-size: 8px 8px;"></div>

            <!-- Header: Company Information -->
            <div class="text-center space-y-1">
                @php
                    $logoPath = null;
                    $rawLogo = $settings['company_logo'] ?? null;
                    if (!empty($rawLogo)) {
                        if (file_exists(public_path($rawLogo))) {
                            $logoPath = asset($rawLogo);
                        } elseif (file_exists(public_path('storage/' . $rawLogo))) {
                            $logoPath = asset('storage/' . $rawLogo);
                        } else {
                            $logoPath = asset($rawLogo);
                        }
                    }
                @endphp
                @if(!empty($logoPath))
                    <div class="flex justify-center mb-1.5">
                        <img src="{{ $logoPath }}" alt="{{ $settings['company_name'] }}" class="h-11 max-w-[130px] object-contain grayscale">
                    </div>
                @endif
                <h1 class="font-extrabold text-[15px] tracking-wider uppercase text-black">{{ $settings['company_name'] }}</h1>
                @if(!empty($settings['company_tagline']))
                    <p class="text-[10px] uppercase text-gray-700 tracking-tight">{{ $settings['company_tagline'] }}</p>
                @endif
                @if(!empty($settings['company_address']))
                    <p class="text-[10.5px] leading-tight text-gray-800">{{ $settings['company_address'] }}</p>
                @endif
                @if(!empty($settings['company_phone']))
                    <p class="text-[10.5px] text-gray-800">Telp/WA: {{ $settings['company_phone'] }}</p>
                @endif
            </div>

            <!-- Heavy Divider -->
            <div class="my-2.5 border-t border-dashed border-black"></div>

            <!-- Receipt Title & Meta Information -->
            <div class="text-center font-bold text-[12px] tracking-wide uppercase mb-2">
                BUKTI PEMBAYARAN TOUR
            </div>

            <div class="space-y-1 text-[11px]">
                <div class="flex justify-between">
                    <span class="text-gray-700">No. Nota</span>
                    <span class="font-bold text-black">{{ $booking->booking_code }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-700">Tanggal</span>
                    <span class="font-bold text-black">{{ $booking->booking_date->format('d/m/Y') }} {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-700">Nama Tamu</span>
                    <span class="font-bold text-black text-right truncate max-w-[170px]">{{ $booking->customer_name }}</span>
                </div>
                @if(!empty($booking->customer_phone))
                <div class="flex justify-between">
                    <span class="text-gray-700">No. HP</span>
                    <span class="font-bold text-black">{{ $booking->customer_phone }}</span>
                </div>
                @endif
                @if($assignedGuide)
                <div class="flex justify-between">
                    <span class="text-gray-700">Guide</span>
                    <span class="font-bold text-black text-right truncate max-w-[170px]">{{ $assignedGuide->nama }} (#{{ $assignedGuide->queue_number }})</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-700">Kasir / Staff</span>
                    <span class="font-bold text-black">{{ $booking->creator?->name ?? 'Staff' }}</span>
                </div>
            </div>

            <!-- Divider -->
            <div class="my-2.5 border-t border-dashed border-black"></div>

            <!-- Items / Tour Packages -->
            <div class="space-y-2 text-[11px]">
                <div>
                    <div class="font-bold text-black uppercase">
                        {{ $booking->tourPackage?->name ?? 'Tour Langsung' }}
                    </div>
                    <div class="flex justify-between items-center text-gray-900 mt-0.5">
                        <span>{{ $booking->guest_count }} Pax x Rp {{ number_format($booking->price_per_guest, 0, ',', '.') }}</span>
                        <span class="font-bold text-black">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Heavy Double/Dashed Divider -->
            <div class="my-3 border-t-2 border-dashed border-black"></div>

            <!-- TOTAL BILL TO PAY (GUEST BILL ONLY - NO DEDUCTIONS) -->
            <div class="space-y-1.5 py-1">
                <div class="flex justify-between items-baseline font-black text-[13px] text-black">
                    <span class="uppercase tracking-wider">TOTAL TAGIHAN</span>
                    <span class="text-[14px]">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-[10.5px] font-bold text-gray-800">
                    <span>METODE BAYAR</span>
                    <span class="font-bold text-black uppercase">{{ $booking->payment_method_label }}</span>
                </div>
                @if($booking->amount_paid && $booking->amount_paid > 0)
                <div class="flex justify-between items-center text-[10.5px] text-gray-800">
                    <span>DITERIMA</span>
                    <span class="font-bold text-black">Rp {{ number_format($booking->amount_paid, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-[10.5px] text-gray-800">
                    <span>KEMBALIAN</span>
                    <span class="font-bold text-black">Rp {{ number_format($booking->change_amount ?? 0, 0, ',', '.') }}</span>
                </div>
                @endif
                @if(!empty($booking->payment_reference))
                <div class="flex justify-between items-center text-[10px] text-gray-700">
                    <span>NO. REF / BUKTI</span>
                    <span class="font-mono text-right truncate max-w-[160px]">{{ $booking->payment_reference }}</span>
                </div>
                @endif
                <div class="flex justify-between items-center text-[10.5px] font-bold text-gray-800 pt-0.5">
                    <span>STATUS PEMBAYARAN</span>
                    <span class="border border-black px-1.5 py-0.2 uppercase text-[10px] tracking-wider">{{ ($booking->payment_status === 'PAID' || empty($booking->payment_status) || $booking->payment_status === 'LUNAS') ? 'LUNAS' : $booking->payment_status }}</span>
                </div>
            </div>

            <!-- Divider -->
            <div class="my-3 border-t border-dashed border-black"></div>

            <!-- Footer Message -->
            <div class="text-center space-y-1 text-[10px] text-gray-800">
                <p class="font-bold uppercase text-[10.5px]">*** TERIMA KASIH ***</p>
                <p>Selamat menikmati liburan bersama kami!</p>
                <p class="text-[9.5px] text-gray-600">Simpan nota ini sebagai bukti pembayaran yang sah.</p>
                <div class="pt-2 text-[9px] text-gray-500 font-mono tracking-widest uppercase">
                    * {{ $booking->booking_code }} *
                </div>
                <p class="text-[8.5px] text-gray-400 pt-0.5">Dicetak: {{ now()->format('d/m/Y H:i:s') }} WITA</p>
            </div>

            <!-- Bottom Jagged Paper Edge Visual Decoration (on screen only) -->
            <div class="no-print absolute -bottom-1.5 left-0 right-0 h-2 bg-repeat-x opacity-30"
                 style="background-image: radial-gradient(circle, #cbd5e1 35%, transparent 40%); background-size: 8px 8px;"></div>
        </div>
    </div>
</div>

<style>
/* Pure Thermal Printer Styling */
@media print {
    @page {
        size: 80mm auto;
        margin: 0;
    }

    html, body {
        width: 80mm !important;
        max-width: 80mm !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        color: #000000 !important;
        font-family: 'Courier New', Courier, monospace, 'Lucida Console' !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Completely hide all UI navigation, sidebars, buttons, headers, and any modals */
    .no-print,
    #art-alert-modal,
    #art-toast-container,
    .art-alert-card,
    .art-toast-item,
    .swal2-container,
    .swal2-popup,
    .swal2-backdrop,
    [class*="swal"],
    nav,
    aside,
    header,
    footer,
    #main-sidebar,
    .sidebar-container,
    .top-navbar,
    .breadcrumbs,
    .clay-navbar {
        display: none !important;
        visibility: hidden !important;
    }

    main {
        margin: 0 !important;
        padding: 0 !important;
        width: 80mm !important;
        max-width: 80mm !important;
    }

    .receipt-container {
        width: 76mm !important;
        max-width: 76mm !important;
        margin: 0 auto !important;
        padding: 4mm 3mm !important;
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        background: #ffffff !important;
        color: #000000 !important;
        font-size: 11px !important;
        line-height: 1.35 !important;
    }
}
</style>

@if(request()->has('autoprint') || session('autoprint'))
<script>
    window.addEventListener('DOMContentLoaded', () => {
        const alertHandler = window.ArtAlert || window.Swal;
        if (alertHandler && typeof alertHandler.close === 'function') {
            alertHandler.close();
        }
        setTimeout(() => {
            if (alertHandler && typeof alertHandler.close === 'function') {
                alertHandler.close();
            }
            window.print();
        }, 500);
    });
</script>
@endif
@endsection
