<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\AccountApprovedNotification;
use App\Notifications\AccountRejectedNotification;
use Illuminate\Http\Request;

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
        $user->update([
            'approval_status' => 'APPROVED',
            'is_active' => true,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'rejection_reason' => null,
        ]);

        // If approved user is a GUIDE and has no Guide profile yet, create one automatically
        if ($user->isGuide() && !$user->guide) {
            $nextQueueNumber = (\App\Models\Guide::max('queue_number') ?? 0) + 1;
            $nextCode = sprintf('GD-%03d', (\App\Models\Guide::count() + 1));

            \App\Models\Guide::create([
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

        // Send approval email notification to the user
        try {
            $user->notify(new AccountApprovedNotification());
        } catch (\Throwable $e) {
            // Log but don't break transaction
        }

        AuditLog::log('APPROVE_USER', 'User', $user->id, null, [
            'email' => $user->email,
            'role' => $user->role,
            'approved_by' => auth()->id(),
        ]);

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
