@extends('layouts.app')

@section('title', 'Edit Guide ' . $guide->nama)
@section('header', 'Edit Guide: ' . $guide->nama)

@section('content')
<div class="max-w-3xl mx-auto clay-card p-6 md:p-8">
    <div class="border-b border-[#E8E6F0] pb-5 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-extrabold text-[#29283D] flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-[#6C63FF]/10 text-[#6C63FF] flex items-center justify-center text-base">✏️</span>
                <span>Perbarui Data Guide</span>
            </h2>
            <p class="text-xs text-[#77768C] mt-1">Edit kontak, nomor antrean permanen, status dan parameter fee</p>
        </div>
        <a href="{{ route('guides.index') }}" class="clay-btn clay-btn-secondary text-xs px-3.5 py-2 inline-flex items-center gap-1.5 self-start sm:self-auto font-bold">
            <span>&larr;</span> Kembali
        </a>
    </div>

    <form action="{{ route('guides.update', $guide) }}" method="POST" class="space-y-5 text-xs">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Nama Lengkap <span class="text-[#FF8A65]">*</span></label>
                <input type="text" name="nama" value="{{ old('nama', $guide->nama) }}" required
                       class="w-full clay-input">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Nomor Antrean Permanen <span class="text-[#FF8A65]">*</span></label>
                <input type="number" name="queue_number" value="{{ old('queue_number', $guide->queue_number) }}" min="1" required
                       class="w-full clay-input font-extrabold text-[#6C63FF]">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Nomor Telepon / WhatsApp <span class="text-[#FF8A65]">*</span></label>
                <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon', $guide->nomor_telepon) }}" required
                       class="w-full clay-input">
            </div>

            <div x-data="{ currentStatus: '{{ old('status', $guide->status) }}' }">
                <label class="block font-bold text-[#29283D] mb-1.5">Status Master Guide</label>
                <select name="status" x-model="currentStatus" class="w-full clay-input font-bold">
                    <option value="AVAILABLE">Siap (AVAILABLE)</option>
                    <option value="ASSIGNED">Ditugaskan (ASSIGNED)</option>
                    <option value="ON_TOUR">Sedang Tur (ON TOUR)</option>
                    <option value="OFF">Libur (OFF)</option>
                    <option value="SUSPENDED">Ditangguhkan (SUSPENDED)</option>
                    <option value="INACTIVE">Non-Aktif (INACTIVE)</option>
                </select>

                <div x-show="currentStatus === 'SUSPENDED'" class="mt-2.5 p-3 bg-[#FF8A65]/10 border border-[#FF8A65]/30 rounded-xl text-xs space-y-1.5">
                    <label class="block font-bold text-[#FF8A65]">Alasan Penangguhan (Suspend Reason)</label>
                    <input type="text" name="suspend_reason" value="{{ old('suspend_reason', $guide->suspend_reason) }}"
                           placeholder="Contoh: Terlambat melapor / evaluasi SOP..."
                           class="w-full clay-input text-xs py-1.5">
                    @if($guide->suspended_at)
                    <div class="text-[10px] text-[#FF8A65] font-semibold">
                        Disuspend pada: {{ $guide->suspended_at->format('d M Y, H:i') }}
                        @if($guide->suspendedBy)
                            oleh {{ $guide->suspendedBy->name }}
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Bahasa yang Dikuasai</label>
                <input type="text" name="bahasa" value="{{ old('bahasa', $guide->bahasa) }}"
                       class="w-full clay-input">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Spesialisasi</label>
                <input type="text" name="spesialisasi" value="{{ old('spesialisasi', $guide->spesialisasi) }}"
                       class="w-full clay-input">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Tipe Fee</label>
                <select name="tipe_fee" class="w-full clay-input">
                    <option value="PER_GUEST" {{ $guide->tipe_fee === 'PER_GUEST' ? 'selected' : '' }}>Per Tamu (PER GUEST)</option>
                    <option value="PER_BOOKING" {{ $guide->tipe_fee === 'PER_BOOKING' ? 'selected' : '' }}>Per Booking (PER BOOKING)</option>
                    <option value="TIERED" {{ $guide->tipe_fee === 'TIERED' ? 'selected' : '' }}>Bertingkat (TIERED)</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Fee per Tamu (Rp)</label>
                <input type="number" name="fee_per_guest" value="{{ old('fee_per_guest', $guide->fee_per_guest) }}" min="0" step="5000"
                       class="w-full clay-input font-bold">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Fee per Booking (Rp)</label>
                <input type="number" name="fee_per_booking" value="{{ old('fee_per_booking', $guide->fee_per_booking) }}" min="0" step="10000"
                       class="w-full clay-input font-bold">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Fee Minimum (Rp)</label>
                <input type="number" name="fee_minimum" value="{{ old('fee_minimum', $guide->fee_minimum) }}" min="0" step="10000"
                       class="w-full clay-input font-bold">
            </div>
        </div>

        <div>
            <label class="block font-bold text-[#29283D] mb-1.5">Alamat Domisili</label>
            <input type="text" name="alamat" value="{{ old('alamat', $guide->alamat) }}"
                   class="w-full clay-input">
        </div>

        <div>
            <label class="block font-bold text-[#29283D] mb-1.5">Catatan Tambahan</label>
            <textarea name="catatan" rows="3" class="w-full clay-input resize-y">{{ old('catatan', $guide->catatan) }}</textarea>
        </div>

        <div class="pt-5 border-t border-[#E8E6F0] flex items-center justify-end space-x-3">
            <a href="{{ route('guides.index') }}" class="clay-btn clay-btn-secondary px-5 py-2.5 font-bold">
                Batal
            </a>
            <button type="submit" class="clay-btn clay-btn-primary px-6 py-2.5 font-bold">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
