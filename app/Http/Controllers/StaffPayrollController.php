<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Expense;
use App\Models\StaffPayroll;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffPayrollController extends Controller
{
    /**
     * Display a listing of staff payroll slips.
     */
    public function index(Request $request)
    {
        $currentYear = (int) Carbon::now()->format('Y');
        $currentMonth = (int) Carbon::now()->format('n');

        $periodMonth = $request->filled('month') ? (int) $request->month : $currentMonth;
        $periodYear = $request->filled('year') ? (int) $request->year : $currentYear;
        $status = $request->query('status', 'ALL');
        $userId = $request->query('user_id');

        // Query payrolls
        $query = StaffPayroll::with(['user', 'paidBy', 'createdBy'])
            ->where('period_month', $periodMonth)
            ->where('period_year', $periodYear);

        if ($status !== 'ALL') {
            $query->where('payment_status', $status);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $payrolls = $query->orderBy('created_at', 'desc')->get();

        // All active staff
        $staffMembers = User::where('role', 'STAFF')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Calculate KPI summary for this period
        $allPeriodPayrolls = StaffPayroll::where('period_month', $periodMonth)
            ->where('period_year', $periodYear)
            ->get();

        $totalNetSalary = (float) $allPeriodPayrolls->sum('net_salary');
        $totalPaid = (float) $allPeriodPayrolls->where('payment_status', 'PAID')->sum('net_salary');
        $totalPending = (float) $allPeriodPayrolls->where('payment_status', 'PENDING')->sum('net_salary');
        $staffPaidCount = $allPeriodPayrolls->where('payment_status', 'PAID')->count();
        $staffPendingCount = $allPeriodPayrolls->where('payment_status', 'PENDING')->count();

        return view('staff-payroll.index', compact(
            'payrolls',
            'staffMembers',
            'periodMonth',
            'periodYear',
            'status',
            'userId',
            'totalNetSalary',
            'totalPaid',
            'totalPending',
            'staffPaidCount',
            'staffPendingCount'
        ));
    }

    /**
     * Show form to create a staff payroll slip.
     */
    public function create(Request $request)
    {
        $staffMembers = User::where('role', 'STAFF')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedUser = null;
        if ($request->filled('user_id')) {
            $selectedUser = $staffMembers->firstWhere('id', (int) $request->user_id);
        }

        $currentYear = (int) Carbon::now()->format('Y');
        $currentMonth = (int) Carbon::now()->format('n');

        return view('staff-payroll.create', compact('staffMembers', 'selectedUser', 'currentYear', 'currentMonth'));
    }

    /**
     * Store a newly created staff payroll slip.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'period_month' => 'required|integer|between:1,12',
            'period_year' => 'required|integer|between:2020,2050',
            'position' => 'nullable|string|max:100',
            'base_salary' => 'required|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:PENDING,PAID',
            'payment_date' => 'nullable|required_if:payment_status,PAID|date',
            'payment_method' => 'nullable|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'record_expense' => 'nullable|boolean',
        ], [
            'user_id.required' => 'Pilih staff yang akan digaji.',
            'base_salary.required' => 'Gaji pokok wajib diisi.',
            'payment_date.required_if' => 'Tanggal pembayaran wajib diisi jika status Lunas.',
        ]);

        // Check if payroll for this user and period already exists
        $exists = StaffPayroll::where('user_id', $validated['user_id'])
            ->where('period_month', $validated['period_month'])
            ->where('period_year', $validated['period_year'])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Slip gaji untuk staff ini pada bulan dan tahun tersebut sudah pernah dibuat.');
        }

        $staff = User::findOrFail($validated['user_id']);
        $base = (float) $validated['base_salary'];
        $allowance = (float) ($validated['allowance'] ?? 0);
        $bonus = (float) ($validated['bonus'] ?? 0);
        $deduction = (float) ($validated['deduction'] ?? 0);
        $netSalary = max(0, ($base + $allowance + $bonus) - $deduction);

        $payrollCode = StaffPayroll::generatePayrollCode($validated['period_year'], $validated['period_month']);

        $payroll = DB::transaction(function () use ($validated, $staff, $base, $allowance, $bonus, $deduction, $netSalary, $payrollCode, $request) {
            $payroll = StaffPayroll::create([
                'payroll_code' => $payrollCode,
                'user_id' => $staff->id,
                'period_month' => $validated['period_month'],
                'period_year' => $validated['period_year'],
                'position' => $validated['position'] ?? $staff->position ?? 'Staff',
                'base_salary' => $base,
                'allowance' => $allowance,
                'bonus' => $bonus,
                'deduction' => $deduction,
                'net_salary' => $netSalary,
                'payment_status' => $validated['payment_status'],
                'payment_date' => $validated['payment_status'] === 'PAID' ? ($validated['payment_date'] ?? now()->toDateString()) : null,
                'payment_method' => $validated['payment_status'] === 'PAID' ? ($validated['payment_method'] ?? 'TRANSFER') : null,
                'payment_reference' => $validated['payment_reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'paid_by' => $validated['payment_status'] === 'PAID' ? auth()->id() : null,
                'created_by' => auth()->id(),
            ]);

            // If marked as paid immediately and requested, record to expenses
            if ($payroll->payment_status === 'PAID' && $request->boolean('record_expense')) {
                Expense::create([
                    'category' => 'Gaji & Upah',
                    'description' => "Pembayaran Gaji {$staff->name} ({$payroll->period_name}) - {$payroll->payroll_code}",
                    'amount' => $payroll->net_salary,
                    'expense_date' => $payroll->payment_date ?? now()->toDateString(),
                    'created_by' => auth()->id(),
                    'notes' => "Otomatis dicatat dari Slip Gaji Staff #{$payroll->payroll_code}",
                ]);
            }

            AuditLog::log('CREATE_STAFF_PAYROLL', StaffPayroll::class, $payroll->id, null, $payroll->toArray());

            return $payroll;
        });

        return redirect()->route('staff-payroll.show', $payroll)
            ->with('success', "Slip Gaji {$payroll->payroll_code} untuk {$staff->name} berhasil dibuat.");
    }

    /**
     * Show official printable salary slip.
     */
    public function show(StaffPayroll $staffPayroll)
    {
        $user = auth()->user();

        // Staff can only view their own salary slip
        if ($user->isStaff() && $staffPayroll->user_id !== $user->id) {
            abort(403, 'Anda tidak berhak melihat slip gaji staff lain.');
        }

        $staffPayroll->load(['user', 'paidBy', 'createdBy']);

        return view('staff-payroll.show', compact('staffPayroll'));
    }

    /**
     * Show form to edit staff payroll.
     */
    public function edit(StaffPayroll $staffPayroll)
    {
        $staffMembers = User::where('role', 'STAFF')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('staff-payroll.edit', compact('staffPayroll', 'staffMembers'));
    }

    /**
     * Update staff payroll slip.
     */
    public function update(Request $request, StaffPayroll $staffPayroll)
    {
        $validated = $request->validate([
            'position' => 'nullable|string|max:100',
            'base_salary' => 'required|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:PENDING,PAID',
            'payment_date' => 'nullable|required_if:payment_status,PAID|date',
            'payment_method' => 'nullable|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $base = (float) $validated['base_salary'];
        $allowance = (float) ($validated['allowance'] ?? 0);
        $bonus = (float) ($validated['bonus'] ?? 0);
        $deduction = (float) ($validated['deduction'] ?? 0);
        $netSalary = max(0, ($base + $allowance + $bonus) - $deduction);

        $oldValues = $staffPayroll->toArray();

        $staffPayroll->update([
            'position' => $validated['position'] ?? $staffPayroll->position,
            'base_salary' => $base,
            'allowance' => $allowance,
            'bonus' => $bonus,
            'deduction' => $deduction,
            'net_salary' => $netSalary,
            'payment_status' => $validated['payment_status'],
            'payment_date' => $validated['payment_status'] === 'PAID' ? ($validated['payment_date'] ?? $staffPayroll->payment_date ?? now()->toDateString()) : null,
            'payment_method' => $validated['payment_status'] === 'PAID' ? ($validated['payment_method'] ?? $staffPayroll->payment_method ?? 'TRANSFER') : null,
            'payment_reference' => $validated['payment_reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'paid_by' => $validated['payment_status'] === 'PAID' ? ($staffPayroll->paid_by ?? auth()->id()) : null,
        ]);

        AuditLog::log('UPDATE_STAFF_PAYROLL', StaffPayroll::class, $staffPayroll->id, $oldValues, $staffPayroll->fresh()->toArray());

        return redirect()->route('staff-payroll.show', $staffPayroll)
            ->with('success', "Slip Gaji {$staffPayroll->payroll_code} berhasil diperbarui.");
    }

    /**
     * Mark a pending payroll slip as paid.
     */
    public function markAsPaid(Request $request, StaffPayroll $staffPayroll)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'record_expense' => 'nullable|boolean',
        ], [
            'payment_date.required' => 'Tanggal pembayaran wajib diisi.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
        ]);

        DB::transaction(function () use ($staffPayroll, $validated, $request) {
            $staffPayroll->update([
                'payment_status' => 'PAID',
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'] ?? null,
                'notes' => $validated['notes'] ? ($staffPayroll->notes ? $staffPayroll->notes . " | " . $validated['notes'] : $validated['notes']) : $staffPayroll->notes,
                'paid_by' => auth()->id(),
            ]);

            if ($request->boolean('record_expense')) {
                Expense::create([
                    'category' => 'Gaji & Upah',
                    'description' => "Pembayaran Gaji {$staffPayroll->user->name} ({$staffPayroll->period_name}) - {$staffPayroll->payroll_code}",
                    'amount' => $staffPayroll->net_salary,
                    'expense_date' => $validated['payment_date'],
                    'created_by' => auth()->id(),
                    'notes' => "Otomatis dicatat saat pelunasan Slip Gaji #{$staffPayroll->payroll_code}",
                ]);
            }

            AuditLog::log('PAY_STAFF_PAYROLL', StaffPayroll::class, $staffPayroll->id, ['status' => 'PENDING'], [
                'status' => 'PAID',
                'amount' => $staffPayroll->net_salary,
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
            ]);
        });

        return back()->with('success', "Pembayaran gaji {$staffPayroll->user->name} ({$staffPayroll->payroll_code}) sebesar Rp " . number_format($staffPayroll->net_salary, 0, ',', '.') . " berhasil dicatat.");
    }

    /**
     * Delete a staff payroll slip.
     */
    public function destroy(StaffPayroll $staffPayroll)
    {
        $code = $staffPayroll->payroll_code;
        $name = $staffPayroll->user?->name;

        $staffPayroll->delete();

        AuditLog::log('DELETE_STAFF_PAYROLL', StaffPayroll::class, $staffPayroll->id, ['payroll_code' => $code]);

        return redirect()->route('staff-payroll.index')
            ->with('success', "Slip Gaji {$code} ({$name}) berhasil dihapus.");
    }

    /**
     * Update default salary and banking settings for a staff user.
     */
    public function updateStaffSalarySetting(Request $request, User $user)
    {
        if (!$user->isStaff()) {
            return back()->with('error', 'Hanya user dengan role Staff yang dapat diatur standar gajinya.');
        }

        $validated = $request->validate([
            'position' => 'nullable|string|max:100',
            'salary_base' => 'required|numeric|min:0',
            'salary_allowance' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:50',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_holder' => 'nullable|string|max:100',
        ]);

        $oldValues = [
            'position' => $user->position,
            'salary_base' => $user->salary_base,
            'salary_allowance' => $user->salary_allowance,
            'bank_name' => $user->bank_name,
            'bank_account_number' => $user->bank_account_number,
            'bank_account_holder' => $user->bank_account_holder,
        ];

        $user->update([
            'position' => $validated['position'] ?? null,
            'salary_base' => (float) $validated['salary_base'],
            'salary_allowance' => (float) ($validated['salary_allowance'] ?? 0),
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_holder' => $validated['bank_account_holder'] ?? null,
        ]);

        AuditLog::log('UPDATE_STAFF_SALARY_SETTING', User::class, $user->id, $oldValues, $validated);

        return back()->with('success', "Standar gaji & rekening untuk {$user->name} berhasil diperbarui.");
    }

    /**
     * Dedicated view for logged in Staff to see their own salary slips.
     */
    public function mySalary(Request $request)
    {
        $user = auth()->user();

        $payrolls = StaffPayroll::where('user_id', $user->id)
            ->orderBy('period_year', 'desc')
            ->orderBy('period_month', 'desc')
            ->get();

        $totalEarned = (float) $payrolls->where('payment_status', 'PAID')->sum('net_salary');
        $latestPayroll = $payrolls->first();

        return view('staff-payroll.my-salary', compact('user', 'payrolls', 'totalEarned', 'latestPayroll'));
    }
}
