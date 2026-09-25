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
    <div class="no-print bg-white rounded-xl border border-slate-200/80 p-4 shadow-2xs flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('staff-payroll.index') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                    <span>&larr; Daftar Gaji</span>
                </a>
            @else
                <a href="{{ route('staff.my-salary') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                    <span>&larr; Riwayat Gaji Saya</span>
                </a>
            @endif

            <span class="text-xs font-mono text-slate-400">#{{ $staffPayroll->payroll_code }}</span>
        </div>

        <div class="flex items-center gap-2">
            @if(!$staffPayroll->isPaid() && auth()->user()->isAdmin())
                <button type="button" @click="paymentModalOpen = true" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Bayar Gaji Sekarang</span>
                </button>

                <a href="{{ route('staff-payroll.edit', $staffPayroll) }}" class="px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>Edit Slip</span>
                </a>
            @endif

            <button type="button" onclick="window.print()" class="px-4 py-1.5 rounded-lg bg-slate-900 hover:bg-black text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Slip Gaji (Print)</span>
            </button>
        </div>
    </div>

    <!-- Official Printable Slip Gaji Container -->
    <div class="printable-slip bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 sm:p-10 space-y-6 text-slate-900 relative overflow-hidden">
        
        <!-- Status Watermark / Stamp -->
        <div class="absolute top-6 right-6 sm:top-10 sm:right-10 pointer-events-none select-none">
            @if($staffPayroll->isPaid())
                <div class="border-2 border-emerald-600 text-emerald-700 px-4 py-1 rounded-lg font-black tracking-widest text-sm uppercase rotate-[-6deg] opacity-80 shadow-2xs flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>LUNAS (PAID)</span>
                </div>
            @else
                <div class="border-2 border-amber-600 text-amber-700 px-4 py-1 rounded-lg font-black tracking-widest text-sm uppercase rotate-[-6deg] opacity-80 shadow-2xs">
                    <span>PENDING (BELUM BAYAR)</span>
                </div>
            @endif
        </div>

        <!-- Header Perusahaan -->
        <div class="border-b-2 border-slate-900 pb-5">
            <div class="flex items-center gap-4">
                @php
                    $appLogo = \App\Models\Setting::get('app_logo');
                    $appName = \App\Models\Setting::get('app_name', 'ARTDEVATA');
                    $appTagline = \App\Models\Setting::get('app_tagline', 'Portal Manajemen Guide & Operasional Tour');
                @endphp

                @if(!empty($appLogo) && file_exists(public_path($appLogo)))
                    <img src="{{ asset($appLogo) }}" alt="{{ $appName }}" class="h-12 w-12 object-contain">
                @else
                    <div class="h-12 w-12 rounded-xl bg-[#14433b] text-[#21c9a4] font-black flex items-center justify-center text-lg shadow-xs">
                        {{ substr($appName, 0, 2) }}
                    </div>
                @endif

                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight uppercase leading-none">{{ $appName }}</h1>
                    <p class="text-xs text-slate-500 font-medium mt-1">{{ $appTagline }}</p>
                    <p class="text-[11px] text-slate-400 font-mono">Bali, Indonesia &bull; Dokumen Resmi Pembayaran Gaji Karyawan</p>
                </div>
            </div>

            <div class="text-center mt-5 pt-3 border-t border-slate-200">
                <h2 class="text-base font-extrabold tracking-wider text-slate-900 uppercase">SLIP GAJI KARYAWAN</h2>
                <p class="text-xs font-semibold text-[#14433b]">PERIODE: {{ strtoupper($staffPayroll->period_name) }}</p>
            </div>
        </div>

        <!-- Informasi Karyawan & Dokumen -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50/80 p-4 rounded-xl border border-slate-200 text-xs">
            <div>
                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Nomor Dokumen</span>
                <span class="font-mono font-bold text-slate-900 text-xs">{{ $staffPayroll->payroll_code }}</span>
            </div>

            <div>
                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Tanggal Terbit</span>
                <span class="font-bold text-slate-900 text-xs">{{ $staffPayroll->created_at->format('d F Y') }}</span>
            </div>

            <div>
                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Nama Karyawan</span>
                <span class="font-bold text-slate-900 text-sm block">{{ $staffPayroll->user->name }}</span>
            </div>

            <div>
                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Jabatan / Posisi</span>
                <span class="font-bold text-[#14433b] text-xs block">{{ $staffPayroll->position ?: ($staffPayroll->user->position ?: 'Staff Operasional') }}</span>
            </div>

            <div>
                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Email / Kontak</span>
                <span class="text-slate-700 font-medium block truncate">{{ $staffPayroll->user->email }}</span>
                <span class="text-[11px] text-slate-500 font-mono">{{ $staffPayroll->user->phone ?: '-' }}</span>
            </div>

            <div>
                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Metode Pembayaran</span>
                <span class="font-bold text-slate-900 block">{{ $staffPayroll->payment_method ?: 'Transfer Bank' }}</span>
                @if($staffPayroll->payment_reference)
                    <span class="text-[10px] text-slate-500 font-mono">Ref: {{ $staffPayroll->payment_reference }}</span>
                @endif
            </div>

            <div class="sm:col-span-2">
                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Rekening Penerima</span>
                @if($staffPayroll->user->bank_name && $staffPayroll->user->bank_account_number)
                    <span class="font-bold text-slate-900">
                        {{ $staffPayroll->user->bank_name }} - {{ $staffPayroll->user->bank_account_number }}
                    </span>
                    <span class="text-[11px] text-slate-500 block">
                        a.n {{ $staffPayroll->user->bank_account_holder ?: $staffPayroll->user->name }}
                    </span>
                @else
                    <span class="text-slate-400 italic">Informasi rekening tidak dicantumkan</span>
                @endif
            </div>
        </div>

        <!-- Tabel Rincian Komponen Gaji -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <!-- Kolom Penerimaan -->
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <div class="bg-slate-100 px-3.5 py-2 font-bold text-slate-800 border-b border-slate-200 flex justify-between">
                    <span>A. PENERIMAAN</span>
                    <span>JUMLAH</span>
                </div>
                <div class="p-3.5 space-y-2.5">
                    <div class="flex justify-between items-center text-slate-700">
                        <span>Gaji Pokok</span>
                        <span class="font-semibold text-slate-900">Rp {{ number_format($staffPayroll->base_salary, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between items-center text-slate-700">
                        <span>Tunjangan Makan & Transport</span>
                        <span class="font-semibold text-slate-900">Rp {{ number_format($staffPayroll->allowance, 0, ',', '.') }}</span>
                    </div>

                    @if($staffPayroll->bonus > 0)
                    <div class="flex justify-between items-center text-emerald-700">
                        <span>Bonus / Lembur / Insentif</span>
                        <span class="font-semibold text-emerald-800">+ Rp {{ number_format($staffPayroll->bonus, 0, ',', '.') }}</span>
                    </div>
                    @endif

                    @php
                        $subtotalPenerimaan = $staffPayroll->base_salary + $staffPayroll->allowance + $staffPayroll->bonus;
                    @endphp

                    <div class="pt-2 border-t border-slate-200 flex justify-between items-center font-bold text-slate-900 text-xs">
                        <span>Total Penerimaan (A)</span>
                        <span class="text-slate-900">Rp {{ number_format($subtotalPenerimaan, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Kolom Potongan -->
            <div class="border border-slate-200 rounded-xl overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="bg-slate-100 px-3.5 py-2 font-bold text-slate-800 border-b border-slate-200 flex justify-between">
                        <span>B. POTONGAN</span>
                        <span>JUMLAH</span>
                    </div>
                    <div class="p-3.5 space-y-2.5">
                        <div class="flex justify-between items-center text-slate-700">
                            <span>Potongan (BPJS / Kasbon / Absensi)</span>
                            <span class="font-semibold text-rose-700">{{ $staffPayroll->deduction > 0 ? '- Rp ' . number_format($staffPayroll->deduction, 0, ',', '.') : 'Rp 0' }}</span>
                        </div>

                        @if($staffPayroll->notes)
                        <div class="pt-2 text-[11px] text-slate-500 bg-slate-50 p-2 rounded border border-slate-100">
                            <span class="font-semibold text-slate-700 block">Keterangan Tambahan:</span>
                            <p class="whitespace-pre-line">{{ $staffPayroll->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="p-3.5 pt-2 border-t border-slate-200 flex justify-between items-center font-bold text-rose-800 text-xs bg-slate-50/50">
                    <span>Total Potongan (B)</span>
                    <span>Rp {{ number_format($staffPayroll->deduction, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Grand Total Take Home Pay -->
        <div class="bg-slate-900 text-white rounded-xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider block">GAJI BERSIH DITERIMA (TAKE HOME PAY)</span>
                <span class="text-sm font-semibold text-white/90 italic block mt-0.5">
                    "{{ $staffPayroll->terbilang }}"
                </span>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    Rp {{ number_format($staffPayroll->net_salary, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-slate-300 block font-mono">
                    {{ $staffPayroll->isPaid() ? 'Status: LUNAS (' . $staffPayroll->payment_date?->format('d/m/Y') . ')' : 'Status: Menunggu Pembayaran' }}
                </span>
            </div>
        </div>

        <!-- Tanda Tangan (Signatures) -->
        <div class="pt-6 grid grid-cols-2 gap-8 text-xs text-center border-t border-slate-200">
            <div>
                <span class="text-[11px] text-slate-500 block mb-1">Diterima Oleh Karyawan,</span>
                <div class="h-20 flex items-end justify-center pb-1">
                    <span class="font-bold border-b border-slate-800 pb-0.5 min-w-[160px] inline-block text-slate-900">
                        {{ $staffPayroll->user->name }}
                    </span>
                </div>
                <span class="text-[10px] text-slate-400">Staff / Penerima</span>
            </div>

            <div>
                <span class="text-[11px] text-slate-500 block mb-1">Disetujui / Diserahkan Oleh,</span>
                <div class="h-20 flex items-end justify-center pb-1">
                    <span class="font-bold border-b border-slate-800 pb-0.5 min-w-[160px] inline-block text-slate-900">
                        {{ $staffPayroll->paidBy?->name ?: (auth()->user()->name) }}
                    </span>
                </div>
                <span class="text-[10px] text-slate-400">Bagian Keuangan / Manajemen</span>
            </div>
        </div>

        <!-- Footer slip -->
        <div class="text-[10px] text-slate-400 text-center pt-4 border-t border-slate-100 font-mono">
            Slip gaji ini dibuat secara otomatis oleh sistem {{ $appName }} pada {{ now()->format('d/m/Y H:i:s') }}. Dokumen sah tanpa cap basah.
        </div>
    </div>

    <!-- Modal Pelunasan Cepat -->
    <div x-show="paymentModalOpen" style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition>
        
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100"
             @click.away="paymentModalOpen = false">
            <div class="p-5 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Catat Pembayaran Gaji</h3>
                    <p class="text-xs text-slate-500">{{ $staffPayroll->payroll_code }} &bull; {{ $staffPayroll->user->name }}</p>
                </div>
                <button type="button" @click="paymentModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('staff-payroll.pay', $staffPayroll) }}" method="POST" class="p-5 space-y-4 text-xs">
                @csrf
                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900">
                    <span class="text-[11px] font-semibold text-emerald-700 block uppercase">Total Gaji Bersih</span>
                    <div class="text-2xl font-black text-emerald-800 mt-0.5">
                        Rp {{ number_format($staffPayroll->net_salary, 0, ',', '.') }}
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal Pembayaran <span class="text-rose-500">*</span></label>
                    <input type="date" name="payment_date" x-model="paymentDate" required
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Metode Pembayaran <span class="text-rose-500">*</span></label>
                    <select name="payment_method" x-model="paymentMethod" required
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20">
                        <option value="TRANSFER">Transfer Bank</option>
                        <option value="CASH">Tunai (Cash)</option>
                        <option value="LAINNYA">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nomor Referensi / Catatan Transaksi</label>
                    <input type="text" name="payment_reference" x-model="paymentReference" placeholder="Contoh: REF-BCA-10291"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan</label>
                    <textarea name="notes" x-model="paymentNotes" rows="2" placeholder="Catatan pembayaran..."
                              class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20"></textarea>
                </div>

                <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="record_expense" value="1" x-model="recordExpense"
                               class="rounded border-slate-300 text-[#14433b] focus:ring-[#14433b] mt-0.5">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Catat Otomatis ke Biaya Operasional</span>
                            <span class="text-[11px] text-slate-500">Mencatat pengeluaran di menu Keuangan & Biaya Operasional (Kategori: Gaji & Upah).</span>
                        </div>
                    </label>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="paymentModalOpen = false" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs">
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
