@extends('layouts.app')

@section('title', 'Buat Tour Langsung (Tanpa Booking)')

@section('content')
<div class="space-y-6" x-data="{
    packageId: '{{ $tourPackages->first()->id ?? '' }}',
    guestCount: 2,
    guideChoice: 'auto',
    packages: {{ Js::from($tourPackages) }},
    pricePerGuest: 0,
    totalPrice: 0,
    defaultGuideFee: {{ $defaultGuideFee ?? 50000 }},
    phoneFee: {{ $defaultPhoneFee ?? 0 }},
    giveDriverFee: false,
    defaultDriverFee: {{ $defaultDriverFee ?? 50000 }},
    driverFee: {{ $defaultDriverFee ?? 50000 }},
    estGuideFee: 0,
    estProfit: 0,
    startImmediately: true,
    paymentMethod: 'CASH',
    amountPaid: '',
    paymentReference: '',
    get changeAmount() {
        if (this.paymentMethod !== 'CASH') return 0;
        const paid = parseFloat(this.amountPaid) || 0;
        return Math.max(0, paid - this.totalPrice);
    },
    get paymentMethodLabel() {
        const map = {
            'CASH': 'Tunai (Cash)',
            'QRIS': 'QRIS Scan',
            'TRANSFER': 'Transfer Bank',
            'CARD': 'Kartu Debit / Kredit (EDC)'
        };
        return map[this.paymentMethod] || this.paymentMethod;
    },
    calculate() {
        const pkg = this.packages.find(p => p.id == this.packageId);
        if (!pkg) return;
        
        let price = parseFloat(pkg.default_price);
        if (pkg.prices && pkg.prices.length > 0) {
            const count = parseInt(this.guestCount) || 1;
            const match = pkg.prices.find(tier => count >= tier.min_guests && count <= tier.max_guests && tier.active);
            if (match) {
                price = parseFloat(match.price_per_guest);
            }
        }
        
        this.pricePerGuest = price;
        this.totalPrice = price * (parseInt(this.guestCount) || 1);
        this.estGuideFee = (parseInt(this.guestCount) || 1) * this.defaultGuideFee;
        const phone = parseFloat(this.phoneFee) || 0;
        const driver = this.giveDriverFee ? (parseFloat(this.defaultDriverFee) || 0) : 0;
        this.estProfit = Math.max(0, this.totalPrice - this.estGuideFee - phone - driver);
    },
    formatRupiah(num) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
    }
}" x-init="calculate(); $watch('packageId', () => calculate()); $watch('guestCount', () => calculate()); $watch('phoneFee', () => calculate()); $watch('giveDriverFee', () => calculate());">

    <!-- Header Section -->
    <div class="clay-card-primary p-6 text-white relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1 relative z-10">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#FFD166] text-[#29283D] shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Fast Walk-in Dispatch
            </div>
            <h1 class="text-2xl font-black tracking-tight font-display text-white">Tour Langsung (Tanpa Booking)</h1>
            <p class="text-white/85 text-xs max-w-xl">
                Layani tamu on-the-spot seketika. Sistem langsung menugaskan guide dan mencatat transaksi operasional.
            </p>
        </div>
        <div class="flex items-center gap-2 relative z-10">
            <a href="{{ route('bookings.index') }}" class="clay-btn bg-white/20 hover:bg-white/30 text-white border border-white/20 py-2 px-3.5 text-xs font-bold backdrop-blur-xs">
                Lihat Semua Booking
            </a>
            <a href="{{ route('queue.board') }}" class="clay-btn bg-[#FFD166] text-[#29283D] py-2 px-3.5 text-xs font-black shadow-md">
                Antrean Guide
            </a>
        </div>
    </div>

    <!-- Form Section -->
    <form action="{{ route('direct-tours.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Main Options -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Card 1: Paket & Tamu -->
                <div class="clay-card p-6 space-y-4">
                    <h2 class="text-base font-bold text-[#29283D] font-display flex items-center gap-2.5 pb-2.5 border-b border-[#E8E6F0]">
                        <span class="w-8 h-8 rounded-xl bg-[#6C63FF]/15 text-[#6C63FF] flex items-center justify-center font-extrabold text-xs">1</span>
                        Paket Wisata & Jumlah Tamu
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-[#29283D] mb-1.5">Pilih Paket Wisata <span class="text-[#FF8A65]">*</span></label>
                            <select name="tour_package_id" x-model="packageId" required
                                class="clay-input text-xs font-bold">
                                @foreach($tourPackages as $pkg)
                                <option value="{{ $pkg->id }}">
                                    {{ $pkg->name }} — Rp {{ number_format($pkg->default_price, 0, ',', '.') }} ({{ $pkg->duration }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#29283D] mb-1.5">Jumlah Tamu (Orang) <span class="text-[#FF8A65]">*</span></label>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="if (guestCount > 1) guestCount--"
                                        class="w-10 h-10 rounded-xl bg-[#F4F3FA] hover:bg-[#E8E6F0] text-[#29283D] font-black text-lg flex items-center justify-center transition shadow-2xs">
                                        -
                                    </button>
                                    <input type="number" name="guest_count" x-model="guestCount" min="1" max="99" required
                                        class="clay-input text-center text-base font-black text-[#29283D]">
                                    <button type="button" @click="guestCount++"
                                        class="w-10 h-10 rounded-xl bg-[#F4F3FA] hover:bg-[#E8E6F0] text-[#29283D] font-black text-lg flex items-center justify-center transition shadow-2xs">
                                        +
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#29283D] mb-1.5">Nama Tamu / Walk-in Group</label>
                                <input type="text" name="customer_name" value="Tamu Walk-in" required placeholder="Contoh: Tamu Walk-in / Mr. Smith"
                                    class="clay-input text-xs">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div>
                                <label class="block text-xs font-bold text-[#29283D] mb-1.5">Nomor WhatsApp / HP (Opsional)</label>
                                <input type="text" name="customer_phone" placeholder="08xxxxxxxxxx"
                                    class="clay-input text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#29283D] mb-1.5">Asal / Kebangsaan</label>
                                <input type="text" name="nationality" value="Indonesia" placeholder="Indonesia / Australia / dsb"
                                    class="clay-input text-xs">
                            </div>
                        </div>

                        <!-- Biaya Telepon Otomatis dari Pengaturan Sistem (seperti Fee Guide) -->
                        <input type="hidden" name="phone_fee" :value="phoneFee">

                        <!-- Pilihan Kasih Uang ke Driver (Jika Diperlukan) -->
                        <div class="p-4 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0] space-y-3 mt-3 transition-all"
                            :class="giveDriverFee ? 'border-[#FF8A65]/50 bg-[#FFF5F2] ring-1 ring-[#FF8A65]/30' : ''">
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                                    <input type="checkbox" name="give_driver_fee" value="1" x-model="giveDriverFee" @change="calculate()"
                                        class="w-4 h-4 rounded text-[#FF8A65] border-[#E8E6F0] focus:ring-[#FF8A65]">
                                    <span class="font-bold text-xs text-[#29283D] flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-[#FF8A65]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        Kasih Uang ke Driver (Jika Diperlukan)
                                    </span>
                                </label>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                    :class="giveDriverFee ? 'bg-[#FF8A65]/15 text-[#d35400] border border-[#FF8A65]/30' : 'bg-[#E8E6F0] text-[#77768C]'">
                                    <span x-text="giveDriverFee ? 'Aktif' : 'Tidak / Opsional'"></span>
                                </span>
                            </div>

                            <!-- Detail Uang Driver Saat Diaktifkan -->
                            <div x-show="giveDriverFee" x-transition class="space-y-3 pt-2.5 border-t border-[#E8E6F0]" x-cloak>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">
                                            Nominal Uang Driver (Rp)
                                        </label>
                                        <div class="flex items-center rounded-xl bg-[#F4F3FA] border border-[#E8E6F0] overflow-hidden select-none cursor-not-allowed shadow-2xs">
                                            <span class="px-3.5 py-2.5 bg-[#ECEBFC] text-xs font-bold text-[#6C63FF] border-r border-[#E8E6F0] select-none">Rp</span>
                                            <input type="text" value="{{ number_format($defaultDriverFee ?? 50000, 0, ',', '.') }}" readonly disabled
                                                class="flex-1 px-3 py-2.5 bg-transparent text-xs font-black text-[#29283D] cursor-not-allowed outline-none select-none min-w-0">
                                            <input type="hidden" name="driver_fee" value="{{ $defaultDriverFee ?? 50000 }}">
                                            <div class="pr-2.5 flex items-center shrink-0">
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-lg bg-[#6C63FF]/15 text-[#6C63FF] border border-[#6C63FF]/30 select-none whitespace-nowrap"
                                                    title="Nominal uang driver terkunci di Pengaturan Admin">
                                                    <svg class="w-3 h-3 text-[#6C63FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                    </svg>
                                                    <span>Terkunci</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-[#29283D] mb-1.5 truncate">
                                            Catatan Driver / Plat Mobil (Opsional)
                                        </label>
                                        <input type="text" name="driver_notes" placeholder="Contoh: Bli Ketut / Avanza DK 1234 XY"
                                            class="clay-input text-xs w-full py-2.5">
                                    </div>
                                </div>
                                <p class="text-[11px] text-[#77768C] leading-relaxed pt-0.5">
                                    Nominal uang driver terkunci otomatis: <strong>Rp {{ number_format($defaultDriverFee ?? 50000, 0, ',', '.') }}</strong> (hanya dapat diubah melalui menu <strong>Pengaturan Admin</strong>). Biaya ini otomatis dicatat ke pengeluaran tour.
                                </p>
                            </div>
                        </div>

                        <!-- Pilihan Metode Pembayaran Tamu -->
                        <div class="p-4 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0] space-y-3.5 mt-3">
                            <div class="flex items-center justify-between pb-2 border-b border-[#E8E6F0]">
                                <label class="block text-xs font-bold text-[#29283D] flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-[#6C63FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    Pilihan Metode Pembayaran <span class="text-[#FF8A65]">*</span>
                                </label>
                                <span class="text-[11px] font-extrabold px-2.5 py-0.5 rounded-full bg-[#6C63FF]/15 text-[#6C63FF] border border-[#6C63FF]/30" x-text="paymentMethodLabel">
                                </span>
                            </div>

                            <!-- Hidden input for form submission -->
                            <input type="hidden" name="payment_method" :value="paymentMethod">

                            <!-- 4 Clay Radio Selector Cards -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                <!-- 1. Tunai / Cash -->
                                <div @click="paymentMethod = 'CASH'"
                                    :class="paymentMethod === 'CASH' ? 'border-[#6C63FF] bg-[#6C63FF]/10 ring-2 ring-[#6C63FF]/30 shadow-xs' : 'border-[#E8E6F0] bg-white hover:border-[#6C63FF]/40'"
                                    class="p-3 rounded-2xl border cursor-pointer transition-all flex flex-col items-center justify-center text-center gap-1.5 select-none">
                                    <span class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-600 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </span>
                                    <span class="font-bold text-xs text-[#29283D]">Tunai (Cash)</span>
                                    <span class="text-[10px] text-[#77768C]">Uang Fisik</span>
                                </div>

                                <!-- 2. QRIS -->
                                <div @click="paymentMethod = 'QRIS'"
                                    :class="paymentMethod === 'QRIS' ? 'border-[#6C63FF] bg-[#6C63FF]/10 ring-2 ring-[#6C63FF]/30 shadow-xs' : 'border-[#E8E6F0] bg-white hover:border-[#6C63FF]/40'"
                                    class="p-3 rounded-2xl border cursor-pointer transition-all flex flex-col items-center justify-center text-center gap-1.5 select-none">
                                    <span class="w-8 h-8 rounded-xl bg-purple-500/15 text-purple-600 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                    </span>
                                    <span class="font-bold text-xs text-[#29283D]">QRIS Scan</span>
                                    <span class="text-[10px] text-[#77768C]">BCA / e-Wallet</span>
                                </div>

                                <!-- 3. Transfer Bank -->
                                <div @click="paymentMethod = 'TRANSFER'"
                                    :class="paymentMethod === 'TRANSFER' ? 'border-[#6C63FF] bg-[#6C63FF]/10 ring-2 ring-[#6C63FF]/30 shadow-xs' : 'border-[#E8E6F0] bg-white hover:border-[#6C63FF]/40'"
                                    class="p-3 rounded-2xl border cursor-pointer transition-all flex flex-col items-center justify-center text-center gap-1.5 select-none">
                                    <span class="w-8 h-8 rounded-xl bg-blue-500/15 text-blue-600 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                        </svg>
                                    </span>
                                    <span class="font-bold text-xs text-[#29283D]">Transfer Bank</span>
                                    <span class="text-[10px] text-[#77768C]">BCA / Mandiri</span>
                                </div>

                                <!-- 4. Kartu Debit / EDC -->
                                <div @click="paymentMethod = 'CARD'"
                                    :class="paymentMethod === 'CARD' ? 'border-[#6C63FF] bg-[#6C63FF]/10 ring-2 ring-[#6C63FF]/30 shadow-xs' : 'border-[#E8E6F0] bg-white hover:border-[#6C63FF]/40'"
                                    class="p-3 rounded-2xl border cursor-pointer transition-all flex flex-col items-center justify-center text-center gap-1.5 select-none">
                                    <span class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-600 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </span>
                                    <span class="font-bold text-xs text-[#29283D]">Kartu EDC</span>
                                    <span class="text-[10px] text-[#77768C]">Debit / Kredit</span>
                                </div>
                            </div>

                            <!-- 1. Conditional: CASH (Kalkulator Uang Diterima & Kembalian) -->
                            <div x-show="paymentMethod === 'CASH'" class="p-3.5 rounded-xl bg-white border border-[#E8E6F0] space-y-2.5" x-cloak>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
                                    <div>
                                        <label class="block text-[11px] font-bold text-[#29283D] mb-1">
                                            Uang Diterima dari Tamu (Rp)
                                        </label>
                                        <div class="flex items-center rounded-xl bg-[#F4F3FA] border border-[#E8E6F0] shadow-2xs overflow-hidden focus-within:border-[#6C63FF] focus-within:ring-2 focus-within:ring-[#6C63FF]/20">
                                            <span class="px-3 py-1.5 bg-[#ECEBFC] text-xs font-bold text-[#6C63FF] border-r border-[#E8E6F0] select-none">Rp</span>
                                            <input type="number" name="amount_paid" x-model="amountPaid" min="0" step="5000" placeholder="0"
                                                class="flex-1 px-3 py-1.5 bg-transparent text-xs font-bold text-[#29283D] outline-none">
                                        </div>
                                    </div>

                                    <div class="flex flex-col justify-end">
                                        <span class="text-[11px] font-bold text-[#29283D] mb-1">Kembalian:</span>
                                        <div class="px-3.5 py-1.5 rounded-xl border font-black text-sm font-display flex items-center justify-between"
                                            :class="(parseFloat(amountPaid) || 0) >= totalPrice && (parseFloat(amountPaid) || 0) > 0 ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-700' : 'bg-slate-100 border-slate-200 text-slate-400'">
                                            <span class="text-[10px] uppercase font-bold tracking-wider">Kembalian</span>
                                            <span x-text="formatRupiah(changeAmount)"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Amount Buttons -->
                                <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                    <span class="text-[10px] font-bold text-[#77768C] mr-1">Tombol Cepat:</span>
                                    <button type="button" @click="amountPaid = totalPrice"
                                        class="px-2.5 py-1 rounded-lg bg-[#ECEBFC] text-[11px] font-bold text-[#6C63FF] hover:bg-[#6C63FF] hover:text-white transition shadow-2xs">
                                        Uang Pas
                                    </button>
                                    <button type="button" @click="amountPaid = Math.ceil(totalPrice / 50000) * 50000"
                                        class="px-2.5 py-1 rounded-lg bg-[#F4F3FA] text-[11px] font-bold text-[#29283D] hover:bg-slate-200 transition shadow-2xs">
                                        Bulat 50rb
                                    </button>
                                    <button type="button" @click="amountPaid = Math.ceil(totalPrice / 100000) * 100000"
                                        class="px-2.5 py-1 rounded-lg bg-[#F4F3FA] text-[11px] font-bold text-[#29283D] hover:bg-slate-200 transition shadow-2xs">
                                        Bulat 100rb
                                    </button>
                                </div>
                            </div>

                            <!-- 2. Conditional: QRIS -->
                            <div x-show="paymentMethod === 'QRIS'" class="p-3.5 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-900 space-y-2 text-xs" x-cloak>
                                <div class="flex items-center gap-2 font-bold text-xs">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Pembayaran via QRIS (BCA / Bank / e-Wallet)</span>
                                </div>
                                <p class="text-[11px] text-purple-800/90 leading-relaxed">
                                    Minta tamu memindai barcode QRIS resmi yang tersedia di meja kasir. Periksa notifikasi saldo masuk pada aplikasi kasir sebelum memulai tour.
                                </p>
                                <div>
                                    <label class="block text-[11px] font-bold text-[#29283D] mb-1">No. Referensi / RRN QRIS (Opsional)</label>
                                    <input type="text" name="payment_reference" x-model="paymentReference" placeholder="Contoh: RRN-98234821 / QRIS BCA"
                                        class="clay-input text-xs w-full bg-white">
                                </div>
                            </div>

                            <!-- 3. Conditional: TRANSFER -->
                            <div x-show="paymentMethod === 'TRANSFER'" class="p-3.5 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-900 space-y-2 text-xs" x-cloak>
                                <div class="flex items-center gap-2 font-bold text-xs">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Transfer Rekening Bank Perusahaan</span>
                                </div>
                                <p class="text-[11px] text-blue-800/90 leading-relaxed">
                                    Pastikan bukti transfer telah dicek dan dana sudah masuk ke rekening resmi perusahaan.
                                </p>
                                <div>
                                    <label class="block text-[11px] font-bold text-[#29283D] mb-1">Catatan Bank / No. Bukti Transfer (Opsional)</label>
                                    <input type="text" name="payment_reference" x-model="paymentReference" placeholder="Contoh: BCA Transfer an. John Doe / No. Ref 12345"
                                        class="clay-input text-xs w-full bg-white">
                                </div>
                            </div>

                            <!-- 4. Conditional: CARD -->
                            <div x-show="paymentMethod === 'CARD'" class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-900 space-y-2 text-xs" x-cloak>
                                <div class="flex items-center gap-2 font-bold text-xs">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Gesek Kartu Mesin EDC (Debit / Kredit)</span>
                                </div>
                                <p class="text-[11px] text-amber-800/90 leading-relaxed">
                                    Proses pembayaran tamu pada mesin EDC hingga struk keluar dan status APPROVED.
                                </p>
                                <div>
                                    <label class="block text-[11px] font-bold text-[#29283D] mb-1">No. Trace / Approval Code Mesin EDC (Opsional)</label>
                                    <input type="text" name="payment_reference" x-model="paymentReference" placeholder="Contoh: EDC Mandiri Trace: 004812"
                                        class="clay-input text-xs w-full bg-white">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Penugasan Guide -->
                <div class="clay-card p-6 space-y-4">
                    <h2 class="text-base font-bold text-[#29283D] font-display flex items-center gap-2.5 pb-2.5 border-b border-[#E8E6F0]">
                        <span class="w-8 h-8 rounded-xl bg-[#6C63FF]/15 text-[#6C63FF] flex items-center justify-center font-extrabold text-xs">2</span>
                        Penugasan Guide
                    </h2>

                    <div class="space-y-3">
                        <input type="hidden" name="guide_id" :value="guideChoice">

                        <!-- Option Auto Circular Queue -->
                        <label class="relative flex items-start p-4 rounded-2xl border cursor-pointer transition-all duration-200"
                            :class="guideChoice === 'auto' ? 'border-[#6C63FF] bg-[#6C63FF]/5 ring-2 ring-[#6C63FF]/20 shadow-sm' : 'border-[#E8E6F0] bg-white hover:border-[#6C63FF]/30'">
                            <input type="radio" value="auto" x-model="guideChoice" class="mt-1 w-4 h-4 text-[#6C63FF] focus:ring-[#6C63FF]">
                            <div class="ml-3 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-sm text-[#29283D] flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-[#6C63FF] inline-block mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg><span>Otomatis Sesuai Antrean Guide (Rekomendasi)</span>
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#6C63FF] text-white">Adil & Merata</span>
                                </div>
                                <p class="text-xs text-[#77768C] mt-1">
                                    Sistem otomatis memilih guide giliran berikutnya yang sudah check-in dan bebas tugas hari ini.
                                </p>
                                @if(!empty($nextCandidateResult['guide']))
                                <div class="mt-2.5 inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#72D6C9]/15 border border-[#72D6C9]/40 text-xs text-[#147a6f] font-bold">
                                    <span class="w-2 h-2 rounded-full bg-[#72D6C9]"></span>
                                    Giliran Terdepan Saat Ini: Antrean #{{ $nextCandidateResult['guide']->queue_number }} — {{ $nextCandidateResult['guide']->nama }}
                                </div>
                                @else
                                <div class="mt-2 text-xs text-[#8a6800] bg-[#FFD166]/20 p-2.5 rounded-xl border border-[#FFD166]/50">
                                    Perhatian: Belum ada guide yang check-in hari ini. Jika memilih otomatis, pastikan ada guide aktif.
                                </div>
                                @endif
                            </div>
                        </label>

                        <!-- Option Manual Pick -->
                        <label class="relative flex items-start p-4 rounded-2xl border cursor-pointer transition-all duration-200"
                            :class="guideChoice !== 'auto' ? 'border-[#6C63FF] bg-[#6C63FF]/5 ring-2 ring-[#6C63FF]/20 shadow-sm' : 'border-[#E8E6F0] bg-white hover:border-[#6C63FF]/30'">
                            <input type="radio" value="manual"
                                @click="if (guideChoice === 'auto') guideChoice = '{{ $guides->first()->guide->id ?? '' }}'"
                                :checked="guideChoice !== 'auto'" class="mt-1 w-4 h-4 text-[#6C63FF] focus:ring-[#6C63FF]">
                            <div class="ml-3 flex-1">
                                <span class="font-bold text-sm text-[#29283D]">Pilih Guide Tertentu Secara Manual</span>
                                <p class="text-xs text-[#77768C] mt-0.5">Tentukan langsung guide spesifik untuk mendampingi tamu ini.</p>

                                <div class="mt-3" x-show="guideChoice !== 'auto'" x-cloak>
                                    <select x-model="guideChoice"
                                        class="clay-input text-xs font-bold text-[#29283D]">
                                        @foreach($guides as $row)
                                        <option value="{{ $row->guide->id }}">
                                            #{{ $row->guide->queue_number }} - {{ $row->guide->nama }}
                                            ({{ $row->is_checked_in ? 'Sudah Check-In' : 'Belum Check-In' }} | Status: {{ $row->guide->status }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Card 3: Status Tour Langsung -->
                <div class="clay-card p-6 space-y-4">
                    <h2 class="text-base font-bold text-[#29283D] font-display flex items-center gap-2.5 pb-2.5 border-b border-[#E8E6F0]">
                        <span class="w-8 h-8 rounded-xl bg-[#6C63FF]/15 text-[#6C63FF] flex items-center justify-center font-extrabold text-xs">3</span>
                        Eksekusi & Status Operasional
                    </h2>

                    <div class="p-4 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0] space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="start_immediately" value="1" x-model="startImmediately"
                                class="w-5 h-5 rounded-lg text-[#6C63FF] border-[#E8E6F0] focus:ring-[#6C63FF]">
                            <div>
                                <span class="font-bold text-sm text-[#29283D]">Langsung Mulai Tour Sekarang (Status: Sedang Berjalan)</span>
                                <p class="text-xs text-[#77768C]">
                                    Tour langsung berstatus aktif (ON_GOING) dan status guide otomatis berubah menjadi ON_TOUR.
                                </p>
                            </div>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" rows="2" placeholder="Catatan khusus, nomor mobil/kendaraan, titik kumpul di dermaga, dll."
                            class="clay-input text-xs"></textarea>
                    </div>
                </div>

            </div>

            <!-- Right 1 Col: Summary & Dispatch -->
            <div class="space-y-6">
                <div class="clay-card p-6 sticky top-4 space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-[#E8E6F0]">
                        <h3 class="font-extrabold text-sm text-[#29283D] font-display">Ringkasan Tour Langsung</h3>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FFD166]/20 text-[#8a6800] border border-[#FFD166]/40">Instan</span>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between text-[#77768C]">
                            <span>Tanggal & Waktu</span>
                            <span class="font-bold text-[#29283D]">{{ date('d M Y') }} • {{ date('H:i') }} WITA</span>
                        </div>
                        <div class="flex justify-between text-[#77768C]">
                            <span>Jumlah Tamu</span>
                            <span class="font-bold text-[#29283D]" x-text="guestCount + ' Orang'"></span>
                        </div>
                        <div class="flex justify-between text-[#77768C]">
                            <span>Harga per Tamu</span>
                            <span class="font-bold text-[#29283D]" x-text="formatRupiah(pricePerGuest)"></span>
                        </div>
                        <div class="pt-2.5 border-t border-[#E8E6F0] flex justify-between items-center">
                            <span class="font-bold text-[#29283D] text-sm">Total Tagihan</span>
                            <span class="font-black text-[#6C63FF] text-lg font-display" x-text="formatRupiah(totalPrice)"></span>
                        </div>

                        <!-- Metode Pembayaran Ringkasan -->
                        <div class="p-3 rounded-xl bg-[#F4F3FA] border border-[#E8E6F0] space-y-1.5 mt-2">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-[#77768C] font-semibold">Metode Bayar:</span>
                                <span class="font-bold text-[#6C63FF] px-2 py-0.5 rounded-lg bg-white border border-[#E8E6F0] text-[11px]" x-text="paymentMethodLabel"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs" x-show="paymentMethod === 'CASH' && (parseFloat(amountPaid) || 0) > 0" x-cloak>
                                <span class="text-[#77768C]">Uang Diterima:</span>
                                <span class="font-bold text-[#29283D]" x-text="formatRupiah(amountPaid)"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs" x-show="paymentMethod === 'CASH' && (parseFloat(amountPaid) || 0) >= totalPrice" x-cloak>
                                <span class="text-emerald-700 font-bold">Kembalian:</span>
                                <span class="font-black text-emerald-700 font-display" x-text="formatRupiah(changeAmount)"></span>
                            </div>
                            <div class="flex justify-between items-center text-[11px]" x-show="paymentMethod !== 'CASH' && paymentReference" x-cloak>
                                <span class="text-[#77768C]">Referensi:</span>
                                <span class="font-mono text-[#29283D] truncate max-w-[130px]" x-text="paymentReference"></span>
                            </div>
                        </div>

                        <!-- Est Guide Fee & Pengurangan Biaya Telepon -->
                        <div class="p-3.5 rounded-2xl bg-[#72D6C9]/15 border border-[#72D6C9]/40 space-y-1.5 mt-2 text-[#147a6f]">
                            <div class="flex justify-between">
                                <span>Estimasi Fee Guide:</span>
                                <span class="font-bold" x-text="formatRupiah(estGuideFee)"></span>
                            </div>
                            <div class="flex justify-between text-[#c0392b]" x-show="parseFloat(phoneFee) > 0">
                                <span>Pengurangan Biaya Telpon:</span>
                                <span class="font-bold" x-text="'- ' + formatRupiah(phoneFee)"></span>
                            </div>
                            <div class="flex justify-between text-[#d35400]" x-show="giveDriverFee && parseFloat(driverFee) > 0">
                                <span>Uang Driver:</span>
                                <span class="font-bold" x-text="'- ' + formatRupiah(driverFee)"></span>
                            </div>
                            <div class="flex justify-between font-bold border-t border-[#72D6C9]/30 pt-1 text-[#0e5c54]">
                                <span>Estimasi Margin Usaha:</span>
                                <span x-text="formatRupiah(estProfit)"></span>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="clay-btn clay-btn-coral w-full py-3.5 px-4 font-bold text-sm tracking-wide flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Mulai Tour Langsung</span>
                    </button>

                    <p class="text-[11px] text-[#77768C] text-center leading-relaxed">
                        Data tour langsung tercatat otomatis di laporan omset harian, antrean guide, dan payroll.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection