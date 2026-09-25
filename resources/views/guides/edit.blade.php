@extends('layouts.app')

@section('title', 'Edit Guide ' . $guide->nama)
@section('header', 'Edit Guide: ' . $guide->nama)

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl border border-slate-200/80 p-6 shadow-2xs">
    <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-base font-bold text-slate-900">Perbarui Data Guide</h2>
            <p class="text-xs text-slate-500">Edit kontak, nomor antrean permanen, status dan parameter fee</p>
        </div>
        <a href="{{ route('guides.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('guides.update', $guide) }}" method="POST" class="space-y-4 text-xs">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="nama" value="{{ old('nama', $guide->nama) }}" required
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nomor Antrean Permanen</label>
                <input type="number" name="queue_number" value="{{ old('queue_number', $guide->queue_number) }}" min="1" required
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md font-bold">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon', $guide->nomor_telepon) }}" required
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div x-data="{ currentStatus: '{{ old('status', $guide->status) }}' }">
                <label class="block font-semibold text-slate-700 mb-1">Status Master Guide</label>
                <select name="status" x-model="currentStatus" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md focus:bg-white focus:border-[#14433b] outline-none">
                    <option value="AVAILABLE">Siap (AVAILABLE)</option>
                    <option value="ASSIGNED">Ditugaskan (ASSIGNED)</option>
                    <option value="ON_TOUR">Sedang Tur (ON TOUR)</option>
                    <option value="OFF">Libur (OFF)</option>
                    <option value="SUSPENDED">Ditangguhkan (SUSPENDED)</option>
                    <option value="INACTIVE">Non-Aktif (INACTIVE)</option>
                </select>

                <div x-show="currentStatus === 'SUSPENDED'" class="mt-2 p-2.5 bg-rose-50 border border-rose-200 rounded-lg text-xs space-y-1">
                    <label class="block font-semibold text-rose-800">Alasan Penangguhan (Suspend Reason)</label>
                    <input type="text" name="suspend_reason" value="{{ old('suspend_reason', $guide->suspend_reason) }}"
                           placeholder="Contoh: Terlambat melapor / evaluasi SOP..."
                           class="w-full px-2.5 py-1.5 bg-white border border-rose-300 rounded text-xs focus:ring-1 focus:ring-rose-500 outline-none">
                    @if($guide->suspended_at)
                    <div class="text-[10px] text-rose-600">
                        Disuspend pada: {{ $guide->suspended_at->format('d M Y, H:i') }}
                        @if($guide->suspendedBy)
                            oleh {{ $guide->suspendedBy->name }}
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Bahasa yang Dikuasai</label>
                <input type="text" name="bahasa" value="{{ old('bahasa', $guide->bahasa) }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Spesialisasi</label>
                <input type="text" name="spesialisasi" value="{{ old('spesialisasi', $guide->spesialisasi) }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Tipe Fee</label>
                <select name="tipe_fee" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
                    <option value="PER_GUEST" {{ $guide->tipe_fee === 'PER_GUEST' ? 'selected' : '' }}>Per Tamu (PER GUEST)</option>
                    <option value="PER_BOOKING" {{ $guide->tipe_fee === 'PER_BOOKING' ? 'selected' : '' }}>Per Booking (PER BOOKING)</option>
                    <option value="TIERED" {{ $guide->tipe_fee === 'TIERED' ? 'selected' : '' }}>Bertingkat (TIERED)</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Fee per Tamu (Rp)</label>
                <input type="number" name="fee_per_guest" value="{{ old('fee_per_guest', $guide->fee_per_guest) }}" min="0" step="5000"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md font-semibold">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Fee per Booking (Rp)</label>
                <input type="number" name="fee_per_booking" value="{{ old('fee_per_booking', $guide->fee_per_booking) }}" min="0" step="10000"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md font-semibold">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Fee Minimum (Rp)</label>
                <input type="number" name="fee_minimum" value="{{ old('fee_minimum', $guide->fee_minimum) }}" min="0" step="10000"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md font-semibold">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Alamat Domisili</label>
            <input type="text" name="alamat" value="{{ old('alamat', $guide->alamat) }}"
                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Catatan Tambahan</label>
            <textarea name="catatan" rows="2" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">{{ old('catatan', $guide->catatan) }}</textarea>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-2">
            <a href="{{ route('guides.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 font-semibold">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white font-bold transition-colors">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
