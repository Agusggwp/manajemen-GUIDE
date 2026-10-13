@extends('layouts.app')

@section('title', 'Log Audit Sistem')
@section('header', 'Log Audit & Rekam Jejak Sistem')

@section('content')
<div class="space-y-6">
    <div class="clay-card p-6">
        <h2 class="text-base font-bold text-[#29283D]">Rekam Jejak Aktivitas Operasional (Audit Trail)</h2>
        <p class="text-xs text-[#77768C] mt-1">
            Log permanen untuk seluruh aksi penting: login, absensi, penugasan, pembayaran, rotasi antrean, dan perubahan data sensitif.
        </p>
    </div>

    <!-- Logs Table -->
    <div class="clay-card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F4F3FA] text-[#77768C] font-bold border-b border-[#E8E6F0]">
                    <tr>
                        <th class="px-5 py-3.5">Waktu</th>
                        <th class="px-5 py-3.5">Pengguna (User)</th>
                        <th class="px-5 py-3.5">Aksi (Action)</th>
                        <th class="px-5 py-3.5">Objek / Model</th>
                        <th class="px-5 py-3.5">Detail Nilai Baru / Parameter</th>
                        <th class="px-5 py-3.5 text-right">Alamat IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E6F0]">
                    @forelse($logs as $log)
                    <tr class="hover:bg-purple-50/30 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-[#29283D]">{{ $log->created_at->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-[#77768C] font-mono">{{ $log->created_at->format('H:i:s') }} WITA</div>
                        </td>

                        <td class="px-5 py-3.5">
                            <span class="font-bold text-[#29283D]">{{ $log->user?->name ?? 'System' }}</span>
                            <span class="text-[10px] text-[#77768C] block font-medium">{{ $log->user?->role ?? '-' }}</span>
                        </td>

                        <td class="px-5 py-3.5">
                            <span class="clay-badge font-mono text-[10px] 
                                  {{ str_contains($log->action, 'PAYMENT') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : (str_contains($log->action, 'FORCE') ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-purple-50 text-[#6C63FF] border-purple-200') }}">
                                {{ $log->action }}
                            </span>
                        </td>

                        <td class="px-5 py-3.5">
                            <span class="font-bold text-[#29283D]">{{ class_basename($log->model) }}</span>
                            @if($log->model_id)
                                <span class="text-[10px] text-[#77768C] font-mono block">#{{ $log->model_id }}</span>
                            @endif
                        </td>

                        <td class="px-5 py-3.5 max-w-xs">
                            @if($log->new_values)
                                <pre class="text-[10px] bg-[#F4F3FA] p-2.5 rounded-xl border border-[#E8E6F0] overflow-x-auto font-mono text-[#29283D]">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            @else
                                <span class="text-[#77768C]">-</span>
                            @endif
                        </td>

                        <td class="px-5 py-3.5 text-right font-mono text-[#77768C] text-xs font-medium">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-[#77768C]">Belum ada riwayat audit log.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-5 py-3.5 border-t border-[#E8E6F0] bg-[#F4F3FA]">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
