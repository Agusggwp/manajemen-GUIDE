@extends('layouts.guide')

@section('title', 'Tugas Saya')

@section('content')
<div class="space-y-4">
    <div class="clay-card p-4">
        <h2 class="text-sm font-extrabold text-[#29283D] font-display">Riwayat Penugasan Tour Saya</h2>
        <p class="text-[11px] text-[#77768C]">Semua job yang pernah Anda tangani atau sedang aktif</p>
    </div>

    <!-- Job Cards -->
    <div class="space-y-3">
        @forelse($assignments as $job)
        <div class="clay-card p-4 space-y-3 transition">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-mono font-bold text-[#6C63FF] bg-[#6C63FF]/10 px-2 py-0.5 rounded-md">
                        {{ $job->booking->booking_code }}
                    </span>
                    <h4 class="text-xs font-bold text-[#29283D] mt-1">{{ $job->booking->customer_name }}</h4>
                    <p class="text-[10px] text-[#77768C]">{{ $job->booking->booking_date->format('d M Y') }} &bull; {{ substr($job->booking->start_time, 0, 5) }} WITA</p>
                </div>

                <div>
                    @if($job->status === 'COMPLETED')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-[#77768C]">Selesai</span>
                    @elseif($job->status === 'ON_GOING')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 animate-pulse">Sedang Tur</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#6C63FF]/10 text-[#6C63FF]">Ditugaskan</span>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between text-xs bg-[#F4F3FA] p-2.5 rounded-2xl border border-[#E8E6F0]/80">
                <div>
                    <span class="text-[10px] text-[#77768C] block">Tamu:</span>
                    <span class="font-bold text-[#29283D]">{{ $job->guest_count }} orang</span>
                </div>
                <div>
                    <span class="text-[10px] text-[#77768C] block">Paket:</span>
                    <span class="font-medium text-[#29283D]">{{ $job->booking->tourPackage?->name ?? 'Tour' }}</span>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-[#77768C] block">Hak Fee:</span>
                    <span class="font-bold text-[#FF8A65]">Rp {{ number_format($job->fee_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="clay-card p-8 text-center text-[#77768C] text-xs">
            Belum ada riwayat penugasan.
        </div>
        @endforelse
    </div>

    @if($assignments->hasPages())
    <div class="pt-2">
        {{ $assignments->links() }}
    </div>
    @endif
</div>
@endsection

