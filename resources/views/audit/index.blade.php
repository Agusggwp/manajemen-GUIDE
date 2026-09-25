@extends('layouts.app')

@section('title', 'Log Audit Sistem')
@section('header', 'Log Audit & Rekam Jejak Sistem')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs">
        <h2 class="text-base font-bold text-slate-900">Rekam Jejak Aktivitas Operasional (Audit Trail)</h2>
        <p class="text-xs text-slate-500 mt-0.5">
            Log permanen untuk seluruh aksi penting: login, absensi, penugasan, pembayaran, rotasi antrean, dan perubahan data sensitif.
        </p>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5">Waktu</th>
                        <th class="px-4 py-3.5">Pengguna (User)</th>
                        <th class="px-4 py-3.5">Aksi (Action)</th>
                        <th class="px-4 py-3.5">Objek / Model</th>
                        <th class="px-4 py-3.5">Detail Nilai Baru / Parameter</th>
                        <th class="px-4 py-3.5 text-right">Alamat IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900">{{ $log->created_at->format('d/m/Y') }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $log->created_at->format('H:i:s') }} WITA</div>
                        </td>

                        <td class="px-4 py-3.5">
                            <span class="font-bold text-slate-800">{{ $log->user?->name ?? 'System' }}</span>
                            <span class="text-[10px] text-slate-400 block">{{ $log->user?->role ?? '-' }}</span>
                        </td>

                        <td class="px-4 py-3.5">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold font-mono 
                                  {{ str_contains($log->action, 'PAYMENT') ? 'bg-emerald-50 text-emerald-700' : (str_contains($log->action, 'FORCE') ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-700') }}">
                                {{ $log->action }}
                            </span>
                        </td>

                        <td class="px-4 py-3.5">
                            <span class="font-medium text-slate-700">{{ class_basename($log->model) }}</span>
                            @if($log->model_id)
                                <span class="text-[10px] text-slate-400 font-mono">#{{ $log->model_id }}</span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 max-w-xs">
                            @if($log->new_values)
                                <pre class="text-[10px] bg-slate-50 p-1.5 rounded border border-slate-200 overflow-x-auto font-mono text-slate-700">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 text-right font-mono text-slate-500 text-[11px]">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada riwayat audit log.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
