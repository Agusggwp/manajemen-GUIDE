@extends('layouts.app')

@section('title', 'Riwayat Antrean & Audit Rotasi')
@section('header', 'Riwayat Antrean & Audit Rotasi')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs">
        <h2 class="text-base font-bold text-slate-900">Riwayat Rotasi Antrean Guide (Continuous Circular Queue)</h2>
        <p class="text-xs text-slate-500 mt-0.5">
            Audit lengkap rotasi antrean: mencatat posisi sebelum, posisi sesudah, guide terpilih, serta daftar guide yang dilewati (skip) beserta alasannya.
        </p>
    </div>

    <!-- History Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5">Waktu Transaksi</th>
                        <th class="px-4 py-3.5">Perubahan Posisi Rotasi</th>
                        <th class="px-4 py-3.5">Guide Terpilih</th>
                        <th class="px-4 py-3.5">Booking & Tamu</th>
                        <th class="px-4 py-3.5">Guide yang Dilewati (Skipped)</th>
                        <th class="px-4 py-3.5">Tipe & Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($histories as $h)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900">{{ $h->created_at->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $h->created_at->format('H:i:s') }} WITA</div>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="flex items-center space-x-1.5 font-bold">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700">#{{ $h->previous_position }}</span>
                                <span class="text-slate-400">&rarr;</span>
                                <span class="px-2 py-0.5 rounded bg-[#14433b] text-[#21c9a4]">#{{ $h->next_position }}</span>
                            </div>
                        </td>

                        <td class="px-4 py-3.5">
                            @if($h->selectedGuide)
                                <div class="font-bold text-slate-900">{{ $h->selectedGuide->nama }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $h->selectedGuide->kode_guide }} &bull; Posisi #{{ $h->next_position }}</div>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5">
                            @if($h->booking)
                                <a href="{{ route('bookings.show', $h->booking) }}" class="font-bold text-[#14433b] hover:underline">
                                    {{ $h->booking->booking_code }}
                                </a>
                                <div class="text-[10px] text-slate-500">{{ $h->booking->guest_count }} orang tamu &bull; {{ $h->booking->customer_name }}</div>
                            @else
                                <span class="text-slate-400">{{ $h->notes ?? '-' }}</span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5">
                            @if(!empty($h->skipped_guides) && is_array($h->skipped_guides))
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @foreach($h->skipped_guides as $skip)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] bg-rose-50 text-rose-700 border border-rose-200">
                                            #{{ $skip['queue_number'] ?? '?' }} {{ $skip['nama'] ?? 'Guide' }} ({{ $skip['reason'] ?? 'Skipped' }})
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-slate-400 text-[11px] italic">Tidak ada guide dilewati</span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $h->type === 'AUTO' ? 'bg-[#e6faf5] text-[#14433b]' : 'bg-slate-100 text-slate-700' }}">
                                {{ $h->type === 'AUTO' ? 'Otomatis' : 'Manual' }}
                            </span>
                            <div class="text-[10px] text-slate-400 mt-0.5">Oleh: {{ $h->assigner?->name ?? 'Sistem' }}</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada catatan riwayat antrean.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($histories->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
            {{ $histories->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
