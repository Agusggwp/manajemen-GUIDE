@extends('layouts.app')

@section('title', 'Guide Queue Board')
@section('header', 'Guide Queue Board (Papan Antrean)')

@section('content')
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<div class="space-y-6">
    <!-- Top Bar: Current Queue Position & Next Candidate -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status Antrean:</span>
                <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-[#14433b] text-white">
                    Penugasan Terakhir: Posisi #{{ $state->current_position }}
                </span>
            </div>
            
            <div class="hidden md:block h-6 w-px bg-slate-200"></div>

            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Giliran Berikutnya:</span>
                @if($nextGuideResult['guide'])
                <span class="px-3 py-1 rounded-md text-xs font-bold bg-[#e6faf5] text-[#14433b] border border-[#21c9a4]">
                    #{{ $nextGuideResult['guide']->queue_number }} {{ $nextGuideResult['guide']->nama }}
                </span>
                @else
                <span class="px-3 py-1 rounded-md text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">
                    Belum ada guide tersedia
                </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('direct-tours.create') }}" class="px-3.5 py-1.5 rounded-lg bg-amber-400 hover:bg-amber-300 text-slate-900 text-xs font-bold tracking-wide transition shadow-sm flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                <span>⚡ Buat Tour Langsung</span>
            </a>
            <a href="{{ route('guides.index') }}" class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                Atur Urutan Antrean
            </a>
        </div>
    </div>

    <!-- Live Kanban Queue Columns (WAITING, AVAILABLE, ASSIGNED, ON TOUR, CHECKED OUT) -->
    <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-4 items-start">
        <!-- 1. WAITING -->
        @php $colWaiting = $boardColumns['WAITING']; @endphp
        <div class="bg-slate-100/70 rounded-xl p-3.5 border border-slate-200/70 flex flex-col shadow-2xs" x-data="{ showAll: false }">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Menunggu (Waiting)</h3>
                </div>
                <span class="text-xs font-bold bg-white px-2 py-0.5 rounded-full text-slate-600 border border-slate-200">
                    {{ $colWaiting->count() }}
                </span>
            </div>

            <div class="space-y-3 overflow-y-auto pr-0.5 custom-scrollbar transition-all" :class="showAll ? 'max-h-[490px]' : ''">
                @forelse($colWaiting->take(3) as $item)
                    @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                @empty
                    <div class="text-center text-xs text-slate-400 py-6">Tidak ada guide menunggu</div>
                @endforelse

                @if($colWaiting->count() > 3)
                    <div x-show="showAll" x-cloak class="space-y-3 pt-1 border-t border-dashed border-slate-200">
                        @foreach($colWaiting->skip(3) as $item)
                            @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                        @endforeach
                    </div>
                @endif
            </div>

            @if($colWaiting->count() > 3)
                <div class="mt-2.5 pt-2 border-t border-slate-200/70">
                    <button @click="showAll = !showAll"
                            type="button"
                            class="w-full py-2 px-3 rounded-lg text-xs font-semibold shadow-2xs flex items-center justify-center gap-1.5 transition-all cursor-pointer"
                            :class="showAll ? 'bg-slate-200 hover:bg-slate-300 text-slate-800' : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200'">
                        <span x-text="showAll ? 'Ciutkan Kembali (3 Guide)' : 'Lihat Semua ({{ $colWaiting->count() }} Guide)'"></span>
                        <svg class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" :class="showAll ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
            @endif
        </div>

        <!-- 2. AVAILABLE -->
        @php $colAvailable = $boardColumns['AVAILABLE']; @endphp
        <div class="bg-slate-100/70 rounded-xl p-3.5 border border-slate-200/70 flex flex-col shadow-2xs" x-data="{ showAll: false }">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#21c9a4]"></span>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Siap (Available)</h3>
                </div>
                <span class="text-xs font-bold bg-white px-2 py-0.5 rounded-full text-slate-600 border border-slate-200">
                    {{ $colAvailable->count() }}
                </span>
            </div>

            <div class="space-y-3 overflow-y-auto pr-0.5 custom-scrollbar transition-all" :class="showAll ? 'max-h-[490px]' : ''">
                @forelse($colAvailable->take(3) as $item)
                    @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                @empty
                    <div class="text-center text-xs text-slate-400 py-6">Tidak ada guide siap</div>
                @endforelse

                @if($colAvailable->count() > 3)
                    <div x-show="showAll" x-cloak class="space-y-3 pt-1 border-t border-dashed border-slate-200">
                        @foreach($colAvailable->skip(3) as $item)
                            @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                        @endforeach
                    </div>
                @endif
            </div>

            @if($colAvailable->count() > 3)
                <div class="mt-2.5 pt-2 border-t border-slate-200/70">
                    <button @click="showAll = !showAll"
                            type="button"
                            class="w-full py-2 px-3 rounded-lg text-xs font-semibold shadow-2xs flex items-center justify-center gap-1.5 transition-all cursor-pointer"
                            :class="showAll ? 'bg-slate-200 hover:bg-slate-300 text-slate-800' : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200'">
                        <span x-text="showAll ? 'Ciutkan Kembali (3 Guide)' : 'Lihat Semua ({{ $colAvailable->count() }} Guide)'"></span>
                        <svg class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" :class="showAll ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
            @endif
        </div>

        <!-- 3. ASSIGNED -->
        @php $colAssigned = $boardColumns['ASSIGNED']; @endphp
        <div class="bg-slate-100/70 rounded-xl p-3.5 border border-slate-200/70 flex flex-col shadow-2xs" x-data="{ showAll: false }">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Ditugaskan (Assigned)</h3>
                </div>
                <span class="text-xs font-bold bg-white px-2 py-0.5 rounded-full text-slate-600 border border-slate-200">
                    {{ $colAssigned->count() }}
                </span>
            </div>

            <div class="space-y-3 overflow-y-auto pr-0.5 custom-scrollbar transition-all" :class="showAll ? 'max-h-[490px]' : ''">
                @forelse($colAssigned->take(3) as $item)
                    @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                @empty
                    <div class="text-center text-xs text-slate-400 py-6">Belum ada guide ditugaskan</div>
                @endforelse

                @if($colAssigned->count() > 3)
                    <div x-show="showAll" x-cloak class="space-y-3 pt-1 border-t border-dashed border-slate-200">
                        @foreach($colAssigned->skip(3) as $item)
                            @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                        @endforeach
                    </div>
                @endif
            </div>

            @if($colAssigned->count() > 3)
                <div class="mt-2.5 pt-2 border-t border-slate-200/70">
                    <button @click="showAll = !showAll"
                            type="button"
                            class="w-full py-2 px-3 rounded-lg text-xs font-semibold shadow-2xs flex items-center justify-center gap-1.5 transition-all cursor-pointer"
                            :class="showAll ? 'bg-slate-200 hover:bg-slate-300 text-slate-800' : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200'">
                        <span x-text="showAll ? 'Ciutkan Kembali (3 Guide)' : 'Lihat Semua ({{ $colAssigned->count() }} Guide)'"></span>
                        <svg class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" :class="showAll ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
            @endif
        </div>

        <!-- 4. ON TOUR -->
        @php $colOnTour = $boardColumns['ON_TOUR']; @endphp
        <div class="bg-slate-100/70 rounded-xl p-3.5 border border-slate-200/70 flex flex-col shadow-2xs" x-data="{ showAll: false }">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Sedang Tur (On Tour)</h3>
                </div>
                <span class="text-xs font-bold bg-white px-2 py-0.5 rounded-full text-slate-600 border border-slate-200">
                    {{ $colOnTour->count() }}
                </span>
            </div>

            <div class="space-y-3 overflow-y-auto pr-0.5 custom-scrollbar transition-all" :class="showAll ? 'max-h-[490px]' : ''">
                @forelse($colOnTour->take(3) as $item)
                    @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                @empty
                    <div class="text-center text-xs text-slate-400 py-6">Tidak ada guide sedang tur</div>
                @endforelse

                @if($colOnTour->count() > 3)
                    <div x-show="showAll" x-cloak class="space-y-3 pt-1 border-t border-dashed border-slate-200">
                        @foreach($colOnTour->skip(3) as $item)
                            @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                        @endforeach
                    </div>
                @endif
            </div>

            @if($colOnTour->count() > 3)
                <div class="mt-2.5 pt-2 border-t border-slate-200/70">
                    <button @click="showAll = !showAll"
                            type="button"
                            class="w-full py-2 px-3 rounded-lg text-xs font-semibold shadow-2xs flex items-center justify-center gap-1.5 transition-all cursor-pointer"
                            :class="showAll ? 'bg-slate-200 hover:bg-slate-300 text-slate-800' : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200'">
                        <span x-text="showAll ? 'Ciutkan Kembali (3 Guide)' : 'Lihat Semua ({{ $colOnTour->count() }} Guide)'"></span>
                        <svg class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" :class="showAll ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div class="bg-slate-100/70 rounded-xl p-3.5 border border-slate-200/70 flex flex-col shadow-2xs" x-data="{ showAll: false }">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Sudah Selesai (Checked Out)</h3>
                </div>
                <span class="text-xs font-bold bg-white px-2 py-0.5 rounded-full text-slate-600 border border-slate-200">
                    {{ $colCheckedOut->count() }}
                </span>
            </div>

            <div class="space-y-3 overflow-y-auto pr-0.5 custom-scrollbar transition-all" :class="showAll ? 'max-h-[490px]' : ''">
                @forelse($colCheckedOut->take(3) as $item)
                    @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                @empty
                    <div class="text-center text-xs text-slate-400 py-6">Tidak ada guide yang sudah selesai</div>
                @endforelse

                @if($colCheckedOut->count() > 3)
                    <div x-show="showAll" x-cloak class="space-y-3 pt-1 border-t border-dashed border-slate-200">
                        @foreach($colCheckedOut->skip(3) as $item)
                            @include('admin.partials.queue-card', ['item' => $item, 'nextPos' => $nextGuideResult['guide']?->queue_number])
                        @endforeach
                    </div>
                @endif
            </div>

            @if($colCheckedOut->count() > 3)
                <div class="mt-2.5 pt-2 border-t border-slate-200/70">
                    <button @click="showAll = !showAll"
                            type="button"
                            class="w-full py-2 px-3 rounded-lg text-xs font-semibold shadow-2xs flex items-center justify-center gap-1.5 transition-all cursor-pointer"
                            :class="showAll ? 'bg-slate-200 hover:bg-slate-300 text-slate-800' : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200'">
                        <span x-text="showAll ? 'Ciutkan Kembali (3 Guide)' : 'Lihat Semua ({{ $colCheckedOut->count() }} Guide)'"></span>
                        <svg class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" :class="showAll ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Pending Bookings (Ready for Auto Assign) -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Booking Menunggu Alokasi Guide</h3>
                <p class="text-xs text-slate-500">Klik "Auto Assign" untuk menugaskan guide otomatis sesuai rotasi melingkar</p>
            </div>
            <a href="{{ route('bookings.create') }}" class="text-xs font-semibold text-[#14433b] hover:text-[#21c9a4]">
                + Buat Booking Baru
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
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
                <tbody class="divide-y divide-slate-100">
                    @forelse($pendingBookings as $b)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-4 py-3 font-semibold text-slate-900">
                            <a href="{{ route('bookings.show', $b) }}" class="hover:text-[#14433b]">
                                {{ $b->booking_code }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <div>{{ $b->booking_date->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-slate-400">{{ substr($b->start_time, 0, 5) }} - {{ substr($b->end_time, 0, 5) }}</div>
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-800">
                            {{ $b->customer_name }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $b->tourPackage?->name ?? 'Custom Tour' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-slate-800">{{ $b->guest_count }} pax</span>
                            <span class="text-[10px] text-amber-600 block">(Sisa: {{ $b->remainingGuestsCount() }})</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                {{ $b->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <form action="{{ route('bookings.auto-assign', $b) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-bold bg-[#14433b] hover:bg-[#1b584d] text-white transition-colors shadow-2xs">
                                        ⚡ Auto Assign Guide
                                    </button>
                                </form>
                                <a href="{{ route('bookings.show', $b) }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium border border-slate-200 text-slate-700 hover:bg-slate-100">
                                    Pilih Guide
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-slate-400">
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
