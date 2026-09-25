@extends('layouts.app')

@section('title', 'Persetujuan User (ACC)')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Persetujuan Registrasi User</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola verifikasi dan persetujuan (ACC) akun baru untuk peran Staff dan Admin.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 text-[#14433b] border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-[#21c9a4]"></span>
                Sistem Keamanan Aktif
            </span>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('admin.user-approvals.index', ['status' => 'PENDING']) }}"
           class="p-4 rounded-xl border transition-all {{ $statusFilter === 'PENDING' ? 'bg-amber-50/80 border-amber-300 ring-2 ring-amber-400/20' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-700">Menunggu ACC</span>
                <span class="p-2 rounded-lg bg-amber-100 text-amber-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-bold text-amber-900">{{ number_format($counts['pending']) }}</div>
            <div class="text-[11px] text-amber-600 mt-0.5">Memerlukan tindakan admin</div>
        </a>

        <a href="{{ route('admin.user-approvals.index', ['status' => 'APPROVED']) }}"
           class="p-4 rounded-xl border transition-all {{ $statusFilter === 'APPROVED' ? 'bg-emerald-50/80 border-emerald-300 ring-2 ring-emerald-400/20' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Disetujui (ACC)</span>
                <span class="p-2 rounded-lg bg-emerald-100 text-[#14433b]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($counts['approved']) }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Akun aktif & beroperasi</div>
        </a>

        <a href="{{ route('admin.user-approvals.index', ['status' => 'REJECTED']) }}"
           class="p-4 rounded-xl border transition-all {{ $statusFilter === 'REJECTED' ? 'bg-rose-50/80 border-rose-300 ring-2 ring-rose-400/20' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-rose-700">Ditolak</span>
                <span class="p-2 rounded-lg bg-rose-100 text-rose-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($counts['rejected']) }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Pendaftaran ditolak</div>
        </a>

        <a href="{{ route('admin.user-approvals.index', ['status' => 'ALL']) }}"
           class="p-4 rounded-xl border transition-all {{ $statusFilter === 'ALL' ? 'bg-slate-100 border-slate-400 ring-2 ring-slate-400/20' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-600">Total User</span>
                <span class="p-2 rounded-lg bg-slate-100 text-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($counts['total']) }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Semua pengguna terdaftar</div>
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-4">
        <form method="GET" action="{{ route('admin.user-approvals.index') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="hidden" name="status" value="{{ $statusFilter }}">

            <!-- Search Field -->
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari nama atau email pendaftar..."
                       class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:outline-hidden focus:ring-2 focus:ring-[#21c9a4] focus:border-transparent">
            </div>

            <!-- Role Filter -->
            <div class="sm:w-44">
                <select name="role" onchange="this.form.submit()"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:outline-hidden focus:ring-2 focus:ring-[#21c9a4]">
                    <option value="">Semua Peran</option>
                    <option value="STAFF" {{ $roleFilter === 'STAFF' ? 'selected' : '' }}>Staff Operasional</option>
                    <option value="GUIDE" {{ $roleFilter === 'GUIDE' ? 'selected' : '' }}>Tour Guide</option>
                    <option value="ADMIN" {{ $roleFilter === 'ADMIN' ? 'selected' : '' }}>Administrator</option>
                </select>
            </div>

            <button type="submit" 
                    class="px-4 py-2 bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-semibold rounded-lg transition-colors shadow-xs">
                Filter Data
            </button>

            @if($search || $roleFilter || $statusFilter !== 'PENDING')
                <a href="{{ route('admin.user-approvals.index') }}" 
                   class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium rounded-lg transition-colors flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">Pengguna</th>
                        <th class="py-3.5 px-4">Peran (Role)</th>
                        <th class="py-3.5 px-4">Verifikasi Email</th>
                        <th class="py-3.5 px-4">Status Persetujuan</th>
                        <th class="py-3.5 px-4">Waktu Daftar</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70">
                    @forelse($users as $u)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <!-- User Name & Email -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-[#14433b] text-[#21c9a4] font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($u->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-slate-900 text-sm truncate">{{ $u->name }}</div>
                                    <div class="text-slate-500 text-[11px] truncate">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Role -->
                        <td class="py-3.5 px-4">
                            @if($u->role === 'GUIDE')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-[#14433b] border border-emerald-300">
                                    Tour Guide
                                </span>
                            @elseif($u->role === 'STAFF')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-sky-100 text-sky-800 border border-sky-200">
                                    Staff Operasional
                                </span>
                            @elseif($u->role === 'ADMIN')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-100 text-purple-800 border border-purple-200">
                                    Administrator
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700">
                                    {{ $u->role }}
                                </span>
                            @endif
                        </td>

                        <!-- Email Verification -->
                        <td class="py-3.5 px-4">
                            @if($u->hasVerifiedEmail())
                                <span class="inline-flex items-center gap-1 text-emerald-700 font-medium">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Terverifikasi
                                </span>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $u->email_verified_at->format('d/m/Y H:i') }}
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1 text-amber-700 font-medium">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Belum Verifikasi
                                </span>
                                <div class="text-[10px] text-slate-400 mt-0.5">Menunggu klik link email</div>
                            @endif
                        </td>

                        <!-- Approval Status -->
                        <td class="py-3.5 px-4">
                            @if($u->approval_status === 'APPROVED')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    Disetujui (ACC)
                                </span>
                                @if($u->approver)
                                    <div class="text-[10px] text-slate-400 mt-0.5">Oleh: {{ $u->approver->name }}</div>
                                @endif
                            @elseif($u->approval_status === 'REJECTED')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                    Ditolak
                                </span>
                                @if($u->rejection_reason)
                                    <div class="text-[10px] text-rose-600 mt-0.5 italic max-w-xs truncate" title="{{ $u->rejection_reason }}">
                                        "{{ $u->rejection_reason }}"
                                    </div>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                    Menunggu ACC
                                </span>
                            @endif
                        </td>

                        <!-- Registered At -->
                        <td class="py-3.5 px-4 text-slate-600">
                            <div>{{ $u->created_at->format('d M Y') }}</div>
                            <div class="text-[10px] text-slate-400">{{ $u->created_at->format('H:i') }} WITA</div>
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($u->approval_status === 'PENDING')
                                    <!-- Approve Button -->
                                    <form id="approve-form-{{ $u->id }}" action="{{ route('admin.user-approvals.approve', $u) }}" method="POST">
                                        @csrf
                                        <button type="button" 
                                                onclick="confirmApprove({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->role }}', {{ $u->hasVerifiedEmail() ? 'true' : 'false' }})"
                                                class="px-2.5 py-1.5 rounded-md bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-semibold transition-colors flex items-center gap-1 shadow-xs"
                                                title="Setujui dan Aktifkan Akun">
                                            <svg class="w-3.5 h-3.5 text-[#21c9a4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
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
                                                class="px-2.5 py-1.5 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold transition-colors flex items-center gap-1"
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
                                                class="px-2.5 py-1.5 rounded-md bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 border border-slate-200 text-xs font-medium transition-colors">
                                            Pulihkan & ACC
                                        </button>
                                    </form>
                                @else
                                    <span class="text-emerald-600 font-semibold text-[11px] inline-flex items-center gap-1">
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
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <div class="font-semibold text-slate-600">Tidak ada data pendaftaran ditemukan</div>
                            <p class="text-xs text-slate-400 mt-1">Saat ini belum ada user pada filter yang dipilih.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function confirmApprove(userId, userName, role, isVerified) {
    let warningText = `Apakah Anda yakin ingin menyetujui akun <b>${userName}</b> sebagai <b>${role}</b>?`;
    if (!isVerified) {
        warningText += `<br><br><span class="text-amber-600 text-xs">⚠️ Catatan: Pengguna ini belum memverifikasi emailnya. Namun setelah di-ACC, akun akan diaktifkan.</span>`;
    }

    Swal.fire({
        title: 'ACC Akun Pengguna',
        html: warningText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#14433b',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Setujui (ACC)',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(`approve-form-${userId}`).submit();
        }
    });
}

function confirmReject(userId, userName) {
    Swal.fire({
        title: 'Tolak Pendaftaran',
        html: `Berikan alasan penolakan untuk pendaftaran akun <b>${userName}</b>:`,
        input: 'textarea',
        inputPlaceholder: 'Tuliskan alasan penolakan (opsional)...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
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
