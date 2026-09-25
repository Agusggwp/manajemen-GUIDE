@extends('layouts.app')

@section('title', 'Data Guide & Urutan Antrean')
@section('header', 'Data Master Guide & Urutan Antrean')

@section('content')
<div class="space-y-6" x-data="{ 
    reorderModalOpen: false,
    suspendModalOpen: false,
    suspendGuideId: null,
    suspendGuideName: '',
    openSuspendModal(id, name) {
        this.suspendGuideId = id;
        this.suspendGuideName = name;
        this.suspendModalOpen = true;
    }
}">
    <!-- Header Summary & Actions -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Master Data Guide Wisata</h2>
            <p class="text-xs text-slate-500">Kelola master guide, status aktif/suspend, dan urutan rotasi antrean</p>
        </div>

        <div class="flex items-center space-x-2">
            @if(auth()->user()->isAdmin())
            <button type="button" @click="reorderModalOpen = true"
                    class="px-3.5 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-colors">
                ↕ Atur Urutan Antrean
            </button>
            <a href="{{ route('guides.create') }}" 
               class="px-4 py-2 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-bold transition-colors shadow-xs">
                + Tambah Guide Baru
            </a>
            @endif
        </div>
    </div>

    <!-- Status Filters & Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-1.5 text-xs">
            <a href="{{ route('guides.index') }}" 
               class="px-3 py-1.5 rounded-lg font-semibold transition-colors {{ !request()->filled('status') ? 'bg-[#14433b] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                Semua
            </a>
            <a href="{{ route('guides.index', ['status' => 'AVAILABLE']) }}" 
               class="px-3 py-1.5 rounded-lg font-semibold transition-colors {{ request('status') === 'AVAILABLE' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                Siap (Available)
            </a>
            <a href="{{ route('guides.index', ['status' => 'ON_TOUR']) }}" 
               class="px-3 py-1.5 rounded-lg font-semibold transition-colors {{ request('status') === 'ON_TOUR' ? 'bg-blue-700 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                Sedang Tur
            </a>
            <a href="{{ route('guides.index', ['status' => 'SUSPENDED']) }}" 
               class="px-3 py-1.5 rounded-lg font-bold transition-colors {{ request('status') === 'SUSPENDED' ? 'bg-rose-700 text-white shadow-xs' : 'bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100' }}">
                Ditangguhkan (Suspended)
            </a>
            <a href="{{ route('guides.index', ['status' => 'OFF']) }}" 
               class="px-3 py-1.5 rounded-lg font-semibold transition-colors {{ request('status') === 'OFF' ? 'bg-slate-800 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                Libur (Off)
            </a>
            <a href="{{ route('guides.index', ['status' => 'INACTIVE']) }}" 
               class="px-3 py-1.5 rounded-lg font-semibold transition-colors {{ request('status') === 'INACTIVE' ? 'bg-slate-700 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                Non-Aktif
            </a>
        </div>

        <form method="GET" action="{{ route('guides.index') }}" class="flex items-center gap-1.5">
            @if(request()->filled('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / kode guide..."
                   class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-[#14433b] outline-none">
            <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold">
                Cari
            </button>
        </form>
    </div>

    <!-- Guides Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 text-center">No Antrean</th>
                        <th class="px-4 py-3.5">Kode & Nama Guide</th>
                        <th class="px-4 py-3.5">Kontak & Email</th>
                        <th class="px-4 py-3.5">Bahasa & Spesialisasi</th>
                        <th class="px-4 py-3.5">Absensi Hari Ini</th>
                        <th class="px-4 py-3.5">Status Guide</th>
                        <th class="px-4 py-3.5 text-right">Fee Rate</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($guides as $g)
                    <tr class="hover:bg-slate-50/70 transition-colors {{ $g->status === 'SUSPENDED' ? 'bg-rose-50/30' : '' }}">
                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg font-extrabold text-xs {{ $g->status === 'SUSPENDED' ? 'bg-rose-100 text-rose-800' : 'bg-[#14433b] text-[#21c9a4]' }} shadow-xs">
                                #{{ $g->queue_number }}
                            </span>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                                <span>{{ $g->nama }}</span>
                                @if($g->status === 'SUSPENDED')
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-600 text-white uppercase">SUSPENDED</span>
                                @endif
                            </div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $g->kode_guide }}</div>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="text-slate-800 font-medium">{{ $g->nomor_telepon }}</div>
                            <div class="text-[10px] text-slate-400">{{ $g->email ?? '-' }}</div>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="text-slate-700 font-medium">{{ $g->bahasa ?? 'Indonesia, English' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $g->spesialisasi ?? 'General Tour' }}</div>
                        </td>

                        <td class="px-4 py-3.5">
                            @php $att = $g->todayAttendance; @endphp
                            @if($g->status === 'SUSPENDED')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                    Ditangguhkan
                                </span>
                            @elseif($att && $att->status === 'CHECKED_IN')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Hadir ({{ $att->check_in_at?->format('H:i') }})
                                </span>
                            @elseif($att && $att->status === 'CHECKED_OUT')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">
                                    Checked Out ({{ $att->check_out_at?->format('H:i') }})
                                </span>
                            @elseif($att && $att->status === 'OFF')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-600">
                                    LIBUR (OFF)
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-600">
                                    Belum Check-in
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5">
                            @if($g->status === 'AVAILABLE')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">SIAP (AVAILABLE)</span>
                            @elseif($g->status === 'ON_TOUR')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700">SEDANG TUR (ON TOUR)</span>
                            @elseif($g->status === 'ASSIGNED')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700">DITUGASKAN</span>
                            @elseif($g->status === 'OFF')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">LIBUR (OFF)</span>
                            @elseif($g->status === 'SUSPENDED')
                                <div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                        SUSPENDED
                                    </span>
                                    @if($g->suspend_reason)
                                    <div class="text-[10px] text-rose-600 mt-0.5 max-w-[150px] truncate" title="{{ $g->suspend_reason }}">
                                        {{ $g->suspend_reason }}
                                    </div>
                                    @endif
                                </div>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500">NON-AKTIF</span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 text-right font-medium text-slate-800">
                            @if($g->tipe_fee === 'PER_GUEST')
                                Rp {{ number_format($g->fee_per_guest, 0, ',', '.') }}/pax
                            @elseif($g->tipe_fee === 'PER_BOOKING')
                                Rp {{ number_format($g->fee_per_booking, 0, ',', '.') }}/booking
                            @else
                                Bertingkat (Tiered)
                            @endif
                        </td>

                        <td class="px-4 py-3.5 text-right space-x-1 whitespace-nowrap">
                            @if(auth()->user()->isAdmin())
                            <a href="{{ route('guides.edit', $g) }}" class="inline-flex items-center px-2 py-1 rounded text-[11px] font-medium border border-slate-300 text-slate-700 hover:bg-slate-100">
                                Edit
                            </a>

                            @if($g->status === 'SUSPENDED')
                            <form action="{{ route('guides.unsuspend', $g) }}" method="POST" class="inline-block"
                                  data-confirm="Buka penangguhan untuk guide '{{ $g->nama }}'? Akun guide akan kembali berstatus SIAP (AVAILABLE) dan dapat menerima tugas kembali."
                                  data-confirm-title="Buka Suspend Guide"
                                  data-confirm-btn="Ya, Aktifkan Kembali">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-2 py-1 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors">
                                    Buka Suspend
                                </button>
                            </form>
                            @else
                            <button type="button" @click="openSuspendModal({{ $g->id }}, '{{ addslashes($g->nama) }}')"
                                    class="inline-flex items-center px-2 py-1 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition-colors">
                                Suspend
                            </button>
                            @endif

                            <form action="{{ route('guides.destroy', $g) }}" method="POST" class="inline-block" data-confirm="Hapus guide {{ $g->nama }}? Data ini tidak dapat dikembalikan." data-confirm-title="Hapus Guide" data-confirm-danger="true" data-confirm-btn="Ya, Hapus!">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 rounded text-rose-600 hover:bg-rose-50" title="Hapus Guide">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-400">Belum ada guide terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Suspend Guide Modal -->
    <div x-show="suspendModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="suspendModalOpen = false" class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-rose-700 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Tangguhkan (Suspend) Guide
                </h3>
                <button type="button" @click="suspendModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Anda akan menangguhkan akun pemandu <strong class="text-slate-900" x-text="suspendGuideName"></strong>. Selama masa suspend, guide tidak akan menerima penugasan tour baru dan tidak dapat melakukan absensi.
            </p>

            <form :action="'/guides/' + suspendGuideId + '/suspend'" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Alasan Penangguhan (Wajib) <span class="text-rose-500">*</span></label>
                    <textarea name="reason" rows="3" required placeholder="Contoh: Keterlambatan berulang, pelanggaran SOP penugasan, atau evaluasi keluhan tamu..."
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md focus:bg-white focus:border-rose-500 outline-none"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                    <button type="button" @click="suspendModalOpen = false" class="px-3 py-2 border border-slate-300 rounded-lg text-slate-600 font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold transition-colors shadow-xs">
                        Tangguhkan Guide Ini
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reorder Queue Modal -->
    <div x-show="reorderModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="reorderModalOpen = false" class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Atur Urutan Antrean Permanen Guide</h3>
                <button type="button" @click="reorderModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <p class="text-xs text-slate-500">
                Ubah angka nomor antrean permanen. Sistem rotasi akan mengikuti urutan angka ini secara berkesinambungan.
            </p>

            <form action="{{ route('guides.reorder-queue') }}" method="POST" class="space-y-3">
                @csrf
                @foreach($guides as $guideItem)
                <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200">
                    <span class="text-xs font-bold text-slate-800">{{ $guideItem->nama }} ({{ $guideItem->kode_guide }})</span>
                    <div class="flex items-center space-x-1">
                        <span class="text-xs text-slate-400">#</span>
                        <input type="number" name="queue_order[{{ $guideItem->id }}]" value="{{ $guideItem->queue_number }}" min="1" max="999" required
                               class="w-16 px-2 py-1 text-center font-bold text-xs bg-white border border-slate-300 rounded focus:ring-1 focus:ring-[#21c9a4]">
                    </div>
                </div>
                @endforeach

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                    <button type="button" @click="reorderModalOpen = false" class="px-3 py-2 border border-slate-300 rounded-lg text-xs font-semibold text-slate-600">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-bold transition-colors">
                        Simpan Urutan &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
