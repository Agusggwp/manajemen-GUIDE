@extends('layouts.app')

@section('title', 'Biaya Operasional')
@section('header', 'Biaya Operasional Lapangan')

@section('content')
<div class="space-y-6" x-data="{ createModalOpen: false }">
    <div class="clay-card p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-[#29283D]">Pencatatan Biaya Operasional</h2>
            <p class="text-xs text-[#77768C] mt-1">Total Biaya Tercatat: <strong class="text-rose-600 font-black">Rp {{ number_format($totalAmount, 0, ',', '.') }}</strong></p>
        </div>

        <div class="flex items-center gap-2.5">
            @if(auth()->user()->isAdmin())
            <a href="{{ route('settings.index') }}" class="clay-btn-secondary px-3.5 py-2 text-xs font-bold inline-flex items-center gap-1.5" title="Atur Pilihan Kategori Biaya di Pengaturan">
                <svg class="w-3.5 h-3.5 text-[#77768C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Kelola Kategori</span>
            </a>
            @endif
            <button type="button" @click="createModalOpen = true"
                    class="clay-btn-primary px-4 py-2 text-xs font-bold inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#FFD166]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span>+ Tambah Biaya Operasional</span>
            </button>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="clay-card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F4F3FA] text-[#77768C] font-bold border-b border-[#E8E6F0]">
                    <tr>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Kategori</th>
                        <th class="px-5 py-3.5">Deskripsi</th>
                        <th class="px-5 py-3.5">Terkait Booking</th>
                        <th class="px-5 py-3.5 text-right">Nominal</th>
                        <th class="px-5 py-3.5">Dicatat Oleh</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($expenses as $e)
                    <tr class="hover:bg-purple-50/30 transition-colors">
                        <td class="px-5 py-3.5 font-medium text-[#29283D]">{{ $e->expense_date->format('d M Y') }}</td>
                        <td class="px-5 py-3.5">
                            <span class="clay-badge bg-[#F4F3FA] text-[#29283D] border-[#E8E6F0]">
                                {{ $e->category }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-[#29283D] font-medium">{{ $e->description }}</td>
                        <td class="px-5 py-3.5">
                            @if($e->booking)
                                <a href="{{ route('bookings.show', $e->booking) }}" class="font-bold text-[#6C63FF] hover:underline">
                                    {{ $e->booking->booking_code }}
                                </a>
                            @else
                                <span class="text-[#77768C]">Umum</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right font-black text-rose-600">
                            Rp {{ number_format($e->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-[#77768C]">{{ $e->creator?->name ?? 'Admin' }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <form action="{{ route('expenses.destroy', $e) }}" method="POST" data-confirm="Hapus pengeluaran sebesar Rp {{ number_format($e->amount, 0, ',', '.') }} ini?" data-confirm-title="Hapus Biaya Operasional" data-confirm-danger="true" data-confirm-btn="Ya, Hapus!">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 font-bold text-xs transition">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-[#77768C]">Belum ada biaya operasional dicatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
        <div class="px-5 py-3.5 border-t border-[#E8E6F0] bg-[#F4F3FA]">
            {{ $expenses->links() }}
        </div>
        @endif
    </div>

    <!-- Create Expense Modal -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div @click.away="createModalOpen = false" class="clay-card max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#E8E6F0] pb-3">
                <h3 class="text-sm font-bold text-[#29283D] flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#6C63FF] shadow-xs"></span>
                    Catat Biaya Operasional Baru
                </h3>
                <button type="button" @click="createModalOpen = false" class="text-[#77768C] hover:text-[#29283D] text-lg font-bold">&times;</button>
            </div>

            <form action="{{ route('expenses.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block font-bold text-[#29283D]">Kategori Biaya <span class="text-rose-500">*</span></label>
                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('settings.index') }}" class="text-[10px] text-[#6C63FF] hover:underline font-bold">Ubah Pilihan di Setting &rarr;</a>
                        @endif
                    </div>
                    <select name="category" required class="clay-input w-full text-xs font-medium">
                        @foreach(\App\Models\Setting::getExpenseCategories() as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#29283D] mb-1.5">Deskripsi Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="description" required placeholder="Contoh: Tiket Pura Tirta Empul 15 orang"
                           class="clay-input w-full text-xs font-medium">
                </div>

                <div>
                    <label class="block font-bold text-[#29283D] mb-1.5">Nominal (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" min="1000" step="1000" required placeholder="150000"
                           class="clay-input w-full text-xs font-bold text-[#29283D]">
                </div>

                <div>
                    <label class="block font-bold text-[#29283D] mb-1.5">Tanggal Pengeluaran <span class="text-rose-500">*</span></label>
                    <input type="date" name="expense_date" value="{{ now()->toDateString() }}" required
                           class="clay-input w-full text-xs font-medium">
                </div>

                <div>
                    <label class="block font-bold text-[#29283D] mb-1.5">Hubungkan ke Booking (Opsional)</label>
                    <select name="booking_id" class="clay-input w-full text-xs font-medium">
                        <option value="">-- Biaya Umum / Tidak Terikat Booking --</option>
                        @foreach($bookings as $bk)
                            <option value="{{ $bk->id }}">{{ $bk->booking_code }} - {{ $bk->customer_name }} ({{ $bk->booking_date->format('d/m/Y') }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-3 border-t border-[#E8E6F0] flex items-center justify-end space-x-2">
                    <button type="button" @click="createModalOpen = false" class="clay-btn-secondary px-4 py-2 text-xs font-bold">
                        Batal
                    </button>
                    <button type="submit" class="clay-btn-primary px-4 py-2 text-xs font-bold">
                        Simpan Biaya
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
