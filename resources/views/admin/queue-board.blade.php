@extends('layouts.app')

@section('title', 'Guide Queue Board')
@section('header', 'Guide Queue Board (Papan Antrean)')

@section('content')
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #E8E6F0;
        border-radius: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #77768C;
    }
</style>

<div class="space-y-6">
    <!-- Top Bar: Current Queue Position & Next Candidate -->
    <div class="clay-card p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-[#77768C] uppercase tracking-wider">Status Antrean:</span>
                <span class="px-3 py-1 rounded-full text-xs font-black bg-[#6C63FF] text-white shadow-xs">
                    Penugasan Terakhir: Posisi #{{ $state->current_position }}
                </span>
            </div>
            
            <div class="hidden md:block h-6 w-px bg-[#E8E6F0]"></div>

            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-[#77768C] uppercase tracking-wider">Giliran Berikutnya:</span>
                @if($nextGuideResult['guide'])
                <span class="px-3 py-1 rounded-full text-xs font-black bg-[#FFD166] text-[#29283D] border border-[#FFD166]/60 shadow-xs">
                    #{{ $nextGuideResult['guide']->queue_number }} {{ $nextGuideResult['guide']->nama }}
                </span>
                @else
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                    Belum ada guide tersedia
                </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('direct-tours.create') }}" class="clay-btn clay-btn-coral py-2 px-4 text-xs font-bold flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                <span>Buat Tour Langsung</span>
            </a>
            <a href="{{ route('guides.index') }}" class="clay-btn clay-btn-secondary py-2 px-3.5 text-xs font-bold text-[#77768C]">
                Atur Urutan Antrean
            </a>
        </div>
    </div>

    <!-- Live Kanban Queue Columns (WAITING, AVAILABLE, ASSIGNED, ON TOUR, CHECKED OUT) -->
    <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-4 items-start">
        <!-- 1. WAITING -->
        @php $colWaiting = $boardColumns['WAITING']; @endphp
        <div class="clay-card bg-[#F4F3FA]/70 p-4 border border-[#E8E6F0] flex flex-col" x-data="{ showAll: false }">
            <div class="flex items-center justify-between pb-3 border-b border-[#E8E6F0] mb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-[#FFD166] shadow-xs"></span>
                    <h3 class="text-xs font-extrabold text-[#29283D] uppercase tracking-wider font-display">Menunggu (Waiting)</h3>
                </div>
                <span class="text-xs font-black bg-white px-2.5 py-0.5 rounded-full text-[#29283D] border border-[#E8E6F0] shadow-xs">
                    {{ $colWaiting->count() }}
                </span>
            </div>

            <div class="space-y-3 overflow-y-auto pr-0.5 custom-scrollbar transition-all" :class="showAll ? 'max-h-[490px]' : ''">
                @forelse($colWaiting->take(3) as $item)
                    @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                @empty
                    <div class="text-center text-xs text-[#77768C] py-6">Tidak ada guide menunggu</div>
                @endforelse

                @if($colWaiting->count() > 3)
                    <div x-show="showAll" x-cloak class="space-y-3 pt-2 border-t border-dashed border-[#E8E6F0]">
                        @foreach($colWaiting->skip(3) as $item)
                            @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                        @endforeach
                    </div>
                @endif
            </div>

            @if($colWaiting->count() > 3)
                <div class="mt-3 pt-2.5 border-t border-[#E8E6F0]">
                    <button @click="showAll = !showAll"
                            type="button"
                            class="clay-btn clay-btn-secondary w-full py-2 px-3 text-xs font-bold text-[#77768C] flex items-center justify-center gap-1.5 cursor-pointer">
                        <span x-text="showAll ? 'Ciutkan Kembali (3 Guide)' : 'Lihat Semua ({{ $colWaiting->count() }} Guide)'"></span>
                        <svg class="w-3.5 h-3.5 text-[#77768C] transition-transform duration-200" :class="showAll ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
            @endif
        </div>

        <!-- 2. AVAILABLE -->
        @php $colAvailable = $boardColumns['AVAILABLE']; @endphp
        <div class="clay-card bg-[#F4F3FA]/70 p-4 border border-[#E8E6F0] flex flex-col" x-data="{ showAll: false }">
            <div class="flex items-center justify-between pb-3 border-b border-[#E8E6F0] mb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-[#72D6C9] shadow-xs"></span>
                    <h3 class="text-xs font-extrabold text-[#29283D] uppercase tracking-wider font-display">Siap (Available)</h3>
                </div>
                <span class="text-xs font-black bg-white px-2.5 py-0.5 rounded-full text-[#147a6f] border border-[#E8E6F0] shadow-xs">
                    {{ $colAvailable->count() }}
                </span>
            </div>

            <div class="space-y-3 overflow-y-auto pr-0.5 custom-scrollbar transition-all" :class="showAll ? 'max-h-[490px]' : ''">
                @forelse($colAvailable->take(3) as $item)
                    @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                @empty
                    <div class="text-center text-xs text-[#77768C] py-6">Tidak ada guide siap</div>
                @endforelse

                @if($colAvailable->count() > 3)
                    <div x-show="showAll" x-cloak class="space-y-3 pt-2 border-t border-dashed border-[#E8E6F0]">
                        @foreach($colAvailable->skip(3) as $item)
                            @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                        @endforeach
                    </div>
                @endif
            </div>

            @if($colAvailable->count() > 3)
                <div class="mt-3 pt-2.5 border-t border-[#E8E6F0]">
                    <button @click="showAll = !showAll"
                            type="button"
                            class="clay-btn clay-btn-secondary w-full py-2 px-3 text-xs font-bold text-[#77768C] flex items-center justify-center gap-1.5 cursor-pointer">
                        <span x-text="showAll ? 'Ciutkan Kembali (3 Guide)' : 'Lihat Semua ({{ $colAvailable->count() }} Guide)'"></span>
                        <svg class="w-3.5 h-3.5 text-[#77768C] transition-transform duration-200" :class="showAll ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
            @endif
        </div>

        <!-- 3. ASSIGNED -->
        @php $colAssigned = $boardColumns['ASSIGNED']; @endphp
        <div class="clay-card bg-[#F4F3FA]/70 p-4 border border-[#E8E6F0] flex flex-col" x-data="{ showAll: false }">
            <div class="flex items-center justify-between pb-3 border-b border-[#E8E6F0] mb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-[#6C63FF] shadow-xs"></span>
                    <h3 class="text-xs font-extrabold text-[#29283D] uppercase tracking-wider font-display">Ditugaskan (Assigned)</h3>
                </div>
                <span class="text-xs font-black bg-white px-2.5 py-0.5 rounded-full text-[#6C63FF] border border-[#E8E6F0] shadow-xs">
                    {{ $colAssigned->count() }}
                </span>
            </div>

            <div class="space-y-3 overflow-y-auto pr-0.5 custom-scrollbar transition-all" :class="showAll ? 'max-h-[490px]' : ''">
                @forelse($colAssigned->take(3) as $item)
                    @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                @empty
                    <div class="text-center text-xs text-[#77768C] py-6">Belum ada guide ditugaskan</div>
                @endforelse

                @if($colAssigned->count() > 3)
                    <div x-show="showAll" x-cloak class="space-y-3 pt-2 border-t border-dashed border-[#E8E6F0]">
                        @foreach($colAssigned->skip(3) as $item)
                            @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                        @endforeach
                    </div>
                @endif
            </div>

            @if($colAssigned->count() > 3)
                <div class="mt-3 pt-2.5 border-t border-[#E8E6F0]">
                    <button @click="showAll = !showAll"
                            type="button"
                            class="clay-btn clay-btn-secondary w-full py-2 px-3 text-xs font-bold text-[#77768C] flex items-center justify-center gap-1.5 cursor-pointer">
                        <span x-text="showAll ? 'Ciutkan Kembali (3 Guide)' : 'Lihat Semua ({{ $colAssigned->count() }} Guide)'"></span>
                        <svg class="w-3.5 h-3.5 text-[#77768C] transition-transform duration-200" :class="showAll ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
            @endif
        </div>

        <!-- 4. ON TOUR -->
        @php $colOnTour = $boardColumns['ON_TOUR']; @endphp
        <div class="clay-card bg-[#F4F3FA]/70 p-4 border border-[#E8E6F0] flex flex-col" x-data="{ showAll: false }">
            <div class="flex items-center justify-between pb-3 border-b border-[#E8E6F0] mb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-blue-500 shadow-xs"></span>
                    <h3 class="text-xs font-extrabold text-[#29283D] uppercase tracking-wider font-display">Sedang Tur (On Tour)</h3>
                </div>
                <span class="text-xs font-black bg-white px-2.5 py-0.5 rounded-full text-blue-700 border border-[#E8E6F0] shadow-xs">
                    {{ $colOnTour->count() }}
                </span>
            </div>

            <div class="space-y-3 overflow-y-auto pr-0.5 custom-scrollbar transition-all" :class="showAll ? 'max-h-[490px]' : ''">
                @forelse($colOnTour->take(3) as $item)
                    @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                @empty
                    <div class="text-center text-xs text-[#77768C] py-6">Tidak ada guide sedang tur</div>
                @endforelse

                @if($colOnTour->count() > 3)
                    <div x-show="showAll" x-cloak class="space-y-3 pt-2 border-t border-dashed border-[#E8E6F0]">
                        @foreach($colOnTour->skip(3) as $item)
                            @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                        @endforeach
                    </div>
                @endif
            </div>

            @if($colOnTour->count() > 3)
                <div class="mt-3 pt-2.5 border-t border-[#E8E6F0]">
                    <button @click="showAll = !showAll"
                            type="button"
                            class="clay-btn clay-btn-secondary w-full py-2 px-3 text-xs font-bold text-[#77768C] flex items-center justify-center gap-1.5 cursor-pointer">
                        <span x-text="showAll ? 'Ciutkan Kembali (3 Guide)' : 'Lihat Semua ({{ $colOnTour->count() }} Guide)'"></span>
                        <svg class="w-3.5 h-3.5 text-[#77768C] transition-transform duration-200" :class="showAll ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
            @endif
        </div>

        <!-- 5. CHECKED OUT (SELESAI) -->
        @php 
            $colCheckedOut = $boardColumns['CHECKED_OUT']; 
        @endphp
        <div class="clay-card bg-[#F4F3FA]/70 p-4 border border-[#E8E6F0] flex flex-col" x-data="{ showAll: false }">
            <div class="flex items-center justify-between pb-3 border-b border-[#E8E6F0] mb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-slate-400 shadow-xs"></span>
                    <h3 class="text-xs font-extrabold text-[#29283D] uppercase tracking-wider font-display">Sudah Selesai (Checked Out)</h3>
                </div>
                <span class="text-xs font-black bg-white px-2.5 py-0.5 rounded-full text-[#77768C] border border-[#E8E6F0] shadow-xs">
                    {{ $colCheckedOut->count() }}
                </span>
            </div>

            <div class="space-y-3 overflow-y-auto pr-0.5 custom-scrollbar transition-all" :class="showAll ? 'max-h-[490px]' : ''">
                @forelse($colCheckedOut->take(3) as $item)
                    @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                @empty
                    <div class="text-center text-xs text-[#77768C] py-6">Tidak ada guide yang sudah selesai</div>
                @endforelse

                @if($colCheckedOut->count() > 3)
                    <div x-show="showAll" x-cloak class="space-y-3 pt-2 border-t border-dashed border-[#E8E6F0]">
                        @foreach($colCheckedOut->skip(3) as $item)
                            @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                        @endforeach
                    </div>
                @endif
            </div>

            @if($colCheckedOut->count() > 3)
                <div class="mt-3 pt-2.5 border-t border-[#E8E6F0]">
                    <button @click="showAll = !showAll"
                            type="button"
                            class="clay-btn clay-btn-secondary w-full py-2 px-3 text-xs font-bold text-[#77768C] flex items-center justify-center gap-1.5 cursor-pointer">
                        <span x-text="showAll ? 'Ciutkan Kembali (3 Guide)' : 'Lihat Semua ({{ $colCheckedOut->count() }} Guide)'"></span>
                        <svg class="w-3.5 h-3.5 text-[#77768C] transition-transform duration-200" :class="showAll ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Pending Bookings (Ready for Auto Assign) -->
    <div class="clay-card p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-extrabold text-[#29283D] font-display">Booking Menunggu Alokasi Guide</h3>
                <p class="text-xs text-[#77768C]">Klik "Auto Assign" untuk menugaskan guide otomatis sesuai rotasi melingkar</p>
            </div>
            <a href="{{ route('bookings.create') }}" class="clay-btn clay-btn-primary py-2 px-3 text-xs font-bold">
                + Buat Booking Baru
            </a>
        </div>

        <div class="clay-table-wrapper">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr>
                        <th class="px-4 py-3">Kode Booking</th>
                        <th class="px-4 py-3">Tanggal / Waktu</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Paket Tour</th>
                        <th class="px-4 py-3 text-center">Tamu (Total / Sisa)</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi Penugasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($pendingBookings as $b)
                    <tr class="hover:bg-[#F4F3FA]/50 transition-colors">
                        <td class="px-4 py-3 font-bold text-[#6C63FF]">
                            <a href="{{ route('bookings.show', $b) }}" class="hover:underline">
                                {{ $b->booking_code }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-bold text-[#29283D]">{{ $b->booking_date->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-[#77768C]">{{ substr($b->start_time, 0, 5) }} - {{ substr($b->end_time, 0, 5) }}</div>
                        </td>
                        <td class="px-4 py-3 font-semibold text-[#29283D]">
                            {{ $b->customer_name }}
                        </td>
                        <td class="px-4 py-3 text-[#77768C] font-medium">
                            {{ $b->tourPackage?->name ?? 'Custom Tour' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-black text-[#29283D]">{{ $b->guest_count }} pax</span>
                            <span class="text-[10px] text-[#FF8A65] font-bold block">(Sisa: {{ $b->remainingGuestsCount() }})</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FFD166]/20 text-[#8a6800] border border-[#FFD166]/50">
                                {{ $b->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <form action="{{ route('bookings.auto-assign', $b) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="clay-btn clay-btn-primary py-1.5 px-3 text-xs font-bold">
                                        <svg class="w-3.5 h-3.5 inline-block mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>Auto Assign Guide
                                    </button>
                                </form>
                                <a href="{{ route('bookings.show', $b) }}" class="clay-btn clay-btn-secondary py-1.5 px-3 text-xs font-bold text-[#77768C]">
                                    Pilih Guide
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-[#77768C]">
                            Tidak ada booking yang menunggu penugasan guide.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

