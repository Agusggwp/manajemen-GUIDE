@extends('layouts.app')

@section('title', 'Paket Wisata & Tarif')
@section('header', 'Paket Wisata & Skema Tarif')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Katalog Paket Wisata Bali & Skema Harga</h2>
            <p class="text-xs text-slate-500">Harga per tamu otomatis dipilih sistem berdasarkan kuota jumlah tamu booking</p>
        </div>

        @if(auth()->user()->isAdmin())
        <a href="{{ route('packages.create') }}" class="px-4 py-2 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-bold transition-colors shadow-xs">
            + Tambah Paket Baru
        </a>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($packages as $pkg)
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs space-y-4">
            <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">{{ $pkg->name }}</h3>
                    <div class="flex flex-wrap items-center gap-1.5 mt-1">
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">Durasi: {{ $pkg->duration }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $pkg->status === 'ACTIVE' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $pkg->status === 'ACTIVE' ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                        @if($pkg->prices->isNotEmpty())
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200/80">
                            Paket Khusus Bertingkat
                        </span>
                        @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                            Tarif 1 Pax (Standar)
                        </span>
                        @endif
                    </div>
                </div>

                @if(auth()->user()->isAdmin())
                <div class="flex items-center space-x-2 shrink-0">
                    <a href="{{ route('packages.edit', $pkg) }}" class="px-2.5 py-1 text-xs font-semibold text-[#14433b] hover:text-[#1b584d] bg-[#e6faf5] hover:bg-[#d0f5ec] rounded-md transition-colors border border-[#21c9a4]/30">
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
                                class="px-2.5 py-1 text-xs font-semibold text-rose-700 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-md transition-colors cursor-pointer">
                            Hapus
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <p class="text-xs text-slate-600 line-clamp-2">{{ $pkg->description ?? 'Tidak ada deskripsi.' }}</p>

            <!-- Price Highlight Box (1 Pax Standard) -->
            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Harga per Tamu (1 Pax)</div>
                    <div class="text-[11px] text-slate-400">
                        {{ $pkg->prices->isNotEmpty() ? 'Tarif dasar (berlaku jika di luar rombongan)' : 'Berlaku tetap untuk berapapun jumlah tamu' }}
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-base font-extrabold text-[#14433b]">Rp {{ number_format($pkg->default_price, 0, ',', '.') }}</span>
                    <span class="text-[11px] font-medium text-slate-400">/ orang</span>
                </div>
            </div>

            <!-- Tiered Pricing Table (Only shown for Special Packages) -->
            @if($pkg->prices->isNotEmpty())
            <div class="space-y-2 pt-1">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-amber-900 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span>
                        Skema Khusus Bertingkat:
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium">{{ $pkg->prices->count() }} tingkatan</span>
                </div>
                <div class="border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 border-b border-slate-100 font-semibold">
                            <tr>
                                <th class="px-3 py-1.5">Rentang Tamu (Pax)</th>
                                <th class="px-3 py-1.5 text-right">Harga per Tamu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($pkg->prices as $priceTier)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-3 py-1.5 font-medium text-slate-700">
                                    {{ $priceTier->min_guests }} - {{ $priceTier->max_guests }} tamu
                                </td>
                                <td class="px-3 py-1.5 text-right font-bold text-[#14433b]">
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
        <div class="md:col-span-2 bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-400 text-xs">
            Belum ada paket wisata terdaftar.
        </div>
        @endforelse
    </div>
</div>
@endsection
