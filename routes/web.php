<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectTourController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\GuidePortalController;
use App\Http\Controllers\GuideQueueBoardController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\QueueHistoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StaffPayrollController;
use App\Http\Controllers\TourPackageController;
use App\Http\Controllers\UserApprovalController;
use Illuminate\Support\Facades\Route;

// Redirect root to login or appropriate dashboard
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    return auth()->user()->isGuide()
        ? redirect()->route('guide.dashboard')
        : redirect()->route('dashboard');
});

// Authentication & Registration
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Email Verification
Route::get('/email/verify', [AuthController::class, 'showVerifyNotice'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])->name('verification.send');

// Staff & Admin Protected Routes
Route::middleware(['auth', 'role:ADMIN,STAFF'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export-csv', [DashboardController::class, 'exportCsv'])->name('dashboard.export-csv');
    Route::get('/queue-board', [GuideQueueBoardController::class, 'index'])->name('queue.board');

    // Bookings & Assignments
    Route::resource('bookings', BookingController::class);
    Route::post('/bookings/{booking}/auto-assign', [BookingController::class, 'autoAssign'])->name('bookings.auto-assign');
    Route::post('/bookings/{booking}/manual-assign', [BookingController::class, 'manualAssign'])->name('bookings.manual-assign');
    Route::delete('/assignments/{assignment}', [BookingController::class, 'removeAssignment'])->name('assignments.destroy');
    Route::get('/api/estimate', [BookingController::class, 'calculateEstimate'])->name('bookings.estimate');

    // Direct / Walk-In Tours (Tanpa Booking)
    Route::get('/direct-tours/create', [DirectTourController::class, 'create'])->name('direct-tours.create');
    Route::post('/direct-tours', [DirectTourController::class, 'store'])->name('direct-tours.store');

    // Attendance
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/{guide}/force-checkin', [AttendanceController::class, 'forceCheckIn'])->name('attendance.force-checkin');
    Route::post('/attendance/{guide}/force-checkout', [AttendanceController::class, 'forceCheckOut'])->name('attendance.force-checkout');

    // Guides (View list & Reorder)
    Route::get('/guides', [GuideController::class, 'index'])->name('guides.index');
    Route::post('/guides/reorder-queue', [GuideController::class, 'reorderQueue'])->name('guides.reorder-queue');

    // Tour Packages (View)
    Route::get('/packages', [TourPackageController::class, 'index'])->name('packages.index');

    // Queue History & Rotation Audit
    Route::get('/queue-history', [QueueHistoryController::class, 'index'])->name('queue.history');

    // Operational Reports
    Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/guides', [ReportController::class, 'guideReport'])->name('reports.guides');
    Route::get('/reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('/reports/queue', [ReportController::class, 'queueReport'])->name('reports.queue');

    // Staff Salary Portal & Slip View
    Route::get('/my-salary', [StaffPayrollController::class, 'mySalary'])->name('staff.my-salary');
    Route::get('/staff-payroll/{staffPayroll}', [StaffPayrollController::class, 'show'])->name('staff-payroll.show')->whereNumber('staffPayroll');
});

// Admin Exclusive Routes
Route::middleware(['auth', 'role:ADMIN'])->group(function () {
    // Guide CRUD & Status Management
    Route::get('/guides/create', [GuideController::class, 'create'])->name('guides.create');
    Route::post('/guides', [GuideController::class, 'store'])->name('guides.store');
    Route::get('/guides/{guide}/edit', [GuideController::class, 'edit'])->name('guides.edit');
    Route::put('/guides/{guide}', [GuideController::class, 'update'])->name('guides.update');
    Route::delete('/guides/{guide}', [GuideController::class, 'destroy'])->name('guides.destroy');
    Route::post('/guides/{guide}/suspend', [GuideController::class, 'suspend'])->name('guides.suspend');
    Route::post('/guides/{guide}/unsuspend', [GuideController::class, 'unsuspend'])->name('guides.unsuspend');

    // Tour Package CRUD
    Route::get('/packages/create', [TourPackageController::class, 'create'])->name('packages.create');
    Route::post('/packages', [TourPackageController::class, 'store'])->name('packages.store');
    Route::get('/packages/{package}/edit', [TourPackageController::class, 'edit'])->name('packages.edit');
    Route::put('/packages/{package}', [TourPackageController::class, 'update'])->name('packages.update');
    Route::delete('/packages/{package}', [TourPackageController::class, 'destroy'])->name('packages.destroy');

    // Payroll & Payments (Guide)
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/{guide}', [PayrollController::class, 'show'])->name('payroll.show');
    Route::post('/payroll/{guide}/payment', [PayrollController::class, 'storePayment'])->name('payroll.payment');
    Route::get('/payments', [PayrollController::class, 'paymentList'])->name('payroll.payments');

    // Staff Payroll (Gaji Staff)
    Route::get('/staff-payroll', [StaffPayrollController::class, 'index'])->name('staff-payroll.index');
    Route::get('/staff-payroll/create', [StaffPayrollController::class, 'create'])->name('staff-payroll.create');
    Route::post('/staff-payroll', [StaffPayrollController::class, 'store'])->name('staff-payroll.store');
    Route::get('/staff-payroll/{staffPayroll}/edit', [StaffPayrollController::class, 'edit'])->name('staff-payroll.edit');
    Route::put('/staff-payroll/{staffPayroll}', [StaffPayrollController::class, 'update'])->name('staff-payroll.update');
    Route::delete('/staff-payroll/{staffPayroll}', [StaffPayrollController::class, 'destroy'])->name('staff-payroll.destroy');
    Route::post('/staff-payroll/{staffPayroll}/pay', [StaffPayrollController::class, 'markAsPaid'])->name('staff-payroll.pay');
    Route::post('/staff-payroll/settings/{user}', [StaffPayrollController::class, 'updateStaffSalarySetting'])->name('staff-payroll.setting');

    // Expenses
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');

    // System Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    // User Registration Approvals (ACC Staff/Admin)
    Route::get('/user-approvals', [UserApprovalController::class, 'index'])->name('admin.user-approvals.index');
    Route::post('/user-approvals/{user}/approve', [UserApprovalController::class, 'approve'])->name('admin.user-approvals.approve');
    Route::post('/user-approvals/{user}/reject', [UserApprovalController::class, 'reject'])->name('admin.user-approvals.reject');
});

// Guide Exclusive Portal (Mobile-First)
Route::middleware(['auth', 'role:GUIDE'])->group(function () {
    Route::get('/guide/dashboard', [GuidePortalController::class, 'dashboard'])->name('guide.dashboard');
    Route::post('/guide/check-in', [GuidePortalController::class, 'checkIn'])->name('guide.check-in');
    Route::post('/guide/check-out', [GuidePortalController::class, 'checkOut'])->name('guide.check-out');
    Route::post('/guide/assignments/{assignment}/start', [GuidePortalController::class, 'startTour'])->name('guide.start-tour');
    Route::post('/guide/assignments/{assignment}/complete', [GuidePortalController::class, 'completeTour'])->name('guide.complete-tour');
    Route::post('/guide/direct-tour', [GuidePortalController::class, 'createDirectTour'])->name('guide.direct-tour.store');
    Route::get('/guide/jobs', [GuidePortalController::class, 'myJobs'])->name('guide.jobs');
    Route::get('/guide/queue', [GuidePortalController::class, 'queueBoard'])->name('guide.queue');
    Route::get('/guide/earnings', [GuidePortalController::class, 'myEarnings'])->name('guide.earnings');
    Route::get('/guide/profile', [GuidePortalController::class, 'profile'])->name('guide.profile');
    Route::put('/guide/profile', [GuidePortalController::class, 'updateProfile'])->name('guide.update-profile');
});
