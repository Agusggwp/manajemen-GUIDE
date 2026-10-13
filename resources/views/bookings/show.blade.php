@extends('layouts.app')

@section('title', 'Detail Booking ' . $booking->booking_code)
@section('header', 'Detail Booking: ' . $booking->booking_code)

@section('content')
<div class="space-y-6" x-data="{
    autoModalOpen: false,
    manualModalOpen: false,
    expenseModalOpen: false,
    selectedGuideId: '',
    manualGuestCount: {{ $booking->remainingGuestsCount() }},
    multiAllocations: ''
}">
    <!-- Status & Quick Action Header -->
    <div class="clay-card p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h2 class="text-xl font-black text-[#29283D] font-display">{{ $booking->booking_code }}</h2>
                @if($booking->is_walkin)
                    <span class="px-3 py-1 rounded-full text-xs font-black bg-[#FFD166]/25 text-[#8a6800] border border-[#FFD166]/60 flex items-center gap-1 shadow-xs">
                        <svg class="w-3.5 h-3.5 inline-block -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg> Tour Langsung (Walk-In)
                    </span>
                @endif
                @if($booking->status === 'FULLY_ASSIGNED')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#72D6C9]/20 text-[#147a6f] border border-[#72D6C9]/50">Guide Lengkap</span>
                @elseif($booking->status === 'PARTIALLY_ASSIGNED')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#FFD166]/20 text-[#8a6800] border border-[#FFD166]/50">Guide Sebagian</span>
                @elseif($booking->status === 'ON_GOING')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">Sedang Tur</span>
                @elseif($booking->status === 'COMPLETED')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-[#77768C] border border-[#E8E6F0]">Selesai</span>
                @elseif($booking->status === 'CANCELLED')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">Dibatalkan</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#FFD166]/20 text-[#8a6800] border border-[#FFD166]/50">Menunggu Guide</span>
                @endif
            </div>
            <p class="text-xs text-[#77768C] mt-1.5 font-medium">
                Dibuat oleh: {{ $booking->creator?->name ?? 'System' }} • {{ $booking->created_at->format('d M Y H:i') }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @if($booking->status !== 'CANCELLED' && $booking->status !== 'COMPLETED')
                @if($booking->remainingGuestsCount() > 0)
                <button type="button" @click="autoModalOpen = true"
                        class="clay-btn clay-btn-primary py-2 px-4 text-xs font-bold flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 inline-block -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg><span>Auto Assign Guide</span>
                </button>

                <button type="button" @click="manualModalOpen = true"
                        class="clay-btn clay-btn-secondary py-2 px-3.5 text-xs font-bold text-[#77768C]">
                    Pilih Manual (Smart)
                </button>
                @endif

                <button type="button" @click="expenseModalOpen = true"
                        class="clay-btn clay-btn-coral py-2 px-3.5 text-xs font-bold">
                    + Biaya Operasional
                </button>

                <a href="{{ route('bookings.edit', $booking) }}" class="clay-btn clay-btn-secondary py-2 px-3 text-xs font-bold text-[#77768C]">
                    Edit Booking
                </a>
            @endif
        </div>
    </div>

    <!-- Booking Information & Financial Summary (Requirement 38) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tour & Customer Details (2 Cols) -->
        <div class="lg:col-span-2 clay-card p-6 space-y-4">
            <h3 class="text-sm font-extrabold text-[#29283D] font-display border-b border-[#E8E6F0] pb-3.5">Informasi Tour & Pelanggan</h3>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-[#77768C] block font-bold uppercase tracking-wider text-[10px]">Nama Pelanggan</span>
                    <span class="font-bold text-[#29283D] text-sm mt-0.5 block font-display">{{ $booking->customer_name }}</span>
                    <span class="text-[#77768C] block text-[11px]">{{ $booking->nationality }}</span>
                </div>

                <div>
                    <span class="text-[#77768C] block font-bold uppercase tracking-wider text-[10px]">Nomor Telepon</span>
                    <span class="font-bold text-[#29283D] mt-0.5 block">{{ $booking->customer_phone ?? '-' }}</span>
                </div>

                <div>
                    <span class="text-[#77768C] block font-bold uppercase tracking-wider text-[10px]">Paket Wisata</span>
                    <span class="font-bold text-[#6C63FF] mt-0.5 block">{{ $booking->tourPackage?->name ?? 'Custom Tour' }}</span>
                    <span class="text-[#77768C] block text-[11px]">Durasi: {{ $booking->tourPackage?->duration ?? '-' }}</span>
                </div>

                <div>
                    <span class="text-[#77768C] block font-bold uppercase tracking-wider text-[10px]">Tanggal Tour</span>
                    <span class="font-bold text-[#29283D] mt-0.5 block">{{ $booking->booking_date->format('d F Y') }}</span>
                </div>

                <div>
                    <span class="text-[#77768C] block font-bold uppercase tracking-wider text-[10px]">Jam Pelaksanaan</span>
                    <span class="font-bold text-[#29283D] mt-0.5 block">{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }} WITA</span>
                </div>

                <div>
                    <span class="text-[#77768C] block font-bold uppercase tracking-wider text-[10px]">Jumlah Tamu</span>
                    <span class="font-black text-[#29283D] text-base mt-0.5 block font-display">{{ $booking->guest_count }} orang</span>
                </div>

                <div class="sm:col-span-2">
                    <span class="text-[#77768C] block font-bold uppercase tracking-wider text-[10px]">Lokasi Pickup & Destinasi</span>
                    <span class="font-medium text-[#29283D] mt-0.5 block">
                        Pickup: {{ $booking->pickup_location ?? 'Hotel Lobby' }} &bull; Area: {{ $booking->location ?? 'Bali Area' }}
                    </span>
                </div>

                <div class="sm:col-span-3 pt-3 border-t border-[#E8E6F0]">
                    <span class="text-[#77768C] block font-bold uppercase tracking-wider text-[10px]">Catatan Khusus:</span>
                    <p class="text-[#77768C] italic mt-1 leading-relaxed">{{ $booking->notes ?? 'Tidak ada catatan khusus.' }}</p>
                </div>
            </div>
        </div>

        <!-- Financial Snapshot & Profit Breakdown (1 Col) -->
        <div class="clay-card p-6 space-y-4">
            <h3 class="text-sm font-extrabold text-[#29283D] font-display border-b border-[#E8E6F0] pb-3.5">Kalkulasi Keuntungan (Snapshot)</h3>

            <div class="space-y-3.5 text-xs">
                <div class="flex items-center justify-between pb-2.5 border-b border-[#E8E6F0]">
                    <div>
                        <span class="text-[#77768C] block font-medium">Harga Snapshot / Tamu</span>
                        <span class="text-[10px] text-[#77768C]">Tercatat permanen</span>
                    </div>
                    <span class="font-bold text-[#29283D]">Rp {{ number_format($booking->price_per_guest, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between pb-2.5 border-b border-[#E8E6F0]">
                    <div>
                        <span class="font-bold text-[#29283D] block">Total Revenue</span>
                        <span class="text-[10px] text-[#77768C]">{{ $booking->guest_count }} tamu × Rp {{ number_format($booking->price_per_guest, 0, ',', '.') }}</span>
                    </div>
                    <span class="font-black text-[#6C63FF] text-sm font-display">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between pb-2.5 border-b border-[#E8E6F0]">
                    <div>
                        <span class="font-semibold text-[#FF8A65] block">Total Fee Guide</span>
                        <span class="text-[10px] text-[#77768C]">Sum of assignment fee</span>
                    </div>
                    <span class="font-bold text-[#FF8A65]">- Rp {{ number_format($booking->guide_fee_total, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between pb-2.5 border-b border-[#E8E6F0]">
                    <div>
                        <span class="font-semibold text-rose-700 block">Biaya Operasional</span>
                        <span class="text-[10px] text-[#77768C]">Bensin, tiket, sewa mobil</span>
                    </div>
                    <span class="font-bold text-rose-700">- Rp {{ number_format($booking->operational_cost, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between pt-1 bg-[#72D6C9]/15 p-3.5 rounded-2xl border border-[#72D6C9]/40">
                    <div>
                        <span class="font-extrabold text-[#147a6f] block text-xs">Laba Bersih Perusahaan</span>
                        <span class="text-[10px] text-[#147a6f]/70">Company Profit</span>
                    </div>
                    <span class="font-black text-[#147a6f] text-base font-display">
                        Rp {{ number_format($booking->company_profit, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Guide Assignments Section (Requirement 21, 22, 23, 24) -->
    <div class="clay-card p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#E8E6F0] pb-3.5">
            <div>
                <h3 class="text-sm font-extrabold text-[#29283D] font-display">Alokasi Guide untuk Booking Ini</h3>
                <p class="text-xs text-[#77768C] mt-0.5">
                    Total tamu: <strong>{{ $booking->guest_count }}</strong> • 
                    Dialokasikan: <strong class="text-[#6C63FF]">{{ $booking->assignedGuestsCount() }}</strong> • 
                    Sisa belum dialokasikan: <strong class="{{ $booking->remainingGuestsCount() > 0 ? 'text-[#FF8A65]' : 'text-[#147a6f]' }}">{{ $booking->remainingGuestsCount() }} tamu</strong>
                </p>
            </div>

            @if($booking->remainingGuestsCount() > 0 && $booking->status !== 'CANCELLED')
            <div class="flex items-center gap-2">
                <button type="button" @click="autoModalOpen = true"
                        class="clay-btn clay-btn-primary py-1.5 px-3.5 text-xs font-bold">
                    <svg class="w-3.5 h-3.5 inline-block mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>Auto Assign
                </button>
                <button type="button" @click="manualModalOpen = true"
                        class="clay-btn clay-btn-secondary py-1.5 px-3 text-xs font-bold text-[#77768C]">
                    + Assign Manual
                </button>
            </div>
            @endif
        </div>

        <div class="clay-table-wrapper">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr>
                        <th class="px-4 py-3">No Antrean</th>
                        <th class="px-4 py-3">Guide</th>
                        <th class="px-4 py-3 text-center">Tamu Ditangani</th>
                        <th class="px-4 py-3">Tipe Fee</th>
                        <th class="px-4 py-3 text-right">Fee Rate</th>
                        <th class="px-4 py-3 text-right">Fee Diterima</th>
                        <th class="px-4 py-3">Status Job</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($booking->assignments as $asg)
                    <tr class="hover:bg-[#F4F3FA]/50 transition-colors {{ $asg->status === 'CANCELLED' ? 'opacity-40' : '' }}">
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl text-xs font-black bg-[#6C63FF] text-white shadow-xs">
                                #{{ $asg->queue_position ?? $asg->guide->queue_number }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-bold text-[#29283D]">{{ $asg->guide->nama }}</div>
                            <div class="text-[10px] text-[#77768C] font-mono">{{ $asg->guide->kode_guide }} &bull; {{ $asg->guide->nomor_telepon }}</div>
                        </td>
                        <td class="px-4 py-3 text-center font-black text-[#29283D] text-sm">
                            {{ $asg->guest_count }} orang
                        </td>
                        <td class="px-4 py-3 text-[#77768C] font-semibold">
                            {{ $asg->fee_type }}
                        </td>
                        <td class="px-4 py-3 text-right text-[#77768C]">
                            Rp {{ number_format($asg->fee_rate, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-[#FF8A65]">
                            Rp {{ number_format($asg->fee_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3">
                            @if($asg->status === 'COMPLETED')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-[#77768C] border border-[#E8E6F0]">Selesai</span>
                            @elseif($asg->status === 'ON_GOING')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Sedang Tur</span>
                            @elseif($asg->status === 'CANCELLED')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-500">Dibatalkan</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#6C63FF]/10 text-[#6C63FF] border border-[#6C63FF]/20">Ditugaskan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($asg->status === 'ASSIGNED')
                            <form action="{{ route('assignments.destroy', $asg) }}" method="POST" data-confirm="Batalkan penugasan guide {{ $asg->guide->nama }} dari booking ini?" data-confirm-title="Batalkan Penugasan" data-confirm-danger="true" data-confirm-btn="Ya, Batalkan!">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 text-[11px] font-bold underline">
                                    Batalkan
                                </button>
                            </form>
                            @else
                            <span class="text-slate-400 text-[11px]">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-[#77768C]">
                            Belum ada guide yang ditugaskan untuk booking ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Operational Expenses Section -->
    <div class="clay-card p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3.5">
            <div>
                <h3 class="text-sm font-extrabold text-[#29283D] font-display">Biaya Operasional Tour</h3>
                <p class="text-xs text-[#77768C]">Pengeluaran lapangan yang dikurangkan dari laba booking</p>
            </div>
            <button type="button" @click="expenseModalOpen = true"
                    class="clay-btn clay-btn-coral py-1.5 px-3 text-xs font-bold">
                + Tambah Biaya
            </button>
        </div>

        <div class="clay-table-wrapper">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Deskripsi</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($booking->expenses as $exp)
                    <tr class="hover:bg-[#F4F3FA]/50 transition-colors">
                        <td class="px-4 py-2.5 font-bold text-[#29283D]">{{ $exp->category }}</td>
                        <td class="px-4 py-2.5 text-[#77768C]">{{ $exp->description }}</td>
                        <td class="px-4 py-2.5 text-[#77768C]">{{ $exp->expense_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-2.5 text-right font-black text-rose-600">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-right">
                            <form action="{{ route('expenses.destroy', $exp) }}" method="POST" data-confirm="Hapus pencatatan biaya {{ $exp->category }} (Rp {{ number_format($exp->amount, 0, ',', '.') }})?" data-confirm-title="Hapus Biaya Operasional" data-confirm-danger="true" data-confirm-btn="Ya, Hapus!">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 text-[11px] font-bold underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-[#77768C]">Belum ada pengeluaran operasional dicatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL 1: Auto Assign Guide Modal -->
    <div x-show="autoModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#29283D]/40 backdrop-blur-sm">
        <div @click.away="autoModalOpen = false" class="clay-card max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3.5">
                <h3 class="text-sm font-extrabold text-[#29283D] font-display"><svg class="w-4 h-4 text-[#6C63FF] inline-block mr-1.5 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>Auto Assign Guide (Continuous Queue)</h3>
                <button type="button" @click="autoModalOpen = false" class="text-[#77768C] hover:text-[#29283D] font-bold text-lg">&times;</button>
            </div>

            <p class="text-xs text-[#77768C] leading-relaxed">
                Sistem akan mencari guide berikutnya berdasarkan <strong>Continuous Circular Queue</strong>. 
                Guide yang OFF, belum check-in, checkout, atau jadwal bentrok akan dilewati secara otomatis tanpa kehilangan giliran.
            </p>

            <form action="{{ route('bookings.auto-assign', $booking) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-[#29283D] mb-1.5">Jumlah Tamu yang Dialokasikan</label>
                    <input type="number" name="guest_count" value="{{ $booking->remainingGuestsCount() }}" min="1" max="{{ $booking->remainingGuestsCount() }}"
                           class="clay-input text-xs font-bold text-[#29283D]">
                    <span class="text-[10px] text-[#77768C] block mt-1">Sisa tamu yang belum dialokasikan: {{ $booking->remainingGuestsCount() }} orang.</span>
                </div>

                <!-- Multi-guide option -->
                <div class="p-4 bg-[#F4F3FA] rounded-2xl border border-[#E8E6F0]">
                    <label class="block text-xs font-bold text-[#29283D] mb-1.5">Atau Alokasi Multi-Guide (Opsional)</label>
                    <input type="text" name="multi_allocations" placeholder="Contoh: 20, 15, 15"
                           class="clay-input text-xs">
                    <span class="text-[10px] text-[#77768C] block mt-1.5 leading-relaxed">
                        Masukkan alokasi tamu dipisah koma (e.g. 50 tamu dibagi: <strong>20, 15, 15</strong>). 
                        Sistem akan mengalokasikan 3 guide secara berurutan sesuai rotasi circular!
                    </span>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="autoModalOpen = false" class="clay-btn clay-btn-secondary px-3.5 py-2 text-xs font-bold text-[#77768C]">
                        Batal
                    </button>
                    <button type="submit" class="clay-btn clay-btn-primary px-4 py-2 text-xs font-bold">
                        Jalankan Auto Assign &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Smart Manual Guide Assignment (Requirement 40) -->
    <div x-show="manualModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#29283D]/40 backdrop-blur-sm">
        <div @click.away="manualModalOpen = false" class="clay-card max-w-2xl w-full p-6 space-y-4 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3.5">
                <div>
                    <h3 class="text-sm font-extrabold text-[#29283D] font-display">Smart Guide Assignment</h3>
                    <p class="text-xs text-[#77768C]">Pilih guide secara manual dengan live availability & conflict check</p>
                </div>
                <button type="button" @click="manualModalOpen = false" class="text-[#77768C] hover:text-[#29283D] font-bold text-lg">&times;</button>
            </div>

            <form action="{{ route('bookings.manual-assign', $booking) }}" method="POST" class="space-y-4 flex-1 overflow-y-auto pr-1">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-bold text-[#29283D] mb-1.5">Jumlah Tamu yang Dialokasikan</label>
                    <input type="number" name="guest_count" x-model="manualGuestCount" min="1" max="{{ $booking->remainingGuestsCount() }}" required
                           class="clay-input text-xs font-bold text-[#29283D]">
                </div>

                <div class="space-y-2.5">
                    <label class="block text-xs font-bold text-[#29283D]">Pilih Guide:</label>
                    @foreach($allGuides as $gItem)
                    <label class="flex items-center justify-between p-3.5 rounded-2xl border transition-all {{ $gItem->has_conflict ? 'border-rose-200 bg-rose-50/40 opacity-75' : ($gItem->is_available ? 'border-[#E8E6F0] hover:border-[#6C63FF] bg-white shadow-2xs' : 'border-slate-200 bg-slate-50 opacity-60') }} cursor-pointer">
                        <div class="flex items-center space-x-3">
                            <input type="radio" name="guide_id" value="{{ $gItem->guide->id }}" 
                                   {{ (!$gItem->is_available || $gItem->has_conflict) ? 'disabled' : '' }}
                                   class="text-[#6C63FF] focus:ring-[#6C63FF]">
                            <div>
                                <div class="font-bold text-xs text-[#29283D] flex items-center gap-1.5">
                                    <span class="bg-[#6C63FF] text-white px-2 py-0.5 rounded-full text-[10px] font-black">#{{ $gItem->guide->queue_number }}</span>
                                    <span>{{ $gItem->guide->nama }}</span>
                                    <span class="text-[#77768C] font-normal font-mono">({{ $gItem->guide->kode_guide }})</span>
                                </div>
                                <div class="text-[10px] text-[#77768C] mt-0.5">
                                    Today: {{ $gItem->today_jobs }} jobs &bull; {{ $gItem->today_guests }} guests &bull; Rp {{ number_format($gItem->today_earnings, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        <div class="text-right text-[11px]">
                            @if($gItem->has_conflict)
                                <span class="font-bold text-rose-600 flex items-center justify-end gap-1"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span>Jadwal bertabrakan</span></span>
                            @elseif(!$gItem->is_checked_in)
                                <span class="text-[#FF8A65] font-semibold block">Belum check-in</span>
                            @elseif($gItem->is_already_assigned)
                                <span class="text-[#6C63FF] font-semibold block">Sudah dialokasikan</span>
                            @elseif($gItem->guide->status === 'OFF')
                                <span class="text-rose-600 font-semibold block">OFF</span>
                            @else
                                <span class="font-bold text-[#147a6f] flex items-center justify-end gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg><span>Guide tersedia</span></span>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-[#E8E6F0]">
                    <button type="button" @click="manualModalOpen = false" class="clay-btn clay-btn-secondary px-3.5 py-2 text-xs font-bold text-[#77768C]">
                        Batal
                    </button>
                    <button type="submit" class="clay-btn clay-btn-primary px-4 py-2 text-xs font-bold">
                        Assign Guide &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: Add Operational Expense Modal -->
    <div x-show="expenseModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#29283D]/40 backdrop-blur-sm">
        <div @click.away="expenseModalOpen = false" class="clay-card max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3.5">
                <h3 class="text-sm font-extrabold text-[#29283D] font-display">+ Catat Biaya Operasional</h3>
                <button type="button" @click="expenseModalOpen = false" class="text-[#77768C] hover:text-[#29283D] font-bold text-lg">&times;</button>
            </div>

            <form action="{{ route('expenses.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                <input type="hidden" name="expense_date" value="{{ $booking->booking_date->toDateString() }}">

                <div>
                    <label class="block text-xs font-bold text-[#29283D] mb-1.5">Kategori Biaya</label>
                    <select name="category" required class="clay-input text-xs font-bold">
                        @foreach(\App\Models\Setting::getExpenseCategories() as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#29283D] mb-1.5">Deskripsi Biaya</label>
                    <input type="text" name="description" required placeholder="Contoh: Tiket Pura Uluwatu 12 orang"
                           class="clay-input text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#29283D] mb-1.5">Nominal (Rp)</label>
                    <input type="number" name="amount" min="1000" step="1000" required placeholder="150000"
                           class="clay-input text-xs font-black text-[#29283D]">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="expenseModalOpen = false" class="clay-btn clay-btn-secondary px-3.5 py-2 text-xs font-bold text-[#77768C]">
                        Batal
                    </button>
                    <button type="submit" class="clay-btn clay-btn-primary px-4 py-2 text-xs font-bold">
                        Simpan Biaya
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

