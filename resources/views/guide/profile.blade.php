@extends('layouts.guide')

@section('title', 'Profil Saya')

@section('content')
<div class="space-y-4">
    <div class="clay-card p-5 space-y-4">
        <div class="flex items-center space-x-3.5 pb-4 border-b border-[#E8E6F0]">
            <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-[#6C63FF] to-[#554cd8] text-white font-extrabold flex items-center justify-center text-lg shadow-md shadow-[#6C63FF]/25 shrink-0">
                #{{ $guide->queue_number }}
            </div>
            <div>
                <h2 class="text-base font-bold text-[#29283D] font-display">{{ $guide->nama }}</h2>
                <p class="text-[11px] text-[#77768C] font-mono">{{ $guide->kode_guide }} &bull; {{ $guide->email }}</p>
                <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#72D6C9]/20 text-[#147a6f] border border-[#72D6C9]/40">
                    STATUS: {{ $guide->status }}
                </span>
            </div>
        </div>

        <form action="{{ route('guide.update-profile') }}" method="POST" class="space-y-3.5 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Nomor WhatsApp / Telepon</label>
                <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon', $guide->nomor_telepon) }}" required
                       class="clay-input text-xs">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Bahasa yang Dikuasai</label>
                <input type="text" name="bahasa" value="{{ old('bahasa', $guide->bahasa) }}"
                       class="clay-input text-xs">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Spesialisasi Tour</label>
                <input type="text" name="spesialisasi" value="{{ old('spesialisasi', $guide->spesialisasi) }}"
                       class="clay-input text-xs">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Alamat Domisili</label>
                <textarea name="alamat" rows="2" class="clay-input text-xs">{{ old('alamat', $guide->alamat) }}</textarea>
            </div>

            <div class="pt-2">
                <button type="submit" class="clay-btn clay-btn-primary w-full py-3.5 px-4 text-xs font-bold tracking-wide">
                    Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

