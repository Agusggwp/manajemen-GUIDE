@extends('layouts.app')

@section('title', 'Tambah Guide Baru')
@section('header', 'Tambah Guide Baru')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl border border-slate-200/80 p-6 shadow-2xs">
    <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-base font-bold text-slate-900">Form Pendaftaran Guide Baru</h2>
            <p class="text-xs text-slate-500">Mendaftarkan data personal, akun login, dan parameter fee guide</p>
        </div>
        <a href="{{ route('guides.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">
            &larr; Kembali
        </a>
    </div>

    <form action="{{ route('guides.store') }}" method="POST" class="space-y-4 text-xs">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="Contoh: Ketut Sujana"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Kode Guide <span class="text-rose-500">*</span></label>
                <input type="text" name="kode_guide" value="{{ old('kode_guide', $nextCode) }}" required placeholder="GD-006"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md font-mono">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nomor Telepon / WhatsApp <span class="text-rose-500">*</span></label>
                <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon') }}" required placeholder="08123456789"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nomor Antrean Permanen <span class="text-rose-500">*</span></label>
                <input type="number" name="queue_number" value="{{ old('queue_number', $nextQueueNumber) }}" min="1" required
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md font-bold">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Email Akun Login <span class="text-rose-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="guide@example.com"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Password Login <span class="text-rose-500">*</span></label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Bahasa yang Dikuasai</label>
                <input type="text" name="bahasa" value="{{ old('bahasa', 'Inggris, Indonesia') }}" placeholder="Inggris, Mandarin, Jepang"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Spesialisasi Tour</label>
                <input type="text" name="spesialisasi" value="{{ old('spesialisasi', 'Culture & Heritage') }}" placeholder="Trekking, Snorkeling, Culture"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Tipe Fee</label>
                <select name="tipe_fee" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
                    <option value="PER_GUEST">PER GUEST (Dihitung per jumlah tamu)</option>
                    <option value="PER_BOOKING">PER BOOKING (Tarif tetap per tour)</option>
                    <option value="TIERED">TIERED (Bertingkat)</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Fee per Tamu (Rp)</label>
                <input type="number" name="fee_per_guest" value="{{ old('fee_per_guest', 50000) }}" min="0" step="5000"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md font-semibold">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Fee per Booking (Rp)</label>
                <input type="number" name="fee_per_booking" value="{{ old('fee_per_booking', 300000) }}" min="0" step="10000"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md font-semibold">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Fee Minimum (Rp)</label>
                <input type="number" name="fee_minimum" value="{{ old('fee_minimum', 150000) }}" min="0" step="10000"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md font-semibold">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Alamat Domisili</label>
            <input type="text" name="alamat" value="{{ old('alamat') }}" placeholder="Denpasar, Bali"
                   class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Catatan Tambahan</label>
            <textarea name="catatan" rows="2" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">{{ old('catatan') }}</textarea>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-2">
            <a href="{{ route('guides.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 font-semibold">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white font-bold transition-colors">
                Simpan Guide Baru
            </button>
        </div>
    </form>
</div>
@endsection
