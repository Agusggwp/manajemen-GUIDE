@php
    $isNext = isset($nextPos) && $nextPos === $item->queue_number;
@endphp
<div class="bg-white rounded-lg p-3.5 border {{ $isNext ? 'border-[#21c9a4] ring-2 ring-[#21c9a4]/30' : 'border-slate-200' }} shadow-2xs hover:shadow-xs transition-shadow">
    <div class="flex items-start justify-between">
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md text-xs font-extrabold {{ $isNext ? 'bg-[#14433b] text-[#21c9a4]' : 'bg-slate-100 text-slate-700' }}">
                #{{ $item->queue_number }}
            </span>
            <div>
                <h4 class="text-xs font-bold text-slate-900 leading-tight">{{ $item->nama }}</h4>
                <div class="text-[10px] text-slate-400">{{ $item->guide->kode_guide }}</div>
            </div>
        </div>

        @if($isNext)
            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-[#21c9a4] text-[#14433b]">
                BERIKUTNYA
            </span>
        @endif
    </div>

    <!-- Attendance & Status Pill -->
    <div class="mt-2.5 flex items-center justify-between text-[10px]">
        <span class="text-slate-400">Absensi:</span>
        @if($item->attendance_status === 'CHECKED_IN')
            <span class="font-semibold text-emerald-700">Hadir ({{ $item->check_in_at?->format('H:i') }})</span>
        @elseif($item->attendance_status === 'CHECKED_OUT')
            <span class="font-semibold text-slate-500">Checkout ({{ $item->check_out_at?->format('H:i') }})</span>
        @elseif($item->attendance_status === 'OFF')
            <span class="font-semibold text-rose-600">OFF (Libur)</span>
        @else
            <span class="font-semibold text-amber-600">Belum Check-in</span>
        @endif
    </div>

    <!-- Stats: Today Jobs & Guests -->
    <div class="mt-2 pt-2 border-t border-slate-100 grid grid-cols-2 gap-1 text-[11px]">
        <div>
            <span class="text-slate-400 text-[10px] block">Tamu Hari Ini:</span>
            <span class="font-bold text-slate-800">{{ $item->today_guests }} org</span>
        </div>
        <div class="text-right">
            <span class="text-slate-400 text-[10px] block">Tugas Tur:</span>
            <span class="font-bold text-slate-800">{{ $item->today_jobs }} booking</span>
        </div>
    </div>

    <!-- Estimated Fee Earned Today -->
    <div class="mt-2 pt-1 text-[10px] flex items-center justify-between bg-slate-50 px-2 py-1 rounded text-slate-600">
        <span>Upah Hari Ini:</span>
        <span class="font-bold text-[#14433b]">Rp {{ number_format($item->today_earnings, 0, ',', '.') }}</span>
    </div>
</div>
