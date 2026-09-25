<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Expense;
use App\Models\Guide;
use App\Models\GuideAssignment;
use App\Models\GuideAttendance;
use App\Models\QueueHistory;
use App\Models\Setting;
use App\Models\StaffPayroll;
use App\Models\TourPackage;
use App\Models\TourPackagePrice;
use App\Models\User;
use App\Services\GuideAttendanceService;
use App\Services\GuideQueueService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteSystemAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $guideUser1;
    private User $guideUser2;
    private Guide $guide1;
    private Guide $guide2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'System Admin',
            'email' => 'admin@artdevata.com',
            'role' => 'ADMIN',
            'is_active' => true,
            'approval_status' => 'APPROVED',
            'email_verified_at' => now(),
        ]);

        $this->staff = User::factory()->create([
            'name' => 'Operational Staff',
            'email' => 'staff@artdevata.com',
            'role' => 'STAFF',
            'position' => 'Staff Reservasi',
            'salary_base' => 3000000,
            'is_active' => true,
            'approval_status' => 'APPROVED',
            'email_verified_at' => now(),
        ]);

        $this->guideUser1 = User::factory()->create([
            'name' => 'Wayan Sudira',
            'email' => 'wayan@artdevata.com',
            'role' => 'GUIDE',
            'is_active' => true,
            'approval_status' => 'APPROVED',
            'email_verified_at' => now(),
        ]);

        $this->guide1 = Guide::create([
            'user_id' => $this->guideUser1->id,
            'nama' => 'Wayan Sudira',
            'kode_guide' => 'GD-001',
            'nomor_telepon' => '081234567890',
            'status' => 'OFF',
            'queue_number' => 1,
            'fee_harian' => 100000,
            'tipe_fee' => 'PER_GUEST',
            'fee_per_guest' => 50000,
        ]);

        $this->guideUser2 = User::factory()->create([
            'name' => 'Made Suarta',
            'email' => 'made@artdevata.com',
            'role' => 'GUIDE',
            'is_active' => true,
            'approval_status' => 'APPROVED',
            'email_verified_at' => now(),
        ]);

        $this->guide2 = Guide::create([
            'user_id' => $this->guideUser2->id,
            'nama' => 'Made Suarta',
            'kode_guide' => 'GD-002',
            'nomor_telepon' => '081234567891',
            'status' => 'OFF',
            'queue_number' => 2,
            'fee_harian' => 100000,
            'tipe_fee' => 'PER_GUEST',
            'fee_per_guest' => 50000,
        ]);
    }

    public function test_complete_attendance_and_circular_queue_cycle()
    {
        $attendanceService = app(GuideAttendanceService::class);
        $queueService = app(GuideQueueService::class);
        $today = Carbon::today()->toDateString();

        // 1. Both guides check in
        $attendanceService->checkIn($this->guide1, $today);
        $attendanceService->checkIn($this->guide2, $today);

        $this->assertTrue($this->guide1->fresh()->isCheckedInOn($today));
        $this->assertTrue($this->guide2->fresh()->isCheckedInOn($today));
        $this->assertEquals('AVAILABLE', $this->guide1->fresh()->status);
        $this->assertEquals('AVAILABLE', $this->guide2->fresh()->status);

        // 2. Circular queue candidate should be guide 1
        $next = $queueService->findNextAvailableGuide(null, $today);
        $this->assertEquals($this->guide1->id, $next['guide']->id);

        // 3. Guide 1 checks out then immediately checks back in (unlimited daily cycle)
        $attendanceService->checkOut($this->guide1, $today);
        $this->assertFalse($this->guide1->fresh()->isCheckedInOn($today));

        $attendanceService->checkIn($this->guide1, $today);
        $this->assertTrue($this->guide1->fresh()->isCheckedInOn($today));
        $this->assertEquals('AVAILABLE', $this->guide1->fresh()->status);
    }

    public function test_guide_suspend_skips_queue_and_unsuspend_restores()
    {
        $attendanceService = app(GuideAttendanceService::class);
        $queueService = app(GuideQueueService::class);
        $today = Carbon::today()->toDateString();

        $attendanceService->checkIn($this->guide1, $today);
        $attendanceService->checkIn($this->guide2, $today);

        // Admin suspends Guide 1
        $response = $this->actingAs($this->admin)->post(route('guides.suspend', $this->guide1), [
            'reason' => 'Melanggar SOP pelayanan tamu',
        ]);
        $response->assertRedirect();
        $this->assertEquals('SUSPENDED', $this->guide1->fresh()->status);
        $this->assertTrue($this->guide1->fresh()->isSuspended());

        // Queue should now automatically skip Guide 1 and select Guide 2
        $next = $queueService->findNextAvailableGuide(null, $today);
        $this->assertEquals($this->guide2->id, $next['guide']->id);

        // Admin unsuspends Guide 1
        $responseUnsuspend = $this->actingAs($this->admin)->post(route('guides.unsuspend', $this->guide1));
        $responseUnsuspend->assertRedirect();
        $this->assertFalse($this->guide1->fresh()->isSuspended());
        $this->assertEquals('AVAILABLE', $this->guide1->fresh()->status);
    }

    public function test_tour_packages_pricing_and_booking_pipeline()
    {
        // 1. Create standard single-price package
        $pkg = TourPackage::create([
            'name' => 'Kintamani Highland Tour',
            'description' => 'Tour pemandangan gunung batur',
            'duration' => 'Full Day',
            'default_price' => 200000,
            'status' => 'ACTIVE',
        ]);

        TourPackagePrice::create([
            'tour_package_id' => $pkg->id,
            'min_guests' => 1,
            'max_guests' => 999,
            'price_per_guest' => 200000,
            'active' => true,
        ]);

        // 2. Create booking for 4 guests
        $today = Carbon::today()->toDateString();
        $booking = Booking::create([
            'booking_code' => 'BK-AUDIT-001',
            'booking_date' => $today,
            'start_time' => '08:00',
            'end_time' => '17:00',
            'customer_name' => 'Mr. Henderson',
            'guest_count' => 4,
            'tour_package_id' => $pkg->id,
            'price_per_guest' => 200000,
            'total_price' => 800000,
            'status' => 'WAITING',
        ]);

        $this->assertEquals(800000, $booking->total_price);

        // 3. Assign Guide 1 to booking
        app(GuideAttendanceService::class)->checkIn($this->guide1, $today);
        $assignment = GuideAssignment::create([
            'booking_id' => $booking->id,
            'guide_id' => $this->guide1->id,
            'guest_count' => 4,
            'assigned_at' => now(),
            'status' => 'ASSIGNED',
            'fee_type' => 'PER_GUEST',
            'fee_rate' => 50000,
            'fee_amount' => 200000,
        ]);

        $booking->update([
            'status' => 'FULLY_ASSIGNED',
            'guide_fee_total' => 200000,
            'company_profit' => 600000,
        ]);

        // 4. Start & Complete tour
        $this->actingAs($this->guideUser1)->post(route('guide.start-tour', $assignment))->assertRedirect();
        $this->assertEquals('ON_GOING', $assignment->fresh()->status);

        $this->actingAs($this->guideUser1)->post(route('guide.complete-tour', $assignment))->assertRedirect();
        $this->assertEquals('COMPLETED', $assignment->fresh()->status);
    }

    public function test_staff_payroll_lifecycle_and_expense_integration()
    {
        $today = Carbon::today()->toDateString();
        $currentMonth = (int) Carbon::today()->format('n');
        $currentYear = (int) Carbon::today()->format('Y');

        // 1. Admin creates staff payroll with allowance and bonus
        $response = $this->actingAs($this->admin)->post(route('staff-payroll.store'), [
            'user_id' => $this->staff->id,
            'period_month' => $currentMonth,
            'period_year' => $currentYear,
            'position' => 'Staff Operasional',
            'base_salary' => 2500000,
            'allowance' => 300000,
            'bonus' => 200000,
            'deduction' => 100000,
            'payment_status' => 'PAID',
            'payment_date' => $today,
            'payment_method' => 'TRANSFER',
            'payment_reference' => 'TRX-AUDIT-999',
            'record_expense' => true,
        ]);

        $response->assertRedirect();
        $payroll = StaffPayroll::where('user_id', $this->staff->id)->first();
        $this->assertNotNull($payroll);
        // Net: 2.500.000 + 300.000 + 200.000 - 100.000 = 2.900.000
        $this->assertEquals(2900000, $payroll->net_salary);
        $this->assertEquals('PAID', $payroll->payment_status);

        // Expense should automatically be created
        $expense = Expense::where('amount', 2900000)->first();
        $this->assertNotNull($expense);
        $this->assertEquals('Gaji & Upah', $expense->category);

        // 2. Staff can view own salary slip
        $responseStaff = $this->actingAs($this->staff)->get(route('staff.my-salary'));
        $responseStaff->assertStatus(200);
        $responseStaff->assertSee($payroll->payroll_code);
        $responseStaff->assertSee('2.900.000');

        // 3. Guide cannot access staff payroll
        $responseGuide = $this->actingAs($this->guideUser1)->get(route('staff-payroll.index'));
        $responseGuide->assertStatus(403);
    }

    public function test_dashboard_and_revenue_reports_with_monthly_filters_and_csv()
    {
        $today = Carbon::today()->toDateString();
        $currentMonth = (int) Carbon::today()->format('n');
        $currentYear = (int) Carbon::today()->format('Y');

        // 1. Dashboard today
        $resDashboardToday = $this->actingAs($this->admin)->get(route('dashboard', ['period' => 'today']));
        $resDashboardToday->assertStatus(200);

        // 2. Dashboard monthly
        $resDashboardMonth = $this->actingAs($this->admin)->get(route('dashboard', [
            'period' => 'month',
            'month' => $currentMonth,
            'year' => $currentYear,
        ]));
        $resDashboardMonth->assertStatus(200);
        $resDashboardMonth->assertSee('Export CSV');

        // 3. Dashboard CSV export
        $resDashboardCsv = $this->actingAs($this->admin)->get(route('dashboard.export-csv', [
            'period' => 'month',
            'month' => $currentMonth,
            'year' => $currentYear,
        ]));
        $resDashboardCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', $resDashboardCsv->headers->get('Content-Type'));

        // 4. Revenue report with monthly filter
        $resRevMonth = $this->actingAs($this->admin)->get(route('reports.revenue', [
            'filter_type' => 'month',
            'month' => $currentMonth,
            'year' => $currentYear,
        ]));
        $resRevMonth->assertStatus(200);
        $resRevMonth->assertSee('Terapkan Bulan');
        $resRevMonth->assertSee('Gaji Staff (Payroll)');

        // 5. Revenue CSV export
        $resRevCsv = $this->actingAs($this->admin)->get(route('reports.revenue', [
            'filter_type' => 'month',
            'month' => $currentMonth,
            'year' => $currentYear,
            'export' => 'csv',
        ]));
        $resRevCsv->assertStatus(200);
        $contentCsv = $resRevCsv->streamedContent();
        $this->assertStringContainsString('LAPORAN PENDAPATAN & PROFITABILITAS', $contentCsv);
        $this->assertStringContainsString('Total Beban Gaji Staff (Payroll)', $contentCsv);
    }

    public function test_all_admin_report_endpoints_render_successfully()
    {
        $today = Carbon::today()->toDateString();
        $start = Carbon::today()->startOfMonth()->toDateString();

        $routes = [
            'reports.revenue' => ['start_date' => $start, 'end_date' => $today],
            'reports.guides' => ['start_date' => $start, 'end_date' => $today],
            'reports.attendance' => ['start_date' => $start, 'end_date' => $today],
            'reports.queue' => ['start_date' => $start, 'end_date' => $today],
            'queue.board' => [],
            'queue.history' => [],
            'bookings.index' => [],
            'packages.index' => [],
            'guides.index' => [],
            'payroll.index' => [],
            'staff-payroll.index' => [],
            'expenses.index' => [],
            'settings.index' => [],
            'admin.user-approvals.index' => [],
        ];

        foreach ($routes as $route => $params) {
            $response = $this->actingAs($this->admin)->get(route($route, $params));
            $this->assertEquals(200, $response->status(), "Route {$route} failed with status {$response->status()}");
        }
    }
}
