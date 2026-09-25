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
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h2 class="text-xl font-bold text-slate-900">{{ $booking->booking_code }}</h2>
                @if($booking->is_walkin)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 flex items-center gap-1">
                        <span>⚡</span> Tour Langsung (Walk-In)
                    </span>
                @endif
                @if($booking->status === 'FULLY_ASSIGNED')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Guide Lengkap</span>
                @elseif($booking->status === 'PARTIALLY_ASSIGNED')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Guide Sebagian</span>
                @elseif($booking->status === 'ON_GOING')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">Sedang Tur</span>
                @elseif($booking->status === 'COMPLETED')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">Selesai</span>
                @elseif($booking->status === 'CANCELLED')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">Dibatalkan</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Menunggu Guide</span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Dibuat oleh: {{ $booking->creator?->name ?? 'System' }} • {{ $booking->created_at->format('d M Y H:i') }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if($booking->status !== 'CANCELLED' && $booking->status !== 'COMPLETED')
                @if($booking->remainingGuestsCount() > 0)
                <button type="button" @click="autoModalOpen = true"
                        class="px-4 py-2 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-bold transition-colors shadow-2xs flex items-center gap-1.5">
                    <span>⚡ Auto Assign Guide</span>
                </button>

                <button type="button" @click="manualModalOpen = true"
                        class="px-3 py-2 rounded-lg bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition-colors">
                    Pilih Manual (Smart)
                </button>
                @endif

                <button type="button" @click="expenseModalOpen = true"
                        class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition-colors">
                    + Biaya Operasional
                </button>

                <a href="{{ route('bookings.edit', $booking) }}" class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-medium">
                    Edit Booking
                </a>
            @endif
        </div>
    </div>

    <!-- Booking Information & Financial Summary (Requirement 38) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tour & Customer Details (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Informasi Tour & Pelanggan</h3>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block font-medium">Nama Pelanggan</span>
                    <span class="font-bold text-slate-800 text-sm">{{ $booking->customer_name }}</span>
                    <span class="text-slate-500 block text-[11px]">{{ $booking->nationality }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block font-medium">Nomor Telepon</span>
                    <span class="font-semibold text-slate-800">{{ $booking->customer_phone ?? '-' }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block font-medium">Paket Wisata</span>
                    <span class="font-bold text-[#14433b]">{{ $booking->tourPackage?->name ?? 'Custom Tour' }}</span>
                    <span class="text-slate-500 block text-[11px]">Durasi: {{ $booking->tourPackage?->duration ?? '-' }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block font-medium">Tanggal Tour</span>
                    <span class="font-semibold text-slate-800">{{ $booking->booking_date->format('d F Y') }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block font-medium">Jam Pelaksanaan</span>
                    <span class="font-semibold text-slate-800">{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }} WITA</span>
                </div>

                <div>
                    <span class="text-slate-400 block font-medium">Jumlah Tamu</span>
                    <span class="font-extrabold text-slate-900 text-sm">{{ $booking->guest_count }} orang</span>
                </div>

                <div class="sm:col-span-2">
                    <span class="text-slate-400 block font-medium">Lokasi Pickup & Destinasi</span>
                    <span class="font-medium text-slate-800">
                        Pickup: {{ $booking->pickup_location ?? 'Hotel Lobby' }} &bull; Area: {{ $booking->location ?? 'Bali Area' }}
                    </span>
                </div>

                <div class="sm:col-span-3 pt-2 border-t border-slate-100">
                    <span class="text-slate-400 block font-medium">Catatan Khusus:</span>
                    <p class="text-slate-600 italic mt-0.5">{{ $booking->notes ?? 'Tidak ada catatan khusus.' }}</p>
                </div>
            </div>
        </div>

        <!-- Financial Snapshot & Profit Breakdown (1 Col) -->
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Kalkulasi Keuntungan (Snapshot)</h3>

            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div>
                        <span class="text-slate-600 block">Harga Snapshot / Tamu</span>
                        <span class="text-[10px] text-slate-400">Tercatat permanen</span>
                    </div>
                    <span class="font-semibold text-slate-800">Rp {{ number_format($booking->price_per_guest, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div>
                        <span class="font-bold text-slate-800 block">Total Revenue</span>
                        <span class="text-[10px] text-slate-400">{{ $booking->guest_count }} tamu × Rp {{ number_format($booking->price_per_guest, 0, ',', '.') }}</span>
                    </div>
                    <span class="font-bold text-[#14433b] text-sm">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div>
                        <span class="font-semibold text-amber-700 block">Total Fee Guide</span>
                        <span class="text-[10px] text-slate-400">Sum of assignment fee</span>
                    </div>
                    <span class="font-bold text-amber-700">- Rp {{ number_format($booking->guide_fee_total, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div>
                        <span class="font-semibold text-rose-700 block">Biaya Operasional</span>
                        <span class="text-[10px] text-slate-400">Bensin, tiket, sewa mobil</span>
                    </div>
                    <span class="font-bold text-rose-700">- Rp {{ number_format($booking->operational_cost, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between pt-1 bg-[#e6faf5] p-3 rounded-lg border border-[#21c9a4]/40">
                    <div>
                        <span class="font-bold text-[#14433b] block text-xs">Laba Bersih Perusahaan</span>
                        <span class="text-[10px] text-[#14433b]/70">Company Profit</span>
                    </div>
                    <span class="font-extrabold text-[#14433b] text-base">
                        Rp {{ number_format($booking->company_profit, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Guide Assignments Section (Requirement 21, 22, 23, 24) -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Alokasi Guide untuk Booking Ini</h3>
                <p class="text-xs text-slate-500">
                    Total tamu: <strong>{{ $booking->guest_count }}</strong> • 
                    Dialokasikan: <strong class="text-[#14433b]">{{ $booking->assignedGuestsCount() }}</strong> • 
                    Sisa belum dialokasikan: <strong class="{{ $booking->remainingGuestsCount() > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $booking->remainingGuestsCount() }} tamu</strong>
                </p>
            </div>

            @if($booking->remainingGuestsCount() > 0 && $booking->status !== 'CANCELLED')
            <div class="flex items-center gap-2">
                <button type="button" @click="autoModalOpen = true"
                        class="px-3 py-1.5 rounded-md text-xs font-bold bg-[#14433b] hover:bg-[#1b584d] text-white shadow-2xs">
                    ⚡ Auto Assign
                </button>
                <button type="button" @click="manualModalOpen = true"
                        class="px-3 py-1.5 rounded-md text-xs font-semibold border border-slate-300 text-slate-700 hover:bg-slate-50">
                    + Assign Manual
                </button>
            </div>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-2.5">No Antrean</th>
                        <th class="px-4 py-2.5">Guide</th>
                        <th class="px-4 py-2.5 text-center">Tamu Ditangani</th>
                        <th class="px-4 py-2.5">Tipe Fee</th>
                        <th class="px-4 py-2.5 text-right">Fee Rate</th>
                        <th class="px-4 py-2.5 text-right">Fee Diterima</th>
                        <th class="px-4 py-2.5">Status Job</th>
                        <th class="px-4 py-2.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($booking->assignments as $asg)
                    <tr class="hover:bg-slate-50/70 transition-colors {{ $asg->status === 'CANCELLED' ? 'opacity-40' : '' }}">
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md text-xs font-bold bg-slate-100 text-slate-800">
                                #{{ $asg->queue_position ?? $asg->guide->queue_number }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-bold text-slate-900">{{ $asg->guide->nama }}</div>
                            <div class="text-[10px] text-slate-400">{{ $asg->guide->kode_guide }} &bull; {{ $asg->guide->nomor_telepon }}</div>
                        </td>
                        <td class="px-4 py-3 text-center font-extrabold text-slate-800 text-sm">
                            {{ $asg->guest_count }} orang
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $asg->fee_type }}
                        </td>
                        <td class="px-4 py-3 text-right text-slate-600">
                            Rp {{ number_format($asg->fee_rate, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-amber-700">
                            Rp {{ number_format($asg->fee_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3">
                            @if($asg->status === 'COMPLETED')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">Selesai</span>
                            @elseif($asg->status === 'ON_GOING')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Sedang Tur</span>
                            @elseif($asg->status === 'CANCELLED')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-500">Dibatalkan</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">Ditugaskan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($asg->status === 'ASSIGNED')
                            <form action="{{ route('assignments.destroy', $asg) }}" method="POST" data-confirm="Batalkan penugasan guide {{ $asg->guide->nama }} dari booking ini?" data-confirm-title="Batalkan Penugasan" data-confirm-danger="true" data-confirm-btn="Ya, Batalkan!">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 text-[11px] font-medium">
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
                        <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                            Belum ada guide yang ditugaskan untuk booking ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Operational Expenses Section -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Biaya Operasional Tour</h3>
                <p class="text-xs text-slate-500">Pengeluaran lapangan yang dikurangkan dari laba booking</p>
            </div>
            <button type="button" @click="expenseModalOpen = true"
                    class="px-3 py-1.5 rounded-md text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700">
                + Tambah Biaya
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-2">Kategori</th>
                        <th class="px-4 py-2">Deskripsi</th>
                        <th class="px-4 py-2">Tanggal</th>
                        <th class="px-4 py-2 text-right">Nominal</th>
                        <th class="px-4 py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($booking->expenses as $exp)
                    <tr>
                        <td class="px-4 py-2.5 font-medium text-slate-800">{{ $exp->category }}</td>
                        <td class="px-4 py-2.5 text-slate-600">{{ $exp->description }}</td>
                        <td class="px-4 py-2.5 text-slate-500">{{ $exp->expense_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-rose-700">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-right">
                            <form action="{{ route('expenses.destroy', $exp) }}" method="POST" data-confirm="Hapus pencatatan biaya {{ $exp->category }} (Rp {{ number_format($exp->amount, 0, ',', '.') }})?" data-confirm-title="Hapus Biaya Operasional" data-confirm-danger="true" data-confirm-btn="Ya, Hapus!">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 text-[11px]">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-slate-400">Belum ada pengeluaran operasional dicatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL 1: Auto Assign Guide Modal -->
    <div x-show="autoModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="autoModalOpen = false" class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">⚡ Auto Assign Guide (Continuous Queue)</h3>
                <button type="button" @click="autoModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Sistem akan mencari guide berikutnya berdasarkan <strong>Continuous Circular Queue</strong>. 
                Guide yang OFF, belum check-in, checkout, atau jadwal bentrok akan dilewati secara otomatis tanpa kehilangan giliran.
            </p>

            <form action="{{ route('bookings.auto-assign', $booking) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Tamu yang Dialokasikan</label>
                    <input type="number" name="guest_count" value="{{ $booking->remainingGuestsCount() }}" min="1" max="{{ $booking->remainingGuestsCount() }}"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:ring-1 focus:ring-[#21c9a4] focus:outline-hidden">
                    <span class="text-[10px] text-slate-400 block mt-0.5">Sisa tamu yang belum dialokasikan: {{ $booking->remainingGuestsCount() }} orang.</span>
                </div>

                <!-- Multi-guide option -->
                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Atau Alokasi Multi-Guide (Opsional)</label>
                    <input type="text" name="multi_allocations" placeholder="Contoh: 20, 15, 15"
                           class="w-full px-3 py-2 bg-white border border-slate-300 rounded-md text-xs focus:ring-1 focus:ring-[#21c9a4] focus:outline-hidden">
                    <span class="text-[10px] text-slate-500 block mt-1">
                        Masukkan alokasi tamu dipisah koma (e.g. 50 tamu dibagi: <strong>20, 15, 15</strong>). 
                        Sistem akan mengalokasikan 3 guide secara berurutan sesuai rotasi circular!
                    </span>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="autoModalOpen = false" class="px-3 py-2 border border-slate-300 rounded-lg text-xs font-semibold text-slate-600">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-bold transition-colors shadow-xs">
                        Jalankan Auto Assign &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Smart Manual Guide Assignment (Requirement 40) -->
    <div x-show="manualModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="manualModalOpen = false" class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-2xl w-full p-6 space-y-4 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Smart Guide Assignment</h3>
                    <p class="text-xs text-slate-500">Pilih guide secara manual dengan live availability & conflict check</p>
                </div>
                <button type="button" @click="manualModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form action="{{ route('bookings.manual-assign', $booking) }}" method="POST" class="space-y-4 flex-1 overflow-y-auto">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Tamu yang Dialokasikan</label>
                    <input type="number" name="guest_count" x-model="manualGuestCount" min="1" max="{{ $booking->remainingGuestsCount() }}" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:ring-1 focus:ring-[#21c9a4] focus:outline-hidden">
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-slate-700">Pilih Guide:</label>
                    @foreach($allGuides as $gItem)
                    <label class="flex items-center justify-between p-3 rounded-lg border {{ $gItem->has_conflict ? 'border-rose-200 bg-rose-50/40 opacity-75' : ($gItem->is_available ? 'border-slate-200 hover:border-[#21c9a4] bg-white' : 'border-slate-200 bg-slate-50 opacity-60') }} cursor-pointer transition-all">
                        <div class="flex items-center space-x-3">
                            <input type="radio" name="guide_id" value="{{ $gItem->guide->id }}" 
                                   {{ (!$gItem->is_available || $gItem->has_conflict) ? 'disabled' : '' }}
                                   class="text-[#14433b] focus:ring-[#21c9a4]">
                            <div>
                                <div class="font-bold text-xs text-slate-900 flex items-center gap-1.5">
                                    <span class="bg-[#14433b] text-[#21c9a4] px-1.5 py-0.2 rounded text-[10px]">#{{ $gItem->guide->queue_number }}</span>
                                    <span>{{ $gItem->guide->nama }}</span>
                                    <span class="text-slate-400 font-normal">({{ $gItem->guide->kode_guide }})</span>
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5">
                                    Today: {{ $gItem->today_jobs }} jobs &bull; {{ $gItem->today_guests }} guests &bull; Rp {{ number_format($gItem->today_earnings, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        <div class="text-right text-[11px]">
                            @if($gItem->has_conflict)
                                <span class="font-bold text-rose-600 block">⚠ Jadwal bertabrakan</span>
                            @elseif(!$gItem->is_checked_in)
                                <span class="text-amber-600 block">Belum check-in</span>
                            @elseif($gItem->is_already_assigned)
                                <span class="text-indigo-600 block">Sudah dialokasikan</span>
                            @elseif($gItem->guide->status === 'OFF')
                                <span class="text-rose-600 block">OFF</span>
                            @else
                                <span class="font-bold text-emerald-600 block">✓ Guide tersedia</span>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="manualModalOpen = false" class="px-3 py-2 border border-slate-300 rounded-lg text-xs font-semibold text-slate-600">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-bold transition-colors shadow-xs">
                        Assign Guide &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: Add Operational Expense Modal -->
    <div x-show="expenseModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="expenseModalOpen = false" class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">+ Catat Biaya Operasional</h3>
                <button type="button" @click="expenseModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form action="{{ route('expenses.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                <input type="hidden" name="expense_date" value="{{ $booking->booking_date->toDateString() }}">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Biaya</label>
                    <select name="category" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md text-xs focus:bg-white focus:border-[#14433b] outline-none">
                        @foreach(\App\Models\Setting::getExpenseCategories() as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Biaya</label>
                    <input type="text" name="description" required placeholder="Contoh: Tiket Pura Uluwatu 12 orang"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal (Rp)</label>
                    <input type="number" name="amount" min="1000" step="1000" required placeholder="150000"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md text-xs font-bold text-slate-900">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="expenseModalOpen = false" class="px-3 py-2 border border-slate-300 rounded-lg text-xs font-semibold text-slate-600">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-bold transition-colors shadow-xs">
                        Simpan Biaya
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
