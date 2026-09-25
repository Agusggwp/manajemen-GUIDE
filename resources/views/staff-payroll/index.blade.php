@extends('layouts.app')

@section('title', 'Gaji Staff (Staff Payroll)')
@section('header', 'Penggajian Staff (Staff Payroll)')

@section('content')
<div class="space-y-6" x-data="{
    paymentModalOpen: false,
    settingsModalOpen: false,
    activeSlip: null,
    paymentDate: '{{ now()->toDateString() }}',
    paymentMethod: 'TRANSFER',
    paymentReference: '',
    paymentNotes: '',
    recordExpense: true,

    openPaymentModal(slip) {
        this.activeSlip = slip;
        this.paymentDate = '{{ now()->toDateString() }}';
        this.paymentMethod = 'TRANSFER';
        this.paymentReference = '';
        this.paymentNotes = '';
        this.recordExpense = true;
        this.paymentModalOpen = true;
    }
}">

    <!-- Filter & Action Header -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form action="{{ route('staff-payroll.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5">
            <!-- Bulan -->
            <div>
                <select name="month" class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#14433b]/20">
                    @php
                        $months = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                    @endphp
                    @foreach($months as $mNum => $mLabel)
                        <option value="{{ $mNum }}" {{ $periodMonth == $mNum ? 'selected' : '' }}>{{ $mLabel }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tahun -->
            <div>
                <select name="year" class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#14433b]/20">
                    @for($y = (int)date('Y') - 1; $y <= (int)date('Y') + 2; $y++)
                        <option value="{{ $y }}" {{ $periodYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <!-- Status -->
            <div>
                <select name="status" class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#14433b]/20">
                    <option value="ALL" {{ $status == 'ALL' ? 'selected' : '' }}>Semua Status</option>
                    <option value="PENDING" {{ $status == 'PENDING' ? 'selected' : '' }}>Belum Bayar (Pending)</option>
                    <option value="PAID" {{ $status == 'PAID' ? 'selected' : '' }}>Sudah Lunas (Paid)</option>
                </select>
            </div>

            <!-- Staff -->
            <div>
                <select name="user_id" class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#14433b]/20">
                    <option value="">Semua Staff</option>
                    @foreach($staffMembers as $sm)
                        <option value="{{ $sm->id }}" {{ $userId == $sm->id ? 'selected' : '' }}>{{ $sm->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition shadow-xs">
                Filter
            </button>
            @if(request()->hasAny(['month', 'year', 'status', 'user_id']))
                <a href="{{ route('staff-payroll.index') }}" class="text-xs text-slate-500 hover:text-slate-700 py-1.5">Reset</a>
            @endif
        </form>

        <div class="flex items-center gap-2">
            <button type="button" @click="settingsModalOpen = true" class="px-3 py-2 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Standar Gaji Staff</span>
            </button>

            <a href="{{ route('staff-payroll.create') }}" class="px-3.5 py-2 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#21c9a4]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Buat Slip Gaji</span>
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Total Gaji Staff ({{ $months[$periodMonth] }} {{ $periodYear }})</span>
            <span class="text-xl font-extrabold text-slate-900 block mt-1">Rp {{ number_format($totalNetSalary, 0, ',', '.') }}</span>
            <span class="text-[11px] text-slate-400 mt-1 block">Akumulasi seluruh slip periode ini</span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Total Gaji Terbayar (Paid)</span>
            <span class="text-xl font-extrabold text-emerald-600 block mt-1">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
            <span class="text-[11px] text-emerald-600/80 mt-1 block font-medium">{{ $staffPaidCount }} slip gaji telah lunas</span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Menunggu Pembayaran (Pending)</span>
            <span class="text-xl font-extrabold text-amber-600 block mt-1">Rp {{ number_format($totalPending, 0, ',', '.') }}</span>
            <span class="text-[11px] text-amber-600/80 mt-1 block font-medium">{{ $staffPendingCount }} slip belum dibayarkan</span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
            <span class="text-xs font-medium text-slate-500 block">Cakupan Staff Aktif</span>
            <span class="text-xl font-extrabold text-[#14433b] block mt-1">{{ $payrolls->count() }} / {{ $staffMembers->count() }} Staff</span>
            <span class="text-[11px] text-slate-400 mt-1 block">Staff telah dibuatkan slip periode ini</span>
        </div>
    </div>

    <!-- Table of Staff Payrolls -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Daftar Penggajian Staff</h3>
                <p class="text-xs text-slate-500">Periode: <strong>{{ $months[$periodMonth] }} {{ $periodYear }}</strong></p>
            </div>
            <span class="text-xs text-slate-400 font-medium">{{ $payrolls->count() }} data slip gaji</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5">Kode Slip</th>
                        <th class="px-4 py-3.5">Staff & Jabatan</th>
                        <th class="px-4 py-3.5 text-right">Gaji Pokok</th>
                        <th class="px-4 py-3.5 text-right">Tunjangan</th>
                        <th class="px-4 py-3.5 text-right">Bonus / Lembur</th>
                        <th class="px-4 py-3.5 text-right">Potongan</th>
                        <th class="px-4 py-3.5 text-right">Gaji Bersih</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payrolls as $row)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-4 py-3.5">
                            <a href="{{ route('staff-payroll.show', $row) }}" class="font-mono font-bold text-[#14433b] hover:text-[#21c9a4] transition">
                                {{ $row->payroll_code }}
                            </a>
                            <div class="text-[10px] text-slate-400">
                                {{ $row->created_at->format('d/m/Y H:i') }}
                            </div>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900 text-sm">{{ $row->user->name }}</div>
                            <div class="text-[11px] text-slate-500 font-medium">
                                {{ $row->position ?: ($row->user->position ?: 'Staff Operasional') }}
                            </div>
                            @if($row->user->bank_name && $row->user->bank_account_number)
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                    {{ $row->user->bank_name }}: {{ $row->user->bank_account_number }} (a.n {{ $row->user->bank_account_holder ?: $row->user->name }})
                                </div>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 text-right text-slate-700 font-medium">
                            Rp {{ number_format($row->base_salary, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3.5 text-right text-slate-700">
                            Rp {{ number_format($row->allowance, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3.5 text-right text-emerald-700 font-medium">
                            {{ $row->bonus > 0 ? '+ Rp ' . number_format($row->bonus, 0, ',', '.') : '-' }}
                        </td>

                        <td class="px-4 py-3.5 text-right text-rose-700 font-medium">
                            {{ $row->deduction > 0 ? '- Rp ' . number_format($row->deduction, 0, ',', '.') : '-' }}
                        </td>

                        <td class="px-4 py-3.5 text-right font-extrabold text-[#14433b] text-sm">
                            Rp {{ number_format($row->net_salary, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            @if($row->isPaid())
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    LUNAS
                                </span>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $row->payment_date?->format('d/m/Y') }} ({{ $row->payment_method }})
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    BELUM BAYAR
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('staff-payroll.show', $row) }}" 
                                   title="Lihat & Cetak Slip Gaji"
                                   class="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </a>

                                @if(!$row->isPaid())
                                    <button type="button" 
                                            @click="openPaymentModal({{ json_encode([
                                                'id' => $row->id,
                                                'code' => $row->payroll_code,
                                                'name' => $row->user->name,
                                                'amount' => $row->net_salary,
                                                'amount_formatted' => number_format($row->net_salary, 0, ',', '.'),
                                                'pay_url' => route('staff-payroll.pay', $row)
                                            ]) }})"
                                            title="Bayar Gaji"
                                            class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition shadow-2xs flex items-center gap-1">
                                        <span>Bayar</span>
                                    </button>

                                    <a href="{{ route('staff-payroll.edit', $row) }}" 
                                       title="Edit Slip Gaji"
                                       class="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                @endif

                                <form action="{{ route('staff-payroll.destroy', $row) }}" method="POST" 
                                      data-confirm="Apakah Anda yakin ingin menghapus slip gaji {{ $row->payroll_code }} untuk {{ $row->user->name }}?"
                                      data-confirm-title="Hapus Slip Gaji" data-confirm-icon="warning" data-confirm-btn="Ya, Hapus!">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            title="Hapus Slip"
                                            class="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center">
                            <div class="max-w-sm mx-auto text-slate-400">
                                <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-sm font-semibold text-slate-600">Belum ada slip gaji pada periode ini.</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol "+ Buat Slip Gaji" di atas untuk menambahkan slip gaji staff.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Pelunasan Gaji Staff -->
    <div x-show="paymentModalOpen" style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100"
             @click.away="paymentModalOpen = false">
            <div class="p-5 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Catat Pembayaran Gaji Staff</h3>
                    <p class="text-xs text-slate-500" x-text="activeSlip ? activeSlip.code + ' &bull; ' + activeSlip.name : ''"></p>
                </div>
                <button type="button" @click="paymentModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <template x-if="activeSlip">
                <form :action="activeSlip.pay_url" method="POST" class="p-5 space-y-4">
                    @csrf
                    <!-- Amount Display -->
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900">
                        <span class="text-[11px] font-semibold text-emerald-700 block uppercase">Total Gaji Bersih yang Dibayarkan</span>
                        <div class="text-2xl font-black text-emerald-800 mt-0.5" x-text="'Rp ' + activeSlip.amount_formatted"></div>
                    </div>

                    <!-- Tanggal Bayar -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pembayaran <span class="text-rose-500">*</span></label>
                        <input type="date" name="payment_date" x-model="paymentDate" required
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20 focus:border-[#14433b]">
                    </div>

                    <!-- Metode Pembayaran -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Metode Pembayaran <span class="text-rose-500">*</span></label>
                        <select name="payment_method" x-model="paymentMethod" required
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20 focus:border-[#14433b]">
                            <option value="TRANSFER">Transfer Bank</option>
                            <option value="CASH">Tunai (Cash)</option>
                            <option value="LAINNYA">Lainnya</option>
                        </select>
                    </div>

                    <!-- No Ref / Bukti Transfer -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Referensi / Catatan Transaksi</label>
                        <input type="text" name="payment_reference" x-model="paymentReference" placeholder="Contoh: REF-BCA-9812903"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20 focus:border-[#14433b]">
                    </div>

                    <!-- Catatan Tambahan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan</label>
                        <textarea name="notes" x-model="paymentNotes" rows="2" placeholder="Catatan pembayaran (opsional)..."
                                  class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20 focus:border-[#14433b]"></textarea>
                    </div>

                    <!-- Checkbox Record Expense -->
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
            </template>
        </div>
    </div>

    <!-- Modal Pengaturan Standar Gaji & Rekening Staff -->
    <div x-show="settingsModalOpen" style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-2xl max-w-3xl w-full shadow-2xl overflow-hidden border border-slate-100 max-h-[90vh] flex flex-col"
             @click.away="settingsModalOpen = false">
            
            <div class="p-5 border-b border-slate-100 bg-slate-50 flex items-center justify-between shrink-0">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Pengaturan Standar Gaji & Rekening Bank Staff</h3>
                    <p class="text-xs text-slate-500">Data standar ini akan otomatis mengisi form saat membuat slip gaji bulanan.</p>
                </div>
                <button type="button" @click="settingsModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-5 overflow-y-auto space-y-6 flex-1">
                @forelse($staffMembers as $staff)
                <form action="{{ route('staff-payroll.setting', $staff) }}" method="POST" class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3">
                    @csrf
                    <div class="flex items-center justify-between border-b border-slate-200/80 pb-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-[#14433b] text-white font-bold flex items-center justify-center text-xs">
                                {{ strtoupper(substr($staff->name, 0, 2)) }}
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900">{{ $staff->name }}</h4>
                                <span class="text-[11px] text-slate-500">{{ $staff->email }} &bull; {{ $staff->phone ?? 'Belum ada telepon' }}</span>
                            </div>
                        </div>
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition shadow-2xs">
                            Simpan Perubahan
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jabatan / Posisi</label>
                            <input type="text" name="position" value="{{ $staff->position }}" placeholder="Contoh: Staff Operasional"
                                   class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Gaji Pokok Default (Rp)</label>
                            <input type="number" step="1000" min="0" name="salary_base" value="{{ (int)$staff->salary_base }}" placeholder="0"
                                   class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tunjangan Tetap (Rp)</label>
                            <input type="number" step="1000" min="0" name="salary_allowance" value="{{ (int)$staff->salary_allowance }}" placeholder="0"
                                   class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nama Bank</label>
                            <input type="text" name="bank_name" value="{{ $staff->bank_name }}" placeholder="Contoh: BCA / BRI / Mandiri"
                                   class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nomor Rekening</label>
                            <input type="text" name="bank_account_number" value="{{ $staff->bank_account_number }}" placeholder="Contoh: 1234567890"
                                   class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nama Pemilik Rekening</label>
                            <input type="text" name="bank_account_holder" value="{{ $staff->bank_account_holder }}" placeholder="Sesuai buku tabungan"
                                   class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-[#14433b]/20">
                        </div>
                    </div>
                </form>
                @empty
                <p class="text-center text-slate-500 py-6 text-xs">Belum ada staff terdaftar dengan role STAFF.</p>
                @endforelse
            </div>

            <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-end shrink-0">
                <button type="button" @click="settingsModalOpen = false" class="px-4 py-2 rounded-lg bg-slate-800 text-white text-xs font-bold hover:bg-slate-900 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
