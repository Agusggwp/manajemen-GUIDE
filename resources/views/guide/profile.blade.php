@extends('layouts.guide')

@section('title', 'Profil Saya')

@section('content')
<div class="space-y-4">
    <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs space-y-4">
        <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
            <div class="w-12 h-12 rounded-xl bg-[#14433b] text-[#21c9a4] font-extrabold flex items-center justify-center text-lg shadow-xs">
                #{{ $guide->queue_number }}
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-900">{{ $guide->nama }}</h2>
                <p class="text-[11px] text-slate-500 font-mono">{{ $guide->kode_guide }} &bull; {{ $guide->email }}</p>
                <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">
                    STATUS: {{ $guide->status }}
                </span>
            </div>
        </div>

        <form action="{{ route('guide.update-profile') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nomor WhatsApp / Telepon</label>
                <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon', $guide->nomor_telepon) }}" required
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Bahasa yang Dikuasai</label>
                <input type="text" name="bahasa" value="{{ old('bahasa', $guide->bahasa) }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Spesialisasi Tour</label>
                <input type="text" name="spesialisasi" value="{{ old('spesialisasi', $guide->spesialisasi) }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Alamat Domisili</label>
                <textarea name="alamat" rows="2" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">{{ old('alamat', $guide->alamat) }}</textarea>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-bold transition-colors shadow-xs">
                    Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
