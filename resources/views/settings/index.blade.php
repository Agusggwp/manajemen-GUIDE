@extends('layouts.app')

@section('title', 'Pengaturan Sistem')
@section('header', 'Pengaturan Sistem')

@section('content')
<div class="max-w-3xl mx-auto clay-card p-6 sm:p-8 space-y-6">
    <div class="border-b border-[#E8E6F0] pb-4">
        <h2 class="text-base font-bold text-[#29283D]">Konfigurasi Operasional ARTDEVATA</h2>
        <p class="text-xs text-[#77768C] mt-1">Sesuaikan profil perusahaan, validasi GPS absensi, dan kontrol antrean</p>
    </div>

    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6 text-xs">
        @csrf

        <div class="space-y-5">
            <!-- Logo & Brand Customization -->
            <div class="p-5 bg-[#F4F3FA] rounded-2xl border border-[#E8E6F0] space-y-4">
                <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3">
                    <div>
                        <h3 class="font-bold text-[#29283D] text-sm">Logo & Identitas Brand</h3>
                        <p class="text-[11px] text-[#77768C]">Logo ini tampil di sidebar dashboard admin, portal guide, dan halaman login</p>
                    </div>
                    <span class="clay-badge bg-purple-50 text-[#6C63FF] border-purple-200">
                        Dapat Diatur
                    </span>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <!-- Current Logo Preview -->
                    <div class="flex flex-col items-center justify-center p-3 bg-white rounded-2xl border border-[#E8E6F0] shadow-sm shrink-0 w-28 h-28">
                        @if(!empty($settings['company_logo']) && file_exists(public_path($settings['company_logo'])))
                            <img src="{{ asset($settings['company_logo']) }}" alt="Logo Perusahaan" class="max-w-full max-h-20 object-contain">
                            <span class="text-[9px] text-[#6C63FF] font-bold mt-1">Logo Kustom</span>
                        @else
                            <div class="w-12 h-12 rounded-2xl bg-[#6C63FF] text-[#FFD166] font-black text-xl flex items-center justify-center shadow-xs">
                                {{ substr($settings['company_name'] ?? 'A', 0, 1) }}
                            </div>
                            <span class="text-[9px] text-[#77768C] font-semibold mt-1">Icon Bawaan</span>
                        @endif
                    </div>

                    <!-- Upload Input -->
                    <div class="space-y-2 flex-1">
                        <div>
                            <label class="block font-bold text-[#29283D] mb-1.5">Unggah File Logo Baru</label>
                            <input type="file" name="company_logo" accept="image/png,image/jpeg,image/svg+xml,image/webp"
                                   class="w-full text-xs text-[#77768C] file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#6C63FF] file:text-white hover:file:bg-[#584FE4] file:cursor-pointer cursor-pointer border border-[#E8E6F0] rounded-xl bg-white p-1.5 shadow-inner">
                            <span class="text-[10px] text-[#77768C] block mt-1">Format: PNG, JPG, SVG, WebP (Rekomendasi rasio 1:1 atau horizontal, transparan, maks. 2MB).</span>
                        </div>

                        @if(!empty($settings['company_logo']) && file_exists(public_path($settings['company_logo'])))
                        <div class="pt-1">
                            <label class="inline-flex items-center text-rose-600 hover:text-rose-700 cursor-pointer font-bold">
                                <input type="checkbox" name="remove_logo" value="1" class="rounded text-rose-600 focus:ring-rose-500 mr-2">
                                <span>Hapus logo kustom & kembali ke icon inisial</span>
                            </label>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-[#E8E6F0]">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-[#29283D] mb-1.5">Nama Aplikasi / Brand <span class="text-rose-500">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name']) }}" required placeholder="ARTDEVATA"
                               class="clay-input w-full font-bold text-[#29283D]">
                        <span class="text-[10px] text-[#77768C] mt-1 block">Nama utama sistem di dashboard & login.</span>
                    </div>

                    <div>
                        <label class="block font-bold text-[#29283D] mb-1.5">Inisial / Singkatan</label>
                        <input type="text" name="app_short_name" value="{{ old('app_short_name', $settings['app_short_name']) }}" maxlength="5" placeholder="A"
                               class="clay-input w-full font-bold text-center text-[#29283D]">
                        <span class="text-[10px] text-[#77768C] mt-1 block">Tampil jika logo gambar kosong.</span>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-[#29283D] mb-1.5">Sub-judul / Tagline Aplikasi</label>
                        <input type="text" name="company_tagline" value="{{ old('company_tagline', $settings['company_tagline']) }}" placeholder="Guide Management"
                               class="clay-input w-full text-[#29283D]">
                        <span class="text-[10px] text-[#77768C] mt-1 block">Tagline di bawah logo (sidebar & login).</span>
                    </div>

                    <div>
                        <label class="block font-bold text-[#29283D] mb-1.5">Judul Portal Guide</label>
                        <input type="text" name="guide_portal_title" value="{{ old('guide_portal_title', $settings['guide_portal_title']) }}" placeholder="Portal Pemandu Wisata"
                               class="clay-input w-full text-[#29283D]">
                        <span class="text-[10px] text-[#77768C] mt-1 block">Label header di HP guide.</span>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block font-bold text-[#29283D] mb-1.5">Teks Footer / Hak Cipta</label>
                        <input type="text" name="app_footer_text" value="{{ old('app_footer_text', $settings['app_footer_text']) }}" placeholder="© 2026 ARTDEVATA Bali. Hak cipta dilindungi."
                               class="clay-input w-full text-[#29283D]">
                    </div>
                </div>
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Nomor Telepon Operasional</label>
                <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone']) }}"
                       class="clay-input w-full">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Alamat Kantor / Basecamp</label>
                <textarea name="company_address" rows="2" class="clay-input w-full">{{ old('company_address', $settings['company_address']) }}</textarea>
            </div>

            <!-- Requirement 8: GPS validation is optional -->
            <div class="p-4 bg-[#F4F3FA] rounded-2xl border border-[#E8E6F0]">
                <label class="block font-bold text-[#29283D] mb-1">Validasi GPS Lokasi saat Check-in / Checkout</label>
                <p class="text-[11px] text-[#77768C] mb-2 leading-relaxed">
                    Sesuai spesifikasi, GPS bersifat opsional dan dapat dinonaktifkan agar tidak menjadi satu-satunya mekanisme validasi operasional.
                </p>
                <select name="gps_required" class="clay-input w-full">
                    <option value="0" {{ $settings['gps_required'] == '0' ? 'selected' : '' }}>GPS Opsional (Disarankan - Guide tetap bisa check-in tanpa sinyal GPS)</option>
                    <option value="1" {{ $settings['gps_required'] == '1' ? 'selected' : '' }}>GPS Wajib (Check-in ditolak jika GPS tidak terdeteksi)</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Tarif Standar Fee Guide per Tamu (Rp)</label>
                <input type="number" name="default_guide_fee" value="{{ old('default_guide_fee', $settings['default_guide_fee']) }}" min="0" step="5000" required
                       class="clay-input w-full font-bold">
            </div>

            <!-- Expense Categories Configuration -->
            <div class="p-5 bg-[#F4F3FA] rounded-2xl border border-[#E8E6F0] space-y-4"
                 x-data="{
                     categories: {{ Js::from($settings['expense_categories']) }},
                     newCategory: '',
                     addCategory() {
                         let val = this.newCategory.trim();
                         if (val && !this.categories.includes(val)) {
                             this.categories.push(val);
                             this.newCategory = '';
                         }
                     },
                     removeCategory(index) {
                         this.categories.splice(index, 1);
                     },
                     resetDefaults() {
                         this.categories = [
                             'Tiket Masuk & Parkir',
                             'Bahan Bakar & Tol',
                             'Sewa Mobil / Bus Pariwisata',
                             'Konsumsi & Air Mineral',
                             'Lain-lain'
                         ];
                     }
                 }">
                <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-2.5">
                    <div>
                        <h3 class="font-bold text-[#29283D] text-sm">Pilihan Kategori Biaya Operasional</h3>
                        <p class="text-[11px] text-[#77768C]">Sesuaikan opsi pilihan kategori dropdown saat mencatat pengeluaran operasional</p>
                    </div>
                    <button type="button" @click="resetDefaults()" class="text-[11px] text-[#6C63FF] hover:underline font-bold">
                        Reset Default
                    </button>
                </div>

                <!-- Input to add new category -->
                <div class="flex gap-2">
                    <input type="text" x-model="newCategory" @keydown.enter.prevent="addCategory()"
                           placeholder="Ketik kategori baru (contoh: Biaya Perahu & Boat)..."
                           class="clay-input flex-1 text-xs">
                    <button type="button" @click="addCategory()"
                            class="clay-btn-primary px-4 py-2 font-bold text-xs flex items-center gap-1 shrink-0">
                        + Tambah Kategori
                    </button>
                </div>

                <!-- List of Categories -->
                <div class="space-y-2">
                    <label class="block font-bold text-[#29283D] text-xs">Daftar Kategori Pilihan (Bisa Diedit):</label>
                    <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                        <template x-for="(cat, index) in categories" :key="index">
                            <div class="flex items-center justify-between p-2.5 bg-white rounded-xl border border-[#E8E6F0] shadow-2xs group hover:border-[#6C63FF]/40 transition-colors">
                                <div class="flex items-center gap-2.5 flex-1">
                                    <span class="w-6 h-6 rounded-full bg-[#F4F3FA] text-[#6C63FF] text-[10px] font-black flex items-center justify-center shrink-0 border border-[#E8E6F0]" x-text="index + 1"></span>
                                    <input type="text" :name="'expense_categories[' + index + ']'" x-model="categories[index]" required
                                           class="flex-1 px-2 py-1 bg-transparent font-bold text-[#29283D] text-xs border-b border-transparent focus:border-[#6C63FF] outline-none">
                                </div>
                                <button type="button" @click="removeCategory(index)"
                                        class="p-1.5 text-[#77768C] hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors"
                                        title="Hapus kategori ini">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <div x-show="categories.length === 0" class="p-4 text-center text-[#77768C] border-2 border-dashed border-[#E8E6F0] rounded-2xl text-xs">
                            Belum ada kategori. Ketik nama kategori di atas lalu tekan tombol <strong>+ Tambah Kategori</strong>.
                        </div>
                    </div>
                    <p class="text-[10px] text-[#77768C]">Pilihan ini akan otomatis muncul pada dropdown pilihan kategori biaya operasional & booking.</p>
                </div>
            </div>

            <!-- Queue Position Adjustment (Admin override) -->
            <div class="p-4 bg-amber-50/70 rounded-2xl border border-amber-200">
                <label class="block font-bold text-amber-900 mb-1">Posisi Antrean Aktif Saat Ini (Queue State)</label>
                <p class="text-[11px] text-amber-800/80 mb-2 leading-relaxed">
                    Posisi antrean terakhir yang mendapatkan penugasan: <strong>#{{ $queueState->current_position }}</strong>. 
                    Anda dapat mengubah angka ini secara manual jika diperlukan intervensi operasional.
                </p>
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-[#29283D]">Set Posisi ke #</span>
                    <input type="number" name="current_position" value="{{ $queueState->current_position }}" min="0" max="999"
                           class="w-24 px-3 py-1.5 clay-input font-bold text-center">
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-[#E8E6F0] flex items-center justify-end">
            <button type="submit" class="clay-btn-primary px-6 py-2.5 font-bold transition-colors shadow-xs">
                Simpan Konfigurasi &rarr;
            </button>
        </div>
    </form>
</div>
@endsection
