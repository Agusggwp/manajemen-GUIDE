@extends('layouts.app')

@section('title', 'Slip Gaji Saya')
@section('header', 'Informasi Gaji & Slip Saya')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header Card -->
    <div class="clay-card bg-gradient-to-r from-[#6C63FF] to-[#837BFF] p-6 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs uppercase tracking-wider text-[#FFD166] font-extrabold block">PORTAL KARYAWAN</span>
            <h2 class="text-xl font-black text-white mt-0.5">{{ $user->name }}</h2>
            <p class="text-xs text-white/80 mt-1">{{ $user->position ?: 'Staff Operasional' }} &bull; {{ $user->email }}</p>
        </div>

        <div class="text-left sm:text-right bg-white/15 p-4 rounded-2xl border border-white/20 backdrop-blur-xs">
            <span class="text-[11px] text-white/80 block uppercase font-bold">Total Akumulasi Gaji Diterima</span>
            <span class="text-2xl font-black text-white block mt-0.5">Rp {{ number_format($totalEarned, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Quick info: Rekening Bank Terdaftar -->
    <div class="clay-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-purple-100 text-[#6C63FF] flex items-center justify-center font-bold text-lg shadow-inner">
                🏦
            </div>
            <div>
                <span class="text-[10px] text-[#77768C] block font-bold uppercase">Rekening Bank Terdaftar untuk Payroll</span>
                @if($user->bank_name && $user->bank_account_number)
                    <div class="font-bold text-[#29283D] mt-0.5">
                        {{ $user->bank_name }} - {{ $user->bank_account_number }}
                        <span class="font-normal text-[#77768C]">(a.n {{ $user->bank_account_holder ?: $user->name }})</span>
                    </div>
                @else
                    <span class="text-[#77768C] italic">Belum ada data rekening bank. Hubungi admin atau manajemen untuk mendaftarkan rekening.</span>
                @endif
            </div>
        </div>

        @if($latestPayroll)
        <div class="text-right border-t sm:border-t-0 sm:border-l border-[#E8E6F0] pt-2 sm:pt-0 sm:pl-4">
            <span class="text-[10px] text-[#77768C] block uppercase font-bold">Slip Terakhir</span>
            <span class="font-bold text-[#6C63FF]">{{ $latestPayroll->period_name }}</span>
        </div>
        @endif
    </div>

    <!-- Table of My Salary Slips -->
    <div class="clay-card p-0 overflow-hidden">
        <div class="p-5 border-b border-[#E8E6F0] flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-[#29283D]">Riwayat Slip Gaji</h3>
                <p class="text-xs text-[#77768C]">Daftar slip gaji bulanan Anda</p>
            </div>
            <span class="text-xs text-[#77768C] font-semibold px-3 py-1 rounded-full bg-[#F4F3FA]">{{ $payrolls->count() }} slip gaji</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F4F3FA] text-[#77768C] font-bold border-b border-[#E8E6F0]">
                    <tr>
                        <th class="px-5 py-3.5">Kode Slip</th>
                        <th class="px-5 py-3.5">Periode</th>
                        <th class="px-5 py-3.5 text-right">Gaji Pokok</th>
                        <th class="px-5 py-3.5 text-right">Tunjangan</th>
                        <th class="px-5 py-3.5 text-right">Bonus / Lembur</th>
                        <th class="px-5 py-3.5 text-right">Potongan</th>
                        <th class="px-5 py-3.5 text-right">Gaji Bersih</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($payrolls as $row)
                    <tr class="hover:bg-purple-50/30 transition-colors">
                        <td class="px-5 py-3.5 font-mono font-bold text-[#29283D]">
                            {{ $row->payroll_code }}
                        </td>

                        <td class="px-5 py-3.5 font-bold text-[#6C63FF]">
                            {{ $row->period_name }}
                        </td>

                        <td class="px-5 py-3.5 text-right text-[#29283D] font-medium">
                            Rp {{ number_format($row->base_salary, 0, ',', '.') }}
                        </td>

                        <td class="px-5 py-3.5 text-right text-[#29283D] font-medium">
                            Rp {{ number_format($row->allowance, 0, ',', '.') }}
                        </td>

                        <td class="px-5 py-3.5 text-right text-[#6C63FF] font-bold">
                            {{ $row->bonus > 0 ? '+ Rp ' . number_format($row->bonus, 0, ',', '.') : '-' }}
                        </td>

                        <td class="px-5 py-3.5 text-right text-rose-600 font-bold">
                            {{ $row->deduction > 0 ? '- Rp ' . number_format($row->deduction, 0, ',', '.') : '-' }}
                        </td>

                        <td class="px-5 py-3.5 text-right font-black text-[#29283D] text-sm">
                            Rp {{ number_format($row->net_salary, 0, ',', '.') }}
                        </td>

                        <td class="px-5 py-3.5 text-center">
                            @if($row->isPaid())
                                <span class="clay-badge bg-emerald-50 text-emerald-700 border-emerald-200">
                                    <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    LUNAS
                                </span>
                                <div class="text-[10px] text-[#77768C] mt-0.5">
                                    {{ $row->payment_date?->format('d/m/Y') }}
                                </div>
                            @else
                                <span class="clay-badge bg-amber-50 text-amber-700 border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    PROSES / BELUM BAYAR
                                </span>
                            @endif
                        </td>

                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('staff-payroll.show', $row) }}" 
                               class="clay-btn-primary px-3.5 py-1.5 text-xs font-bold inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#FFD166]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Lihat & Cetak Slip</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-[#77768C]">
                            <svg class="w-10 h-10 mx-auto mb-2 text-[#77768C]/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-sm font-bold text-[#29283D]">Belum ada slip gaji yang diterbitkan untuk Anda.</p>
                            <p class="text-xs text-[#77768C] mt-1">Slip gaji akan muncul di sini setelah diterbitkan oleh bagian keuangan atau admin.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
