@extends('layouts.app')

@section('title', 'Buat Tour Langsung (Tanpa Booking)')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto" x-data="{
    packageId: '{{ $tourPackages->first()->id ?? '' }}',
    guestCount: 2,
    guideChoice: 'auto',
    packages: {{ Js::from($tourPackages) }},
    pricePerGuest: 0,
    totalPrice: 0,
    estGuideFee: 0,
    estProfit: 0,
    startImmediately: true,
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
        this.estGuideFee = (parseInt(this.guestCount) || 1) * 50000;
        this.estProfit = Math.max(0, this.totalPrice - this.estGuideFee);
    },
    formatRupiah(num) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
    }
}" x-init="calculate(); $watch('packageId', () => calculate()); $watch('guestCount', () => calculate());">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-[#14433b] to-[#1c5d52] p-6 rounded-2xl text-white shadow-lg">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-400/20 text-emerald-300 border border-emerald-400/30">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Fast Walk-in Dispatch
            </div>
            <h1 class="text-2xl font-bold tracking-tight">Tour Langsung (Tanpa Booking)</h1>
            <p class="text-emerald-100/80 text-sm">
                Layani tamu on-the-spot seketika. Sistem langsung menugaskan guide dan mencatat transaksi operasional.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('bookings.index') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-medium backdrop-blur-xs transition">
                Lihat Semua Booking
            </a>
            <a href="{{ route('queue.board') }}" class="px-4 py-2 bg-[#21c9a4] hover:bg-[#1bb391] text-slate-900 rounded-xl text-xs font-bold transition shadow-sm">
                Antrean Guide
            </a>
        </div>
    </div>

    <!-- Form Section -->
    <form action="{{ route('direct-tours.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Main Options -->
            <div class="md:col-span-2 space-y-6">
                
                <!-- Card 1: Paket & Tamu -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2 pb-2 border-b border-slate-100">
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 text-[#14433b] flex items-center justify-center font-bold text-xs">1</span>
                        Paket Wisata & Jumlah Tamu
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Paket Wisata <span class="text-rose-500">*</span></label>
                            <select name="tour_package_id" x-model="packageId" required
                                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-[#21c9a4] focus:border-transparent font-medium">
                                @foreach($tourPackages as $pkg)
                                    <option value="{{ $pkg->id }}">
                                        {{ $pkg->name }} — Rp {{ number_format($pkg->default_price, 0, ',', '.') }} ({{ $pkg->duration }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Tamu (Orang) <span class="text-rose-500">*</span></label>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="if (guestCount > 1) guestCount--" 
                                            class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center transition">
                                        -
                                    </button>
                                    <input type="number" name="guest_count" x-model="guestCount" min="1" max="99" required
                                           class="w-full text-center py-2 bg-slate-50 border border-slate-300 rounded-xl text-base font-bold text-slate-800 focus:ring-2 focus:ring-[#21c9a4]">
                                    <button type="button" @click="guestCount++" 
                                            class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center transition">
                                        +
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Tamu / Walk-in Group</label>
                                <input type="text" name="customer_name" value="Tamu Walk-in" required placeholder="Contoh: Tamu Walk-in / Mr. Smith"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-[#21c9a4]">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp / HP (Opsional)</label>
                                <input type="text" name="customer_phone" placeholder="08xxxxxxxxxx"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-[#21c9a4]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Asal / Kebangsaan</label>
                                <input type="text" name="nationality" value="Indonesia" placeholder="Indonesia / Australia / dsb"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-[#21c9a4]">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Penugasan Guide -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2 pb-2 border-b border-slate-100">
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 text-[#14433b] flex items-center justify-center font-bold text-xs">2</span>
                        Penugasan Guide
                    </h2>

                    <div class="space-y-3">
                        <input type="hidden" name="guide_id" :value="guideChoice">

                        <!-- Option Auto Circular Queue -->
                        <label class="relative flex items-start p-4 rounded-xl border cursor-pointer transition"
                               :class="guideChoice === 'auto' ? 'border-[#14433b] bg-emerald-50/40 ring-1 ring-[#14433b]' : 'border-slate-200 hover:border-slate-300 bg-white'">
                            <input type="radio" value="auto" x-model="guideChoice" class="mt-1 w-4 h-4 text-[#14433b] focus:ring-[#21c9a4]">
                            <div class="ml-3 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-sm text-slate-800 flex items-center gap-1.5">
                                        ⚡ Otomatis Sesuai Antrean Guide (Rekomendasi)
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#14433b] text-white">Adil & Merata</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">
                                    Sistem otomatis memilih guide giliran berikutnya yang sudah check-in dan bebas tugas hari ini.
                                </p>
                                @if(!empty($nextCandidateResult['guide']))
                                    <div class="mt-2.5 inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white border border-emerald-200 text-xs text-emerald-900 font-semibold shadow-xs">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        Giliran Terdepan Saat Ini: Antrean #{{ $nextCandidateResult['guide']->queue_number }} — {{ $nextCandidateResult['guide']->nama }}
                                    </div>
                                @else
                                    <div class="mt-2 text-xs text-amber-700 bg-amber-50 p-2 rounded-lg border border-amber-200">
                                        Perhatian: Belum ada guide yang check-in hari ini. Jika memilih otomatis, pastikan ada guide aktif.
                                    </div>
                                @endif
                            </div>
                        </label>

                        <!-- Option Manual Pick -->
                        <label class="relative flex items-start p-4 rounded-xl border cursor-pointer transition"
                               :class="guideChoice !== 'auto' ? 'border-[#14433b] bg-emerald-50/40 ring-1 ring-[#14433b]' : 'border-slate-200 hover:border-slate-300 bg-white'">
                            <input type="radio" value="manual" 
                                   @click="if (guideChoice === 'auto') guideChoice = '{{ $guides->first()->guide->id ?? '' }}'" 
                                   :checked="guideChoice !== 'auto'" class="mt-1 w-4 h-4 text-[#14433b] focus:ring-[#21c9a4]">
                            <div class="ml-3 flex-1">
                                <span class="font-bold text-sm text-slate-800">Pilih Guide Tertentu Secara Manual</span>
                                <p class="text-xs text-slate-500 mt-0.5">Tentukan langsung guide spesifik untuk mendampingi tamu ini.</p>
                                
                                <div class="mt-3" x-show="guideChoice !== 'auto'" x-cloak>
                                    <select x-model="guideChoice" 
                                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-[#21c9a4]">
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
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2 pb-2 border-b border-slate-100">
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 text-[#14433b] flex items-center justify-center font-bold text-xs">3</span>
                        Eksekusi & Status Operasional
                    </h2>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="start_immediately" value="1" x-model="startImmediately"
                                   class="w-5 h-5 rounded text-[#14433b] border-slate-300 focus:ring-[#21c9a4]">
                            <div>
                                <span class="font-bold text-sm text-slate-800">Langsung Mulai Tour Sekarang (Status: Sedang Berjalan)</span>
                                <p class="text-xs text-slate-500">
                                    Tour langsung berstatus aktif (ON_GOING) dan status guide otomatis berubah menjadi ON_TOUR.
                                </p>
                            </div>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" rows="2" placeholder="Catatan khusus, nomor mobil/kendaraan, titik kumpul di dermaga, dll."
                                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-[#21c9a4]"></textarea>
                    </div>
                </div>

            </div>

            <!-- Right 1 Col: Summary & Dispatch -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs sticky top-20 space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="font-bold text-sm text-slate-800">Ringkasan Tour Langsung</h3>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Instan</span>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Tanggal & Waktu</span>
                            <span class="font-semibold text-slate-800">{{ date('d M Y') }} • {{ date('H:i') }} WITA</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Jumlah Tamu</span>
                            <span class="font-semibold text-slate-800" x-text="guestCount + ' Orang'"></span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Harga per Tamu</span>
                            <span class="font-semibold text-slate-800" x-text="formatRupiah(pricePerGuest)"></span>
                        </div>
                        <div class="pt-2 border-t border-slate-100 flex justify-between items-center">
                            <span class="font-bold text-slate-700 text-sm">Total Tagihan</span>
                            <span class="font-extrabold text-[#14433b] text-base" x-text="formatRupiah(totalPrice)"></span>
                        </div>

                        <!-- Est Guide Fee -->
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100 space-y-1.5 mt-2">
                            <div class="flex justify-between text-emerald-800">
                                <span>Estimasi Fee Guide:</span>
                                <span class="font-bold" x-text="formatRupiah(estGuideFee)"></span>
                            </div>
                            <div class="flex justify-between text-emerald-900 font-bold border-t border-emerald-200/60 pt-1">
                                <span>Estimasi Margin Usaha:</span>
                                <span x-text="formatRupiah(estProfit)"></span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full py-3 px-4 rounded-xl bg-[#14433b] hover:bg-[#1c5d52] text-white font-bold text-sm tracking-wide transition shadow-md flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 text-[#21c9a4]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Mulai Tour Langsung</span>
                    </button>

                    <p class="text-[11px] text-slate-400 text-center leading-relaxed">
                        Data tour langsung tercatat otomatis di laporan omset harian, antrean guide, dan payroll.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
