@extends('layouts.app')

@section('title', 'Buat Slip Gaji Staff')
@section('header', 'Buat Slip Gaji Staff')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    staffList: {{ json_encode($staffMembers->map(function($s) {
        return [
            'id' => $s->id,
            'name' => $s->name,
            'email' => $s->email,
            'phone' => $s->phone,
            'position' => $s->position ?? 'Staff Operasional',
            'base_salary' => (float)($s->salary_base ?? 0),
            'allowance' => (float)($s->salary_allowance ?? 0),
            'bank_name' => $s->bank_name ?? '',
            'bank_account_number' => $s->bank_account_number ?? '',
            'bank_account_holder' => $s->bank_account_holder ?? $s->name,
        ];
    })) }},
    selectedUserId: '{{ old('user_id', $selectedUser?->id ?? '') }}',
    position: '{{ old('position', '') }}',
    baseSalary: {{ (float)old('base_salary', 0) }},
    allowance: {{ (float)old('allowance', 0) }},
    bonus: {{ (float)old('bonus', 0) }},
    deduction: {{ (float)old('deduction', 0) }},
    paymentStatus: '{{ old('payment_status', 'PENDING') }}',
    paymentDate: '{{ old('payment_date', now()->toDateString()) }}',
    paymentMethod: '{{ old('payment_method', 'TRANSFER') }}',
    paymentReference: '{{ old('payment_reference', '') }}',
    notes: '{{ old('notes', '') }}',
    recordExpense: true,

    get currentStaff() {
        return this.staffList.find(s => s.id == this.selectedUserId) || null;
    },

    onStaffChange() {
        const staff = this.currentStaff;
        if (staff) {
            this.position = staff.position || 'Staff Operasional';
            this.baseSalary = staff.base_salary || 0;
            this.allowance = staff.allowance || 0;
        }
    },

    get grossEarnings() {
        return Math.max(0, (Number(this.baseSalary) || 0) + (Number(this.allowance) || 0) + (Number(this.bonus) || 0));
    },

    get netSalary() {
        return Math.max(0, this.grossEarnings - (Number(this.deduction) || 0));
    },

    formatRupiah(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
}" x-init="if (selectedUserId) onStaffChange()">

    <!-- Header breadcrumb -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-[#77768C]">
            <a href="{{ route('staff-payroll.index') }}" class="hover:text-[#29283D] font-bold transition">Penggajian Staff</a>
            <span>/</span>
            <span class="text-[#29283D] font-extrabold">Buat Slip Gaji Baru</span>
        </div>

        <a href="{{ route('staff-payroll.index') }}" class="clay-btn clay-btn-secondary text-xs px-3.5 py-1.5 font-bold inline-flex items-center gap-1 transition">
            <span>&larr;</span> Kembali ke Daftar Gaji
        </a>
    </div>

    <form action="{{ route('staff-payroll.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Card: Staff Selection & Period -->
        <div class="clay-card p-6 md:p-7 space-y-5">
            <h3 class="text-base font-extrabold text-[#29283D] border-b border-[#E8E6F0] pb-3 flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-[#6C63FF]/10 text-[#6C63FF] flex items-center justify-center"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></span>
                <span>Informasi Staff & Periode Gaji</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 text-xs">
                <!-- Select Staff -->
                <div class="md:col-span-1">
                    <label class="block font-bold text-[#29283D] mb-1.5">Pilih Staff Karyawan <span class="text-[#FF8A65]">*</span></label>
                    <select name="user_id" x-model="selectedUserId" @change="onStaffChange()" required
                            class="w-full clay-input font-bold">
                        <option value="">-- Pilih Staff --</option>
                        <template x-for="stf in staffList" :key="stf.id">
                            <option :value="stf.id" x-text="stf.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Periode Bulan -->
                <div>
                    <label class="block font-bold text-[#29283D] mb-1.5">Periode Bulan <span class="text-[#FF8A65]">*</span></label>
                    <select name="period_month" required
                            class="w-full clay-input font-bold">
                        @php
                            $months = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ];
                        @endphp
                        @foreach($months as $mNum => $mLabel)
                            <option value="{{ $mNum }}" {{ old('period_month', $currentMonth) == $mNum ? 'selected' : '' }}>{{ $mLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Periode Tahun -->
                <div>
                    <label class="block font-bold text-[#29283D] mb-1.5">Periode Tahun <span class="text-[#FF8A65]">*</span></label>
                    <select name="period_year" required
                            class="w-full clay-input font-bold">
                        @for($y = (int)date('Y') - 1; $y <= (int)date('Y') + 2; $y++)
                            <option value="{{ $y }}" {{ old('period_year', $currentYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Jabatan / Posisi -->
                <div class="md:col-span-1">
                    <label class="block font-bold text-[#29283D] mb-1.5">Jabatan / Posisi di Slip</label>
                    <input type="text" name="position" x-model="position" placeholder="Contoh: Staff Operasional"
                           class="w-full clay-input">
                </div>

                <!-- Info Rekening Preview -->
                <div class="md:col-span-2 p-3.5 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0]" x-show="currentStaff">
                    <span class="text-[10px] uppercase font-extrabold text-[#77768C] block mb-1">Informasi Rekening Bank Karyawan:</span>
                    <template x-if="currentStaff && currentStaff.bank_name">
                        <div class="text-xs text-[#29283D] font-bold flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-lg bg-white border border-[#E8E6F0] text-[#6C63FF] font-mono text-[11px]" x-text="currentStaff.bank_name"></span>
                            <span class="font-mono text-[#29283D] font-extrabold" x-text="currentStaff.bank_account_number"></span>
                            <span class="text-[#77768C] text-[11px]" x-text="'(a.n ' + currentStaff.bank_account_holder + ')'"></span>
                        </div>
                    </template>
                    <template x-if="currentStaff && !currentStaff.bank_name">
                        <span class="text-xs text-[#77768C] italic">Data rekening bank belum dilengkapi di pengaturan staff.</span>
                    </template>
                </div>
            </div>
        </div>

        <!-- Card: Komponen Gaji & Penghitungan Realtime -->
        <div class="clay-card p-6 md:p-7 space-y-5">
            <h3 class="text-base font-extrabold text-[#29283D] border-b border-[#E8E6F0] pb-3 flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-[#6C63FF]/10 text-[#6C63FF] flex items-center justify-center"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                <span>Rincian Komponen Gaji</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kolom Kiri: Penerimaan -->
                <div class="space-y-4 bg-[#72D6C9]/10 p-5 rounded-2xl border border-[#72D6C9]/25">
                    <div class="flex items-center gap-2 text-xs font-extrabold text-[#0f766e] pb-2 border-b border-[#72D6C9]/30">
                        <span class="w-5 h-5 rounded-lg bg-[#72D6C9]/30 text-[#0f766e] flex items-center justify-center text-xs font-bold">+</span>
                        <span>A. PENERIMAAN / PENGHASILAN</span>
                    </div>

                    <!-- Gaji Pokok -->
                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Gaji Pokok (Rp) <span class="text-[#FF8A65]">*</span></label>
                        <input type="number" step="1000" min="0" name="base_salary" x-model.number="baseSalary" required
                               class="w-full clay-input font-extrabold text-[#6C63FF]">
                    </div>

                    <!-- Tunjangan -->
                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Tunjangan Makan & Transport (Rp)</label>
                        <input type="number" step="1000" min="0" name="allowance" x-model.number="allowance"
                               class="w-full clay-input font-bold">
                    </div>

                    <!-- Bonus / Lembur -->
                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Bonus / Lembur / Insentif (Rp)</label>
                        <input type="number" step="1000" min="0" name="bonus" x-model.number="bonus"
                               class="w-full clay-input font-bold">
                    </div>

                    <div class="pt-3 border-t border-[#72D6C9]/30 flex justify-between items-center text-xs font-extrabold text-[#0f766e]">
                        <span>Subtotal Penerimaan:</span>
                        <span class="text-sm font-extrabold" x-text="'Rp ' + formatRupiah(grossEarnings)"></span>
                    </div>
                </div>

                <!-- Kolom Kanan: Potongan -->
                <div class="space-y-4 bg-[#FF8A65]/10 p-5 rounded-2xl border border-[#FF8A65]/25 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 text-xs font-extrabold text-[#FF8A65] pb-2 border-b border-[#FF8A65]/30">
                            <span class="w-5 h-5 rounded-lg bg-[#FF8A65]/20 text-[#FF8A65] flex items-center justify-center text-xs font-bold">-</span>
                            <span>B. POTONGAN</span>
                        </div>

                        <!-- Potongan -->
                        <div>
                            <label class="block text-xs font-bold text-[#29283D] mb-1.5">Total Potongan (Rp)</label>
                            <input type="number" step="1000" min="0" name="deduction" x-model.number="deduction"
                                   placeholder="0"
                                   class="w-full clay-input font-extrabold text-[#FF8A65]">
                            <span class="text-[10px] text-[#77768C] mt-1 block">Kasbon, keterlambatan, BPJS, atau potongan lain.</span>
                        </div>

                        <!-- Catatan Rincian -->
                        <div>
                            <label class="block text-xs font-bold text-[#29283D] mb-1.5">Catatan / Rincian Slip</label>
                            <textarea name="notes" x-model="notes" rows="3" placeholder="Contoh: Lembur 4 jam, potongan kasbon Rp 100.000..."
                                      class="w-full clay-input resize-y"></textarea>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-[#FF8A65]/30 flex justify-between items-center text-xs font-extrabold text-[#FF8A65]">
                        <span>Subtotal Potongan:</span>
                        <span class="text-sm font-extrabold" x-text="'Rp ' + formatRupiah(deduction || 0)"></span>
                    </div>
                </div>
            </div>

            <!-- Grand Total Take Home Pay Banner -->
            <div class="p-5 rounded-2xl bg-[#6C63FF] text-white flex flex-col sm:flex-row items-center justify-between gap-3 shadow-sm">
                <div>
                    <span class="text-xs uppercase tracking-wider text-[#FFD166] font-extrabold block">TOTAL GAJI BERSIH (TAKE HOME PAY)</span>
                    <span class="text-[11px] text-white/80">Penerimaan Kotor dikurangi Total Potongan</span>
                </div>
                <div class="text-right">
                    <span class="text-2xl sm:text-3xl font-black text-white" x-text="'Rp ' + formatRupiah(netSalary)"></span>
                </div>
            </div>
        </div>

        <!-- Card: Status Pembayaran -->
        <div class="clay-card p-6 md:p-7 space-y-5">
            <h3 class="text-base font-extrabold text-[#29283D] border-b border-[#E8E6F0] pb-3 flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-[#6C63FF]/10 text-[#6C63FF] flex items-center justify-center"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></span>
                <span>Status Pembayaran Slip Gaji</span>
            </h3>

            <div class="space-y-4 text-xs">
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-[#29283D]">
                        <input type="radio" name="payment_status" value="PENDING" x-model="paymentStatus"
                               class="text-[#6C63FF] focus:ring-[#6C63FF]">
                        <span>Simpan sebagai Draf (Belum Bayar)</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer font-bold text-[#29283D]">
                        <input type="radio" name="payment_status" value="PAID" x-model="paymentStatus"
                                class="text-[#6C63FF] focus:ring-[#6C63FF]">
                        <span>Langsung Bayar (Lunas)</span>
                    </label>
                </div>

                <!-- Form detail pembayaran jika PAID -->
                <div x-show="paymentStatus === 'PAID'" x-transition class="p-5 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0] space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-bold text-[#29283D] mb-1.5">Tanggal Pembayaran <span class="text-[#FF8A65]">*</span></label>
                            <input type="date" name="payment_date" x-model="paymentDate"
                                   class="w-full clay-input">
                        </div>

                        <div>
                            <label class="block font-bold text-[#29283D] mb-1.5">Metode Pembayaran <span class="text-[#FF8A65]">*</span></label>
                            <select name="payment_method" x-model="paymentMethod"
                                    class="w-full clay-input font-bold">
                                <option value="TRANSFER">Transfer Bank</option>
                                <option value="CASH">Tunai (Cash)</option>
                                <option value="LAINNYA">Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-[#29283D] mb-1.5">Nomor Referensi Transaksi</label>
                            <input type="text" name="payment_reference" x-model="paymentReference" placeholder="Contoh: REF-BCA-10291"
                                   class="w-full clay-input">
                        </div>
                    </div>

                    <div class="pt-2">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="record_expense" value="1" x-model="recordExpense"
                                   class="rounded-lg text-[#6C63FF] focus:ring-[#6C63FF] mt-0.5">
                            <div>
                                <span class="font-bold text-[#29283D] block">Catat ke Biaya Operasional (Expenses)</span>
                                <span class="text-[11px] text-[#77768C]">Mencatat pengeluaran di menu Keuangan & Biaya Operasional (Kategori: Gaji & Upah).</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('staff-payroll.index') }}" class="clay-btn clay-btn-secondary px-5 py-2.5 font-bold">
                Batal
            </a>
            <button type="submit" class="clay-btn clay-btn-primary px-6 py-2.5 font-bold inline-flex items-center gap-2">
                <span>Simpan Slip Gaji</span>
            </button>
        </div>
    </form>
</div>
@endsection
