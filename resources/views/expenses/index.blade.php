@extends('layouts.app')

@section('title', 'Biaya Operasional')
@section('header', 'Biaya Operasional Lapangan')

@section('content')
<div class="space-y-6" x-data="{ createModalOpen: false }">
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Pencatatan Biaya Operasional</h2>
            <p class="text-xs text-slate-500">Total Biaya Tercatat: <strong class="text-rose-600">Rp {{ number_format($totalAmount, 0, ',', '.') }}</strong></p>
        </div>

        <div class="flex items-center gap-2">
            @if(auth()->user()->isAdmin())
            <a href="{{ route('settings.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-600 text-xs font-semibold transition-colors flex items-center gap-1.5" title="Atur Pilihan Kategori Biaya di Pengaturan">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Kelola Kategori</span>
            </a>
            @endif
            <button type="button" @click="createModalOpen = true"
                    class="px-4 py-2 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-bold transition-colors shadow-xs">
                + Tambah Biaya Operasional
            </button>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5">Kategori</th>
                        <th class="px-4 py-3.5">Deskripsi</th>
                        <th class="px-4 py-3.5">Terkait Booking</th>
                        <th class="px-4 py-3.5 text-right">Nominal</th>
                        <th class="px-4 py-3.5">Dicatat Oleh</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expenses as $e)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-4 py-3.5 font-medium text-slate-900">{{ $e->expense_date->format('d M Y') }}</td>
                        <td class="px-4 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">
                                {{ $e->category }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-slate-700 font-medium">{{ $e->description }}</td>
                        <td class="px-4 py-3.5">
                            @if($e->booking)
                                <a href="{{ route('bookings.show', $e->booking) }}" class="font-bold text-[#14433b] hover:underline">
                                    {{ $e->booking->booking_code }}
                                </a>
                            @else
                                <span class="text-slate-400">Umum</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right font-extrabold text-rose-700">
                            Rp {{ number_format($e->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-slate-500">{{ $e->creator?->name ?? 'Admin' }}</td>
                        <td class="px-4 py-3.5 text-right">
                            <form action="{{ route('expenses.destroy', $e) }}" method="POST" data-confirm="Hapus pengeluaran sebesar Rp {{ number_format($e->amount, 0, ',', '.') }} ini?" data-confirm-title="Hapus Biaya Operasional" data-confirm-danger="true" data-confirm-btn="Ya, Hapus!">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 text-[11px] font-medium">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada biaya operasional dicatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
            {{ $expenses->links() }}
        </div>
        @endif
    </div>

    <!-- Create Expense Modal -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="createModalOpen = false" class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">+ Catat Biaya Operasional Baru</h3>
                <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form action="{{ route('expenses.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-semibold text-slate-700">Kategori Biaya <span class="text-rose-500">*</span></label>
                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('settings.index') }}" class="text-[10px] text-[#14433b] hover:underline font-medium">Ubah Pilihan di Setting &rarr;</a>
                        @endif
                    </div>
                    <select name="category" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md focus:bg-white focus:border-[#14433b] outline-none">
                        @foreach(\App\Models\Setting::getExpenseCategories() as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Deskripsi Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="description" required placeholder="Contoh: Tiket Pura Tirta Empul 15 orang"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nominal (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" min="1000" step="1000" required placeholder="150000"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md font-bold text-sm text-slate-900">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tanggal Pengeluaran <span class="text-rose-500">*</span></label>
                    <input type="date" name="expense_date" value="{{ now()->toDateString() }}" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Hubungkan ke Booking (Opsional)</label>
                    <select name="booking_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
                        <option value="">-- Biaya Umum / Tidak Terikat Booking --</option>
                        @foreach($bookings as $bk)
                            <option value="{{ $bk->id }}">{{ $bk->booking_code }} - {{ $bk->customer_name }} ({{ $bk->booking_date->format('d/m/Y') }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                    <button type="button" @click="createModalOpen = false" class="px-3 py-2 border border-slate-300 rounded-lg text-slate-600 font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white font-bold transition-colors shadow-xs">
                        Simpan Biaya
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
