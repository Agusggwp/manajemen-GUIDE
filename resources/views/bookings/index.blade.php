@extends('layouts.app')

@section('title', 'Bookings')
@section('header', 'Daftar Booking & Tamu')

@section('content')
<div class="space-y-6">
    <!-- Header Controls & Filters -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Manajemen Booking Wisata</h2>
                <p class="text-xs text-slate-500">Kelola reservasi tamu, jadwal tour, dan alokasi guide operasional</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('direct-tours.create') }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-amber-400 hover:bg-amber-300 text-slate-900 text-xs font-bold tracking-wide transition shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    <span>⚡ Tour Langsung (Walk-In)</span>
                </a>
                <a href="{{ route('bookings.create') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-bold tracking-wide transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Buat Booking Baru</span>
                </a>
            </div>
        </div>

        <form action="{{ route('bookings.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Cari Pelanggan / Kode</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode, nama, no HP..." 
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md focus:ring-1 focus:ring-[#21c9a4] focus:outline-hidden">
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Tanggal Booking</label>
                <input type="date" name="date" value="{{ request('date') }}" 
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md focus:ring-1 focus:ring-[#21c9a4] focus:outline-hidden">
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Status Booking</label>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md focus:ring-1 focus:ring-[#21c9a4] focus:outline-hidden">
                    <option value="">Semua Status</option>
                    <option value="WAITING" {{ request('status') === 'WAITING' ? 'selected' : '' }}>Menunggu Guide</option>
                    <option value="PARTIALLY_ASSIGNED" {{ request('status') === 'PARTIALLY_ASSIGNED' ? 'selected' : '' }}>Guide Sebagian</option>
                    <option value="FULLY_ASSIGNED" {{ request('status') === 'FULLY_ASSIGNED' ? 'selected' : '' }}>Guide Lengkap</option>
                    <option value="ON_GOING" {{ request('status') === 'ON_GOING' ? 'selected' : '' }}>Sedang Tur</option>
                    <option value="COMPLETED" {{ request('status') === 'COMPLETED' ? 'selected' : '' }}>Selesai</option>
                    <option value="CANCELLED" {{ request('status') === 'CANCELLED' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-md font-semibold transition-colors">
                    Filter Data
                </button>
                @if(request()->hasAny(['search', 'date', 'status']))
                <a href="{{ route('bookings.index') }}" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-md font-medium transition-colors">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Bookings Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5">Kode & Tanggal</th>
                        <th class="px-4 py-3.5">Pelanggan</th>
                        <th class="px-4 py-3.5">Paket Tour & Lokasi</th>
                        <th class="px-4 py-3.5 text-center">Tamu</th>
                        <th class="px-4 py-3.5">Guide Ditugaskan</th>
                        <th class="px-4 py-3.5 text-right">Total Harga</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bookings as $b)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-1.5">
                                @if($b->is_walkin)
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300">⚡ Walk-in</span>
                                @endif
                                <a href="{{ route('bookings.show', $b) }}" class="font-bold text-slate-900 hover:text-[#14433b]">
                                    {{ $b->booking_code }}
                                </a>
                            </div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                {{ $b->booking_date->format('d M Y') }} • {{ substr($b->start_time, 0, 5) }} WITA
                            </div>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="font-semibold text-slate-800">{{ $b->customer_name }}</div>
                            <div class="text-[10px] text-slate-400">
                                {{ $b->customer_phone ?? '-' }} • {{ $b->nationality }}
                            </div>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="font-medium text-slate-800">{{ $b->tourPackage?->name ?? 'Custom Tour' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $b->location ?? 'Bali' }}</div>
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-block px-2 py-0.5 rounded-full font-bold text-slate-800 bg-slate-100 border border-slate-200">
                                {{ $b->guest_count }} org
                            </span>
                            @if($b->remainingGuestsCount() > 0 && $b->status !== 'CANCELLED')
                                <span class="text-[9px] text-amber-600 block mt-0.5">Sisa: {{ $b->remainingGuestsCount() }}</span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="flex flex-wrap gap-1 max-w-[190px]">
                                @forelse($b->assignments as $asg)
                                    @if($asg->status !== 'CANCELLED')
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-[#e6faf5] text-[#14433b] border border-[#21c9a4]/40" title="Fee: Rp {{ number_format($asg->fee_amount, 0, ',', '.') }}">
                                        #{{ $asg->guide->queue_number }} {{ $asg->guide->nama }} ({{ $asg->guest_count }}p)
                                    </span>
                                    @endif
                                @empty
                                    <span class="text-slate-400 italic text-[11px]">Belum ada guide</span>
                                @endforelse
                            </div>
                        </td>

                        <td class="px-4 py-3.5 text-right font-bold text-slate-900">
                            Rp {{ number_format($b->total_price, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3.5">
                            @if($b->status === 'FULLY_ASSIGNED')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Guide Lengkap</span>
                            @elseif($b->status === 'PARTIALLY_ASSIGNED')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Guide Sebagian</span>
                            @elseif($b->status === 'ON_GOING')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Sedang Tur</span>
                            @elseif($b->status === 'COMPLETED')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">Selesai</span>
                            @elseif($b->status === 'CANCELLED')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-400">Dibatalkan</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">Menunggu Guide</span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 text-right space-x-1">
                            <a href="{{ route('bookings.show', $b) }}" 
                               class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold bg-[#14433b] hover:bg-[#1b584d] text-white transition-colors">
                                Detail & Guide
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                            Tidak ada booking yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
            {{ $bookings->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
