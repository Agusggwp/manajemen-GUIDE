@extends('layouts.app')

@section('title', 'Edit Paket Wisata ' . $package->name)
@section('header', 'Edit Paket Wisata: ' . $package->name)

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl border border-slate-200/80 p-6 shadow-2xs"
     x-data="{
         defaultPrice: {{ old('default_price', $package->default_price) }},
         hasSpecialTiers: {{ old('has_special_tiers', $package->prices->count() > 0 ? 'true' : 'false') }},
         tiers: {{ Js::from(old('tiers', $package->prices->count() > 0 ? $package->prices->map(function($p) {
             return [
                 'min' => $p->min_guests,
                 'max' => $p->max_guests,
                 'price' => (int)$p->price_per_guest
             ];
         })->values() : [
             ['min' => 1, 'max' => 5, 'price' => (int)$package->default_price],
             ['min' => 6, 'max' => 10, 'price' => (int)round($package->default_price * 0.9)],
             ['min' => 11, 'max' => 20, 'price' => (int)round($package->default_price * 0.8)],
             ['min' => 21, 'max' => 100, 'price' => (int)round($package->default_price * 0.7)],
         ])) }},
         addTier() {
             let lastMax = this.tiers.length > 0 ? (parseInt(this.tiers[this.tiers.length - 1].max) || 0) : 0;
             let newMin = lastMax + 1;
             let newMax = newMin + 9;
             let lastPrice = this.tiers.length > 0 ? (parseInt(this.tiers[this.tiers.length - 1].price) || 100000) : 100000;
             this.tiers.push({ min: newMin, max: newMax, price: Math.max(10000, lastPrice - 10000) });
         },
         removeTier(index) {
             this.tiers.splice(index, 1);
         }
     }">
    <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-base font-bold text-slate-900">Perbarui Paket Wisata & Skema Pricing</h2>
            <p class="text-xs text-slate-500">Edit harga per tamu (1 pax), deskripsi, status paket, atau kelola paket khusus bertingkat</p>
        </div>
        <a href="{{ route('packages.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700 flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>

    <form action="{{ route('packages.update', $package) }}" method="POST" class="space-y-5 text-xs">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block font-semibold text-slate-700 mb-1">Nama Paket Wisata <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $package->name) }}" required
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md focus:bg-white focus:border-[#14433b] focus:ring-1 focus:ring-[#14433b] outline-none">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Durasi Tour <span class="text-rose-500">*</span></label>
                <input type="text" name="duration" value="{{ old('duration', $package->duration) }}" required
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md focus:bg-white focus:border-[#14433b] focus:ring-1 focus:ring-[#14433b] outline-none">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Status Paket <span class="text-rose-500">*</span></label>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md focus:bg-white focus:border-[#14433b] focus:ring-1 focus:ring-[#14433b] outline-none">
                    <option value="ACTIVE" {{ old('status', $package->status) === 'ACTIVE' ? 'selected' : '' }}>Aktif (ACTIVE)</option>
                    <option value="INACTIVE" {{ old('status', $package->status) === 'INACTIVE' ? 'selected' : '' }}>Non-Aktif (INACTIVE)</option>
                </select>
            </div>

            <!-- Standard Price per Pax (Default Price) -->
            <div class="md:col-span-2">
                <label class="block font-semibold text-slate-700 mb-1">
                    Harga per Tamu (1 Pax) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-xs">Rp</span>
                    <input type="number" name="default_price" x-model="defaultPrice" min="0" step="5000" required
                           class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-md font-bold text-slate-900 focus:bg-white focus:border-[#14433b] focus:ring-1 focus:ring-[#14433b] outline-none">
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Tarif standar per orang yang berlaku tetap untuk pemesanan paket ini.</p>
            </div>

            <div class="md:col-span-2">
                <label class="block font-semibold text-slate-700 mb-1">Deskripsi Paket</label>
                <textarea name="description" rows="2.5" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md focus:bg-white focus:border-[#14433b] focus:ring-1 focus:ring-[#14433b] outline-none">{{ old('description', $package->description) }}</textarea>
            </div>
        </div>

        <!-- Special Package / Tiered Pricing Option -->
        <div class="pt-4 border-t border-slate-100 space-y-3">
            <div class="p-3.5 rounded-xl border transition-all"
                 :class="hasSpecialTiers ? 'bg-amber-50/40 border-amber-200' : 'bg-slate-50/70 border-slate-200/80'">
                <label class="flex items-start gap-3 cursor-pointer select-none">
                    <input type="checkbox" name="has_special_tiers" value="1" x-model="hasSpecialTiers"
                           class="mt-0.5 rounded border-slate-300 text-[#14433b] focus:ring-[#14433b] w-4 h-4">
                    <div>
                        <div class="font-bold text-slate-900 flex items-center gap-2">
                            <span>Paket Khusus: Skema Harga Bertingkat / Rombongan</span>
                            <span x-show="hasSpecialTiers" class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Aktif</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Aktifkan opsi ini jika paket memiliki tarif berbeda untuk rentang kuota tamu (misal: diskon rombongan). Jika dimatikan, semua skema harga bertingkat paket ini akan dinonaktifkan dan menggunakan harga standar 1 pax di atas.
                        </p>
                    </div>
                </label>
            </div>

            <!-- Tiered Pricing Editor (Only shown when Special Package is enabled) -->
            <div x-show="hasSpecialTiers" x-transition class="space-y-3 p-4 bg-white rounded-xl border border-slate-200 shadow-2xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div>
                        <h4 class="font-bold text-slate-800 uppercase tracking-wider text-[11px]">Tingkatan Harga Tamu (Tier Pricing)</h4>
                        <p class="text-[11px] text-slate-500">Tentukan rentang minimum, maksimum tamu, dan harga per tamu untuk paket khusus ini:</p>
                    </div>
                    <button type="button" @click="addTier()"
                            class="px-2.5 py-1 text-xs font-semibold text-[#14433b] hover:text-[#1b584d] bg-[#e6faf5] hover:bg-[#d0f5ec] rounded-md transition-colors border border-[#21c9a4]/30 flex items-center gap-1">
                        + Tambah Baris
                    </button>
                </div>

                <div class="space-y-2">
                    <div class="grid grid-cols-12 gap-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider px-1">
                        <div class="col-span-3">Rentang Tamu Min</div>
                        <div class="col-span-3">Rentang Tamu Max</div>
                        <div class="col-span-5">Harga per Tamu (Rp)</div>
                        <div class="col-span-1 text-center">Hapus</div>
                    </div>

                    <template x-for="(tier, index) in tiers" :key="index">
                        <div class="grid grid-cols-12 gap-2 items-center">
                            <div class="col-span-3">
                                <input type="number" :name="'tiers[' + index + '][min]'" x-model="tier.min" min="1" required
                                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-md text-center text-xs focus:bg-white focus:border-[#14433b] outline-none">
                            </div>
                            <div class="col-span-3">
                                <input type="number" :name="'tiers[' + index + '][max]'" x-model="tier.max" min="1" required
                                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-md text-center text-xs focus:bg-white focus:border-[#14433b] outline-none">
                            </div>
                            <div class="col-span-5">
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-slate-400 font-semibold text-[11px]">Rp</span>
                                    <input type="number" :name="'tiers[' + index + '][price]'" x-model="tier.price" min="0" step="5000" required
                                           class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-300 rounded-md font-bold text-slate-900 text-xs focus:bg-white focus:border-[#14433b] outline-none">
                                </div>
                            </div>
                            <div class="col-span-1 flex justify-center">
                                <button type="button" @click="removeTier(index)"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded transition-colors"
                                        title="Hapus tingkat harga ini">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>

                    <div x-show="tiers.length === 0" class="p-4 text-center text-slate-400 border border-dashed border-slate-200 rounded-lg">
                        Belum ada tingkatan harga khusus. Klik tombol <strong>+ Tambah Baris</strong> di atas untuk membuat rentang harga tamu.
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
            <button type="button" 
                    onclick="document.getElementById('delete-pkg-form').dispatchEvent(new Event('submit', { cancelable: true }))"
                    class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-semibold transition-colors cursor-pointer">
                Hapus Paket Ini
            </button>

            <div class="flex items-center space-x-2">
                <a href="{{ route('packages.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 font-semibold hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white font-bold transition-colors shadow-xs">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

    <form id="delete-pkg-form" action="{{ route('packages.destroy', $package) }}" method="POST" class="hidden"
          data-confirm="Apakah Anda yakin ingin menghapus paket wisata '{{ $package->name }}'? Seluruh skema harga dan data terkait akan dihapus permanen."
          data-confirm-title="Hapus Paket Wisata"
          data-confirm-btn="Ya, Hapus Paket"
          data-confirm-danger="true">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
