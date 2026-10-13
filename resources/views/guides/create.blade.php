@extends('layouts.app')

@section('title', 'Tambah Guide Baru')
@section('header', 'Tambah Guide Baru')

@section('content')
<div class="max-w-3xl mx-auto clay-card p-6 md:p-8">
    <div class="border-b border-[#E8E6F0] pb-5 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-extrabold text-[#29283D] flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-[#6C63FF]/10 text-[#6C63FF] flex items-center justify-center text-base">🧭</span>
                <span>Form Pendaftaran Guide Baru</span>
            </h2>
            <p class="text-xs text-[#77768C] mt-1">Mendaftarkan data personal, akun login, dan parameter fee guide</p>
        </div>
        <a href="{{ route('guides.index') }}" class="clay-btn clay-btn-secondary text-xs px-3.5 py-2 inline-flex items-center gap-1.5 self-start sm:self-auto font-bold">
            <span>&larr;</span> Kembali
        </a>
    </div>

    <form action="{{ route('guides.store') }}" method="POST" class="space-y-5 text-xs">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Nama Lengkap <span class="text-[#FF8A65]">*</span></label>
                <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="Contoh: Ketut Sujana"
                       class="w-full clay-input">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Kode Guide <span class="text-[#FF8A65]">*</span></label>
                <input type="text" name="kode_guide" value="{{ old('kode_guide', $nextCode) }}" required placeholder="GD-006"
                       class="w-full clay-input font-mono font-bold text-[#6C63FF]">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Nomor Telepon / WhatsApp <span class="text-[#FF8A65]">*</span></label>
                <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon') }}" required placeholder="08123456789"
                       class="w-full clay-input">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Nomor Antrean Permanen <span class="text-[#FF8A65]">*</span></label>
                <input type="number" name="queue_number" value="{{ old('queue_number', $nextQueueNumber) }}" min="1" required
                       class="w-full clay-input font-extrabold text-[#6C63FF]">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Email Akun Login <span class="text-[#FF8A65]">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="guide@example.com"
                       class="w-full clay-input">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Password Login <span class="text-[#FF8A65]">*</span></label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter"
                       class="w-full clay-input">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Bahasa yang Dikuasai</label>
                <input type="text" name="bahasa" value="{{ old('bahasa', 'Inggris, Indonesia') }}" placeholder="Inggris, Mandarin, Jepang"
                       class="w-full clay-input">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Spesialisasi Tour</label>
                <input type="text" name="spesialisasi" value="{{ old('spesialisasi', 'Culture & Heritage') }}" placeholder="Trekking, Snorkeling, Culture"
                       class="w-full clay-input">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Tipe Fee</label>
                <select name="tipe_fee" class="w-full clay-input">
                    <option value="PER_GUEST">PER GUEST (Dihitung per jumlah tamu)</option>
                    <option value="PER_BOOKING">PER BOOKING (Tarif tetap per tour)</option>
                    <option value="TIERED">TIERED (Bertingkat)</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Fee per Tamu (Rp)</label>
                <input type="number" name="fee_per_guest" value="{{ old('fee_per_guest', 50000) }}" min="0" step="5000"
                       class="w-full clay-input font-bold">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Fee per Booking (Rp)</label>
                <input type="number" name="fee_per_booking" value="{{ old('fee_per_booking', 300000) }}" min="0" step="10000"
                       class="w-full clay-input font-bold">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Fee Minimum (Rp)</label>
                <input type="number" name="fee_minimum" value="{{ old('fee_minimum', 150000) }}" min="0" step="10000"
                       class="w-full clay-input font-bold">
            </div>
        </div>

        <div>
            <label class="block font-bold text-[#29283D] mb-1.5">Alamat Domisili</label>
            <input type="text" name="alamat" value="{{ old('alamat') }}" placeholder="Denpasar, Bali"
                   class="w-full clay-input">
        </div>

        <div>
            <label class="block font-bold text-[#29283D] mb-1.5">Catatan Tambahan</label>
            <textarea name="catatan" rows="3" class="w-full clay-input resize-y" placeholder="Catatan lisensi, preferensi jadwal, dsb...">{{ old('catatan') }}</textarea>
        </div>

        <div class="pt-5 border-t border-[#E8E6F0] flex items-center justify-end space-x-3">
            <a href="{{ route('guides.index') }}" class="clay-btn clay-btn-secondary px-5 py-2.5 font-bold">
                Batal
            </a>
            <button type="submit" class="clay-btn clay-btn-primary px-6 py-2.5 font-bold">
                Simpan Guide Baru
            </button>
        </div>
    </form>
</div>
@endsection
