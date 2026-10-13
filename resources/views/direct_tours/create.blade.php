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

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Main Options -->
            <div class="md:col-span-2 space-y-6">
                
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
                                        ⚡ Otomatis Sesuai Antrean Guide (Rekomendasi)
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
                <div class="clay-card p-6 sticky top-20 space-y-5">
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

                        <!-- Est Guide Fee -->
                        <div class="p-3.5 rounded-2xl bg-[#72D6C9]/15 border border-[#72D6C9]/40 space-y-1.5 mt-2 text-[#147a6f]">
                            <div class="flex justify-between">
                                <span>Estimasi Fee Guide:</span>
                                <span class="font-bold" x-text="formatRupiah(estGuideFee)"></span>
                            </div>
                            <div class="flex justify-between font-bold border-t border-[#72D6C9]/30 pt-1">
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

