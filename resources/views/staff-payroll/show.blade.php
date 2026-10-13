@extends('layouts.app')

@section('title', 'Slip Gaji ' . $staffPayroll->payroll_code)
@section('header', 'Slip Gaji Staff')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto" x-data="{
    paymentModalOpen: false,
    paymentDate: '{{ now()->toDateString() }}',
    paymentMethod: 'TRANSFER',
    paymentReference: '',
    paymentNotes: '',
    recordExpense: true,
}">

    <!-- Top Action Bar (Hidden during Print) -->
    <div class="no-print clay-card p-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('staff-payroll.index') }}" class="clay-btn-secondary px-3.5 py-1.5 text-xs font-bold inline-flex items-center gap-1.5">
                    <span>&larr; Daftar Gaji</span>
                </a>
            @else
                <a href="{{ route('staff.my-salary') }}" class="clay-btn-secondary px-3.5 py-1.5 text-xs font-bold inline-flex items-center gap-1.5">
                    <span>&larr; Riwayat Gaji Saya</span>
                </a>
            @endif

            <span class="text-xs font-mono text-[#77768C] px-2.5 py-1 rounded-xl bg-[#F4F3FA] border border-[#E8E6F0]">#{{ $staffPayroll->payroll_code }}</span>
        </div>

        <div class="flex items-center gap-2">
            @if(!$staffPayroll->isPaid() && auth()->user()->isAdmin())
                <button type="button" @click="paymentModalOpen = true" class="clay-btn-coral px-4 py-2 text-xs font-bold inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Bayar Gaji Sekarang</span>
                </button>

                <a href="{{ route('staff-payroll.edit', $staffPayroll) }}" class="clay-btn-secondary px-3.5 py-2 text-xs font-bold inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#77768C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>Edit Slip</span>
                </a>
            @endif

            <button type="button" onclick="window.print()" class="clay-btn-primary px-4 py-2 text-xs font-bold inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#FFD166]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Slip Gaji (Print)</span>
            </button>
        </div>
    </div>

    <!-- Official Printable Slip Gaji Container -->
    <div class="printable-slip bg-white rounded-3xl border border-[#E8E6F0] shadow-md p-6 sm:p-10 space-y-6 text-[#29283D] relative overflow-hidden">
        
        <!-- Status Watermark / Stamp -->
        <div class="absolute top-6 right-6 sm:top-10 sm:right-10 pointer-events-none select-none">
            @if($staffPayroll->isPaid())
                <div class="border-2 border-emerald-500 text-emerald-600 bg-emerald-50/80 px-4 py-1.5 rounded-2xl font-black tracking-widest text-xs uppercase rotate-[-6deg] shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>LUNAS (PAID)</span>
                </div>
            @else
                <div class="border-2 border-[#FF8A65] text-[#FF8A65] bg-coral-50/80 px-4 py-1.5 rounded-2xl font-black tracking-widest text-xs uppercase rotate-[-6deg] shadow-sm">
                    <span>PENDING (BELUM BAYAR)</span>
                </div>
            @endif
        </div>

        <!-- Header Perusahaan -->
        <div class="border-b-2 border-[#29283D] pb-5">
            <div class="flex items-center gap-4">
                @php
                    $appLogo = \App\Models\Setting::get('app_logo');
                    $appName = \App\Models\Setting::get('app_name', 'ARTDEVATA');
                    $appTagline = \App\Models\Setting::get('app_tagline', 'Portal Manajemen Guide & Operasional Tour');
                @endphp

                @if(!empty($appLogo) && file_exists(public_path($appLogo)))
                    <img src="{{ asset($appLogo) }}" alt="{{ $appName }}" class="h-12 w-12 object-contain">
                @else
                    <div class="h-12 w-12 rounded-2xl bg-[#6C63FF] text-[#FFD166] font-black flex items-center justify-center text-lg shadow-sm">
                        {{ substr($appName, 0, 2) }}
                    </div>
                @endif

                <div>
                    <h1 class="text-xl font-black text-[#29283D] tracking-tight uppercase leading-none">{{ $appName }}</h1>
                    <p class="text-xs text-[#77768C] font-medium mt-1">{{ $appTagline }}</p>
                    <p class="text-[11px] text-[#77768C] font-mono">Bali, Indonesia &bull; Dokumen Resmi Pembayaran Gaji Karyawan</p>
                </div>
            </div>

            <div class="text-center mt-5 pt-3 border-t border-[#E8E6F0]">
                <h2 class="text-base font-extrabold tracking-wider text-[#29283D] uppercase">SLIP GAJI KARYAWAN</h2>
                <p class="text-xs font-bold text-[#6C63FF]">PERIODE: {{ strtoupper($staffPayroll->period_name) }}</p>
            </div>
        </div>

        <!-- Informasi Karyawan & Dokumen -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-[#F4F3FA] p-5 rounded-2xl border border-[#E8E6F0] text-xs">
            <div>
                <span class="text-[10px] text-[#77768C] block font-semibold uppercase">Nomor Dokumen</span>
                <span class="font-mono font-bold text-[#29283D] text-xs">{{ $staffPayroll->payroll_code }}</span>
            </div>

            <div>
                <span class="text-[10px] text-[#77768C] block font-semibold uppercase">Tanggal Terbit</span>
                <span class="font-bold text-[#29283D] text-xs">{{ $staffPayroll->created_at->format('d F Y') }}</span>
            </div>

            <div>
                <span class="text-[10px] text-[#77768C] block font-semibold uppercase">Nama Karyawan</span>
                <span class="font-bold text-[#29283D] text-sm block">{{ $staffPayroll->user->name }}</span>
            </div>

            <div>
                <span class="text-[10px] text-[#77768C] block font-semibold uppercase">Jabatan / Posisi</span>
                <span class="font-bold text-[#6C63FF] text-xs block">{{ $staffPayroll->position ?: ($staffPayroll->user->position ?: 'Staff Operasional') }}</span>
            </div>

            <div>
                <span class="text-[10px] text-[#77768C] block font-semibold uppercase">Email / Kontak</span>
                <span class="text-[#29283D] font-medium block truncate">{{ $staffPayroll->user->email }}</span>
                <span class="text-[11px] text-[#77768C] font-mono">{{ $staffPayroll->user->phone ?: '-' }}</span>
            </div>

            <div>
                <span class="text-[10px] text-[#77768C] block font-semibold uppercase">Metode Pembayaran</span>
                <span class="font-bold text-[#29283D] block">{{ $staffPayroll->payment_method ?: 'Transfer Bank' }}</span>
                @if($staffPayroll->payment_reference)
                    <span class="text-[10px] text-[#77768C] font-mono">Ref: {{ $staffPayroll->payment_reference }}</span>
                @endif
            </div>

            <div class="sm:col-span-2">
                <span class="text-[10px] text-[#77768C] block font-semibold uppercase">Rekening Penerima</span>
                @if($staffPayroll->user->bank_name && $staffPayroll->user->bank_account_number)
                    <span class="font-bold text-[#29283D]">
                        {{ $staffPayroll->user->bank_name }} - {{ $staffPayroll->user->bank_account_number }}
                    </span>
                    <span class="text-[11px] text-[#77768C] block">
                        a.n {{ $staffPayroll->user->bank_account_holder ?: $staffPayroll->user->name }}
                    </span>
                @else
                    <span class="text-[#77768C] italic">Informasi rekening tidak dicantumkan</span>
                @endif
            </div>
        </div>

        <!-- Tabel Rincian Komponen Gaji -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <!-- Kolom Penerimaan -->
            <div class="border border-[#E8E6F0] rounded-2xl overflow-hidden bg-white">
                <div class="bg-[#F4F3FA] px-4 py-2.5 font-bold text-[#29283D] border-b border-[#E8E6F0] flex justify-between">
                    <span>A. PENERIMAAN</span>
                    <span>JUMLAH</span>
                </div>
                <div class="p-4 space-y-3">
                    <div class="flex justify-between items-center text-[#77768C]">
                        <span>Gaji Pokok</span>
                        <span class="font-bold text-[#29283D]">Rp {{ number_format($staffPayroll->base_salary, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between items-center text-[#77768C]">
                        <span>Tunjangan Makan & Transport</span>
                        <span class="font-bold text-[#29283D]">Rp {{ number_format($staffPayroll->allowance, 0, ',', '.') }}</span>
                    </div>

                    @if($staffPayroll->bonus > 0)
                    <div class="flex justify-between items-center text-[#6C63FF]">
                        <span>Bonus / Lembur / Insentif</span>
                        <span class="font-bold text-[#6C63FF]">+ Rp {{ number_format($staffPayroll->bonus, 0, ',', '.') }}</span>
                    </div>
                    @endif

                    @php
                        $subtotalPenerimaan = $staffPayroll->base_salary + $staffPayroll->allowance + $staffPayroll->bonus;
                    @endphp

                    <div class="pt-2.5 border-t border-[#E8E6F0] flex justify-between items-center font-bold text-[#29283D] text-xs">
                        <span>Total Penerimaan (A)</span>
                        <span class="text-[#29283D]">Rp {{ number_format($subtotalPenerimaan, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Kolom Potongan -->
            <div class="border border-[#E8E6F0] rounded-2xl overflow-hidden flex flex-col justify-between bg-white">
                <div>
                    <div class="bg-[#F4F3FA] px-4 py-2.5 font-bold text-[#29283D] border-b border-[#E8E6F0] flex justify-between">
                        <span>B. POTONGAN</span>
                        <span>JUMLAH</span>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between items-center text-[#77768C]">
                            <span>Potongan (BPJS / Kasbon / Absensi)</span>
                            <span class="font-bold text-rose-600">{{ $staffPayroll->deduction > 0 ? '- Rp ' . number_format($staffPayroll->deduction, 0, ',', '.') : 'Rp 0' }}</span>
                        </div>

                        @if($staffPayroll->notes)
                        <div class="pt-2 text-[11px] text-[#77768C] bg-[#F4F3FA] p-3 rounded-xl border border-[#E8E6F0]">
                            <span class="font-semibold text-[#29283D] block">Keterangan Tambahan:</span>
                            <p class="whitespace-pre-line">{{ $staffPayroll->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="p-4 pt-2.5 border-t border-[#E8E6F0] flex justify-between items-center font-bold text-rose-700 text-xs bg-rose-50/30">
                    <span>Total Potongan (B)</span>
                    <span>Rp {{ number_format($staffPayroll->deduction, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Grand Total Take Home Pay -->
        <div class="bg-gradient-to-r from-[#6C63FF] to-[#837BFF] text-white rounded-2xl p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-md">
            <div>
                <span class="text-xs font-black text-[#FFD166] uppercase tracking-wider block">GAJI BERSIH DITERIMA (TAKE HOME PAY)</span>
                <span class="text-sm font-semibold text-white/90 italic block mt-0.5">
                    "{{ $staffPayroll->terbilang }}"
                </span>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    Rp {{ number_format($staffPayroll->net_salary, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-white/80 block font-mono">
                    {{ $staffPayroll->isPaid() ? 'Status: LUNAS (' . $staffPayroll->payment_date?->format('d/m/Y') . ')' : 'Status: Menunggu Pembayaran' }}
                </span>
            </div>
        </div>

        <!-- Tanda Tangan (Signatures) -->
        <div class="pt-6 grid grid-cols-2 gap-8 text-xs text-center border-t border-[#E8E6F0]">
            <div>
                <span class="text-[11px] text-[#77768C] block mb-1">Diterima Oleh Karyawan,</span>
                <div class="h-20 flex items-end justify-center pb-1">
                    <span class="font-bold border-b-2 border-[#29283D] pb-0.5 min-w-[160px] inline-block text-[#29283D]">
                        {{ $staffPayroll->user->name }}
                    </span>
                </div>
                <span class="text-[10px] text-[#77768C]">Staff / Penerima</span>
            </div>

            <div>
                <span class="text-[11px] text-[#77768C] block mb-1">Disetujui / Diserahkan Oleh,</span>
                <div class="h-20 flex items-end justify-center pb-1">
                    <span class="font-bold border-b-2 border-[#29283D] pb-0.5 min-w-[160px] inline-block text-[#29283D]">
                        {{ $staffPayroll->paidBy?->name ?: (auth()->user()->name) }}
                    </span>
                </div>
                <span class="text-[10px] text-[#77768C]">Bagian Keuangan / Manajemen</span>
            </div>
        </div>

        <!-- Footer slip -->
        <div class="text-[10px] text-[#77768C] text-center pt-4 border-t border-[#E8E6F0] font-mono">
            Slip gaji ini dibuat secara otomatis oleh sistem {{ $appName }} pada {{ now()->format('d/m/Y H:i:s') }}. Dokumen sah tanpa cap basah.
        </div>
    </div>

    <!-- Modal Pelunasan Cepat -->
    <div x-show="paymentModalOpen" style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition>
        
        <div class="clay-card max-w-md w-full overflow-hidden p-6 space-y-4"
             @click.away="paymentModalOpen = false">
            <div class="border-b border-[#E8E6F0] pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-[#29283D]">Catat Pembayaran Gaji</h3>
                    <p class="text-xs text-[#77768C]">{{ $staffPayroll->payroll_code }} &bull; {{ $staffPayroll->user->name }}</p>
                </div>
                <button type="button" @click="paymentModalOpen = false" class="text-[#77768C] hover:text-[#29283D] p-1 rounded-xl">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('staff-payroll.pay', $staffPayroll) }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="p-4 rounded-2xl bg-purple-50 border border-purple-200 text-[#29283D]">
                    <span class="text-[11px] font-bold text-[#6C63FF] block uppercase">Total Gaji Bersih</span>
                    <div class="text-2xl font-black text-[#6C63FF] mt-0.5">
                        Rp {{ number_format($staffPayroll->net_salary, 0, ',', '.') }}
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-[#29283D] mb-1.5">Tanggal Pembayaran <span class="text-rose-500">*</span></label>
                    <input type="date" name="payment_date" x-model="paymentDate" required
                           class="clay-input w-full text-xs font-medium">
                </div>

                <div>
                    <label class="block font-bold text-[#29283D] mb-1.5">Metode Pembayaran <span class="text-rose-500">*</span></label>
                    <select name="payment_method" x-model="paymentMethod" required
                            class="clay-input w-full text-xs font-medium">
                        <option value="TRANSFER">Transfer Bank</option>
                        <option value="CASH">Tunai (Cash)</option>
                        <option value="LAINNYA">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#29283D] mb-1.5">Nomor Referensi / Catatan Transaksi</label>
                    <input type="text" name="payment_reference" x-model="paymentReference" placeholder="Contoh: REF-BCA-10291"
                           class="clay-input w-full text-xs font-medium">
                </div>

                <div>
                    <label class="block font-bold text-[#29283D] mb-1.5">Catatan</label>
                    <textarea name="notes" x-model="paymentNotes" rows="2" placeholder="Catatan pembayaran..."
                              class="clay-input w-full text-xs font-medium"></textarea>
                </div>

                <div class="p-3.5 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0]">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="record_expense" value="1" x-model="recordExpense"
                               class="rounded border-[#E8E6F0] text-[#6C63FF] focus:ring-[#6C63FF] mt-0.5">
                        <div>
                            <span class="text-xs font-bold text-[#29283D] block">Catat Otomatis ke Biaya Operasional</span>
                            <span class="text-[11px] text-[#77768C]">Mencatat pengeluaran di menu Keuangan & Biaya Operasional (Kategori: Gaji & Upah).</span>
                        </div>
                    </label>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-[#E8E6F0]">
                    <button type="button" @click="paymentModalOpen = false" class="clay-btn-secondary px-4 py-2 text-xs font-bold">
                        Batal
                    </button>
                    <button type="submit" class="clay-btn-coral px-4 py-2 text-xs font-bold">
                        Konfirmasi Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@media print {
    /* Hide layout elements during print */
    .no-print, nav, aside, header, #main-sidebar, .sidebar-container, .breadcrumbs {
        display: none !important;
    }

    body, main {
        background: white !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .printable-slip {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
}
</style>
@endsection
