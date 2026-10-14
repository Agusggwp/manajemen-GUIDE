@extends('layouts.app')

@section('title', 'Persetujuan User (ACC)')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-[#29283D]">Persetujuan Registrasi User</h1>
            <p class="text-xs text-[#77768C] mt-1">Kelola verifikasi dan persetujuan (ACC) akun baru untuk peran Staff dan Admin.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="clay-badge bg-purple-50 text-[#6C63FF] border-purple-200">
                <span class="w-2 h-2 rounded-full bg-[#6C63FF] animate-pulse"></span>
                Sistem Keamanan Aktif
            </span>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('admin.user-approvals.index', ['status' => 'PENDING']) }}"
           class="clay-card p-5 transition-all {{ $statusFilter === 'PENDING' ? 'ring-2 ring-amber-400/50 border-amber-300' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-700">Menunggu ACC</span>
                <span class="p-2 rounded-xl bg-amber-100 text-amber-700 shadow-inner">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-amber-900">{{ number_format($counts['pending']) }}</div>
            <div class="text-[11px] text-amber-700 mt-0.5 font-medium">Memerlukan tindakan admin</div>
        </a>

        <a href="{{ route('admin.user-approvals.index', ['status' => 'APPROVED']) }}"
           class="clay-card p-5 transition-all {{ $statusFilter === 'APPROVED' ? 'ring-2 ring-[#6C63FF]/50 border-[#6C63FF]' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#6C63FF]">Disetujui (ACC)</span>
                <span class="p-2 rounded-xl bg-purple-100 text-[#6C63FF] shadow-inner">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-[#29283D]">{{ number_format($counts['approved']) }}</div>
            <div class="text-[11px] text-[#77768C] mt-0.5 font-medium">Akun aktif & beroperasi</div>
        </a>

        <a href="{{ route('admin.user-approvals.index', ['status' => 'REJECTED']) }}"
           class="clay-card p-5 transition-all {{ $statusFilter === 'REJECTED' ? 'ring-2 ring-rose-400/50 border-rose-300' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-rose-700">Ditolak</span>
                <span class="p-2 rounded-xl bg-rose-100 text-rose-700 shadow-inner">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-[#29283D]">{{ number_format($counts['rejected']) }}</div>
            <div class="text-[11px] text-[#77768C] mt-0.5 font-medium">Pendaftaran ditolak</div>
        </a>

        <a href="{{ route('admin.user-approvals.index', ['status' => 'ALL']) }}"
           class="clay-card p-5 transition-all {{ $statusFilter === 'ALL' ? 'ring-2 ring-[#77768C]/50 border-[#77768C]' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#77768C]">Total User</span>
                <span class="p-2 rounded-xl bg-[#F4F3FA] text-[#77768C] shadow-inner">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-[#29283D]">{{ number_format($counts['total']) }}</div>
            <div class="text-[11px] text-[#77768C] mt-0.5 font-medium">Semua pengguna terdaftar</div>
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="clay-card p-4">
        <form method="GET" action="{{ route('admin.user-approvals.index') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="hidden" name="status" value="{{ $statusFilter }}">

            <!-- Search Field -->
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#77768C]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari nama atau email pendaftar..."
                       class="clay-input w-full pl-10 pr-3.5 py-2 text-xs">
            </div>

            <!-- Role Filter -->
            <div class="sm:w-44">
                <select name="role" onchange="this.form.submit()"
                        class="clay-input w-full py-2 text-xs">
                    <option value="">Semua Peran</option>
                    <option value="STAFF" {{ $roleFilter === 'STAFF' ? 'selected' : '' }}>Staff Operasional</option>
                    <option value="GUIDE" {{ $roleFilter === 'GUIDE' ? 'selected' : '' }}>Tour Guide</option>
                    <option value="ADMIN" {{ $roleFilter === 'ADMIN' ? 'selected' : '' }}>Administrator</option>
                </select>
            </div>

            <button type="submit" 
                    class="clay-btn-primary px-4 py-2 text-xs font-bold">
                Filter Data
            </button>

            @if($search || $roleFilter || $statusFilter !== 'PENDING')
                <a href="{{ route('admin.user-approvals.index') }}" 
                   class="clay-btn-secondary px-3.5 py-2 text-xs font-bold flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="clay-card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F4F3FA] border-b border-[#E8E6F0] text-[#77768C] uppercase tracking-wider font-bold">
                    <tr>
                        <th class="py-3.5 px-5">Pengguna</th>
                        <th class="py-3.5 px-5">Peran (Role)</th>
                        <th class="py-3.5 px-5">Verifikasi Email</th>
                        <th class="py-3.5 px-5">Status Persetujuan</th>
                        <th class="py-3.5 px-5">Waktu Daftar</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($users as $u)
                    <tr class="hover:bg-purple-50/30 transition-colors">
                        <!-- User Name & Email -->
                        <td class="py-3.5 px-5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-2xl bg-purple-100 text-[#6C63FF] font-black text-xs flex items-center justify-center shrink-0 shadow-inner">
                                    {{ strtoupper(substr($u->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-[#29283D] text-sm truncate">{{ $u->name }}</div>
                                    <div class="text-[#77768C] text-[11px] truncate">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Role -->
                        <td class="py-3.5 px-5">
                            @if($u->role === 'GUIDE')
                                <span class="clay-badge bg-purple-50 text-[#6C63FF] border-purple-200">
                                    Tour Guide
                                </span>
                            @elseif($u->role === 'STAFF')
                                <span class="clay-badge bg-sky-50 text-sky-800 border-sky-200">
                                    Staff Operasional
                                </span>
                            @elseif($u->role === 'ADMIN')
                                <span class="clay-badge bg-amber-50 text-amber-800 border-amber-200">
                                    Administrator
                                </span>
                            @else
                                <span class="clay-badge bg-[#F4F3FA] text-[#77768C] border-[#E8E6F0]">
                                    {{ $u->role }}
                                </span>
                            @endif
                        </td>

                        <!-- Email Verification -->
                        <td class="py-3.5 px-5">
                            @if($u->hasVerifiedEmail())
                                <span class="inline-flex items-center gap-1 text-emerald-700 font-bold">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Terverifikasi
                                </span>
                                <div class="text-[10px] text-[#77768C] mt-0.5">
                                    {{ $u->email_verified_at->format('d/m/Y H:i') }}
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1 text-amber-700 font-bold">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Belum Verifikasi
                                </span>
                                <div class="text-[10px] text-[#77768C] mt-0.5">Menunggu klik link email</div>
                            @endif
                        </td>

                        <!-- Approval Status -->
                        <td class="py-3.5 px-5">
                            @if($u->approval_status === 'APPROVED')
                                <span class="clay-badge bg-emerald-50 text-emerald-800 border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    Disetujui (ACC)
                                </span>
                                @if($u->approver)
                                    <div class="text-[10px] text-[#77768C] mt-0.5">Oleh: {{ $u->approver->name }}</div>
                                @endif
                            @elseif($u->approval_status === 'REJECTED')
                                <span class="clay-badge bg-rose-50 text-rose-800 border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                    Ditolak
                                </span>
                                @if($u->rejection_reason)
                                    <div class="text-[10px] text-rose-600 mt-0.5 italic max-w-xs truncate" title="{{ $u->rejection_reason }}">
                                        "{{ $u->rejection_reason }}"
                                    </div>
                                @endif
                            @else
                                <span class="clay-badge bg-amber-50 text-amber-800 border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                    Menunggu ACC
                                </span>
                            @endif
                        </td>

                        <!-- Registered At -->
                        <td class="py-3.5 px-5 text-[#29283D]">
                            <div class="font-bold">{{ $u->created_at->format('d M Y') }}</div>
                            <div class="text-[10px] text-[#77768C]">{{ $u->created_at->format('H:i') }} WITA</div>
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($u->approval_status === 'PENDING')
                                    <!-- Approve Button -->
                                    <form id="approve-form-{{ $u->id }}" action="{{ route('admin.user-approvals.approve', $u) }}" method="POST">
                                        @csrf
                                        <button type="button" 
                                                onclick="confirmApprove({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->role }}', {{ $u->hasVerifiedEmail() ? 'true' : 'false' }})"
                                                class="clay-btn-primary px-3 py-1.5 text-xs font-bold inline-flex items-center gap-1"
                                                title="Setujui dan Aktifkan Akun">
                                            <svg class="w-3.5 h-3.5 text-[#FFD166]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                            ACC
                                        </button>
                                    </form>

                                    <!-- Reject Button -->
                                    <form id="reject-form-{{ $u->id }}" action="{{ route('admin.user-approvals.reject', $u) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="reason" id="reject-reason-{{ $u->id }}">
                                        <button type="button" 
                                                onclick="confirmReject({{ $u->id }}, '{{ addslashes($u->name) }}')"
                                                class="clay-btn-secondary text-rose-600 hover:text-rose-700 px-3 py-1.5 text-xs font-bold inline-flex items-center gap-1"
                                                title="Tolak Registrasi">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Tolak
                                        </button>
                                    </form>
                                @elseif($u->approval_status === 'REJECTED')
                                    <!-- Re-approve option -->
                                    <form id="approve-form-{{ $u->id }}" action="{{ route('admin.user-approvals.approve', $u) }}" method="POST">
                                        @csrf
                                        <button type="button" 
                                                onclick="confirmApprove({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->role }}', {{ $u->hasVerifiedEmail() ? 'true' : 'false' }})"
                                                class="clay-btn-secondary px-3 py-1.5 text-xs font-bold hover:text-[#6C63FF]">
                                            Pulihkan & ACC
                                        </button>
                                    </form>
                                @else
                                    <span class="text-emerald-600 font-bold text-[11px] inline-flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Aktif
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-[#77768C]">
                            <svg class="w-12 h-12 mx-auto mb-3 text-[#77768C]/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <div class="font-bold text-[#29283D]">Tidak ada data pendaftaran ditemukan</div>
                            <p class="text-xs text-[#77768C] mt-1">Saat ini belum ada user pada filter yang dipilih.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-4 border-t border-[#E8E6F0] bg-[#F4F3FA]">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function confirmApprove(userId, userName, role, isVerified) {
    let warningText = `Apakah Anda yakin ingin menyetujui akun <b>${userName}</b> sebagai <b>${role}</b>?`;
    if (!isVerified) {
        warningText += `<br><br><span class="text-amber-600 text-xs">Catatan: Pengguna ini belum memverifikasi emailnya. Namun setelah di-ACC, akun akan diaktifkan.</span>`;
    }

    (window.ArtAlert || window.Swal).fire({
        title: 'ACC Akun Pengguna',
        html: warningText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#6C63FF',
        cancelButtonColor: '#77768C',
        confirmButtonText: 'Ya, Setujui (ACC)',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(`approve-form-${userId}`).submit();
        }
    });
}

function confirmReject(userId, userName) {
    (window.ArtAlert || window.Swal).fire({
        title: 'Tolak Pendaftaran',
        html: `Berikan alasan penolakan untuk pendaftaran akun <b>${userName}</b>:`,
        input: 'textarea',
        inputPlaceholder: 'Tuliskan alasan penolakan (opsional)...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#77768C',
        confirmButtonText: 'Tolak Pendaftaran',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(`reject-reason-${userId}`).value = result.value || '';
            document.getElementById(`reject-form-${userId}`).submit();
        }
    });
}
</script>
@endsection
