@extends('layouts.app')

@section('title', 'Edit Slip Gaji ' . $staffPayroll->payroll_code)
@section('header', 'Edit Slip Gaji Staff')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    baseSalary: {{ (float)old('base_salary', $staffPayroll->base_salary) }},
    allowance: {{ (float)old('allowance', $staffPayroll->allowance) }},
    bonus: {{ (float)old('bonus', $staffPayroll->bonus) }},
    deduction: {{ (float)old('deduction', $staffPayroll->deduction) }},
    paymentStatus: '{{ old('payment_status', $staffPayroll->payment_status) }}',
    paymentDate: '{{ old('payment_date', $staffPayroll->payment_date?->toDateString() ?? now()->toDateString()) }}',
    paymentMethod: '{{ old('payment_method', $staffPayroll->payment_method ?? 'TRANSFER') }}',
    paymentReference: '{{ old('payment_reference', $staffPayroll->payment_reference ?? '') }}',

    get grossEarnings() {
        return Math.max(0, (Number(this.baseSalary) || 0) + (Number(this.allowance) || 0) + (Number(this.bonus) || 0));
    },

    get netSalary() {
        return Math.max(0, this.grossEarnings - (Number(this.deduction) || 0));
    },

    formatRupiah(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
}">

    <!-- Header breadcrumb -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs font-medium text-[#77768C]">
            <a href="{{ route('staff-payroll.index') }}" class="hover:text-[#6C63FF] transition">Penggajian Staff</a>
            <span>/</span>
            <a href="{{ route('staff-payroll.show', $staffPayroll) }}" class="hover:text-[#6C63FF] transition font-mono">{{ $staffPayroll->payroll_code }}</a>
            <span>/</span>
            <span class="text-[#29283D] font-bold">Edit Slip</span>
        </div>

        <a href="{{ route('staff-payroll.show', $staffPayroll) }}" class="clay-btn-secondary px-3.5 py-1.5 text-xs font-bold inline-flex items-center gap-1.5">
            <span>&larr; Kembali ke Slip Gaji</span>
        </a>
    </div>

    <form action="{{ route('staff-payroll.update', $staffPayroll) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Card: Staff Info (Read-only reference) -->
        <div class="clay-card p-6 space-y-4">
            <h3 class="text-sm font-bold text-[#29283D] border-b border-[#E8E6F0] pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#6C63FF] shadow-xs"></span>
                Informasi Staff & Periode
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div class="p-3.5 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0]">
                    <span class="text-[10px] text-[#77768C] block font-semibold">Nama Staff:</span>
                    <span class="text-sm font-bold text-[#29283D]">{{ $staffPayroll->user->name }}</span>
                    <span class="text-[11px] text-[#77768C] block">{{ $staffPayroll->user->email }}</span>
                </div>

                <div class="p-3.5 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0]">
                    <span class="text-[10px] text-[#77768C] block font-semibold">Periode Slip:</span>
                    <span class="text-sm font-bold text-[#6C63FF]">{{ $staffPayroll->period_name }}</span>
                    <span class="text-[11px] font-mono text-[#77768C] block">{{ $staffPayroll->payroll_code }}</span>
                </div>

                <div>
                    <label class="block font-bold text-[#29283D] mb-1.5">Jabatan / Posisi di Slip</label>
                    <input type="text" name="position" value="{{ old('position', $staffPayroll->position) }}" placeholder="Contoh: Staff Operasional"
                           class="clay-input w-full text-xs font-medium">
                </div>
            </div>
        </div>

        <!-- Card: Komponen Gaji -->
        <div class="clay-card p-6 space-y-5">
            <h3 class="text-sm font-bold text-[#29283D] border-b border-[#E8E6F0] pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#FF8A65] shadow-xs"></span>
                Komponen Gaji & Potongan
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kolom Kiri: Penerimaan -->
                <div class="space-y-4 bg-purple-50/60 p-5 rounded-2xl border border-purple-100/80">
                    <div class="flex items-center gap-2 text-xs font-bold text-[#6C63FF] pb-2 border-b border-purple-100">
                        <svg class="w-4 h-4 text-[#6C63FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        <span>A. PENERIMAAN</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Gaji Pokok (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="1000" min="0" name="base_salary" x-model.number="baseSalary" required
                               class="clay-input w-full text-xs font-bold text-[#29283D]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Tunjangan Makan & Transport (Rp)</label>
                        <input type="number" step="1000" min="0" name="allowance" x-model.number="allowance"
                               class="clay-input w-full text-xs font-medium text-[#29283D]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Bonus / Lembur / Insentif (Rp)</label>
                        <input type="number" step="1000" min="0" name="bonus" x-model.number="bonus"
                               class="clay-input w-full text-xs font-medium text-[#29283D]">
                    </div>

                    <div class="pt-3 border-t border-purple-200/60 flex justify-between items-center text-xs font-bold text-[#6C63FF]">
                        <span>Subtotal Penerimaan:</span>
                        <span x-text="'Rp ' + formatRupiah(grossEarnings)"></span>
                    </div>
                </div>

                <!-- Kolom Kanan: Potongan -->
                <div class="space-y-4 bg-rose-50/60 p-5 rounded-2xl border border-rose-100/80 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 text-xs font-bold text-rose-800 pb-2 border-b border-rose-200/60">
                            <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/>
                            </svg>
                            <span>B. POTONGAN</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#29283D] mb-1.5">Total Potongan (Rp)</label>
                            <input type="number" step="1000" min="0" name="deduction" x-model.number="deduction"
                                   class="clay-input w-full text-xs font-medium text-[#29283D]">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#29283D] mb-1.5">Catatan / Rincian Tambahan</label>
                            <textarea name="notes" rows="3" placeholder="Catatan slip gaji..."
                                      class="clay-input w-full text-xs font-medium">{{ old('notes', $staffPayroll->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-rose-200/60 flex justify-between items-center text-xs font-bold text-rose-800">
                        <span>Subtotal Potongan:</span>
                        <span x-text="'Rp ' + formatRupiah(deduction || 0)"></span>
                    </div>
                </div>
            </div>

            <!-- Grand Total Take Home Pay Banner -->
            <div class="p-5 rounded-2xl bg-gradient-to-r from-[#6C63FF] to-[#837BFF] text-white flex flex-col sm:flex-row items-center justify-between gap-3 shadow-md">
                <div>
                    <span class="text-xs uppercase tracking-wider text-[#FFD166] font-extrabold block">TOTAL GAJI BERSIH (TAKE HOME PAY)</span>
                    <span class="text-xs text-white/80">Penerimaan dikurangi Potongan</span>
                </div>
                <div class="text-right">
                    <span class="text-2xl sm:text-3xl font-black text-white" x-text="'Rp ' + formatRupiah(netSalary)"></span>
                </div>
            </div>
        </div>

        <!-- Card: Status Pembayaran -->
        <div class="clay-card p-6 space-y-4">
            <h3 class="text-sm font-bold text-[#29283D] border-b border-[#E8E6F0] pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#72D6C9] shadow-xs"></span>
                Status Pembayaran
            </h3>

            <div class="space-y-4 text-xs">
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-[#29283D]">
                        <input type="radio" name="payment_status" value="PENDING" x-model="paymentStatus"
                               class="text-[#6C63FF] focus:ring-[#6C63FF]">
                        <span>Belum Bayar (Pending)</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer font-bold text-[#29283D]">
                        <input type="radio" name="payment_status" value="PAID" x-model="paymentStatus"
                               class="text-[#6C63FF] focus:ring-[#6C63FF]">
                        <span>Sudah Lunas (Paid)</span>
                    </label>
                </div>

                <div x-show="paymentStatus === 'PAID'" x-transition class="p-4 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0] grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-[#29283D] mb-1.5">Tanggal Pembayaran <span class="text-rose-500">*</span></label>
                        <input type="date" name="payment_date" x-model="paymentDate"
                               class="clay-input w-full text-xs font-medium">
                    </div>

                    <div>
                        <label class="block font-bold text-[#29283D] mb-1.5">Metode Pembayaran <span class="text-rose-500">*</span></label>
                        <select name="payment_method" x-model="paymentMethod"
                                class="clay-input w-full text-xs font-medium">
                            <option value="TRANSFER">Transfer Bank</option>
                            <option value="CASH">Tunai (Cash)</option>
                            <option value="LAINNYA">Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-[#29283D] mb-1.5">Nomor Referensi Transaksi</label>
                        <input type="text" name="payment_reference" x-model="paymentReference" placeholder="Contoh: REF-BCA-10291"
                               class="clay-input w-full text-xs font-medium">
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('staff-payroll.show', $staffPayroll) }}" class="clay-btn-secondary px-5 py-2.5 text-xs font-bold">
                Batal
            </a>
            <button type="submit" class="clay-btn-primary px-6 py-2.5 text-xs font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-[#FFD166]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </form>
</div>
@endsection
