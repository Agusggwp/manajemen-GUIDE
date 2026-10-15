@extends('layouts.app')

@section('title', 'Pengaturan Sistem')
@section('header', 'Pengaturan Sistem')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" 
     x-data="{
         activeTab: '{{ request()->query('tab', $activeTab ?? 'brand') }}',
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
                 'Biaya Telepon / Komunikasi',
                 'Lain-lain'
             ];
         },
         updateTab(tab) {
             this.activeTab = tab;
             const url = new URL(window.location);
             url.searchParams.set('tab', tab);
             window.history.replaceState({}, '', url);
         },
         // Tour Package Quick Modals
         editPackageModal: false,
         addPackageModal: false,
         pkgForm: {
             id: null,
             actionUrl: '',
             name: '',
             duration: '8 Jam',
             default_price: 150000,
             status: 'ACTIVE',
             description: ''
         },
         openEditPackage(pkg) {
             this.pkgForm = {
                 id: pkg.id,
                 actionUrl: '{{ url('/packages') }}/' + pkg.id,
                 name: pkg.name,
                 duration: pkg.duration,
                 default_price: pkg.default_price,
                 status: pkg.status,
                 description: pkg.description || ''
             };
             this.editPackageModal = true;
         },
         openAddPackage() {
             this.pkgForm = {
                 id: null,
                 actionUrl: '{{ route('packages.store') }}',
                 name: '',
                 duration: '8 Jam',
                 default_price: 150000,
                 status: 'ACTIVE',
                 description: ''
             };
             this.addPackageModal = true;
         },
         // Role Management Modals & State
         addRoleModal: false,
         editRoleModal: false,
         newRoleForm: {
             name: '',
             label: '',
             description: '',
             permissions: []
         },
         editRoleForm: {
             id: null,
             actionUrl: '',
             name: '',
             label: '',
             description: '',
             is_protected: false,
             permissions: []
         },
         openAddRole() {
             this.newRoleForm = {
                 name: '',
                 label: '',
                 description: '',
                 permissions: []
             };
             this.addRoleModal = true;
         },
         openEditRole(role) {
             this.editRoleForm = {
                 id: role.id,
                 actionUrl: '{{ url('/settings/roles') }}/' + role.id,
                 name: role.name,
                 label: role.label,
                 description: role.description || '',
                 is_protected: !!role.is_system,
                 permissions: Array.isArray(role.permissions) ? [...role.permissions] : []
             };
             this.editRoleModal = true;
         },
         toggleNewRolePerm(perm) {
             const idx = this.newRoleForm.permissions.indexOf(perm);
             if (idx > -1) {
                 this.newRoleForm.permissions.splice(idx, 1);
             } else {
                 this.newRoleForm.permissions.push(perm);
             }
         },
         toggleEditRolePerm(perm) {
             const idx = this.editRoleForm.permissions.indexOf(perm);
             if (idx > -1) {
                 this.editRoleForm.permissions.splice(idx, 1);
             } else {
                 this.editRoleForm.permissions.push(perm);
             }
         },
         toggleCategoryNew(categoryKeys, selectAll) {
             categoryKeys.forEach(k => {
                 const idx = this.newRoleForm.permissions.indexOf(k);
                 if (selectAll && idx === -1) {
                     this.newRoleForm.permissions.push(k);
                 } else if (!selectAll && idx > -1) {
                     this.newRoleForm.permissions.splice(idx, 1);
                 }
             });
         },
         toggleCategoryEdit(categoryKeys, selectAll) {
             categoryKeys.forEach(k => {
                 const idx = this.editRoleForm.permissions.indexOf(k);
                 if (selectAll && idx === -1) {
                     this.editRoleForm.permissions.push(k);
                 } else if (!selectAll && idx > -1) {
                     this.editRoleForm.permissions.splice(idx, 1);
                 }
             });
         }
     }">

    <!-- Page Header Card -->
    <div class="clay-card p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-[#E8E6F0]">
        <div class="flex items-center gap-3">
            <span class="w-12 h-12 rounded-2xl bg-[#6C63FF]/15 text-[#6C63FF] flex items-center justify-center shrink-0 shadow-2xs">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </span>
            <div>
                <h1 class="text-lg font-black text-[#29283D] font-display">Pengaturan Sistem ARTDEVATA</h1>
                <p class="text-xs text-[#77768C] mt-0.5">Kelola identitas aplikasi, tarif biaya, paket wisata, kategori operasional, dan parameter sistem.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-[#77768C] font-semibold">Tersimpan di:</span>
            <span class="clay-badge bg-[#72D6C9]/20 text-[#147a6f] border-[#72D6C9]/40 font-bold">
                Database Cloud
            </span>
        </div>
    </div>

    <!-- Sub Menu Navigation (Tab Bar) -->
    <div class="clay-card p-1.5 bg-[#F4F3FA] border border-[#E8E6F0] grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-1.5">
        <!-- Sub-Menu 1: Identitas & Brand -->
        <button type="button" 
                @click="updateTab('brand')"
                :class="activeTab === 'brand' ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-bold' : 'text-[#77768C] hover:text-[#29283D] hover:bg-white/80 font-semibold'"
                class="px-2.5 py-2.5 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span class="truncate">Identitas & Brand</span>
        </button>

        <!-- Sub-Menu 2: Tarif & Biaya Tour -->
        <button type="button" 
                @click="updateTab('tariffs')"
                :class="activeTab === 'tariffs' ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-bold' : 'text-[#77768C] hover:text-[#29283D] hover:bg-white/80 font-semibold'"
                class="px-2.5 py-2.5 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="truncate">Standar Potongan</span>
        </button>

        <!-- Sub-Menu 3: Paket Wisata & Harga (Baru) -->
        <button type="button" 
                @click="updateTab('packages')"
                :class="activeTab === 'packages' ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-bold' : 'text-[#77768C] hover:text-[#29283D] hover:bg-white/80 font-semibold'"
                class="px-2.5 py-2.5 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <span class="truncate">Paket Wisata & Harga</span>
        </button>

        <!-- Sub-Menu 4: Kategori Biaya -->
        <button type="button" 
                @click="updateTab('categories')"
                :class="activeTab === 'categories' ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-bold' : 'text-[#77768C] hover:text-[#29283D] hover:bg-white/80 font-semibold'"
                class="px-2.5 py-2.5 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            <span class="truncate">Kategori Biaya</span>
        </button>

        <!-- Sub-Menu 5: Validasi & Antrean -->
        <button type="button" 
                @click="updateTab('operational')"
                :class="activeTab === 'operational' ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-bold' : 'text-[#77768C] hover:text-[#29283D] hover:bg-white/80 font-semibold'"
                class="px-2.5 py-2.5 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
            <span class="truncate">Operasional & Shift</span>
        </button>

        <!-- Sub-Menu 6: Role & Hak Akses -->
        <button type="button" 
                @click="updateTab('roles')"
                :class="activeTab === 'roles' ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-bold' : 'text-[#77768C] hover:text-[#29283D] hover:bg-white/80 font-semibold'"
                class="px-2.5 py-2.5 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span class="truncate">Role & Hak Akses</span>
        </button>
    </div>

    <!-- Main Settings Form (Hidden when on packages or roles tab) -->
    <form id="main-settings-form" x-show="activeTab !== 'packages' && activeTab !== 'roles'" action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6 text-xs">
        @csrf
        <input type="hidden" name="active_tab" :value="activeTab">

        <!-- ========================================== -->
        <!-- SUB-MENU 1: IDENTITAS & BRAND             -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'brand'" class="space-y-6" x-cloak>
            <!-- Card: Logo & Identitas Brand -->
            <div class="clay-card p-6 sm:p-8 space-y-5">
                <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3.5">
                    <div>
                        <h3 class="font-extrabold text-[#29283D] text-sm font-display">Logo & Identitas Brand</h3>
                        <p class="text-[11px] text-[#77768C]">Logo ini tampil di sidebar dashboard admin, portal mobile guide, dan halaman login.</p>
                    </div>
                    <span class="clay-badge bg-purple-50 text-[#6C63FF] border-purple-200 font-bold">
                        Dapat Diatur
                    </span>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                    <!-- Current Logo Preview -->
                    <div class="flex flex-col items-center justify-center p-3.5 bg-[#F4F3FA] rounded-2xl border border-[#E8E6F0] shadow-sm shrink-0 w-32 h-32">
                        @if(!empty($settings['company_logo']) && file_exists(public_path($settings['company_logo'])))
                            <img src="{{ asset($settings['company_logo']) }}" alt="Logo Perusahaan" class="max-w-full max-h-20 object-contain">
                            <span class="text-[9px] text-[#6C63FF] font-bold mt-1.5">Logo Kustom</span>
                        @else
                            <div class="w-14 h-14 rounded-2xl bg-[#6C63FF] text-[#FFD166] font-black text-2xl flex items-center justify-center shadow-xs">
                                {{ substr($settings['company_name'] ?? 'A', 0, 1) }}
                            </div>
                            <span class="text-[9px] text-[#77768C] font-semibold mt-1.5">Icon Bawaan</span>
                        @endif
                    </div>

                    <!-- Upload Input -->
                    <div class="space-y-2.5 flex-1">
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

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-[#E8E6F0]">
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

            <!-- Card: Kontak & Alamat Kantor -->
            <div class="clay-card p-6 sm:p-8 space-y-4">
                <div class="border-b border-[#E8E6F0] pb-3">
                    <h3 class="font-extrabold text-[#29283D] text-sm font-display">Kontak & Alamat Kantor Operasional</h3>
                    <p class="text-[11px] text-[#77768C]">Informasi kontak ini dicantumkan pada invoice dan slip informasi tour.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-[#29283D] mb-1.5">Nomor Telepon Operasional</label>
                        <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone']) }}"
                               placeholder="+62 812-3456-7890" class="clay-input w-full">
                    </div>

                    <div>
                        <label class="block font-bold text-[#29283D] mb-1.5">Alamat Kantor / Basecamp</label>
                        <textarea name="company_address" rows="2" placeholder="Jl. Sunset Road, Seminyak, Kuta, Bali" class="clay-input w-full">{{ old('company_address', $settings['company_address']) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- SUB-MENU 2: TARIF & BIAYA TOUR            -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'tariffs'" class="space-y-6" x-cloak>
            <div class="clay-card p-6 sm:p-8 space-y-5">
                <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3.5">
                    <div>
                        <h3 class="font-extrabold text-[#29283D] text-sm font-display">Standar Tarif & Potongan Operasional Tour</h3>
                        <p class="text-[11px] text-[#77768C]">Pengaturan tarif acuan untuk pembagian fee pemandu dan potongan operasional pada setiap transaksi.</p>
                    </div>
                    <span class="clay-badge bg-emerald-50 text-emerald-700 border-emerald-200 font-bold">
                        Finansial Otomatis
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <!-- Fee Guide Standar -->
                    <div class="p-4 bg-[#F4F3FA] rounded-2xl border border-[#E8E6F0] space-y-2">
                        <label class="block font-bold text-[#29283D] flex items-center gap-1.5 text-xs">
                            <span class="w-6 h-6 rounded-lg bg-[#6C63FF]/15 text-[#6C63FF] flex items-center justify-center font-bold text-xs">1</span>
                            Tarif Standar Fee Guide per Tamu (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center rounded-2xl bg-white border border-[#E8E6F0] shadow-2xs overflow-hidden focus-within:border-[#6C63FF] focus-within:ring-2 focus-within:ring-[#6C63FF]/20">
                            <span class="px-3.5 py-2.5 bg-[#F4F3FA] text-xs font-bold text-[#77768C] border-r border-[#E8E6F0] select-none">Rp</span>
                            <input type="number" name="default_guide_fee" value="{{ old('default_guide_fee', $settings['default_guide_fee']) }}" min="0" step="5000" required
                                   class="flex-1 px-3 py-2 bg-transparent text-xs font-bold text-[#29283D] outline-none">
                        </div>
                        <span class="text-[10px] text-[#77768C] block leading-relaxed">
                            Tarif standar yang dibayarkan kepada guide per orang tamu saat melayani tour langsung/booking (bila guide bertipe fee <em>PER_GUEST</em>).
                        </span>
                    </div>

                    <!-- Potongan Biaya Telepon -->
                    <div class="p-4 bg-[#F4F3FA] rounded-2xl border border-[#E8E6F0] space-y-2">
                        <label class="block font-bold text-[#29283D] flex items-center gap-1.5 text-xs">
                            <span class="w-6 h-6 rounded-lg bg-[#FF8A65]/15 text-[#FF8A65] flex items-center justify-center font-bold text-xs">2</span>
                            Tarif Standar Biaya Telepon / Tour (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center rounded-2xl bg-white border border-[#E8E6F0] shadow-2xs overflow-hidden focus-within:border-[#FF8A65] focus-within:ring-2 focus-within:ring-[#FF8A65]/20">
                            <span class="px-3.5 py-2.5 bg-[#F4F3FA] text-xs font-bold text-[#77768C] border-r border-[#E8E6F0] select-none">Rp</span>
                            <input type="number" name="default_phone_fee" value="{{ old('default_phone_fee', $settings['default_phone_fee']) }}" min="0" step="1000" required
                                   class="flex-1 px-3 py-2 bg-transparent text-xs font-bold text-[#29283D] outline-none">
                        </div>
                        <span class="text-[10px] text-[#77768C] block leading-relaxed">
                            Biaya operasional telepon/komunikasi yang otomatis disimpan dan memotong total margin tour langsung (bekerja otomatis seperti fee guide).
                        </span>
                    </div>

                    <!-- Standar Uang Driver -->
                    <div class="p-4 bg-[#F4F3FA] rounded-2xl border border-[#E8E6F0] space-y-2">
                        <label class="block font-bold text-[#29283D] flex items-center gap-1.5 text-xs">
                            <span class="w-6 h-6 rounded-lg bg-[#FFD166]/30 text-[#8a6800] flex items-center justify-center font-bold text-xs">3</span>
                            Tarif Standar Uang Driver (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center rounded-2xl bg-white border border-[#E8E6F0] shadow-2xs overflow-hidden focus-within:border-[#FFD166] focus-within:ring-2 focus-within:ring-[#FFD166]/20">
                            <span class="px-3.5 py-2.5 bg-[#F4F3FA] text-xs font-bold text-[#77768C] border-r border-[#E8E6F0] select-none">Rp</span>
                            <input type="number" name="default_driver_fee" value="{{ old('default_driver_fee', $settings['default_driver_fee']) }}" min="0" step="5000" required
                                   class="flex-1 px-3 py-2 bg-transparent text-xs font-bold text-[#29283D] outline-none">
                        </div>
                        <span class="text-[10px] text-[#77768C] block leading-relaxed">
                            Nominal acuan uang jalan/tip driver yang otomatis terisi saat opsi "Kasih Uang ke Driver" diaktifkan pada form tour langsung.
                        </span>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="p-4 rounded-2xl bg-[#72D6C9]/15 border border-[#72D6C9]/40 text-[#147a6f] space-y-1.5">
                    <div class="flex items-center gap-2 font-bold text-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Mekanisme Kalkulasi Otomatis (Live Snapshot)</span>
                    </div>
                    <p class="text-[11px] leading-relaxed text-[#147a6f]/90">
                        Kedua parameter tarif di atas akan langsung menjadi nilai default saat admin atau staf membuka form <strong>Tour Langsung (Walk-in)</strong>. 
                        Rumus margin: <code class="font-mono bg-white/60 px-1.5 py-0.5 rounded text-[10px]">Estimasi Margin = Total Tagihan - Fee Guide - Biaya Telepon</code>.
                    </p>
                </div>

                <!-- Shortcut Card: Paket Wisata & Harga Tamu -->
                <div class="p-4 bg-gradient-to-r from-[#6C63FF]/10 via-[#ECEBFC] to-white rounded-2xl border border-[#6C63FF]/25 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-[#6C63FF] text-white flex items-center justify-center font-bold shrink-0 shadow-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </span>
                        <div>
                            <h4 class="font-extrabold text-[#29283D] text-xs">Mau Mengubah Nama & Harga Paket Wisata Tamu?</h4>
                            <p class="text-[11px] text-[#77768C]">Ubah nama paket (seperti Ubud Cultural Tour), tarif per orang tamu, atau durasi tour langsung di tab Paket Wisata.</p>
                        </div>
                    </div>
                    <button type="button" @click="updateTab('packages')" class="clay-btn-primary px-3.5 py-2 text-xs font-bold shrink-0 inline-flex items-center gap-1.5 shadow-xs">
                        <span>Atur Paket Wisata &rarr;</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- SUB-MENU 3: KATEGORI PENGELUARAN          -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'categories'" class="space-y-6" x-cloak>
            <div class="clay-card p-6 sm:p-8 space-y-4">
                <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3">
                    <div>
                        <h3 class="font-extrabold text-[#29283D] text-sm font-display">Pilihan Kategori Biaya Operasional</h3>
                        <p class="text-[11px] text-[#77768C]">Sesuaikan opsi pilihan kategori dropdown saat mencatat pengeluaran operasional & tour.</p>
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
                <div class="space-y-2 pt-2">
                    <label class="block font-bold text-[#29283D] text-xs">Daftar Kategori Pilihan (Bisa Diedit):</label>
                    <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                        <template x-for="(cat, index) in categories" :key="index">
                            <div class="flex items-center justify-between p-2.5 bg-[#F4F3FA] rounded-xl border border-[#E8E6F0] shadow-2xs group hover:border-[#6C63FF]/40 transition-colors">
                                <div class="flex items-center gap-2.5 flex-1">
                                    <span class="w-6 h-6 rounded-full bg-white text-[#6C63FF] text-[10px] font-black flex items-center justify-center shrink-0 border border-[#E8E6F0]" x-text="index + 1"></span>
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
                    <p class="text-[10px] text-[#77768C]">Pilihan kategori ini otomatis muncul pada modul Pengeluaran Operasional, Biaya Booking, dan Laporan Keuangan.</p>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- SUB-MENU 4: OPERASIONAL & SHIFT           -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'operational'" class="space-y-6" x-cloak>
            <!-- Card 0: Buka Tutup Operasional Loket (Sistem Kasir) -->
            <div class="clay-card p-6 sm:p-8 space-y-6 border border-[#E8E6F0]">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#E8E6F0] pb-4">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h3 class="font-extrabold text-[#29283D] text-sm font-display">Sistem Buka - Tutup Operasional Loket (Shift Kasir)</h3>
                            @if($operationalShift['is_open'])
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-[#72D6C9]/20 text-[#147a6f] border border-[#72D6C9]/50 inline-flex items-center gap-1.5 shadow-2xs">
                                    <span class="w-2 h-2 rounded-full bg-[#147a6f] animate-pulse"></span>
                                    LOKET BUKA
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-rose-100 text-rose-800 border border-rose-300 inline-flex items-center gap-1.5 shadow-2xs">
                                    <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                                    LOKET TUTUP
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-[#77768C] mt-1">
                            Sebelum loket dibuka, guide <strong>tidak bisa check-in</strong>. Saat loket ditutup, semua guide yang belum checkout <strong>otomatis di-checkout</strong>.
                        </p>
                    </div>

                    <!-- Live Quick Action Button -->
                    <div class="shrink-0 flex items-center gap-2">
                        @if($operationalShift['is_open'])
                            <button type="submit" 
                                    form="settings-close-shift-form"
                                    class="clay-btn bg-rose-500 hover:bg-rose-600 text-white border-rose-600 px-4 py-2 text-xs font-black shadow-md flex items-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span>Tutup Loket Sekarang (Manual)</span>
                            </button>
                        @else
                            @if($operationalShift['active_guides_count'] > 0)
                                <button type="submit" 
                                        form="settings-force-close-shift-form"
                                        class="clay-btn bg-rose-500 hover:bg-rose-600 text-white border-rose-600 px-4 py-2 text-xs font-black shadow-md flex items-center gap-2 cursor-pointer animate-pulse">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <span>Force Tutup & Checkout ({{ $operationalShift['active_guides_count'] }})</span>
                                </button>
                            @endif

                            <button type="submit" 
                                    form="settings-open-shift-form"
                                    class="clay-btn bg-[#72D6C9] hover:bg-[#5fc0b3] text-[#147a6f] border-[#72D6C9] px-4 py-2 text-xs font-black shadow-md flex items-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                </svg>
                                <span>Buka Loket Sekarang (Manual)</span>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Shift Metrics Banner -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="p-3.5 bg-[#F4F3FA] rounded-2xl border border-[#E8E6F0] space-y-1">
                        <span class="text-[10px] text-[#77768C] font-bold block uppercase tracking-wider">Status Operasional Hari Ini</span>
                        <div class="text-xs font-black {{ $operationalShift['is_open'] ? 'text-[#147a6f]' : 'text-rose-700' }}">
                            {{ $operationalShift['is_open'] ? '● Loket Sedang BUKA (Guide Bisa Check-In)' : '○ Loket Sedang TUTUP (Check-In Ditutup)' }}
                        </div>
                    </div>

                    <div class="p-3.5 bg-[#F4F3FA] rounded-2xl border border-[#E8E6F0] space-y-1">
                        <span class="text-[10px] text-[#77768C] font-bold block uppercase tracking-wider">Guide Aktif Belum Checkout</span>
                        <div class="text-xs font-black text-[#6C63FF]">
                            {{ $operationalShift['active_guides_count'] }} Guide Sedang Check-In
                        </div>
                    </div>

                    <div class="p-3.5 bg-[#F4F3FA] rounded-2xl border border-[#E8E6F0] space-y-1">
                        <span class="text-[10px] text-[#77768C] font-bold block uppercase tracking-wider">Riwayat Terakhir</span>
                        <div class="text-xs font-medium text-[#29283D] truncate">
                            @if($operationalShift['is_open'] && $operationalShift['opened_at'])
                                Buka: {{ \Carbon\Carbon::parse($operationalShift['opened_at'])->format('H:i') }} ({{ $operationalShift['opened_by'] ?? 'Staf' }})
                            @elseif($operationalShift['closed_at'])
                                Tutup: {{ \Carbon\Carbon::parse($operationalShift['closed_at'])->format('H:i') }} ({{ $operationalShift['closed_by'] ?? 'Sistem' }})
                            @else
                                Belum ada riwayat hari ini
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Auto-Schedule Setting Configuration -->
                <div class="p-5 bg-gradient-to-r from-purple-50/50 via-[#F4F3FA] to-white rounded-2xl border border-[#6C63FF]/20 space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-[#6C63FF] text-white flex items-center justify-center font-bold text-xs shadow-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-[#29283D] text-xs">Jadwal Buka - Tutup Otomatis Berdasarkan Jam</h4>
                            <p class="text-[10px] text-[#77768C]">Sistem akan secara otomatis membuka loket pada jam buka dan menutup loket (otomatis checkout) pada jam tutup.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                        <!-- Toggle Auto Schedule -->
                        <div class="space-y-1.5">
                            <label class="block font-bold text-[#29283D] text-xs">Mode Jadwal Otomatis</label>
                            <select name="operational_auto_schedule_enabled" class="clay-input w-full font-bold text-[#29283D] text-xs">
                                <option value="0" {{ $settings['operational_auto_schedule_enabled'] == '0' ? 'selected' : '' }}>
                                    Non-Aktif (Manual oleh Staf / Admin)
                                </option>
                                <option value="1" {{ $settings['operational_auto_schedule_enabled'] == '1' ? 'selected' : '' }}>
                                    Aktif Otomatis (Berdasarkan Jam Buka & Tutup)
                                </option>
                            </select>
                            <span class="text-[10px] text-[#77768C] block">Jika non-aktif, staf/admin mengontrol buka-tutup loket secara manual.</span>
                        </div>

                        <!-- Jam Buka Otomatis -->
                        <div class="space-y-1.5">
                            <label class="block font-bold text-[#29283D] text-xs">Jam Buka Otomatis (WITA)</label>
                            <input type="time" name="operational_auto_open_time" value="{{ old('operational_auto_open_time', $settings['operational_auto_open_time']) }}"
                                   class="clay-input w-full font-bold text-[#29283D] text-xs">
                            <span class="text-[10px] text-[#77768C] block">Mulai jam ini, guide diizinkan untuk check-in.</span>
                        </div>

                        <!-- Jam Tutup Otomatis -->
                        <div class="space-y-1.5">
                            <label class="block font-bold text-[#29283D] text-xs">Jam Tutup Otomatis (WITA)</label>
                            <input type="time" name="operational_auto_close_time" value="{{ old('operational_auto_close_time', $settings['operational_auto_close_time']) }}"
                                   class="clay-input w-full font-bold text-[#29283D] text-xs">
                            <span class="text-[10px] text-[#77768C] block">Pada jam ini, semua guide aktif otomatis di-checkout.</span>
                        </div>
                    </div>

                    <div class="p-3 bg-white/80 rounded-xl border border-[#E8E6F0] text-[11px] text-[#77768C] leading-relaxed flex items-start gap-2">
                        <svg class="w-4 h-4 text-[#6C63FF] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>
                            <strong>Catatan:</strong> Meskipun jadwal otomatis aktif, Staf dan Admin tetap memiliki wewenang untuk menekan tombol <strong>Buka Loket</strong> atau <strong>Tutup Loket</strong> manual sewaktu-waktu jika ada perubahan kondisi di dermaga/lapangan.
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card 1: GPS Validation -->
            <div class="clay-card p-6 sm:p-8 space-y-4">
                <div class="border-b border-[#E8E6F0] pb-3">
                    <h3 class="font-extrabold text-[#29283D] text-sm font-display">Validasi GPS Lokasi saat Check-in / Checkout</h3>
                    <p class="text-[11px] text-[#77768C] mt-0.5">Atur apakah guide wajib menyalakan GPS saat melakukan absensi harian.</p>
                </div>

                <div class="p-4 bg-[#F4F3FA] rounded-2xl border border-[#E8E6F0] space-y-3">
                    <p class="text-[11px] text-[#77768C] leading-relaxed">
                        Sesuai spesifikasi, GPS bersifat opsional dan dapat dinonaktifkan agar tidak menjadi satu-satunya mekanisme validasi operasional bila sinyal di dermaga/basecamp lemah.
                    </p>
                    <select name="gps_required" class="clay-input w-full font-bold text-[#29283D]">
                        <option value="0" {{ $settings['gps_required'] == '0' ? 'selected' : '' }}>GPS Opsional (Disarankan - Guide tetap bisa check-in tanpa sinyal GPS)</option>
                        <option value="1" {{ $settings['gps_required'] == '1' ? 'selected' : '' }}>GPS Wajib (Check-in ditolak jika GPS tidak terdeteksi)</option>
                    </select>
                </div>
            </div>

            <!-- Card 2: Queue Rotation Adjustment -->
            <div class="clay-card p-6 sm:p-8 space-y-4">
                <div class="border-b border-[#E8E6F0] pb-3">
                    <h3 class="font-extrabold text-[#29283D] text-sm font-display">Kontrol Rotasi Antrean Guide (Queue State)</h3>
                    <p class="text-[11px] text-[#77768C] mt-0.5">Override manual giliran antrean sirkular pemandu jika diperlukan penyesuaian khusus.</p>
                </div>

                <div class="p-5 bg-amber-50/70 rounded-2xl border border-amber-200 space-y-3">
                    <p class="text-[11px] text-amber-900 leading-relaxed">
                        Posisi antrean terakhir yang mendapatkan giliran penugasan: <strong>#{{ $queueState->current_position }}</strong>. 
                        Anda dapat mengubah nomor ini secara manual jika terjadi pertukaran giliran atau pembatalan di dermaga.
                    </p>
                    <div class="flex items-center space-x-3">
                        <span class="font-bold text-[#29283D] text-xs">Set Posisi Giliran ke #</span>
                        <input type="number" name="current_position" value="{{ $queueState->current_position }}" min="0" max="999"
                               class="w-28 px-3 py-1.5 clay-input font-bold text-center text-sm text-[#29283D]">
                    </div>
                </div>
            </div>

            <!-- Card 3: Integrasi Notifikasi Webhook Discord -->
            <div class="clay-card p-6 sm:p-8 space-y-4 border border-[#5865F2]/30 bg-gradient-to-br from-indigo-50/40 via-white to-white">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#E8E6F0] pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-[#5865F2]/15 text-[#5865F2] flex items-center justify-center shrink-0 shadow-2xs">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.929 1.793 8.18 1.793 12.061 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.894.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.028zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-[#29283D] text-sm font-display">Integrasi Notifikasi Webhook Discord</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ ($discordWebhookConfigured ?? false) ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-gray-100 text-[#77768C] border border-[#E8E6F0]' }}">
                                    {{ ($discordWebhookConfigured ?? false) ? '● TERHUBUNG (.ENV)' : '○ BELUM DIATUR (.ENV)' }}
                                </span>
                            </div>
                            <p class="text-[11px] text-[#77768C] mt-0.5">Konfigurasi link webhook dikelola terpusat melalui file <code class="font-mono font-bold text-[#5865F2]">.env</code> server (<code class="font-mono">DISCORD_WEBHOOK_URL</code>).</p>
                        </div>
                    </div>
                </div>

                <!-- Webhook Status & Test Notification Action -->
                <div class="p-4 bg-white/95 rounded-2xl border border-[#E8E6F0] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1 max-w-xl">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-[#29283D]">Status Integrasi Webhook:</span>
                            @if($discordWebhookConfigured ?? false)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Aktif dari .env
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                    Belum diatur di file .env
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-[#77768C] leading-relaxed">
                            @if($discordWebhookConfigured ?? false)
                                Webhook Discord aktif terdeteksi. Sistem secara otomatis mengirimkan log saat loket BUKA dan rekap lengkap saat loket TUTUP.
                            @else
                                Untuk mengaktifkan, tambahkan variabel <code class="font-mono text-purple-700 font-bold">DISCORD_WEBHOOK_URL</code> di file <code class="font-mono font-bold">.env</code> server Anda.
                            @endif
                        </p>
                    </div>

                    <button type="button" id="btn-test-discord"
                            onclick="testDiscordWebhook()"
                            style="background-color: #5865F2 !important; color: #ffffff !important;"
                            class="px-5 py-2.5 rounded-xl font-bold text-xs text-white shrink-0 flex items-center justify-center gap-2 shadow-md shadow-[#5865F2]/25 hover:opacity-90 active:scale-[0.97] transition-all cursor-pointer">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span id="btn-test-discord-text" class="text-white font-bold">Tes Notifikasi</span>
                    </button>
                </div>

                <!-- Feature Highlights Preview Banner -->
                <div class="p-4 bg-white/90 rounded-2xl border border-[#E8E6F0] space-y-2">
                    <span class="text-[11px] font-extrabold text-[#29283D] block">Format Rekap Yang Dikirim ke Discord:</span>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-[11px] text-[#77768C]">
                        <div class="p-2.5 bg-emerald-50/60 rounded-xl border border-emerald-200/80 space-y-1">
                            <span class="font-extrabold text-emerald-800 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Saat Buka Loket
                            </span>
                            <p class="text-[10px] text-emerald-900 leading-relaxed">
                                Notifikasi pembukaan loket, nama staf/admin yang bertugas, tanggal & waktu buka, jumlah guide aktif siap tugas, dan mode jadwal operasional.
                            </p>
                        </div>
                        <div class="p-2.5 bg-rose-50/60 rounded-xl border border-rose-200/80 space-y-1">
                            <span class="font-extrabold text-rose-800 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span> Saat Tutup Loket
                            </span>
                            <p class="text-[10px] text-rose-900 leading-relaxed">
                                Rekap lengkap: <strong>Berapa Pax laku</strong>, <strong>Berapa uang masuk</strong> (Total omset & rincian Tunai, QRIS, Transfer, EDC), <strong>Berapa guide hadir</strong> (termasuk auto-checkout), biaya operasional, dan estimasi laba bersih.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Bottom Action Bar -->
        <div class="clay-card p-4 flex items-center justify-between gap-4 border border-[#E8E6F0]">
            <div class="text-xs text-[#77768C]">
                Sub-menu aktif: 
                <strong class="text-[#6C63FF]" 
                        x-text="activeTab === 'brand' ? 'Identitas & Brand' : (activeTab === 'tariffs' ? 'Standar Potongan' : (activeTab === 'categories' ? 'Kategori Biaya' : 'Operasional & Shift'))"></strong>
            </div>
            <button type="submit" form="main-settings-form" class="clay-btn-primary px-6 py-2.5 font-bold transition-all shadow-md flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Simpan Konfigurasi</span>
            </button>
        </div>
    </form>

    <!-- Standalone forms for Quick Shift Operations (Outside main form to prevent HTML5 nested form invalidation) -->
    <form id="settings-close-shift-form" action="{{ route('operational.close') }}" method="POST" class="hidden"
          data-confirm="Konfirmasi Tutup Loket Operasional? Semua guide ({{ $operationalShift['active_guides_count'] }} orang) yang saat ini berstatus CHECK-IN akan OTOMATIS di-checkout oleh sistem!"
          data-confirm-title="Tutup Loket Operasional"
          data-confirm-danger="true"
          data-confirm-btn="Ya, Tutup Loket Sekarang">
        @csrf
        <input type="hidden" name="reason" value="Penutupan manual loket dari Pengaturan Admin">
    </form>

    <form id="settings-force-close-shift-form" action="{{ route('operational.close') }}" method="POST" class="hidden"
          data-confirm="Terdapat {{ $operationalShift['active_guides_count'] }} guide yang masih berstatus CHECK-IN. Lakukan Force Tutup Loket untuk checkout seluruh guide tersebut?"
          data-confirm-title="Force Tutup Loket"
          data-confirm-danger="true"
          data-confirm-btn="Ya, Force Checkout Semua">
        @csrf
        <input type="hidden" name="reason" value="Force tutup loket dari Pengaturan Admin">
    </form>

    <form id="settings-open-shift-form" action="{{ route('operational.open') }}" method="POST" class="hidden">
        @csrf
    </form>

    <!-- ========================================== -->
    <!-- SUB-MENU 3: PAKET WISATA & HARGA          -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'packages'" class="space-y-6" x-cloak>
        <!-- Header Card -->
        <div class="clay-card p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-[#E8E6F0]">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h3 class="font-extrabold text-[#29283D] text-sm font-display">Katalog Paket Wisata & Tarif Tamu</h3>
                    <span class="clay-badge bg-[#72D6C9]/20 text-[#147a6f] border-[#72D6C9]/40 font-bold">
                        {{ $tourPackages->count() }} Paket Terdaftar
                    </span>
                </div>
                <p class="text-[11px] text-[#77768C] leading-relaxed max-w-2xl">
                    Atur nama paket wisata, tarif harga per orang tamu (1 Pax), durasi, dan status. Data ini otomatis muncul pada dropdown pilihan paket di form <strong>Tour Langsung (Walk-In)</strong> dan <strong>Booking Baru</strong>.
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="openAddPackage()" 
                        class="clay-btn-primary px-4 py-2 font-bold text-xs inline-flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4 text-[#FFD166]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>+ Tambah Paket Baru</span>
                </button>
            </div>
        </div>

        <!-- Packages Grid / List -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($tourPackages as $pkg)
            <div class="clay-card p-5 space-y-4 border border-[#E8E6F0] hover:border-[#6C63FF]/40 transition-all flex flex-col justify-between">
                <div class="space-y-2.5">
                    <div class="flex items-start justify-between gap-3 border-b border-[#E8E6F0] pb-3">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="font-extrabold text-[#29283D] text-sm">{{ $pkg->name }}</h4>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $pkg->status === 'ACTIVE' ? 'bg-[#72D6C9]/20 text-[#147a6f] border border-[#72D6C9]/50' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                    {{ $pkg->status === 'ACTIVE' ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </div>
                            <p class="text-[11px] text-[#77768C] line-clamp-2 leading-relaxed">{{ $pkg->description ?? 'Tidak ada deskripsi.' }}</p>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <button type="button" @click="openEditPackage({{ Js::from($pkg) }})" 
                                    class="clay-btn-secondary px-2.5 py-1.5 text-xs font-bold inline-flex items-center gap-1 text-[#6C63FF]"
                                    title="Edit Nama & Harga">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit</span>
                            </button>
                            <a href="{{ route('packages.edit', ['package' => $pkg, 'from' => 'settings']) }}" 
                               class="p-1.5 rounded-xl text-[#77768C] hover:text-[#6C63FF] hover:bg-[#ECEBFC] transition-colors" 
                               title="Pengaturan Skema Bertingkat (Lanjutan)">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </a>
                            <form action="{{ route('packages.destroy', $pkg) }}" method="POST"
                                  data-confirm="Apakah Anda yakin ingin menghapus paket '{{ $pkg->name }}'? Seluruh skema harga akan dihapus."
                                  data-confirm-title="Hapus Paket Wisata"
                                  data-confirm-btn="Ya, Hapus"
                                  data-confirm-danger="true">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="from" value="settings">
                                <button type="submit" class="p-1.5 rounded-xl text-[#77768C] hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus Paket">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Meta Specs Grid -->
                    <div class="grid grid-cols-3 gap-2.5 text-xs">
                        <div class="p-2.5 bg-[#F4F3FA] rounded-xl border border-[#E8E6F0]">
                            <span class="text-[10px] font-bold text-[#77768C] uppercase tracking-wider block">Harga per Tamu</span>
                            <span class="text-sm font-extrabold text-[#6C63FF] mt-0.5 block">Rp {{ number_format($pkg->default_price, 0, ',', '.') }}</span>
                        </div>
                        <div class="p-2.5 bg-[#F4F3FA] rounded-xl border border-[#E8E6F0]">
                            <span class="text-[10px] font-bold text-[#77768C] uppercase tracking-wider block">Durasi</span>
                            <span class="text-xs font-bold text-[#29283D] mt-0.5 block">{{ $pkg->duration }}</span>
                        </div>
                        <div class="p-2.5 bg-[#F4F3FA] rounded-xl border border-[#E8E6F0]">
                            <span class="text-[10px] font-bold text-[#77768C] uppercase tracking-wider block">Skema Tarif</span>
                            <span class="text-xs font-bold text-[#29283D] mt-0.5 block">
                                {{ $pkg->prices->isNotEmpty() ? $pkg->prices->count() . ' Tingkat' : '1 Pax Tetap' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-between text-[11px] text-[#77768C] border-t border-[#E8E6F0]/60">
                    <span>Muncul di: <strong>Tour Langsung & Booking</strong></span>
                    <button type="button" @click="openEditPackage({{ Js::from($pkg) }})" class="text-[#6C63FF] hover:underline font-bold">
                        Ubah Harga / Nama &rarr;
                    </button>
                </div>
            </div>
            @empty
            <div class="col-span-2 clay-card p-10 text-center space-y-3">
                <p class="text-sm font-bold text-[#77768C]">Belum ada paket wisata yang terdaftar.</p>
                <button type="button" @click="openAddPackage()" class="clay-btn-primary px-4 py-2 text-xs font-bold">
                    + Tambah Paket Pertama
                </button>
            </div>
            @endforelse
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SUB-MENU 6: ROLE & HAK AKSES PENGGUNA      -->
    <!-- ========================================== -->
    @include('settings.partials.roles')

    <!-- MODAL: QUICK EDIT TOUR PACKAGE -->
    <div x-show="editPackageModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-xs" @click="editPackageModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-3xl shadow-2xl border border-[#E8E6F0] relative z-10"
                 @click.away="editPackageModal = false">
                <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3.5 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-[#6C63FF]/15 text-[#6C63FF] flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </span>
                        <div>
                            <h3 class="font-extrabold text-[#29283D] text-sm">Edit Paket Wisata & Harga</h3>
                            <p class="text-[11px] text-[#77768C]">Ubah nama paket, harga per tamu, atau durasi.</p>
                        </div>
                    </div>
                    <button type="button" @click="editPackageModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="pkgForm.actionUrl" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="from" value="settings">

                    <div>
                        <label class="block font-bold text-[#29283D] mb-1.5">Nama Paket Wisata <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="pkgForm.name" required placeholder="Contoh: Ubud Cultural Tour"
                               class="clay-input w-full font-bold text-xs">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block font-bold text-[#29283D] mb-1.5">Harga per Tamu (Rp) <span class="text-rose-500">*</span></label>
                            <div class="flex items-center rounded-2xl bg-white border border-[#E8E6F0] shadow-2xs overflow-hidden focus-within:border-[#6C63FF] focus-within:ring-2 focus-within:ring-[#6C63FF]/20">
                                <span class="px-3 py-2 bg-[#F4F3FA] text-xs font-bold text-[#77768C] border-r border-[#E8E6F0] select-none">Rp</span>
                                <input type="number" name="default_price" x-model="pkgForm.default_price" min="0" step="5000" required
                                       class="flex-1 px-3 py-2 bg-transparent text-xs font-bold text-[#29283D] outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-[#29283D] mb-1.5">Durasi Tour <span class="text-rose-500">*</span></label>
                            <input type="text" name="duration" x-model="pkgForm.duration" required placeholder="Contoh: 8 Jam / Full Day"
                                   class="clay-input w-full font-bold text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-[#29283D] mb-1.5">Status Paket <span class="text-rose-500">*</span></label>
                        <select name="status" x-model="pkgForm.status" class="clay-input w-full font-bold text-xs">
                            <option value="ACTIVE">Aktif (Tampil di Dropdown Tour Langsung & Booking)</option>
                            <option value="INACTIVE">Non-Aktif (Disembunyikan)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-[#29283D] mb-1.5">Deskripsi Singkat (Opsional)</label>
                        <textarea name="description" x-model="pkgForm.description" rows="2" placeholder="Keterangan singkat destinasi..." class="clay-input w-full text-xs"></textarea>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-[#E8E6F0]">
                        <button type="button" @click="editPackageModal = false" class="clay-btn-secondary px-4 py-2 font-bold text-xs">
                            Batal
                        </button>
                        <button type="submit" class="clay-btn-primary px-5 py-2 font-bold text-xs shadow-md">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: QUICK ADD TOUR PACKAGE -->
    <div x-show="addPackageModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-xs" @click="addPackageModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-3xl shadow-2xl border border-[#E8E6F0] relative z-10"
                 @click.away="addPackageModal = false">
                <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3.5 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-[#72D6C9]/20 text-[#147a6f] flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        </span>
                        <div>
                            <h3 class="font-extrabold text-[#29283D] text-sm">Tambah Paket Wisata Baru</h3>
                            <p class="text-[11px] text-[#77768C]">Paket baru akan langsung tersedia di Tour Langsung.</p>
                        </div>
                    </div>
                    <button type="button" @click="addPackageModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('packages.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <input type="hidden" name="from" value="settings">

                    <div>
                        <label class="block font-bold text-[#29283D] mb-1.5">Nama Paket Wisata <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="pkgForm.name" required placeholder="Contoh: Kintamani & Batur Sunrise"
                               class="clay-input w-full font-bold text-xs">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block font-bold text-[#29283D] mb-1.5">Harga per Tamu (Rp) <span class="text-rose-500">*</span></label>
                            <div class="flex items-center rounded-2xl bg-white border border-[#E8E6F0] shadow-2xs overflow-hidden focus-within:border-[#6C63FF] focus-within:ring-2 focus-within:ring-[#6C63FF]/20">
                                <span class="px-3 py-2 bg-[#F4F3FA] text-xs font-bold text-[#77768C] border-r border-[#E8E6F0] select-none">Rp</span>
                                <input type="number" name="default_price" x-model="pkgForm.default_price" min="0" step="5000" required
                                       class="flex-1 px-3 py-2 bg-transparent text-xs font-bold text-[#29283D] outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-[#29283D] mb-1.5">Durasi Tour <span class="text-rose-500">*</span></label>
                            <input type="text" name="duration" x-model="pkgForm.duration" required placeholder="Contoh: 8 Jam / Full Day"
                                   class="clay-input w-full font-bold text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-[#29283D] mb-1.5">Deskripsi Singkat (Opsional)</label>
                        <textarea name="description" x-model="pkgForm.description" rows="2" placeholder="Keterangan singkat destinasi..." class="clay-input w-full text-xs"></textarea>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-[#E8E6F0]">
                        <button type="button" @click="addPackageModal = false" class="clay-btn-secondary px-4 py-2 font-bold text-xs">
                            Batal
                        </button>
                        <button type="submit" class="clay-btn-primary px-5 py-2 font-bold text-xs shadow-md">
                            + Simpan Paket Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function testDiscordWebhook() {
    const btn = document.getElementById('btn-test-discord');
    const btnText = document.getElementById('btn-test-discord-text');

    if (btn) btn.disabled = true;
    if (btnText) btnText.innerText = 'Mengirim Tes...';

    fetch('{{ route('settings.test-discord') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(async (res) => {
        const data = await res.json();
        if (res.ok && data.success) {
            if ((window.ArtAlert || window.Swal)) {
                (window.ArtAlert || window.Swal).fire({
                    icon: 'success',
                    title: 'Tes Notifikasi Berhasil!',
                    text: data.message || 'Pesan uji coba berhasil terkirim ke channel Discord.',
                    confirmButtonColor: '#5865F2'
                });
            } else {
                alert(data.message || 'Pesan uji coba berhasil terkirim ke Discord.');
            }
        } else {
            if ((window.ArtAlert || window.Swal)) {
                (window.ArtAlert || window.Swal).fire({
                    icon: 'warning',
                    title: 'Pemberitahuan Webhook',
                    html: (data.message || 'Gagal mengirim pesan uji coba ke Discord.') + '<br><br><span class="text-xs text-slate-500">Pastikan <code>DISCORD_WEBHOOK_URL</code> telah diatur di file <code>.env</code> server.</span>',
                    confirmButtonColor: '#5865F2'
                });
            } else {
                alert(data.message || 'Gagal mengirim pesan uji coba ke Discord.');
            }
        }
    })
    .catch((err) => {
        if ((window.ArtAlert || window.Swal)) {
            (window.ArtAlert || window.Swal).fire({
                icon: 'error',
                title: 'Kesalahan Jaringan',
                text: 'Terjadi kesalahan saat menghubungi server: ' + err.message,
                confirmButtonColor: '#EF4444'
            });
        } else {
            alert('Terjadi kesalahan jaringan: ' + err.message);
        }
    })
    .finally(() => {
        if (btn) btn.disabled = false;
        if (btnText) btnText.innerText = 'Tes Notifikasi';
    });
}
</script>
@endsection
