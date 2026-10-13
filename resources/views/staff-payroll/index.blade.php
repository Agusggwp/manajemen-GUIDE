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
    <div class="clay-card p-5 md:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form action="{{ route('staff-payroll.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5">
            <!-- Bulan -->
            <div>
                <select name="month" class="clay-input text-xs py-1.5 px-3">
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
                <select name="year" class="clay-input text-xs py-1.5 px-3">
                    @for($y = (int)date('Y') - 1; $y <= (int)date('Y') + 2; $y++)
                        <option value="{{ $y }}" {{ $periodYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <!-- Status -->
            <div>
                <select name="status" class="clay-input text-xs py-1.5 px-3">
                    <option value="ALL" {{ $status == 'ALL' ? 'selected' : '' }}>Semua Status</option>
                    <option value="PENDING" {{ $status == 'PENDING' ? 'selected' : '' }}>Belum Bayar (Pending)</option>
                    <option value="PAID" {{ $status == 'PAID' ? 'selected' : '' }}>Sudah Lunas (Paid)</option>
                </select>
            </div>

            <!-- Staff -->
            <div>
                <select name="user_id" class="clay-input text-xs py-1.5 px-3">
                    <option value="">Semua Staff</option>
                    @foreach($staffMembers as $sm)
                        <option value="{{ $sm->id }}" {{ $userId == $sm->id ? 'selected' : '' }}>{{ $sm->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="clay-btn clay-btn-primary text-xs px-3.5 py-1.5 font-bold">
                Filter
            </button>
            @if(request()->hasAny(['month', 'year', 'status', 'user_id']))
                <a href="{{ route('staff-payroll.index') }}" class="text-xs font-bold text-[#77768C] hover:text-[#29283D] py-1.5">Reset</a>
            @endif
        </form>

        <div class="flex items-center gap-2.5">
            <button type="button" @click="settingsModalOpen = true" class="clay-btn clay-btn-secondary text-xs px-3 py-2 font-bold inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#77768C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Standar Gaji Staff</span>
            </button>

            <a href="{{ route('staff-payroll.create') }}" class="clay-btn clay-btn-primary text-xs px-3.5 py-2 font-bold inline-flex items-center gap-1.5">
                <span>+</span> Buat Slip Gaji
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="clay-card p-5">
            <span class="text-xs font-bold text-[#77768C] block uppercase tracking-wider">Total Gaji Staff ({{ $months[$periodMonth] }} {{ $periodYear }})</span>
            <span class="text-xl font-extrabold text-[#29283D] block mt-1">Rp {{ number_format($totalNetSalary, 0, ',', '.') }}</span>
            <span class="text-[11px] text-[#77768C] mt-0.5 block">Akumulasi seluruh slip periode ini</span>
        </div>

        <div class="clay-card p-5">
            <span class="text-xs font-bold text-[#0f766e] block uppercase tracking-wider">Total Gaji Terbayar (Paid)</span>
            <span class="text-xl font-extrabold text-[#72D6C9] block mt-1">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
            <span class="text-[11px] text-[#77768C] mt-0.5 block font-bold">{{ $staffPaidCount }} slip gaji telah lunas</span>
        </div>

        <div class="clay-card p-5">
            <span class="text-xs font-bold text-[#b45309] block uppercase tracking-wider">Menunggu Pembayaran (Pending)</span>
            <span class="text-xl font-extrabold text-[#FFD166] block mt-1">Rp {{ number_format($totalPending, 0, ',', '.') }}</span>
            <span class="text-[11px] text-[#77768C] mt-0.5 block font-bold">{{ $staffPendingCount }} slip belum dibayarkan</span>
        </div>

        <div class="clay-card p-5">
            <span class="text-xs font-bold text-[#77768C] block uppercase tracking-wider">Cakupan Staff Aktif</span>
            <span class="text-xl font-extrabold text-[#6C63FF] block mt-1">{{ $payrolls->count() }} / {{ $staffMembers->count() }} Staff</span>
            <span class="text-[11px] text-[#77768C] mt-0.5 block">Staff telah dibuatkan slip periode ini</span>
        </div>
    </div>

    <!-- Table of Staff Payrolls -->
    <div class="clay-table-wrapper">
        <div class="p-5 border-b border-[#E8E6F0] flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold text-[#29283D]">Daftar Penggajian Staff</h3>
                <p class="text-xs text-[#77768C] mt-0.5">Periode: <strong class="text-[#29283D]">{{ $months[$periodMonth] }} {{ $periodYear }}</strong></p>
            </div>
            <span class="text-xs text-[#77768C] font-bold">{{ $payrolls->count() }} data slip gaji</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F4F3FA] text-[#77768C] font-bold border-b border-[#E8E6F0]">
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
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($payrolls as $row)
                    <tr class="hover:bg-[#F4F3FA]/70 transition-colors">
                        <td class="px-4 py-3.5">
                            <a href="{{ route('staff-payroll.show', $row) }}" class="font-mono font-extrabold text-[#6C63FF] hover:underline transition">
                                {{ $row->payroll_code }}
                            </a>
                            <div class="text-[10px] text-[#77768C] mt-0.5">
                                {{ $row->created_at->format('d/m/Y H:i') }}
                            </div>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="font-extrabold text-[#29283D] text-sm">{{ $row->user->name }}</div>
                            <div class="text-[11px] text-[#77768C] font-medium">
                                {{ $row->position ?: ($row->user->position ?: 'Staff Operasional') }}
                            </div>
                            @if($row->user->bank_name && $row->user->bank_account_number)
                                <div class="text-[10px] text-[#77768C] font-mono mt-0.5">
                                    {{ $row->user->bank_name }}: {{ $row->user->bank_account_number }} (a.n {{ $row->user->bank_account_holder ?: $row->user->name }})
                                </div>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 text-right text-[#29283D] font-medium">
                            Rp {{ number_format($row->base_salary, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3.5 text-right text-[#29283D]">
                            Rp {{ number_format($row->allowance, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3.5 text-right text-[#0f766e] font-bold">
                            {{ $row->bonus > 0 ? '+ Rp ' . number_format($row->bonus, 0, ',', '.') : '-' }}
                        </td>

                        <td class="px-4 py-3.5 text-right text-[#FF8A65] font-bold">
                            {{ $row->deduction > 0 ? '- Rp ' . number_format($row->deduction, 0, ',', '.') : '-' }}
                        </td>

                        <td class="px-4 py-3.5 text-right font-extrabold text-[#6C63FF] text-sm">
                            Rp {{ number_format($row->net_salary, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            @if($row->isPaid())
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[10px] font-bold bg-[#72D6C9]/20 text-[#0f766e] border border-[#72D6C9]/40">
                                    <svg class="w-3 h-3 text-[#0f766e]" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    LUNAS
                                </span>
                                <div class="text-[10px] text-[#77768C] mt-0.5">
                                    {{ $row->payment_date?->format('d/m/Y') }} ({{ $row->payment_method }})
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[10px] font-bold bg-[#FFD166]/25 text-[#b45309] border border-[#FFD166]/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FFD166] animate-pulse"></span>
                                    BELUM BAYAR
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('staff-payroll.show', $row) }}" 
                                   title="Lihat & Cetak Slip Gaji"
                                   class="clay-btn clay-btn-secondary p-1.5 inline-flex items-center">
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
                                            class="clay-btn clay-btn-turquoise px-2.5 py-1 text-[11px] font-bold inline-flex items-center gap-1">
                                        <span>Bayar</span>
                                    </button>

                                    <a href="{{ route('staff-payroll.edit', $row) }}" 
                                       title="Edit Slip Gaji"
                                       class="clay-btn clay-btn-secondary p-1.5 inline-flex items-center">
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
                                            class="p-1.5 rounded-xl text-[#FF8A65] hover:bg-[#FF8A65]/10 transition-colors">
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
                            <div class="max-w-sm mx-auto text-[#77768C]">
                                <div class="w-12 h-12 mx-auto mb-2 text-[#6C63FF]/40"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
                                <p class="text-sm font-bold text-[#29283D]">Belum ada slip gaji pada periode ini.</p>
                                <p class="text-xs text-[#77768C] mt-1">Klik tombol "+ Buat Slip Gaji" di atas untuk menambahkan slip gaji staff.</p>
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
         class="fixed inset-0 z-50 overflow-y-auto bg-[#29283D]/50 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="clay-card max-w-md w-full overflow-hidden"
             @click.away="paymentModalOpen = false">
            <div class="p-5 border-b border-[#E8E6F0] bg-[#F4F3FA] flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-[#29283D]">Catat Pembayaran Gaji Staff</h3>
                    <p class="text-xs text-[#77768C]" x-text="activeSlip ? activeSlip.code + ' &bull; ' + activeSlip.name : ''"></p>
                </div>
                <button type="button" @click="paymentModalOpen = false" class="text-[#77768C] hover:text-[#29283D] text-lg font-bold">
                    &times;
                </button>
            </div>

            <template x-if="activeSlip">
                <form :action="activeSlip.pay_url" method="POST" class="p-5 space-y-4">
                    @csrf
                    <!-- Amount Display -->
                    <div class="p-4 rounded-2xl bg-[#72D6C9]/15 border border-[#72D6C9]/30 text-[#0f766e]">
                        <span class="text-[11px] font-bold text-[#0f766e] block uppercase">Total Gaji Bersih yang Dibayarkan</span>
                        <div class="text-2xl font-extrabold text-[#0f766e] mt-1" x-text="'Rp ' + activeSlip.amount_formatted"></div>
                    </div>

                    <!-- Tanggal Bayar -->
                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Tanggal Pembayaran <span class="text-[#FF8A65]">*</span></label>
                        <input type="date" name="payment_date" x-model="paymentDate" required
                               class="w-full clay-input">
                    </div>

                    <!-- Metode Pembayaran -->
                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Metode Pembayaran <span class="text-[#FF8A65]">*</span></label>
                        <select name="payment_method" x-model="paymentMethod" required
                                class="w-full clay-input font-bold">
                            <option value="TRANSFER">Transfer Bank</option>
                            <option value="CASH">Tunai (Cash)</option>
                            <option value="LAINNYA">Lainnya</option>
                        </select>
                    </div>

                    <!-- No Ref / Bukti Transfer -->
                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Nomor Referensi / Catatan Transaksi</label>
                        <input type="text" name="payment_reference" x-model="paymentReference" placeholder="Contoh: REF-BCA-9812903"
                               class="w-full clay-input">
                    </div>

                    <!-- Catatan Tambahan -->
                    <div>
                        <label class="block text-xs font-bold text-[#29283D] mb-1.5">Catatan</label>
                        <textarea name="notes" x-model="paymentNotes" rows="2" placeholder="Catatan pembayaran (opsional)..."
                                  class="w-full clay-input resize-y"></textarea>
                    </div>

                    <!-- Checkbox Record Expense -->
                    <div class="p-3.5 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0]">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="record_expense" value="1" x-model="recordExpense"
                                   class="rounded-lg text-[#6C63FF] focus:ring-[#6C63FF] mt-0.5">
                            <div>
                                <span class="text-xs font-bold text-[#29283D] block">Catat Otomatis ke Biaya Operasional</span>
                                <span class="text-[11px] text-[#77768C]">Mencatat pengeluaran di menu Keuangan & Biaya Operasional (Kategori: Gaji & Upah).</span>
                            </div>
                        </label>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2.5 border-t border-[#E8E6F0]">
                        <button type="button" @click="paymentModalOpen = false" class="clay-btn clay-btn-secondary px-4 py-2 font-bold text-xs">
                            Batal
                        </button>
                        <button type="submit" class="clay-btn clay-btn-turquoise px-4 py-2 font-bold text-xs">
                            Konfirmasi Pembayaran
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- Modal Pengaturan Standar Gaji & Rekening Staff -->
    <div x-show="settingsModalOpen" style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto bg-[#29283D]/50 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="clay-card max-w-3xl w-full overflow-hidden max-h-[90vh] flex flex-col"
             @click.away="settingsModalOpen = false">
            
            <div class="p-5 border-b border-[#E8E6F0] bg-[#F4F3FA] flex items-center justify-between shrink-0">
                <div>
                    <h3 class="text-sm font-extrabold text-[#29283D]">Pengaturan Standar Gaji & Rekening Bank Staff</h3>
                    <p class="text-xs text-[#77768C]">Data standar ini akan otomatis mengisi form saat membuat slip gaji bulanan.</p>
                </div>
                <button type="button" @click="settingsModalOpen = false" class="text-[#77768C] hover:text-[#29283D] text-lg font-bold">
                    &times;
                </button>
            </div>

            <div class="p-5 overflow-y-auto space-y-5 flex-1">
                @forelse($staffMembers as $staff)
                <form action="{{ route('staff-payroll.setting', $staff) }}" method="POST" class="p-4 rounded-2xl border border-[#E8E6F0] bg-[#F4F3FA]/50 space-y-3">
                    @csrf
                    <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-[#6C63FF]/15 text-[#6C63FF] font-extrabold flex items-center justify-center text-xs">
                                {{ strtoupper(substr($staff->name, 0, 2)) }}
                            </div>
                            <div>
                                <h4 class="text-sm font-extrabold text-[#29283D]">{{ $staff->name }}</h4>
                                <span class="text-[11px] text-[#77768C]">{{ $staff->email }} &bull; {{ $staff->phone ?? 'Belum ada telepon' }}</span>
                            </div>
                        </div>
                        <button type="submit" class="clay-btn clay-btn-primary px-3 py-1.5 text-xs font-bold">
                            Simpan Perubahan
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                        <div>
                            <label class="block font-bold text-[#29283D] mb-1">Jabatan / Posisi</label>
                            <input type="text" name="position" value="{{ $staff->position }}" placeholder="Contoh: Staff Operasional"
                                   class="w-full clay-input py-1.5 px-2.5 text-xs">
                        </div>

                        <div>
                            <label class="block font-bold text-[#29283D] mb-1">Gaji Pokok Default (Rp)</label>
                            <input type="number" step="1000" min="0" name="salary_base" value="{{ (int)$staff->salary_base }}" placeholder="0"
                                   class="w-full clay-input py-1.5 px-2.5 text-xs font-bold text-[#6C63FF]">
                        </div>

                        <div>
                            <label class="block font-bold text-[#29283D] mb-1">Tunjangan Tetap (Rp)</label>
                            <input type="number" step="1000" min="0" name="salary_allowance" value="{{ (int)$staff->salary_allowance }}" placeholder="0"
                                   class="w-full clay-input py-1.5 px-2.5 text-xs font-bold">
                        </div>

                        <div>
                            <label class="block font-bold text-[#29283D] mb-1">Nama Bank</label>
                            <input type="text" name="bank_name" value="{{ $staff->bank_name }}" placeholder="Contoh: BCA / BRI / Mandiri"
                                   class="w-full clay-input py-1.5 px-2.5 text-xs">
                        </div>

                        <div>
                            <label class="block font-bold text-[#29283D] mb-1">Nomor Rekening</label>
                            <input type="text" name="bank_account_number" value="{{ $staff->bank_account_number }}" placeholder="Contoh: 1234567890"
                                   class="w-full clay-input py-1.5 px-2.5 text-xs font-mono">
                        </div>

                        <div>
                            <label class="block font-bold text-[#29283D] mb-1">Nama Pemilik Rekening</label>
                            <input type="text" name="bank_account_holder" value="{{ $staff->bank_account_holder }}" placeholder="Sesuai buku tabungan"
                                   class="w-full clay-input py-1.5 px-2.5 text-xs">
                        </div>
                    </div>
                </form>
                @empty
                <p class="text-center text-[#77768C] py-6 text-xs font-bold">Belum ada staff terdaftar dengan role STAFF.</p>
                @endforelse
            </div>

            <div class="p-4 border-t border-[#E8E6F0] bg-[#F4F3FA] flex justify-end shrink-0">
                <button type="button" @click="settingsModalOpen = false" class="clay-btn clay-btn-secondary px-5 py-2 text-xs font-bold">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
