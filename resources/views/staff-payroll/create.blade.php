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
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('staff-payroll.index') }}" class="hover:text-slate-800 transition">Penggajian Staff</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">Buat Slip Gaji Baru</span>
        </div>

        <a href="{{ route('staff-payroll.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 flex items-center gap-1 transition">
            <span>&larr; Kembali ke Daftar Gaji</span>
        </a>
    </div>

    <form action="{{ route('staff-payroll.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Card: Staff Selection & Period -->
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#14433b]"></span>
                Informasi Staff & Periode Gaji
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <!-- Select Staff -->
                <div class="md:col-span-1">
                    <label class="block font-bold text-slate-700 mb-1">Pilih Staff Karyawan <span class="text-rose-500">*</span></label>
                    <select name="user_id" x-model="selectedUserId" @change="onStaffChange()" required
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-[#14433b]/20 focus:border-[#14433b]">
                        <option value="">-- Pilih Staff --</option>
                        <template x-for="stf in staffList" :key="stf.id">
                            <option :value="stf.id" x-text="stf.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Periode Bulan -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Periode Bulan <span class="text-rose-500">*</span></label>
                    <select name="period_month" required
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-[#14433b]/20 focus:border-[#14433b]">
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
                    <label class="block font-bold text-slate-700 mb-1">Periode Tahun <span class="text-rose-500">*</span></label>
                    <select name="period_year" required
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-[#14433b]/20 focus:border-[#14433b]">
                        @for($y = (int)date('Y') - 1; $y <= (int)date('Y') + 2; $y++)
                            <option value="{{ $y }}" {{ old('period_year', $currentYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Jabatan / Posisi -->
                <div class="md:col-span-1">
                    <label class="block font-bold text-slate-700 mb-1">Jabatan / Posisi di Slip</label>
                    <input type="text" name="position" x-model="position" placeholder="Contoh: Staff Operasional"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20 focus:border-[#14433b]">
                </div>

                <!-- Info Rekening Preview -->
                <div class="md:col-span-2 p-3 rounded-lg bg-slate-50 border border-slate-200" x-show="currentStaff">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Informasi Rekening Bank Karyawan:</span>
                    <template x-if="currentStaff && currentStaff.bank_name">
                        <div class="text-xs text-slate-800 font-semibold flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-700 font-mono text-[11px]" x-text="currentStaff.bank_name"></span>
                            <span class="font-mono text-slate-900 font-bold" x-text="currentStaff.bank_account_number"></span>
                            <span class="text-slate-500 text-[11px]" x-text="'(a.n ' + currentStaff.bank_account_holder + ')'"></span>
                        </div>
                    </template>
                    <template x-if="currentStaff && !currentStaff.bank_name">
                        <span class="text-xs text-slate-400 italic">Data rekening bank belum dilengkapi di pengaturan staff.</span>
                    </template>
                </div>
            </div>
        </div>

        <!-- Card: Komponen Gaji & Penghitungan Realtime -->
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#14433b]"></span>
                Rincian Komponen Gaji
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kolom Kiri: Penerimaan -->
                <div class="space-y-3.5 bg-emerald-50/40 p-4 rounded-xl border border-emerald-100">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-900 pb-1 border-b border-emerald-200">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        <span>A. PENERIMAAN / PENGHASILAN</span>
                    </div>

                    <!-- Gaji Pokok -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Gaji Pokok (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="1000" min="0" name="base_salary" x-model.number="baseSalary" required
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-[#14433b]/20">
                    </div>

                    <!-- Tunjangan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tunjangan Makan & Transport (Rp)</label>
                        <input type="number" step="1000" min="0" name="allowance" x-model.number="allowance"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium text-slate-900 focus:ring-2 focus:ring-[#14433b]/20">
                    </div>

                    <!-- Bonus / Lembur -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Bonus / Lembur / Insentif (Rp)</label>
                        <input type="number" step="1000" min="0" name="bonus" x-model.number="bonus"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium text-slate-900 focus:ring-2 focus:ring-[#14433b]/20">
                    </div>

                    <div class="pt-2 border-t border-emerald-200 flex justify-between items-center text-xs font-bold text-emerald-900">
                        <span>Subtotal Penerimaan:</span>
                        <span x-text="'Rp ' + formatRupiah(grossEarnings)"></span>
                    </div>
                </div>

                <!-- Kolom Kanan: Potongan -->
                <div class="space-y-3.5 bg-rose-50/40 p-4 rounded-xl border border-rose-100 flex flex-col justify-between">
                    <div class="space-y-3.5">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-rose-900 pb-1 border-b border-rose-200">
                            <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                            <span>B. POTONGAN</span>
                        </div>

                        <!-- Potongan -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Total Potongan (Rp)</label>
                            <input type="number" step="1000" min="0" name="deduction" x-model.number="deduction"
                                   placeholder="0"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium text-slate-900 focus:ring-2 focus:ring-[#14433b]/20">
                            <span class="text-[10px] text-slate-400 mt-1 block">Kasbon, keterlambatan, BPJS, atau potongan lain.</span>
                        </div>

                        <!-- Catatan Rincian -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Catatan / Rincian Slip</label>
                            <textarea name="notes" x-model="notes" rows="3" placeholder="Contoh: Lembur 4 jam, potongan kasbon Rp 100.000..."
                                      class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20"></textarea>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-rose-200 flex justify-between items-center text-xs font-bold text-rose-900">
                        <span>Subtotal Potongan:</span>
                        <span x-text="'Rp ' + formatRupiah(deduction || 0)"></span>
                    </div>
                </div>
            </div>

            <!-- Grand Total Take Home Pay Banner -->
            <div class="p-4 rounded-xl bg-gradient-to-r from-[#14433b] to-[#1b584d] text-white flex flex-col sm:flex-row items-center justify-between gap-3 shadow-sm">
                <div>
                    <span class="text-xs uppercase tracking-wider text-[#21c9a4] font-bold block">TOTAL GAJI BERSIH (TAKE HOME PAY)</span>
                    <span class="text-[11px] text-white/80">Penerimaan Kotor dikurangi Total Potongan</span>
                </div>
                <div class="text-right">
                    <span class="text-2xl sm:text-3xl font-black text-white" x-text="'Rp ' + formatRupiah(netSalary)"></span>
                </div>
            </div>
        </div>

        <!-- Card: Status Pembayaran -->
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#14433b]"></span>
                Status Pembayaran Slip Gaji
            </h3>

            <div class="space-y-4 text-xs">
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
                        <input type="radio" name="payment_status" value="PENDING" x-model="paymentStatus"
                               class="text-[#14433b] focus:ring-[#14433b]">
                        <span>Simpan sebagai Draf (Belum Bayar)</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
                        <input type="radio" name="payment_status" value="PAID" x-model="paymentStatus"
                               class="text-[#14433b] focus:ring-[#14433b]">
                        <span>Langsung Bayar (Lunas)</span>
                    </label>
                </div>

                <!-- Form detail pembayaran jika PAID -->
                <div x-show="paymentStatus === 'PAID'" x-transition class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tanggal Pembayaran <span class="text-rose-500">*</span></label>
                            <input type="date" name="payment_date" x-model="paymentDate"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Metode Pembayaran <span class="text-rose-500">*</span></label>
                            <select name="payment_method" x-model="paymentMethod"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20">
                                <option value="TRANSFER">Transfer Bank</option>
                                <option value="CASH">Tunai (Cash)</option>
                                <option value="LAINNYA">Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nomor Referensi Transaksi</label>
                            <input type="text" name="payment_reference" x-model="paymentReference" placeholder="Contoh: REF-BCA-10291"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20">
                        </div>
                    </div>

                    <div class="pt-2">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="record_expense" value="1" x-model="recordExpense"
                                   class="rounded border-slate-300 text-[#14433b] focus:ring-[#14433b] mt-0.5">
                            <div>
                                <span class="font-bold text-slate-800 block">Catat ke Biaya Operasional (Expenses)</span>
                                <span class="text-[11px] text-slate-500">Mencatat pengeluaran di menu Keuangan & Biaya Operasional (Kategori: Gaji & Upah).</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('staff-payroll.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-bold transition shadow-xs flex items-center gap-2">
                <svg class="w-4 h-4 text-[#21c9a4]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Slip Gaji</span>
            </button>
        </div>
    </form>
</div>
@endsection
