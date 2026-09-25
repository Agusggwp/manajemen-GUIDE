@extends('layouts.app')

@section('title', 'Pengaturan Sistem')
@section('header', 'Pengaturan Sistem')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl border border-slate-200/80 p-6 shadow-2xs">
    <div class="border-b border-slate-100 pb-4 mb-6">
        <h2 class="text-base font-bold text-slate-900">Konfigurasi Operasional ARTDEVATA</h2>
        <p class="text-xs text-slate-500">Sesuaikan profil perusahaan, validasi GPS absensi, dan kontrol antrean</p>
    </div>

    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5 text-xs">
        @csrf

        <div class="space-y-4">
            <!-- Logo & Brand Customization -->
            <div class="p-4 bg-slate-50/80 rounded-xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Logo & Identitas Brand</h3>
                        <p class="text-[11px] text-slate-500">Logo ini tampil di sidebar dashboard admin, portal guide, dan halaman login</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#14433b] text-[#21c9a4]">
                        Dapat Diatur
                    </span>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <!-- Current Logo Preview -->
                    <div class="flex flex-col items-center justify-center p-3 bg-white rounded-lg border border-slate-200 shadow-2xs shrink-0 w-28 h-28">
                        @if(!empty($settings['company_logo']) && file_exists(public_path($settings['company_logo'])))
                            <img src="{{ asset($settings['company_logo']) }}" alt="Logo Perusahaan" class="max-w-full max-h-20 object-contain">
                            <span class="text-[9px] text-emerald-600 font-bold mt-1">Logo Kustom</span>
                        @else
                            <div class="w-12 h-12 rounded-xl bg-[#14433b] text-[#21c9a4] font-bold text-xl flex items-center justify-center shadow-xs">
                                {{ substr($settings['company_name'] ?? 'A', 0, 1) }}
                            </div>
                            <span class="text-[9px] text-slate-400 font-medium mt-1">Icon Bawaan</span>
                        @endif
                    </div>

                    <!-- Upload Input -->
                    <div class="space-y-2 flex-1">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Unggah File Logo Baru</label>
                            <input type="file" name="company_logo" accept="image/png,image/jpeg,image/svg+xml,image/webp"
                                   class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#14433b] file:text-[#21c9a4] hover:file:bg-[#1b584d] file:cursor-pointer cursor-pointer border border-slate-300 rounded-md bg-white p-1">
                            <span class="text-[10px] text-slate-500 block mt-1">Format: PNG, JPG, SVG, WebP (Rekomendasi rasio 1:1 atau horizontal, transparan, maks. 2MB).</span>
                        </div>

                        @if(!empty($settings['company_logo']) && file_exists(public_path($settings['company_logo'])))
                        <div class="pt-1">
                            <label class="inline-flex items-center text-rose-600 hover:text-rose-700 cursor-pointer font-medium">
                                <input type="checkbox" name="remove_logo" value="1" class="rounded text-rose-600 focus:ring-rose-500 mr-1.5">
                                <span>Hapus logo kustom & kembali ke icon inisial</span>
                            </label>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-slate-200/80">
                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-slate-700 mb-1">Nama Aplikasi / Brand <span class="text-rose-500">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name']) }}" required placeholder="ARTDEVATA"
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-md font-bold text-slate-900">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Nama utama sistem di dashboard & login.</span>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Inisial / Singkatan</label>
                        <input type="text" name="app_short_name" value="{{ old('app_short_name', $settings['app_short_name']) }}" maxlength="5" placeholder="A"
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-md font-bold text-center text-slate-900">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Tampil jika logo gambar kosong.</span>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-slate-700 mb-1">Sub-judul / Tagline Aplikasi</label>
                        <input type="text" name="company_tagline" value="{{ old('company_tagline', $settings['company_tagline']) }}" placeholder="Guide Management"
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-slate-800">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Tagline di bawah logo (sidebar & login).</span>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Judul Portal Guide</label>
                        <input type="text" name="guide_portal_title" value="{{ old('guide_portal_title', $settings['guide_portal_title']) }}" placeholder="Portal Pemandu Wisata"
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-slate-800">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Label header di HP guide.</span>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block font-semibold text-slate-700 mb-1">Teks Footer / Hak Cipta</label>
                        <input type="text" name="app_footer_text" value="{{ old('app_footer_text', $settings['app_footer_text']) }}" placeholder="© 2026 ARTDEVATA Bali. Hak cipta dilindungi."
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-slate-800">
                    </div>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nomor Telepon Operasional</label>
                <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone']) }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Alamat Kantor / Basecamp</label>
                <textarea name="company_address" rows="2" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">{{ old('company_address', $settings['company_address']) }}</textarea>
            </div>

            <!-- Requirement 8: GPS validation is optional -->
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                <label class="block font-semibold text-slate-800 mb-1">Validasi GPS Lokasi saat Check-in / Checkout</label>
                <p class="text-[11px] text-slate-500 mb-2 leading-relaxed">
                    Sesuai spesifikasi, GPS bersifat opsional dan dapat dinonaktifkan agar tidak menjadi satu-satunya mekanisme validasi operasional.
                </p>
                <select name="gps_required" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-md">
                    <option value="0" {{ $settings['gps_required'] == '0' ? 'selected' : '' }}>GPS Opsional (Disarankan - Guide tetap bisa check-in tanpa sinyal GPS)</option>
                    <option value="1" {{ $settings['gps_required'] == '1' ? 'selected' : '' }}>GPS Wajib (Check-in ditolak jika GPS tidak terdeteksi)</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Tarif Standar Fee Guide per Tamu (Rp)</label>
                <input type="number" name="default_guide_fee" value="{{ old('default_guide_fee', $settings['default_guide_fee']) }}" min="0" step="5000" required
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md font-bold">
            </div>

            <!-- Expense Categories Configuration -->
            <div class="p-4 bg-slate-50/80 rounded-xl border border-slate-200 space-y-4"
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
                <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Pilihan Kategori Biaya Operasional</h3>
                        <p class="text-[11px] text-slate-500">Sesuaikan opsi pilihan kategori dropdown saat mencatat pengeluaran operasional</p>
                    </div>
                    <button type="button" @click="resetDefaults()" class="text-[10px] text-slate-400 hover:text-slate-600 underline">
                        Reset Default
                    </button>
                </div>

                <!-- Input to add new category -->
                <div class="flex gap-2">
                    <input type="text" x-model="newCategory" @keydown.enter.prevent="addCategory()"
                           placeholder="Ketik kategori baru (contoh: Biaya Perahu & Boat)..."
                           class="flex-1 px-3 py-2 bg-white border border-slate-300 rounded-md text-xs focus:border-[#14433b] focus:ring-1 focus:ring-[#14433b] outline-none">
                    <button type="button" @click="addCategory()"
                            class="px-3.5 py-2 bg-[#14433b] hover:bg-[#1b584d] text-white rounded-md font-bold text-xs transition-colors flex items-center gap-1 shrink-0">
                        + Tambah Kategori
                    </button>
                </div>

                <!-- List of Categories -->
                <div class="space-y-1.5">
                    <label class="block font-semibold text-slate-700 text-[11px]">Daftar Kategori Pilihan (Bisa Diedit):</label>
                    <div class="space-y-1.5 max-h-56 overflow-y-auto pr-1">
                        <template x-for="(cat, index) in categories" :key="index">
                            <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200 shadow-2xs group hover:border-[#14433b]/40 transition-colors">
                                <div class="flex items-center gap-2 flex-1">
                                    <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold flex items-center justify-center shrink-0" x-text="index + 1"></span>
                                    <input type="text" :name="'expense_categories[' + index + ']'" x-model="categories[index]" required
                                           class="flex-1 px-2 py-1 bg-transparent font-medium text-slate-800 text-xs border-b border-transparent focus:border-[#14433b] outline-none">
                                </div>
                                <button type="button" @click="removeCategory(index)"
                                        class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded transition-colors"
                                        title="Hapus kategori ini">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <div x-show="categories.length === 0" class="p-3 text-center text-slate-400 border border-dashed border-slate-200 rounded-lg text-[11px]">
                            Belum ada kategori. Ketik nama kategori di atas lalu tekan tombol <strong>+ Tambah Kategori</strong>.
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-400">Pilihan ini akan otomatis muncul pada dropdown pilihan kategori biaya operasional & booking.</p>
                </div>
            </div>

            <!-- Queue Position Adjustment (Admin override) -->
            <div class="p-3 bg-amber-50/60 rounded-lg border border-amber-200">
                <label class="block font-bold text-amber-800 mb-1">Posisi Antrean Aktif Saat Ini (Queue State)</label>
                <p class="text-[11px] text-amber-700/80 mb-2 leading-relaxed">
                    Posisi antrean terakhir yang mendapatkan penugasan: <strong>#{{ $queueState->current_position }}</strong>. 
                    Anda dapat mengubah angka ini secara manual jika diperlukan intervensi operasional.
                </p>
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-slate-700">Set Posisi ke #</span>
                    <input type="number" name="current_position" value="{{ $queueState->current_position }}" min="0" max="999"
                           class="w-24 px-3 py-1.5 bg-white border border-slate-300 rounded-md font-bold text-center">
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white font-bold transition-colors shadow-xs">
                Simpan Konfigurasi &rarr;
            </button>
        </div>
    </form>
</div>
@endsection
