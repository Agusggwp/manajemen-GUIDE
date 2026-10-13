@extends('layouts.app')

@section('title', 'Bookings')
@section('header', 'Daftar Booking & Tamu')

@section('content')
<div class="space-y-6">
    <!-- Header Controls & Filters -->
    <div class="clay-card p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-base font-extrabold text-[#29283D] font-display">Manajemen Booking Wisata</h2>
                <p class="text-xs text-[#77768C]">Kelola reservasi tamu, jadwal tour, dan alokasi guide operasional</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('direct-tours.create') }}" 
                   class="clay-btn clay-btn-coral py-2 px-3.5 text-xs font-bold tracking-wide flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    <span>⚡ Tour Langsung (Walk-In)</span>
                </a>
                <a href="{{ route('bookings.create') }}" 
                   class="clay-btn clay-btn-primary py-2 px-3.5 text-xs font-bold tracking-wide flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Buat Booking Baru</span>
                </a>
            </div>
        </div>

        <form action="{{ route('bookings.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block text-[11px] font-bold text-[#77768C] mb-1.5">Cari Pelanggan / Kode</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode, nama, no HP..." 
                       class="clay-input text-xs">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-[#77768C] mb-1.5">Tanggal Booking</label>
                <input type="date" name="date" value="{{ request('date') }}" 
                       class="clay-input text-xs">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-[#77768C] mb-1.5">Status Booking</label>
                <select name="status" class="clay-input text-xs">
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
                <button type="submit" class="clay-btn clay-btn-primary w-full py-2.5 px-4 text-xs font-bold">
                    Filter Data
                </button>
                @if(request()->hasAny(['search', 'date', 'status']))
                <a href="{{ route('bookings.index') }}" class="clay-btn clay-btn-secondary px-3 py-2.5 text-xs font-bold text-[#77768C]">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Bookings Table -->
    <div class="clay-card p-6 overflow-hidden">
        <div class="clay-table-wrapper">
            <table class="w-full text-left text-xs">
                <thead>
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
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($bookings as $b)
                    <tr class="hover:bg-[#F4F3FA]/50 transition-colors">
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-1.5">
                                @if($b->is_walkin)
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-[#FFD166]/25 text-[#8a6800] border border-[#FFD166]/60">⚡ Walk-in</span>
                                @endif
                                <a href="{{ route('bookings.show', $b) }}" class="font-bold text-[#6C63FF] hover:underline">
                                    {{ $b->booking_code }}
                                </a>
                            </div>
                            <div class="text-[11px] text-[#77768C] mt-0.5">
                                {{ $b->booking_date->format('d M Y') }} • {{ substr($b->start_time, 0, 5) }} WITA
                            </div>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="font-bold text-[#29283D]">{{ $b->customer_name }}</div>
                            <div class="text-[10px] text-[#77768C]">
                                {{ $b->customer_phone ?? '-' }} • {{ $b->nationality }}
                            </div>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="font-semibold text-[#29283D]">{{ $b->tourPackage?->name ?? 'Custom Tour' }}</div>
                            <div class="text-[10px] text-[#77768C]">{{ $b->location ?? 'Bali' }}</div>
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-block px-2.5 py-0.5 rounded-full font-black text-[#29283D] bg-[#F4F3FA] border border-[#E8E6F0]">
                                {{ $b->guest_count }} org
                            </span>
                            @if($b->remainingGuestsCount() > 0 && $b->status !== 'CANCELLED')
                                <span class="text-[9px] text-[#FF8A65] font-bold block mt-0.5">Sisa: {{ $b->remainingGuestsCount() }}</span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="flex flex-wrap gap-1 max-w-[190px]">
                                @forelse($b->assignments as $asg)
                                    @if($asg->status !== 'CANCELLED')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#6C63FF]/10 text-[#6C63FF] border border-[#6C63FF]/20" title="Fee: Rp {{ number_format($asg->fee_amount, 0, ',', '.') }}">
                                        #{{ $asg->guide->queue_number }} {{ $asg->guide->nama }} ({{ $asg->guest_count }}p)
                                    </span>
                                    @endif
                                @empty
                                    <span class="text-slate-400 italic text-[11px]">Belum ada guide</span>
                                @endforelse
                            </div>
                        </td>

                        <td class="px-4 py-3.5 text-right font-black text-[#29283D]">
                            Rp {{ number_format($b->total_price, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3.5">
                            @if($b->status === 'FULLY_ASSIGNED')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#72D6C9]/20 text-[#147a6f] border border-[#72D6C9]/50">Guide Lengkap</span>
                            @elseif($b->status === 'PARTIALLY_ASSIGNED')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FFD166]/20 text-[#8a6800] border border-[#FFD166]/50">Guide Sebagian</span>
                            @elseif($b->status === 'ON_GOING')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Sedang Tur</span>
                            @elseif($b->status === 'COMPLETED')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-[#77768C] border border-[#E8E6F0]">Selesai</span>
                            @elseif($b->status === 'CANCELLED')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-400">Dibatalkan</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Menunggu Guide</span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 text-right space-x-1">
                            <a href="{{ route('bookings.show', $b) }}" 
                               class="clay-btn clay-btn-primary py-1.5 px-3 text-xs font-bold inline-flex items-center">
                                Detail & Guide
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-[#77768C]">
                            Tidak ada booking yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
        <div class="px-4 py-3.5 border-t border-[#E8E6F0] bg-[#F4F3FA]">
            {{ $bookings->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

