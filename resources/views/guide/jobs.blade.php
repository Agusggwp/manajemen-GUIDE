@extends('layouts.guide')

@section('title', 'Tugas Saya')

@section('content')
<div class="space-y-4">
    <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs">
        <h2 class="text-sm font-bold text-slate-900">Riwayat Penugasan Tour Saya</h2>
        <p class="text-[11px] text-slate-500">Semua job yang pernah Anda tangani atau sedang aktif</p>
    </div>

    <!-- Job Cards -->
    <div class="space-y-3">
        @forelse($assignments as $job)
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs space-y-2.5">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-mono font-bold text-[#14433b] bg-[#e6faf5] px-2 py-0.5 rounded">
                        {{ $job->booking->booking_code }}
                    </span>
                    <h4 class="text-xs font-bold text-slate-900 mt-1">{{ $job->booking->customer_name }}</h4>
                    <p class="text-[10px] text-slate-400">{{ $job->booking->booking_date->format('d M Y') }} &bull; {{ substr($job->booking->start_time, 0, 5) }} WITA</p>
                </div>

                <div>
                    @if($job->status === 'COMPLETED')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Selesai</span>
                    @elseif($job->status === 'ON_GOING')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700">Sedang Tur</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700">Ditugaskan</span>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between text-xs bg-slate-50 p-2 rounded-lg border border-slate-100">
                <div>
                    <span class="text-[10px] text-slate-400 block">Tamu:</span>
                    <span class="font-bold text-slate-800">{{ $job->guest_count }} orang</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block">Paket:</span>
                    <span class="font-medium text-slate-700">{{ $job->booking->tourPackage?->name ?? 'Tour' }}</span>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 block">Hak Fee:</span>
                    <span class="font-bold text-amber-700">Rp {{ number_format($job->fee_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-400 text-xs">
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
