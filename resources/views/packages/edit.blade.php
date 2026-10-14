@extends('layouts.app')

@section('title', 'Edit Paket Wisata ' . $package->name)
@section('header', 'Edit Paket Wisata: ' . $package->name)

@section('content')
<div class="max-w-3xl mx-auto clay-card p-6 md:p-8"
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
    <div class="border-b border-[#E8E6F0] pb-5 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-extrabold text-[#29283D] flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-[#6C63FF]/10 text-[#6C63FF] flex items-center justify-center"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg></span>
                <span>Perbarui Paket Wisata & Skema Pricing</span>
            </h2>
            <p class="text-xs text-[#77768C] mt-1">Edit harga per tamu (1 pax), deskripsi, status paket, atau kelola paket khusus bertingkat</p>
        </div>
        <a href="{{ request('from') === 'settings' ? route('settings.index', ['tab' => 'packages']) : route('packages.index') }}" class="clay-btn clay-btn-secondary text-xs px-3.5 py-2 inline-flex items-center gap-1.5 self-start sm:self-auto font-bold">
            <span>&larr;</span> Kembali ke {{ request('from') === 'settings' ? 'Pengaturan' : 'Daftar' }}
        </a>
    </div>

    <form action="{{ route('packages.update', $package) }}" method="POST" class="space-y-5 text-xs">
        @csrf
        @method('PUT')
        <input type="hidden" name="from" value="{{ request('from', 'packages') }}">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
                <label class="block font-bold text-[#29283D] mb-1.5">Nama Paket Wisata <span class="text-[#FF8A65]">*</span></label>
                <input type="text" name="name" value="{{ old('name', $package->name) }}" required
                       class="w-full clay-input">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Durasi Tour <span class="text-[#FF8A65]">*</span></label>
                <input type="text" name="duration" value="{{ old('duration', $package->duration) }}" required
                       class="w-full clay-input">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Status Paket <span class="text-[#FF8A65]">*</span></label>
                <select name="status" class="w-full clay-input font-bold">
                    <option value="ACTIVE" {{ old('status', $package->status) === 'ACTIVE' ? 'selected' : '' }}>Aktif (ACTIVE)</option>
                    <option value="INACTIVE" {{ old('status', $package->status) === 'INACTIVE' ? 'selected' : '' }}>Non-Aktif (INACTIVE)</option>
                </select>
            </div>

            <!-- Standard Price per Pax (Default Price) -->
            <div class="md:col-span-2">
                <label class="block font-bold text-[#29283D] mb-1.5">
                    Harga per Tamu (1 Pax) <span class="text-[#FF8A65]">*</span>
                </label>
                <div class="flex items-center rounded-2xl bg-white border border-[#E8E6F0] shadow-2xs overflow-hidden focus-within:border-[#6C63FF] focus-within:ring-2 focus-within:ring-[#6C63FF]/20">
                    <span class="px-3.5 py-2.5 bg-[#F4F3FA] text-xs font-bold text-[#77768C] border-r border-[#E8E6F0] select-none">Rp</span>
                    <input type="number" name="default_price" x-model="defaultPrice" min="0" step="5000" required
                           class="flex-1 px-3 py-2 bg-transparent font-extrabold text-[#6C63FF] outline-none">
                </div>
                <p class="text-[11px] text-[#77768C] mt-1">Tarif standar per orang yang berlaku tetap untuk pemesanan paket ini.</p>
            </div>

            <div class="md:col-span-2">
                <label class="block font-bold text-[#29283D] mb-1.5">Deskripsi Paket</label>
                <textarea name="description" rows="3" class="w-full clay-input resize-y">{{ old('description', $package->description) }}</textarea>
            </div>
        </div>

        <!-- Special Package / Tiered Pricing Option -->
        <div class="pt-5 border-t border-[#E8E6F0] space-y-4">
            <div class="p-4 rounded-2xl border transition-all"
                 :class="hasSpecialTiers ? 'bg-[#FFD166]/15 border-[#FFD166]/50' : 'bg-[#F4F3FA] border-[#E8E6F0]'">
                <label class="flex items-start gap-3.5 cursor-pointer select-none">
                    <input type="checkbox" name="has_special_tiers" value="1" x-model="hasSpecialTiers"
                           class="mt-1 rounded-lg border-[#E8E6F0] text-[#6C63FF] focus:ring-[#6C63FF] w-4 h-4">
                    <div>
                        <div class="font-extrabold text-[#29283D] flex items-center gap-2">
                            <span>Paket Khusus: Skema Harga Bertingkat / Rombongan</span>
                            <span x-show="hasSpecialTiers" class="px-2.5 py-0.5 rounded-xl text-[10px] font-extrabold bg-[#FFD166] text-[#b45309]">Aktif</span>
                        </div>
                        <p class="text-[11px] text-[#77768C] mt-1 leading-relaxed">
                            Aktifkan opsi ini jika paket memiliki tarif berbeda untuk rentang kuota tamu (misal: diskon rombongan). Jika dimatikan, semua skema harga bertingkat paket ini akan dinonaktifkan dan menggunakan harga standar 1 pax di atas.
                        </p>
                    </div>
                </label>
            </div>

            <!-- Tiered Pricing Editor (Only shown when Special Package is enabled) -->
            <div x-show="hasSpecialTiers" x-transition class="space-y-4 p-5 bg-white rounded-2xl border border-[#E8E6F0] shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-[#E8E6F0]">
                    <div>
                        <h4 class="font-extrabold text-[#29283D] uppercase tracking-wider text-[11px]">Tingkatan Harga Tamu (Tier Pricing)</h4>
                        <p class="text-[11px] text-[#77768C] mt-0.5">Tentukan rentang minimum, maksimum tamu, dan harga per tamu untuk paket khusus ini:</p>
                    </div>
                    <button type="button" @click="addTier()"
                            class="clay-btn clay-btn-turquoise text-xs px-3 py-1.5 font-bold flex items-center gap-1">
                        + Tambah Baris
                    </button>
                </div>

                <div class="space-y-2.5">
                    <div class="grid grid-cols-12 gap-2 text-[11px] font-extrabold text-[#77768C] uppercase tracking-wider px-1">
                        <div class="col-span-3">Rentang Tamu Min</div>
                        <div class="col-span-3">Rentang Tamu Max</div>
                        <div class="col-span-5">Harga per Tamu (Rp)</div>
                        <div class="col-span-1 text-center">Hapus</div>
                    </div>

                    <template x-for="(tier, index) in tiers" :key="index">
                        <div class="grid grid-cols-12 gap-2.5 items-center">
                            <div class="col-span-3">
                                <input type="number" :name="'tiers[' + index + '][min]'" x-model="tier.min" min="1" required
                                       class="w-full clay-input text-center text-xs py-1.5 font-bold">
                            </div>
                            <div class="col-span-3">
                                <input type="number" :name="'tiers[' + index + '][max]'" x-model="tier.max" min="1" required
                                       class="w-full clay-input text-center text-xs py-1.5 font-bold">
                            </div>
                            <div class="col-span-5">
                                <div class="flex items-center rounded-xl bg-white border border-[#E8E6F0] shadow-2xs overflow-hidden focus-within:border-[#6C63FF] focus-within:ring-2 focus-within:ring-[#6C63FF]/20">
                                    <span class="px-2.5 py-1.5 bg-[#F4F3FA] text-[11px] font-bold text-[#77768C] border-r border-[#E8E6F0] select-none">Rp</span>
                                    <input type="number" :name="'tiers[' + index + '][price]'" x-model="tier.price" min="0" step="5000" required
                                           class="flex-1 px-2.5 py-1.5 bg-transparent font-extrabold text-[#6C63FF] text-xs outline-none">
                                </div>
                            </div>
                            <div class="col-span-1 flex justify-center">
                                <button type="button" @click="removeTier(index)"
                                        class="p-1.5 text-[#FF8A65] hover:bg-[#FF8A65]/10 rounded-xl transition-colors"
                                        title="Hapus tingkat harga ini">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>

                    <div x-show="tiers.length === 0" class="p-6 text-center text-[#77768C] border border-dashed border-[#E8E6F0] rounded-2xl">
                        Belum ada tingkatan harga khusus. Klik tombol <strong>+ Tambah Baris</strong> di atas untuk membuat rentang harga tamu.
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-5 border-t border-[#E8E6F0] flex items-center justify-between">
            <button type="button" 
                    onclick="document.getElementById('delete-pkg-form').dispatchEvent(new Event('submit', { cancelable: true }))"
                    class="clay-btn clay-btn-coral px-4 py-2 font-bold text-xs cursor-pointer">
                Hapus Paket Ini
            </button>

            <div class="flex items-center space-x-3">
                <a href="{{ route('packages.index') }}" class="clay-btn clay-btn-secondary px-5 py-2.5 font-bold">
                    Batal
                </a>
                <button type="submit" class="clay-btn clay-btn-primary px-6 py-2.5 font-bold">
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
