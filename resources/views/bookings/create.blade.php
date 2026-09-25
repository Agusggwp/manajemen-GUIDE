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

    <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-2xs">
        <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Form Quick Booking Wisata Bali</h2>
                <p class="text-xs text-slate-500">Estimasi harga, fee guide, dan margin keuntungan dihitung real-time</p>
            </div>
            <a href="{{ route('bookings.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">
                &larr; Kembali
            </a>
        </div>

        <form action="{{ route('bookings.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Section 1: Customer Details -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">1. Data Pelanggan / Tamu</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pelanggan / Leader <span class="text-rose-500">*</span></label>
                        <input type="text" name="customer_name" required value="{{ old('customer_name') }}" placeholder="Contoh: John Smith"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-[#21c9a4] focus:outline-hidden">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="+62 812-xxxx-xxxx"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-[#21c9a4] focus:outline-hidden">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kebangsaan / Asal</label>
                        <input type="text" name="nationality" value="{{ old('nationality', 'Indonesia') }}" placeholder="Indonesia, Australia, dll"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-[#21c9a4] focus:outline-hidden">
                    </div>
                </div>
            </div>

            <!-- Section 2: Tour & Date -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">2. Detail Tour & Jadwal</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Paket Wisata <span class="text-rose-500">*</span></label>
                        <select name="tour_package_id" x-model="packageId" @change="updatePricing()" required
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-[#21c9a4] focus:outline-hidden">
                            @foreach($tourPackages as $pkg)
                                <option value="{{ $pkg->id }}">{{ $pkg->name }} ({{ $pkg->duration }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Tour <span class="text-rose-500">*</span></label>
                        <input type="date" name="booking_date" required value="{{ old('booking_date', $today) }}"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-[#21c9a4] focus:outline-hidden">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Tamu (Pax) <span class="text-rose-500">*</span></label>
                        <input type="number" name="guest_count" x-model.number="guestCount" @input="updatePricing()" min="1" max="500" required
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-bold text-slate-900 focus:ring-1 focus:ring-[#21c9a4] focus:outline-hidden">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Mulai <span class="text-rose-500">*</span></label>
                        <input type="time" name="start_time" required value="{{ old('start_time', '08:30') }}"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-[#21c9a4] focus:outline-hidden">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Selesai <span class="text-rose-500">*</span></label>
                        <input type="time" name="end_time" required value="{{ old('end_time', '16:30') }}"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-[#21c9a4] focus:outline-hidden">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi Pickup</label>
                        <input type="text" name="pickup_location" value="{{ old('pickup_location') }}" placeholder="Nama Hotel / Villa"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-[#21c9a4] focus:outline-hidden">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Area Destinasi</label>
                        <input type="text" name="location" value="{{ old('location') }}" placeholder="Ubud / Kintamani / Uluwatu"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-[#21c9a4] focus:outline-hidden">
                    </div>
                </div>

                <div class="mt-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan / Permintaan Khusus</label>
                    <textarea name="notes" rows="2" placeholder="Catatan preferensi tamu, dietary, atau rute..."
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-[#21c9a4] focus:outline-hidden">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Requirement 39: Live Quick Calculation Panel -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <div class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Estimasi Kalkulasi Finansial & Guide (Live Snapshot):</div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                    <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-2xs">
                        <span class="text-[10px] text-slate-400 block font-medium">Harga / Tamu</span>
                        <span class="text-sm font-bold text-slate-900" x-text="formatRupiah(pricePerGuest)"></span>
                    </div>

                    <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-2xs">
                        <span class="text-[10px] text-slate-400 block font-medium">Total Harga</span>
                        <span class="text-sm font-bold text-[#14433b]" x-text="formatRupiah(totalPrice)"></span>
                    </div>

                    <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-2xs">
                        <span class="text-[10px] text-slate-400 block font-medium">Estimasi Fee Guide</span>
                        <span class="text-sm font-bold text-amber-600" x-text="formatRupiah(estimatedGuideFee)"></span>
                    </div>

                    <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-2xs">
                        <span class="text-[10px] text-slate-400 block font-medium">Estimasi Laba</span>
                        <span class="text-sm font-bold text-emerald-600" x-text="formatRupiah(estimatedProfit)"></span>
                    </div>

                    <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-2xs">
                        <span class="text-[10px] text-slate-400 block font-medium">Sisa Tamu</span>
                        <span class="text-sm font-bold text-slate-700" x-text="guestCount + ' pax'"></span>
                    </div>

                    <div class="bg-white p-3 rounded-lg border border-[#21c9a4] shadow-2xs">
                        <span class="text-[10px] text-[#21c9a4] block font-semibold">Rekomendasi Guide</span>
                        <span class="text-xs font-bold text-[#14433b]" x-text="recommendedGuide"></span>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('bookings.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-bold tracking-wide transition-colors shadow-xs">
                    Simpan Booking & Lanjutkan ke Penugasan Guide &rarr;
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
