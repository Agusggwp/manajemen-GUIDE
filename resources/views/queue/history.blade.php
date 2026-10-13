@extends('layouts.app')

@section('title', 'Riwayat Antrean & Audit Rotasi')
@section('header', 'Riwayat Antrean & Audit Rotasi')

@section('content')
<div class="space-y-6">
    <div class="clay-card p-6">
        <h2 class="text-base font-bold text-[#29283D]">Riwayat Rotasi Antrean Guide (Continuous Circular Queue)</h2>
        <p class="text-xs text-[#77768C] mt-1">
            Audit lengkap rotasi antrean: mencatat posisi sebelum, posisi sesudah, guide terpilih, serta daftar guide yang dilewati (skip) beserta alasannya.
        </p>
    </div>

    <!-- History Table -->
    <div class="clay-card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F4F3FA] text-[#77768C] font-bold border-b border-[#E8E6F0]">
                    <tr>
                        <th class="px-5 py-3.5">Waktu Transaksi</th>
                        <th class="px-5 py-3.5">Perubahan Posisi Rotasi</th>
                        <th class="px-5 py-3.5">Guide Terpilih</th>
                        <th class="px-5 py-3.5">Booking & Tamu</th>
                        <th class="px-5 py-3.5">Guide yang Dilewati (Skipped)</th>
                        <th class="px-5 py-3.5">Tipe & Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($histories as $h)
                    <tr class="hover:bg-purple-50/30 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-[#29283D]">{{ $h->created_at->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-[#77768C] font-mono">{{ $h->created_at->format('H:i:s') }} WITA</div>
                        </td>

                        <td class="px-5 py-3.5">
                            <div class="flex items-center space-x-2 font-bold">
                                <span class="px-2.5 py-1 rounded-xl bg-[#F4F3FA] text-[#77768C] border border-[#E8E6F0]">#{{ $h->previous_position }}</span>
                                <span class="text-[#77768C]">&rarr;</span>
                                <span class="px-2.5 py-1 rounded-xl bg-[#6C63FF] text-white shadow-xs">#{{ $h->next_position }}</span>
                            </div>
                        </td>

                        <td class="px-5 py-3.5">
                            @if($h->selectedGuide)
                                <div class="font-bold text-[#29283D]">{{ $h->selectedGuide->nama }}</div>
                                <div class="text-[10px] text-[#77768C] font-mono">{{ $h->selectedGuide->kode_guide }} &bull; Posisi #{{ $h->next_position }}</div>
                            @else
                                <span class="text-[#77768C]">-</span>
                            @endif
                        </td>

                        <td class="px-5 py-3.5">
                            @if($h->booking)
                                <a href="{{ route('bookings.show', $h->booking) }}" class="font-bold text-[#6C63FF] hover:underline">
                                    {{ $h->booking->booking_code }}
                                </a>
                                <div class="text-[10px] text-[#77768C]">{{ $h->booking->guest_count }} orang tamu &bull; {{ $h->booking->customer_name }}</div>
                            @else
                                <span class="text-[#77768C]">{{ $h->notes ?? '-' }}</span>
                            @endif
                        </td>

                        <td class="px-5 py-3.5">
                            @if(!empty($h->skipped_guides) && is_array($h->skipped_guides))
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @foreach($h->skipped_guides as $skip)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            #{{ $skip['queue_number'] ?? '?' }} {{ $skip['nama'] ?? 'Guide' }} ({{ $skip['reason'] ?? 'Skipped' }})
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-[#77768C] text-[11px] italic">Tidak ada guide dilewati</span>
                            @endif
                        </td>

                        <td class="px-5 py-3.5">
                            <span class="clay-badge {{ $h->type === 'AUTO' ? 'bg-purple-50 text-[#6C63FF] border-purple-200' : 'bg-[#F4F3FA] text-[#77768C] border-[#E8E6F0]' }}">
                                {{ $h->type === 'AUTO' ? 'Otomatis' : 'Manual' }}
                            </span>
                            <div class="text-[10px] text-[#77768C] mt-0.5">Oleh: {{ $h->assigner?->name ?? 'Sistem' }}</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-[#77768C]">Belum ada catatan riwayat antrean.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($histories->hasPages())
        <div class="px-5 py-3.5 border-t border-[#E8E6F0] bg-[#F4F3FA]">
            {{ $histories->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
