@php
    $isNext = isset($nextPos) && $nextPos === $item->queue_number;
@endphp
<div class="clay-card p-3.5 transition-all duration-200 {{ $isNext ? 'ring-2 ring-[#6C63FF]/40 border-[#6C63FF]/40 bg-[#6C63FF]/5 shadow-md shadow-[#6C63FF]/15' : '' }}">
    <div class="flex items-start justify-between">
        <div class="flex items-center space-x-2.5">
            <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl text-xs font-black {{ $isNext ? 'bg-[#FFD166] text-[#29283D] shadow-xs' : 'bg-[#6C63FF] text-white shadow-xs' }}">
                #{{ $item->queue_number }}
            </span>
            <div>
                <h4 class="text-xs font-bold text-[#29283D] leading-tight font-display">{{ $item->nama }}</h4>
                <div class="text-[10px] text-[#77768C] font-mono">{{ $item->guide->kode_guide }}</div>
            </div>
        </div>

        @if($isNext)
            <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-[#FFD166] text-[#29283D] border border-[#FFD166]/60">
                BERIKUTNYA
            </span>
        @endif
    </div>

    <!-- Attendance & Status Pill -->
    <div class="mt-2.5 flex items-center justify-between text-[10px]">
        <span class="text-[#77768C] font-medium">Absensi:</span>
        @if($item->attendance_status === 'CHECKED_IN')
            <span class="font-bold text-[#147a6f]">Hadir ({{ $item->check_in_at?->format('H:i') }})</span>
        @elseif($item->attendance_status === 'CHECKED_OUT')
            <span class="font-semibold text-[#77768C]">Checkout ({{ $item->check_out_at?->format('H:i') }})</span>
        @elseif($item->attendance_status === 'OFF')
            <span class="font-bold text-rose-600">OFF (Libur)</span>
        @else
            <span class="font-bold text-[#c9522e]">Belum Check-in</span>
        @endif
    </div>

    <!-- Stats: Today Jobs & Guests -->
    <div class="mt-2 pt-2 border-t border-[#E8E6F0] grid grid-cols-2 gap-1 text-[11px]">
        <div>
            <span class="text-[#77768C] text-[10px] block font-medium">Tamu Hari Ini:</span>
            <span class="font-extrabold text-[#29283D]">{{ $item->today_guests }} org</span>
        </div>
        <div class="text-right">
            <span class="text-[#77768C] text-[10px] block font-medium">Tugas Tur:</span>
            <span class="font-extrabold text-[#29283D]">{{ $item->today_jobs }} booking</span>
        </div>
    </div>

    <!-- Estimated Fee Earned Today -->
    <div class="mt-2.5 pt-1.5 text-[10px] flex items-center justify-between bg-[#F4F3FA] px-2.5 py-1.5 rounded-xl text-[#29283D] border border-[#E8E6F0]/80">
        <span class="text-[#77768C] font-medium">Upah Hari Ini:</span>
        <span class="font-black text-[#FF8A65]">Rp {{ number_format($item->today_earnings, 0, ',', '.') }}</span>
    </div>
</div>

