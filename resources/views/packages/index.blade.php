@extends('layouts.app')

@section('title', 'Paket Wisata & Tarif')
@section('header', 'Paket Wisata & Skema Tarif')

@section('content')
<div class="space-y-6">
    <div class="clay-card p-5 md:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-[#29283D] flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-[#6C63FF]/10 text-[#6C63FF] flex items-center justify-center"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
                <span>Katalog Paket Wisata Bali & Skema Harga</span>
            </h2>
            <p class="text-xs text-[#77768C] mt-1">Harga per tamu otomatis dipilih sistem berdasarkan kuota jumlah tamu booking</p>
        </div>

        @if(auth()->user()->isAdmin())
        <a href="{{ route('packages.create') }}" class="clay-btn clay-btn-primary px-4 py-2 font-bold text-xs inline-flex items-center gap-1.5 self-start sm:self-auto">
            <span>+</span> Tambah Paket Baru
        </a>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($packages as $pkg)
        <div class="clay-card p-6 space-y-4">
            <div class="flex items-start justify-between border-b border-[#E8E6F0] pb-4">
                <div>
                    <h3 class="text-lg font-extrabold text-[#29283D]">{{ $pkg->name }}</h3>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-[#F4F3FA] text-[#77768C] border border-[#E8E6F0]">
                            Durasi: {{ $pkg->duration }}
                        </span>
                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold {{ $pkg->status === 'ACTIVE' ? 'bg-[#72D6C9]/20 text-[#0f766e] border border-[#72D6C9]/40' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                            {{ $pkg->status === 'ACTIVE' ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                        @if($pkg->prices->isNotEmpty())
                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold bg-[#FFD166]/25 text-[#b45309] border border-[#FFD166]/50">
                            Paket Khusus Bertingkat
                        </span>
                        @else
                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-[#6C63FF]/10 text-[#6C63FF] border border-[#6C63FF]/25">
                            Tarif 1 Pax (Standar)
                        </span>
                        @endif
                    </div>
                </div>

                @if(auth()->user()->isAdmin())
                <div class="flex items-center space-x-2 shrink-0">
                    <a href="{{ route('packages.edit', $pkg) }}" class="clay-btn clay-btn-secondary px-3 py-1.5 text-xs font-bold inline-flex items-center">
                        Edit
                    </a>
                    <form action="{{ route('packages.destroy', $pkg) }}" method="POST"
                          data-confirm="Apakah Anda yakin ingin menghapus paket wisata '{{ $pkg->name }}'? Seluruh skema harga dan data terkait akan dihapus permanen."
                          data-confirm-title="Hapus Paket Wisata"
                          data-confirm-btn="Ya, Hapus Paket"
                          data-confirm-danger="true">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="clay-btn clay-btn-coral px-3 py-1.5 text-xs font-bold inline-flex items-center cursor-pointer">
                            Hapus
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <p class="text-xs text-[#77768C] line-clamp-2 leading-relaxed">{{ $pkg->description ?? 'Tidak ada deskripsi.' }}</p>

            <!-- Price Highlight Box (1 Pax Standard) -->
            <div class="bg-[#F4F3FA] border border-[#E8E6F0] rounded-2xl p-4 flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-extrabold text-[#77768C] uppercase tracking-wider">Harga per Tamu (1 Pax)</div>
                    <div class="text-[11px] text-[#77768C] mt-0.5">
                        {{ $pkg->prices->isNotEmpty() ? 'Tarif dasar (berlaku jika di luar rombongan)' : 'Berlaku tetap untuk berapapun jumlah tamu' }}
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-lg font-extrabold text-[#6C63FF]">Rp {{ number_format($pkg->default_price, 0, ',', '.') }}</span>
                    <span class="text-[11px] font-bold text-[#77768C]">/ orang</span>
                </div>
            </div>

            <!-- Tiered Pricing Table (Only shown for Special Packages) -->
            @if($pkg->prices->isNotEmpty())
            <div class="space-y-2 pt-1">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-extrabold text-[#b45309] uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#FFD166] inline-block"></span>
                        Skema Khusus Bertingkat:
                    </span>
                    <span class="text-[10px] text-[#77768C] font-bold">{{ $pkg->prices->count() }} tingkatan</span>
                </div>
                <div class="border border-[#E8E6F0] rounded-xl overflow-hidden shadow-xs">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#F4F3FA] text-[#77768C] border-b border-[#E8E6F0] font-bold">
                            <tr>
                                <th class="px-3.5 py-2">Rentang Tamu (Pax)</th>
                                <th class="px-3.5 py-2 text-right">Harga per Tamu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E8E6F0]">
                            @foreach($pkg->prices as $priceTier)
                            <tr class="hover:bg-[#F4F3FA]/50 transition-colors">
                                <td class="px-3.5 py-2 font-bold text-[#29283D]">
                                    {{ $priceTier->min_guests }} - {{ $priceTier->max_guests }} tamu
                                </td>
                                <td class="px-3.5 py-2 text-right font-extrabold text-[#6C63FF]">
                                    Rp {{ number_format($priceTier->price_per_guest, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="md:col-span-2 clay-card p-10 text-center text-[#77768C] text-xs">
            <div class="w-12 h-12 mx-auto mb-2 text-[#6C63FF]/40"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
            <p class="font-bold">Belum ada paket wisata terdaftar.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
