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
    <div class="clay-card p-5 md:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-[#29283D] flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-[#6C63FF]/10 text-[#6C63FF] flex items-center justify-center"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 1010 10A10 10 0 0012 2zm3.5 6.5l-2 5-5 2 2-5 5-2z"/></svg></span>
                <span>Master Data Guide Wisata</span>
            </h2>
            <p class="text-xs text-[#77768C] mt-1">Kelola master guide, status aktif/suspend, dan urutan rotasi antrean</p>
        </div>

        <div class="flex items-center space-x-2.5">
            @if(auth()->user()->isAdmin())
            <button type="button" @click="reorderModalOpen = true"
                    class="clay-btn clay-btn-secondary text-xs px-3.5 py-2 inline-flex items-center gap-1.5 font-bold">
                <span>↕</span> Atur Urutan Antrean
            </button>
            <a href="{{ route('guides.create') }}" 
               class="clay-btn clay-btn-primary text-xs px-4 py-2 font-bold inline-flex items-center gap-1.5">
                <span>+</span> Tambah Guide Baru
            </a>
            @endif
        </div>
    </div>

    <!-- Status Filters & Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-1.5 text-xs">
            <a href="{{ route('guides.index') }}" 
               class="px-3.5 py-1.5 rounded-xl font-bold transition-all text-xs {{ !request()->filled('status') ? 'bg-[#6C63FF] text-white shadow-sm' : 'bg-white border border-[#E8E6F0] text-[#77768C] hover:bg-[#F4F3FA]' }}">
                Semua
            </a>
            <a href="{{ route('guides.index', ['status' => 'AVAILABLE']) }}" 
               class="px-3.5 py-1.5 rounded-xl font-bold transition-all text-xs {{ request('status') === 'AVAILABLE' ? 'bg-[#72D6C9] text-[#134e4a] shadow-sm' : 'bg-white border border-[#E8E6F0] text-[#77768C] hover:bg-[#F4F3FA]' }}">
                Siap (Available)
            </a>
            <a href="{{ route('guides.index', ['status' => 'ON_TOUR']) }}" 
               class="px-3.5 py-1.5 rounded-xl font-bold transition-all text-xs {{ request('status') === 'ON_TOUR' ? 'bg-[#6C63FF] text-white shadow-sm' : 'bg-white border border-[#E8E6F0] text-[#77768C] hover:bg-[#F4F3FA]' }}">
                Sedang Tur
            </a>
            <a href="{{ route('guides.index', ['status' => 'SUSPENDED']) }}" 
               class="px-3.5 py-1.5 rounded-xl font-bold transition-all text-xs {{ request('status') === 'SUSPENDED' ? 'bg-[#FF8A65] text-white shadow-sm' : 'bg-[#FF8A65]/10 border border-[#FF8A65]/30 text-[#FF8A65] hover:bg-[#FF8A65]/20' }}">
                Ditangguhkan (Suspended)
            </a>
            <a href="{{ route('guides.index', ['status' => 'OFF']) }}" 
               class="px-3.5 py-1.5 rounded-xl font-bold transition-all text-xs {{ request('status') === 'OFF' ? 'bg-[#29283D] text-white shadow-sm' : 'bg-white border border-[#E8E6F0] text-[#77768C] hover:bg-[#F4F3FA]' }}">
                Libur (Off)
            </a>
            <a href="{{ route('guides.index', ['status' => 'INACTIVE']) }}" 
               class="px-3.5 py-1.5 rounded-xl font-bold transition-all text-xs {{ request('status') === 'INACTIVE' ? 'bg-slate-600 text-white shadow-sm' : 'bg-white border border-[#E8E6F0] text-[#77768C] hover:bg-[#F4F3FA]' }}">
                Non-Aktif
            </a>
        </div>

        <form method="GET" action="{{ route('guides.index') }}" class="flex items-center gap-2">
            @if(request()->filled('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / kode guide..."
                   class="clay-input text-xs py-1.5 px-3 min-w-[200px]">
            <button type="submit" class="clay-btn clay-btn-primary px-3.5 py-1.5 text-xs font-bold">
                Cari
            </button>
        </form>
    </div>

    <!-- Guides Table -->
    <div class="clay-table-wrapper">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F4F3FA] text-[#77768C] font-bold border-b border-[#E8E6F0]">
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
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($guides as $g)
                    <tr class="hover:bg-[#F4F3FA]/70 transition-colors {{ $g->status === 'SUSPENDED' ? 'bg-[#FF8A65]/5' : '' }}">
                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl font-extrabold text-xs {{ $g->status === 'SUSPENDED' ? 'bg-[#FF8A65]/15 text-[#FF8A65] border border-[#FF8A65]/30' : 'bg-[#6C63FF]/15 text-[#6C63FF] border border-[#6C63FF]/20' }} shadow-xs">
                                #{{ $g->queue_number }}
                            </span>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="font-extrabold text-[#29283D] text-sm flex items-center gap-1.5">
                                <span>{{ $g->nama }}</span>
                                @if($g->status === 'SUSPENDED')
                                <span class="px-2 py-0.5 rounded-lg text-[9px] font-extrabold bg-[#FF8A65] text-white uppercase tracking-wider">SUSPENDED</span>
                                @endif
                            </div>
                            <div class="text-[10px] text-[#77768C] font-mono mt-0.5">{{ $g->kode_guide }}</div>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="text-[#29283D] font-medium">{{ $g->nomor_telepon }}</div>
                            <div class="text-[10px] text-[#77768C]">{{ $g->email ?? '-' }}</div>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="text-[#29283D] font-medium">{{ $g->bahasa ?? 'Indonesia, English' }}</div>
                            <div class="text-[10px] text-[#77768C]">{{ $g->spesialisasi ?? 'General Tour' }}</div>
                        </td>

                        <td class="px-4 py-3.5">
                            @php $att = $g->todayAttendance; @endphp
                            @if($g->status === 'SUSPENDED')
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-[#FF8A65]/15 text-[#FF8A65] border border-[#FF8A65]/30">
                                    Ditangguhkan
                                </span>
                            @elseif($att && $att->status === 'CHECKED_IN')
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-[#72D6C9]/20 text-[#0f766e] border border-[#72D6C9]/40">
                                    Hadir ({{ $att->check_in_at?->format('H:i') }})
                                </span>
                            @elseif($att && $att->status === 'CHECKED_OUT')
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                    Checked Out ({{ $att->check_out_at?->format('H:i') }})
                                </span>
                            @elseif($att && $att->status === 'OFF')
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-[#FF8A65]/15 text-[#FF8A65] border border-[#FF8A65]/30">
                                    LIBUR (OFF)
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-[#FFD166]/30 text-[#b45309] border border-[#FFD166]/50">
                                    Belum Check-in
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5">
                            @if($g->status === 'AVAILABLE')
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-[#72D6C9]/20 text-[#0f766e] border border-[#72D6C9]/40">SIAP (AVAILABLE)</span>
                            @elseif($g->status === 'ON_TOUR')
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-[#6C63FF]/15 text-[#6C63FF] border border-[#6C63FF]/30">SEDANG TUR (ON TOUR)</span>
                            @elseif($g->status === 'ASSIGNED')
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-[#6C63FF]/10 text-[#6C63FF] border border-[#6C63FF]/20">DITUGASKAN</span>
                            @elseif($g->status === 'OFF')
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">LIBUR (OFF)</span>
                            @elseif($g->status === 'SUSPENDED')
                                <div>
                                    <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold bg-[#FF8A65]/15 text-[#FF8A65] border border-[#FF8A65]/30">
                                        SUSPENDED
                                    </span>
                                    @if($g->suspend_reason)
                                    <div class="text-[10px] text-[#FF8A65] mt-1 max-w-[150px] truncate" title="{{ $g->suspend_reason }}">
                                        {{ $g->suspend_reason }}
                                    </div>
                                    @endif
                                </div>
                            @else
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-slate-100 text-slate-500">NON-AKTIF</span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 text-right font-bold text-[#29283D]">
                            @if($g->tipe_fee === 'PER_GUEST')
                                Rp {{ number_format($g->fee_per_guest, 0, ',', '.') }}/pax
                            @elseif($g->tipe_fee === 'PER_BOOKING')
                                Rp {{ number_format($g->fee_per_booking, 0, ',', '.') }}/booking
                            @else
                                Bertingkat (Tiered)
                            @endif
                        </td>

                        <td class="px-4 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                            @if(auth()->user()->isAdmin())
                            <a href="{{ route('guides.edit', $g) }}" class="clay-btn clay-btn-secondary px-2.5 py-1 text-[11px] font-bold inline-flex items-center">
                                Edit
                            </a>

                            @if($g->status === 'SUSPENDED')
                            <form action="{{ route('guides.unsuspend', $g) }}" method="POST" class="inline-block"
                                  data-confirm="Buka penangguhan untuk guide '{{ $g->nama }}'? Akun guide akan kembali berstatus SIAP (AVAILABLE) dan dapat menerima tugas kembali."
                                  data-confirm-title="Buka Suspend Guide"
                                  data-confirm-btn="Ya, Aktifkan Kembali">
                                @csrf
                                <button type="submit" class="clay-btn clay-btn-turquoise px-2.5 py-1 text-[11px] font-bold inline-flex items-center">
                                    Buka Suspend
                                </button>
                            </form>
                            @else
                            <button type="button" @click="openSuspendModal({{ $g->id }}, '{{ addslashes($g->nama) }}')"
                                    class="clay-btn clay-btn-coral px-2.5 py-1 text-[11px] font-bold inline-flex items-center">
                                Suspend
                            </button>
                            @endif

                            <form action="{{ route('guides.destroy', $g) }}" method="POST" class="inline-block" data-confirm="Hapus guide {{ $g->nama }}? Data ini tidak dapat dikembalikan." data-confirm-title="Hapus Guide" data-confirm-danger="true" data-confirm-btn="Ya, Hapus!">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-xl text-[#FF8A65] hover:bg-[#FF8A65]/10 transition-colors" title="Hapus Guide">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-[#77768C]">
                            <div class="w-12 h-12 mx-auto mb-2 text-[#6C63FF]/40"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2a10 10 0 1010 10A10 10 0 0012 2zm3.5 6.5l-2 5-5 2 2-5 5-2z"/></svg></div>
                            <p class="font-bold">Belum ada guide terdaftar.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Suspend Guide Modal -->
    <div x-show="suspendModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#29283D]/50 backdrop-blur-xs">
        <div @click.away="suspendModalOpen = false" class="clay-card max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3">
                <h3 class="text-sm font-extrabold text-[#FF8A65] flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Tangguhkan (Suspend) Guide
                </h3>
                <button type="button" @click="suspendModalOpen = false" class="text-[#77768C] hover:text-[#29283D] text-lg font-bold">&times;</button>
            </div>

            <p class="text-xs text-[#77768C] leading-relaxed">
                Anda akan menangguhkan akun pemandu <strong class="text-[#29283D]" x-text="suspendGuideName"></strong>. Selama masa suspend, guide tidak akan menerima penugasan tour baru dan tidak dapat melakukan absensi.
            </p>

            <form :action="'/guides/' + suspendGuideId + '/suspend'" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-[#29283D] mb-1.5">Alasan Penangguhan (Wajib) <span class="text-[#FF8A65]">*</span></label>
                    <textarea name="reason" rows="3" required placeholder="Contoh: Keterlambatan berulang, pelanggaran SOP penugasan, atau evaluasi keluhan tamu..."
                              class="w-full clay-input resize-y"></textarea>
                </div>

                <div class="pt-3 border-t border-[#E8E6F0] flex items-center justify-end space-x-2.5">
                    <button type="button" @click="suspendModalOpen = false" class="clay-btn clay-btn-secondary px-4 py-2 font-bold">
                        Batal
                    </button>
                    <button type="submit" class="clay-btn clay-btn-coral px-4 py-2 font-bold">
                        Tangguhkan Guide Ini
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reorder Queue Modal -->
    <div x-show="reorderModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#29283D]/50 backdrop-blur-xs">
        <div @click.away="reorderModalOpen = false" class="clay-card max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3">
                <h3 class="text-sm font-extrabold text-[#29283D] flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-[#6C63FF]/10 text-[#6C63FF] flex items-center justify-center text-xs">↕</span>
                    Atur Urutan Antrean Permanen Guide
                </h3>
                <button type="button" @click="reorderModalOpen = false" class="text-[#77768C] hover:text-[#29283D] text-lg font-bold">&times;</button>
            </div>

            <p class="text-xs text-[#77768C]">
                Ubah angka nomor antrean permanen. Sistem rotasi akan mengikuti urutan angka ini secara berkesinambungan.
            </p>

            <form action="{{ route('guides.reorder-queue') }}" method="POST" class="space-y-3">
                @csrf
                <div class="max-h-72 overflow-y-auto space-y-2 pr-1">
                    @foreach($guides as $guideItem)
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#F4F3FA] border border-[#E8E6F0]">
                        <span class="text-xs font-bold text-[#29283D]">{{ $guideItem->nama }} ({{ $guideItem->kode_guide }})</span>
                        <div class="flex items-center space-x-1">
                            <span class="text-xs font-bold text-[#77768C]">#</span>
                            <input type="number" name="queue_order[{{ $guideItem->id }}]" value="{{ $guideItem->queue_number }}" min="1" max="999" required
                                   class="w-16 px-2 py-1 text-center font-bold text-xs clay-input">
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="pt-3 border-t border-[#E8E6F0] flex items-center justify-end space-x-2.5">
                    <button type="button" @click="reorderModalOpen = false" class="clay-btn clay-btn-secondary px-4 py-2 font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="clay-btn clay-btn-primary px-4 py-2 font-bold text-xs">
                        Simpan Urutan &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
