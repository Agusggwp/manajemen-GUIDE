@extends('layouts.app')

@section('title', 'Booking Baru')
@section('header', 'Quick Booking Tamu Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    packageId: '{{ $tourPackages->first()?->id }}',
    guestCount: 2,
    pricePerGuest: 150000,
    packagesData: {{ Js::from($tourPackages) }},
    recommendedGuide: '{{ $nextGuideResult['guide'] ? '#' . $nextGuideResult['guide']->queue_number . ' ' . $nextGuideResult['guide']->nama : 'Belum ada guide' }}',

    updatePricing() {
        const pkg = this.packagesData.find(p => p.id == this.packageId);
        if (!pkg) return;

        let foundPrice = pkg.default_price;
        if (pkg.prices && pkg.prices.length > 0) {
            for (let tier of pkg.prices) {
                if (tier.active && this.guestCount >= tier.min_guests && this.guestCount <= tier.max_guests) {
                    foundPrice = tier.price_per_guest;
                    break;
                }
            }
        }
        this.pricePerGuest = parseFloat(foundPrice);
    },

    get totalPrice() {
        return this.guestCount * this.pricePerGuest;
    },

    get estimatedGuideFee() {
        return this.guestCount * 50000;
    },

    get estimatedProfit() {
        return this.totalPrice - this.estimatedGuideFee;
    },

    formatRupiah(num) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
    }
}" x-init="updatePricing()">

    <div class="clay-card p-6 sm:p-8">
        <div class="border-b border-[#E8E6F0] pb-4 mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-[#29283D] font-display">Form Quick Booking Wisata Bali</h2>
                <p class="text-xs text-[#77768C]">Estimasi harga, fee guide, dan margin keuntungan dihitung real-time</p>
            </div>
            <a href="{{ route('bookings.index') }}" class="clay-btn clay-btn-secondary py-1.5 px-3 text-xs font-bold text-[#77768C]">
                &larr; Kembali
            </a>
        </div>

        <form action="{{ route('bookings.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Section 1: Customer Details -->
            <div>
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-[#6C63FF] mb-3">1. Data Pelanggan / Tamu</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Nama Pelanggan / Leader <span class="text-[#FF8A65]">*</span></label>
                        <input type="text" name="customer_name" required value="{{ old('customer_name') }}" placeholder="Contoh: John Smith"
                               class="clay-input text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="+62 812-xxxx-xxxx"
                               class="clay-input text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Kebangsaan / Asal</label>
                        <input type="text" name="nationality" value="{{ old('nationality', 'Indonesia') }}" placeholder="Indonesia, Australia, dll"
                               class="clay-input text-xs">
                    </div>
                </div>
            </div>

            <!-- Section 2: Tour & Date -->
            <div class="pt-5 border-t border-[#E8E6F0]">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-[#6C63FF] mb-3">2. Detail Tour & Jadwal</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Paket Wisata <span class="text-[#FF8A65]">*</span></label>
                        <select name="tour_package_id" x-model="packageId" @change="updatePricing()" required
                                class="clay-input text-xs font-bold">
                            @foreach($tourPackages as $pkg)
                                <option value="{{ $pkg->id }}">{{ $pkg->name }} ({{ $pkg->duration }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Tanggal Tour <span class="text-[#FF8A65]">*</span></label>
                        <input type="date" name="booking_date" required value="{{ old('booking_date', $today) }}"
                               class="clay-input text-xs font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Jumlah Tamu (Pax) <span class="text-[#FF8A65]">*</span></label>
                        <input type="number" name="guest_count" x-model.number="guestCount" @input="updatePricing()" min="1" max="500" required
                               class="clay-input text-xs font-black text-[#29283D]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Jam Mulai <span class="text-[#FF8A65]">*</span></label>
                        <input type="time" name="start_time" required value="{{ old('start_time', '08:30') }}"
                               class="clay-input text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Jam Selesai <span class="text-[#FF8A65]">*</span></label>
                        <input type="time" name="end_time" required value="{{ old('end_time', '16:30') }}"
                               class="clay-input text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Lokasi Pickup</label>
                        <input type="text" name="pickup_location" value="{{ old('pickup_location') }}" placeholder="Nama Hotel / Villa"
                               class="clay-input text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Area Destinasi</label>
                        <input type="text" name="location" value="{{ old('location') }}" placeholder="Ubud / Kintamani / Uluwatu"
                               class="clay-input text-xs">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-bold text-[#29283D] mb-1.5">Catatan Tambahan / Permintaan Khusus</label>
                    <textarea name="notes" rows="2" placeholder="Catatan preferensi tamu, dietary, atau rute..."
                              class="clay-input text-xs">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Requirement 39: Live Quick Calculation Panel -->
            <div class="p-5 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0]">
                <div class="text-xs font-extrabold text-[#29283D] uppercase tracking-wider mb-3">Estimasi Kalkulasi Finansial & Guide (Live Snapshot):</div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                    <div class="clay-card p-3">
                        <span class="text-[10px] text-[#77768C] block font-bold uppercase tracking-wider">Harga / Tamu</span>
                        <span class="text-sm font-black text-[#29283D] block mt-0.5" x-text="formatRupiah(pricePerGuest)"></span>
                    </div>

                    <div class="clay-card p-3">
                        <span class="text-[10px] text-[#77768C] block font-bold uppercase tracking-wider">Total Harga</span>
                        <span class="text-sm font-black text-[#6C63FF] block mt-0.5 font-display" x-text="formatRupiah(totalPrice)"></span>
                    </div>

                    <div class="clay-card p-3">
                        <span class="text-[10px] text-[#77768C] block font-bold uppercase tracking-wider">Estimasi Fee Guide</span>
                        <span class="text-sm font-black text-[#FF8A65] block mt-0.5" x-text="formatRupiah(estimatedGuideFee)"></span>
                    </div>

                    <div class="clay-card p-3">
                        <span class="text-[10px] text-[#77768C] block font-bold uppercase tracking-wider">Estimasi Laba</span>
                        <span class="text-sm font-black text-[#147a6f] block mt-0.5" x-text="formatRupiah(estimatedProfit)"></span>
                    </div>

                    <div class="clay-card p-3">
                        <span class="text-[10px] text-[#77768C] block font-bold uppercase tracking-wider">Sisa Tamu</span>
                        <span class="text-sm font-black text-[#29283D] block mt-0.5" x-text="guestCount + ' pax'"></span>
                    </div>

                    <div class="clay-card p-3 border border-[#FFD166]/60 bg-[#FFD166]/10">
                        <span class="text-[10px] text-[#8a6800] block font-bold uppercase tracking-wider">Rekomendasi Guide</span>
                        <span class="text-xs font-black text-[#29283D] truncate block mt-0.5" x-text="recommendedGuide"></span>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-[#E8E6F0] flex items-center justify-end space-x-3">
                <a href="{{ route('bookings.index') }}" class="clay-btn clay-btn-secondary px-4 py-2.5 text-xs font-bold text-[#77768C]">
                    Batal
                </a>
                <button type="submit" class="clay-btn clay-btn-primary px-5 py-2.5 text-xs font-bold">
                    Simpan Booking & Lanjutkan ke Penugasan Guide &rarr;
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

