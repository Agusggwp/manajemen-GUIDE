<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Guide;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GuideController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();

        $query = Guide::with(['user', 'todayAttendance' => fn($q) => $q->where('attendance_date', $today)])
            ->orderBy('queue_number', 'asc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode_guide', 'like', "%{$search}%")
                  ->orWhere('nomor_telepon', 'like', "%{$search}%")
                  ->orWhere('bahasa', 'like', "%{$search}%");
            });
        }

        $guides = $query->paginate(15)->withQueryString();

        return view('guides.index', compact('guides', 'today'));
    }

    public function create()
    {
        $nextQueueNumber = (Guide::max('queue_number') ?? 0) + 1;
        $nextCode = sprintf('GD-%03d', (Guide::count() + 1));

        return view('guides.create', compact('nextQueueNumber', 'nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kode_guide' => 'required|string|max:30|unique:guides,kode_guide',
            'nomor_telepon' => 'required|string|max:30',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'alamat' => 'nullable|string',
            'bahasa' => 'nullable|string|max:150',
            'spesialisasi' => 'nullable|string|max:150',
            'queue_number' => 'required|integer|min:1',
            'tipe_fee' => 'required|in:PER_GUEST,PER_BOOKING,TIERED',
            'fee_per_guest' => 'required|numeric|min:0',
            'fee_per_booking' => 'required|numeric|min:0',
            'fee_minimum' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            // Create user for the guide
            $user = User::create([
                'name' => $validated['nama'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'GUIDE',
                'phone' => $validated['nomor_telepon'],
                'is_active' => true,
            ]);

            $guide = Guide::create([
                'user_id' => $user->id,
                'kode_guide' => $validated['kode_guide'],
                'nama' => $validated['nama'],
                'nomor_telepon' => $validated['nomor_telepon'],
                'email' => $validated['email'],
                'alamat' => $validated['alamat'] ?? null,
                'bahasa' => $validated['bahasa'] ?? null,
                'spesialisasi' => $validated['spesialisasi'] ?? null,
                'queue_number' => $validated['queue_number'],
                'status' => 'AVAILABLE',
                'tanggal_gabung' => now()->toDateString(),
                'tipe_fee' => $validated['tipe_fee'],
                'fee_per_guest' => $validated['fee_per_guest'],
                'fee_per_booking' => $validated['fee_per_booking'],
                'fee_minimum' => $validated['fee_minimum'] ?? 0,
                'catatan' => $validated['catatan'] ?? null,
            ]);

            AuditLog::log('CREATE_GUIDE', Guide::class, $guide->id, null, [
                'nama' => $guide->nama,
                'queue_number' => $guide->queue_number,
            ]);
        });

        return redirect()->route('guides.index')->with('success', 'Guide berhasil ditambahkan.');
    }

    public function edit(Guide $guide)
    {
        return view('guides.edit', compact('guide'));
    }

    public function update(Request $request, Guide $guide)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'nomor_telepon' => 'required|string|max:30',
            'alamat' => 'nullable|string',
            'bahasa' => 'nullable|string|max:150',
            'spesialisasi' => 'nullable|string|max:150',
            'queue_number' => 'required|integer|min:1',
            'status' => 'required|in:AVAILABLE,ASSIGNED,ON_TOUR,OFF,INACTIVE,SUSPENDED',
            'suspend_reason' => 'nullable|string|max:255',
            'tipe_fee' => 'required|in:PER_GUEST,PER_BOOKING,TIERED',
            'fee_per_guest' => 'required|numeric|min:0',
            'fee_per_booking' => 'required|numeric|min:0',
            'fee_minimum' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string',
        ]);

        $oldValues = $guide->toArray();

        if ($validated['status'] === 'SUSPENDED') {
            $validated['suspended_at'] = $guide->suspended_at ?? now();
            $validated['suspended_by'] = $guide->suspended_by ?? auth()->id();
            $validated['suspend_reason'] = $validated['suspend_reason'] ?? ($guide->suspend_reason ?? 'Ditangguhkan oleh admin');
        } elseif ($guide->status === 'SUSPENDED') {
            $validated['suspended_at'] = null;
            $validated['suspended_by'] = null;
            $validated['suspend_reason'] = null;
        }

        $guide->update($validated);

        if ($guide->user) {
            $guide->user->update([
                'name' => $guide->nama,
                'phone' => $guide->nomor_telepon,
            ]);
        }

        AuditLog::log('UPDATE_GUIDE', Guide::class, $guide->id, $oldValues, $guide->toArray());

        return redirect()->route('guides.index')->with('success', "Data Guide {$guide->nama} berhasil diperbarui.");
    }

    public function suspend(Request $request, Guide $guide)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang dapat menangguhkan guide.');
        }

        $reason = $request->input('reason') ?? $request->input('suspend_reason');
        $request->merge(['reason' => $reason, 'suspend_reason' => $reason]);

        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ], [
            'reason.required' => 'Alasan suspend wajib diisi.',
        ]);

        $oldStatus = $guide->status;
        $guide->suspend($validated['reason'], auth()->id());

        AuditLog::log('SUSPEND_GUIDE', Guide::class, $guide->id, ['status' => $oldStatus], [
            'status' => 'SUSPENDED',
            'reason' => $validated['reason'],
        ]);

        return back()->with('success', "Guide {$guide->nama} berhasil disuspend.");
    }

    public function unsuspend(Guide $guide)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang dapat mengaktifkan kembali guide.');
        }

        $oldReason = $guide->suspend_reason;
        $guide->unsuspend();

        AuditLog::log('UNSUSPEND_GUIDE', Guide::class, $guide->id, [
            'status' => 'SUSPENDED',
            'reason' => $oldReason,
        ], [
            'status' => 'AVAILABLE',
        ]);

        return back()->with('success', "Status suspend Guide {$guide->nama} berhasil dicabut. Guide kembali aktif.");
    }

    public function reorderQueue(Request $request)
    {
        $request->validate([
            'queue_order' => 'required|array', // guide_id => queue_number
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->queue_order as $guideId => $order) {
                Guide::where('id', $guideId)->update(['queue_number' => (int) $order]);
            }

            AuditLog::log('REORDER_QUEUE', Guide::class, null, null, $request->queue_order);
        });

        return back()->with('success', 'Urutan antrean guide berhasil diperbarui.');
    }

    public function destroy(Guide $guide)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang dapat menghapus guide.');
        }

        DB::transaction(function () use ($guide) {
            $name = $guide->nama;
            $user = $guide->user;

            $guide->delete();
            if ($user) {
                $user->delete();
            }

            AuditLog::log('DELETE_GUIDE', Guide::class, $guide->id, ['nama' => $name]);
        });

        return redirect()->route('guides.index')->with('success', "Guide berhasil dihapus.");
    }
}
