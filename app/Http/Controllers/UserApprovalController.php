<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Guide;
use App\Models\User;
use App\Notifications\AccountApprovedNotification;
use App\Notifications\AccountRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserApprovalController extends Controller
{
    /**
     * Display a listing of user registration approval requests.
     */
    public function index(Request $request)
    {
        $statusFilter = $request->query('status', 'PENDING');
        $roleFilter = $request->query('role');
        $search = $request->query('search');

        $query = User::whereIn('role', ['STAFF', 'GUIDE', 'ADMIN'])
            ->orderBy('created_at', 'desc');

        if ($statusFilter !== 'ALL') {
            $query->where('approval_status', $statusFilter);
        }

        if (!empty($roleFilter) && in_array($roleFilter, ['STAFF', 'GUIDE', 'ADMIN'])) {
            $query->where('role', $roleFilter);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        $pendingCount = User::whereIn('role', ['STAFF', 'GUIDE', 'ADMIN'])->where('approval_status', 'PENDING')->count();
        $approvedCount = User::whereIn('role', ['STAFF', 'GUIDE', 'ADMIN'])->where('approval_status', 'APPROVED')->count();
        $rejectedCount = User::whereIn('role', ['STAFF', 'GUIDE', 'ADMIN'])->where('approval_status', 'REJECTED')->count();
        $totalCount = User::whereIn('role', ['STAFF', 'GUIDE', 'ADMIN'])->count();

        $counts = [
            'pending' => $pendingCount,
            'approved' => $approvedCount,
            'rejected' => $rejectedCount,
            'total' => $totalCount,
        ];

        return view('admin.user_approvals', compact(
            'users',
            'statusFilter',
            'roleFilter',
            'search',
            'counts',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    /**
     * Approve (ACC) a registered user.
     */
    public function approve(User $user, Request $request)
    {
        DB::transaction(function () use ($user) {
            $user->update([
                'approval_status' => 'APPROVED',
                'is_active' => true,
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'rejection_reason' => null,
            ]);

            // If approved user is a GUIDE and has no Guide profile yet, create one automatically
            if ($user->isGuide() && !$user->guide) {
                $nextQueueNumber = (Guide::max('queue_number') ?? 0) + 1;
                $maxNum = Guide::where('kode_guide', 'like', 'GD-%')
                    ->get()
                    ->map(function ($g) {
                        return preg_match('/GD-(\d+)/', $g->kode_guide, $m) ? (int) $m[1] : 0;
                    })
                    ->max() ?? 0;
                $nextCode = sprintf('GD-%03d', max($maxNum + 1, Guide::count() + 1));
                while (Guide::where('kode_guide', $nextCode)->exists()) {
                    $maxNum++;
                    $nextCode = sprintf('GD-%03d', $maxNum);
                }

                Guide::create([
                    'user_id' => $user->id,
                    'kode_guide' => $nextCode,
                    'nama' => $user->name,
                    'nomor_telepon' => $user->phone ?? '-',
                    'email' => $user->email,
                    'queue_number' => $nextQueueNumber,
                    'status' => 'AVAILABLE',
                    'tanggal_gabung' => now(),
                    'tipe_fee' => 'PER_GUEST',
                    'fee_per_guest' => 50000,
                    'fee_per_booking' => 0,
                    'fee_minimum' => 0,
                ]);
            }

            AuditLog::log('APPROVE_USER', 'User', $user->id, null, [
                'email' => $user->email,
                'role' => $user->role,
                'approved_by' => auth()->id(),
            ]);
        });

        // Send approval email notification to the user outside the database transaction
        try {
            $user->notify(new AccountApprovedNotification());
        } catch (\Throwable $e) {
            // Log but don't break transaction
        }

        return back()->with('success', "Akun {$user->name} ({$user->email}) berhasil di-ACC (disetujui) dan sekarang telah AKTIF!");
    }

    /**
     * Reject a registered user.
     */
    public function reject(User $user, Request $request)
    {
        $reason = $request->input('reason', 'Tidak memenuhi kriteria tim operasional ARTDEVATA.');

        $user->update([
            'approval_status' => 'REJECTED',
            'is_active' => false,
            'rejection_reason' => $reason,
        ]);

        // Send rejection email notification to the user
        try {
            $user->notify(new AccountRejectedNotification($reason));
        } catch (\Throwable $e) {
            // Log but don't break transaction
        }

        AuditLog::log('REJECT_USER', 'User', $user->id, null, [
            'email' => $user->email,
            'role' => $user->role,
            'rejection_reason' => $reason,
        ]);

        return back()->with('success', "Pendaftaran akun {$user->name} ({$user->email}) telah ditolak.");
    }
}
