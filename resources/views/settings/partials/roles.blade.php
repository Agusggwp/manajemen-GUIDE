<!-- SUB-MENU 6: ROLE & HAK AKSES PENGGUNA -->
<div x-show="activeTab === 'roles'" class="space-y-6" x-cloak>
    <!-- Header Card -->
    <div class="clay-card p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-[#E8E6F0]">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <h3 class="font-extrabold text-[#29283D] text-sm font-display">Manajemen Role & Hak Akses Pengguna</h3>
                <span class="clay-badge bg-[#6C63FF]/20 text-[#6C63FF] border-[#6C63FF]/40 font-bold">
                    {{ $roles->count() }} Role Terdaftar
                </span>
            </div>
            <p class="text-[11px] text-[#77768C] leading-relaxed max-w-2xl">
                Tentukan jabatan staf, otorisasi menu, dan batasan operasional untuk setiap peran pengguna. Anda dapat membuat role baru, mengatur izin secara granular, maupun mengubah hak akses yang sudah ada.
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button type="button" @click="openAddRole()" 
                    class="clay-btn-primary px-4 py-2 font-bold text-xs inline-flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4 text-[#FFD166]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Tambah Role Baru</span>
            </button>
        </div>
    </div>

    <!-- Role Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($roles as $role)
        @php
            $isSuperAdmin = $role->name === 'ADMIN';
            $isGuide = $role->name === 'GUIDE';
            $isStaff = $role->name === 'STAFF';
            $perms = $role->permissions ?? [];
            $hasAll = in_array('*', $perms) || $isSuperAdmin;
        @endphp
        <div class="clay-card p-5 space-y-4 border border-[#E8E6F0] hover:border-[#6C63FF]/40 transition-all flex flex-col justify-between">
            <div class="space-y-3">
                <!-- Header Card -->
                <div class="flex items-start justify-between gap-3 border-b border-[#E8E6F0] pb-3">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-lg text-xs font-black tracking-wider uppercase
                                {{ $isSuperAdmin ? 'bg-purple-100 text-purple-700 border border-purple-200' : ($isStaff ? 'bg-blue-100 text-blue-700 border border-blue-200' : ($isGuide ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-amber-100 text-amber-700 border border-amber-200')) }}">
                                {{ $role->name }}
                            </span>
                            <h4 class="font-black text-[#29283D] text-sm">{{ $role->label }}</h4>
                        </div>
                        <p class="text-[11px] text-[#77768C] leading-relaxed line-clamp-2">
                            {{ $role->description ?: 'Tidak ada deskripsi peran.' }}
                        </p>
                    </div>

                    <!-- Protected / Custom Badge -->
                    <span class="shrink-0 px-2 py-0.5 rounded-md text-[10px] font-bold 
                        {{ $role->isProtected() ? 'bg-slate-100 text-slate-600 border border-slate-200' : 'bg-[#72D6C9]/20 text-[#147a6f] border border-[#72D6C9]/40' }}">
                        {{ $role->isProtected() ? 'Sistem' : 'Custom' }}
                    </span>
                </div>

                <!-- Metrics & User Count -->
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="p-2.5 rounded-xl bg-[#F4F3FA] border border-[#E8E6F0] flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-white flex items-center justify-center text-[#6C63FF] shadow-2xs font-bold text-xs">👥</span>
                        <div>
                            <span class="text-[10px] text-[#77768C] block font-semibold">Pengguna Aktif</span>
                            <span class="font-extrabold text-[#29283D] text-xs">{{ $role->users_count }} Akun</span>
                        </div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-[#F4F3FA] border border-[#E8E6F0] flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-white flex items-center justify-center text-[#147a6f] shadow-2xs font-bold text-xs">🔑</span>
                        <div>
                            <span class="text-[10px] text-[#77768C] block font-semibold">Cakupan Izin</span>
                            <span class="font-extrabold text-[#29283D] text-xs">
                                {{ $hasAll ? 'Akses Penuh (*)' : count($perms) . ' Hak Akses' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Permissions Snippet Preview -->
                <div class="space-y-1.5 pt-1">
                    <span class="text-[10px] font-bold text-[#77768C] uppercase tracking-wider block">Ringkasan Wewenang:</span>
                    <div class="flex flex-wrap gap-1">
                        @if($hasAll)
                            <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-200 text-[10px] font-bold">
                                ✨ Semua Modul Terbuka (Full Administrator)
                            </span>
                        @elseif(empty($perms) && $isGuide)
                            <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                🧭 Portal Mobile Mandiri Pemandu Wisata
                            </span>
                        @elseif(!empty($perms))
                            @foreach(array_slice($perms, 0, 5) as $permKey)
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-semibold">
                                    {{ $permKey }}
                                </span>
                            @endforeach
                            @if(count($perms) > 5)
                                <span class="px-2 py-0.5 rounded-md bg-purple-50 text-[#6C63FF] border border-purple-200 text-[10px] font-bold">
                                    +{{ count($perms) - 5 }} lainnya
                                </span>
                            @endif
                        @else
                            <span class="text-[11px] text-slate-400 italic">Belum ada izin khusus yang diaktifkan.</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="pt-3 border-t border-[#E8E6F0] flex items-center justify-between gap-2">
                <button type="button" @click="openEditRole({{ Js::from($role) }})" 
                        class="clay-btn-secondary px-3 py-1.5 text-xs font-bold inline-flex items-center gap-1.5 text-[#6C63FF] hover:bg-[#6C63FF] hover:text-white transition-all shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>Atur Hak Akses</span>
                </button>

                @if(!$role->isProtected())
                    @if($role->users_count > 0)
                        <button type="button" 
                                onclick="alert('Role {{ $role->name }} tidak dapat dihapus karena masih digunakan oleh {{ $role->users_count }} akun pengguna.')"
                                class="px-2.5 py-1.5 rounded-xl text-slate-400 hover:text-slate-600 text-xs font-semibold inline-flex items-center gap-1"
                                title="Masih ada pengguna terhubung">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Terkunci</span>
                        </button>
                    @else
                        <form action="{{ route('roles.destroy', $role) }}" method="POST"
                              data-confirm="Apakah Anda yakin ingin menghapus role '{{ $role->label }} ({{ $role->name }})'? Tindakan ini tidak dapat dibatalkan."
                              data-confirm-title="Hapus Role Pengguna"
                              data-confirm-btn="Ya, Hapus Role"
                              data-confirm-danger="true">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-xl text-[#77768C] hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus Role">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    @endif
                @else
                    <span class="text-[11px] text-slate-400 font-semibold flex items-center gap-1" title="Role dasar sistem tidak dapat dihapus">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span>Sistem</span>
                    </span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- ==================================================== -->
<!-- MODAL: TAMBAH ROLE BARU                              -->
<!-- ==================================================== -->
<div x-show="addRoleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-xs" @click="addRoleModal = false"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-3xl shadow-2xl border border-[#E8E6F0] relative z-10"
             @click.away="addRoleModal = false">
            
            <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3.5 mb-4">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-[#6C63FF]/20 text-[#6C63FF] flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    </span>
                    <div>
                        <h3 class="font-extrabold text-[#29283D] text-sm">Tambah Role & Hak Akses Baru</h3>
                        <p class="text-[11px] text-[#77768C]">Buat jabatan baru dan tentukan wewenang operasionalnya.</p>
                    </div>
                </div>
                <button type="button" @click="addRoleModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('roles.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-bold text-[#29283D] mb-1.5">Kode Role (Identifier) <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="newRoleForm.name" required placeholder="Contoh: KASIR / SUPERVISOR"
                               class="clay-input w-full font-bold uppercase text-xs">
                        <p class="text-[10px] text-[#77768C] mt-1">Gunakan huruf kapital & angka tanpa spasi.</p>
                    </div>

                    <div>
                        <label class="block font-bold text-[#29283D] mb-1.5">Nama Jabatan (Label) <span class="text-rose-500">*</span></label>
                        <input type="text" name="label" x-model="newRoleForm.label" required placeholder="Contoh: Staff Kasir & Tiket"
                               class="clay-input w-full font-bold text-xs">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-[#29283D] mb-1.5">Deskripsi Peran (Opsional)</label>
                    <input type="text" name="description" x-model="newRoleForm.description" placeholder="Uraian singkat wewenang dan tugas jabatan ini..."
                           class="clay-input w-full text-xs">
                </div>

                <!-- Checkbox Permissions Terkelompok -->
                <div class="space-y-3 pt-2">
                    <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-1.5">
                        <label class="font-black text-[#29283D] text-xs">Daftar Hak Akses Sistem (Permissions)</label>
                        <span class="text-[10px] text-[#77768C]">Centang izin yang diberikan untuk role ini.</span>
                    </div>

                    <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
                        @foreach($availablePermissions as $categoryName => $perms)
                        @php
                            $catKeys = array_keys($perms);
                        @endphp
                        <div class="p-3 bg-[#F4F3FA] rounded-2xl border border-[#E8E6F0] space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-[#29283D] text-xs">{{ $categoryName }}</span>
                                <div class="flex items-center gap-2 text-[10px]">
                                    <button type="button" @click="toggleCategoryNew({{ Js::from($catKeys) }}, true)" class="text-[#6C63FF] hover:underline font-bold">Pilih Semua</button>
                                    <span class="text-slate-300">•</span>
                                    <button type="button" @click="toggleCategoryNew({{ Js::from($catKeys) }}, false)" class="text-slate-500 hover:underline">Batal</button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                                @foreach($perms as $permKey => $permDesc)
                                <label class="flex items-start gap-2 p-1.5 rounded-xl bg-white border border-[#E8E6F0] hover:border-[#6C63FF]/30 cursor-pointer select-none transition-all">
                                    <input type="checkbox" name="permissions[]" value="{{ $permKey }}"
                                           x-model="newRoleForm.permissions"
                                           class="mt-0.5 rounded text-[#6C63FF] focus:ring-[#6C63FF] h-4 w-4">
                                    <div class="text-[11px] leading-tight">
                                        <span class="font-bold text-[#29283D] block">{{ $permDesc }}</span>
                                        <code class="text-[9px] text-[#77768C]">{{ $permKey }}</code>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-[#E8E6F0]">
                    <button type="button" @click="addRoleModal = false" class="clay-btn-secondary px-4 py-2 font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="clay-btn-primary px-5 py-2 font-bold text-xs shadow-md">
                        + Simpan Role Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================================================== -->
<!-- MODAL: EDIT ROLE & HAK AKSES                         -->
<!-- ==================================================== -->
<div x-show="editRoleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-xs" @click="editRoleModal = false"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-3xl shadow-2xl border border-[#E8E6F0] relative z-10"
             @click.away="editRoleModal = false">
            
            <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3.5 mb-4">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-[#6C63FF]/20 text-[#6C63FF] flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="font-extrabold text-[#29283D] text-sm">
                            Pengaturan Hak Akses: <span x-text="editRoleForm.label" class="text-[#6C63FF]"></span>
                        </h3>
                        <p class="text-[11px] text-[#77768C]">Ubah nama peran, deskripsi, dan perbarui centang izin operasional.</p>
                    </div>
                </div>
                <button type="button" @click="editRoleModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="editRoleForm.actionUrl" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-bold text-[#29283D] mb-1.5">Kode Role</label>
                        <input type="text" x-model="editRoleForm.name" readonly
                               class="clay-input w-full font-bold uppercase text-xs bg-slate-100 cursor-not-allowed">
                        <p class="text-[10px] text-[#77768C] mt-1">Kode peran sistem permanen.</p>
                    </div>

                    <div>
                        <label class="block font-bold text-[#29283D] mb-1.5">Nama Jabatan (Label) <span class="text-rose-500">*</span></label>
                        <input type="text" name="label" x-model="editRoleForm.label" required
                               class="clay-input w-full font-bold text-xs">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-[#29283D] mb-1.5">Deskripsi Peran</label>
                    <input type="text" name="description" x-model="editRoleForm.description"
                           class="clay-input w-full text-xs">
                </div>

                <!-- Admin Notice If Editing Admin -->
                <template x-if="editRoleForm.name === 'ADMIN'">
                    <div class="p-3 bg-purple-50 rounded-2xl border border-purple-200 text-[#6C63FF] text-xs">
                        <strong>Perhatian:</strong> Role Administrator (ADMIN) secara otomatis mewarisi akses master penuh ke seluruh modul sistem. Centang di bawah bersifat opsional untuk kustomisasi khusus.
                    </div>
                </template>

                <!-- Checkbox Permissions Terkelompok -->
                <div class="space-y-3 pt-2">
                    <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-1.5">
                        <label class="font-black text-[#29283D] text-xs">Daftar Hak Akses Sistem (Permissions)</label>
                        <span class="text-[10px] text-[#77768C]">Centang izin yang aktif untuk jabatan ini.</span>
                    </div>

                    <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
                        @foreach($availablePermissions as $categoryName => $perms)
                        @php
                            $catKeys = array_keys($perms);
                        @endphp
                        <div class="p-3 bg-[#F4F3FA] rounded-2xl border border-[#E8E6F0] space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-[#29283D] text-xs">{{ $categoryName }}</span>
                                <div class="flex items-center gap-2 text-[10px]">
                                    <button type="button" @click="toggleCategoryEdit({{ Js::from($catKeys) }}, true)" class="text-[#6C63FF] hover:underline font-bold">Pilih Semua</button>
                                    <span class="text-slate-300">•</span>
                                    <button type="button" @click="toggleCategoryEdit({{ Js::from($catKeys) }}, false)" class="text-slate-500 hover:underline">Batal</button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                                @foreach($perms as $permKey => $permDesc)
                                <label class="flex items-start gap-2 p-1.5 rounded-xl bg-white border border-[#E8E6F0] hover:border-[#6C63FF]/30 cursor-pointer select-none transition-all">
                                    <input type="checkbox" name="permissions[]" value="{{ $permKey }}"
                                           x-model="editRoleForm.permissions"
                                           class="mt-0.5 rounded text-[#6C63FF] focus:ring-[#6C63FF] h-4 w-4">
                                    <div class="text-[11px] leading-tight">
                                        <span class="font-bold text-[#29283D] block">{{ $permDesc }}</span>
                                        <code class="text-[9px] text-[#77768C]">{{ $permKey }}</code>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-[#E8E6F0]">
                    <button type="button" @click="editRoleModal = false" class="clay-btn-secondary px-4 py-2 font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="clay-btn-primary px-5 py-2 font-bold text-xs shadow-md">
                        Simpan Hak Akses
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
