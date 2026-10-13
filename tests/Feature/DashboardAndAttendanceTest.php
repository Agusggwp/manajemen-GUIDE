<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Expense;
use App\Models\Guide;
use App\Models\GuideAssignment;
use App\Models\TourPackage;
use App\Models\User;
use App\Services\GuideAttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAndAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $guideUser;
    private Guide $guide;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
            'is_active' => true,
            'email_verified_at' => now(),
            'approval_status' => 'APPROVED',
        ]);

        $this->guideUser = User::factory()->create([
            'role' => 'GUIDE',
            'is_active' => true,
            'email_verified_at' => now(),
            'approval_status' => 'APPROVED',
        ]);

        $this->guide = Guide::create([
            'user_id' => $this->guideUser->id,
            'nama' => 'Wayan Test',
            'kode_guide' => 'GD-001',
            'nomor_telepon' => '08123456789',
            'status' => 'OFF',
            'queue_number' => 1,
            'fee_harian' => 100000,
        ]);
    }

    public function test_guide_can_check_in_and_check_out_multiple_times_a_day()
    {
        $attendanceService = app(GuideAttendanceService::class);
        $today = Carbon::today()->toDateString();

        // 1st Check-in
        $att1 = $attendanceService->checkIn($this->guide, $today);
        $this->assertEquals('CHECKED_IN', $att1->status);
        $this->assertTrue($this->guide->fresh()->isCheckedInOn($today));

        // 1st Check-out
        $att2 = $attendanceService->checkOut($this->guide, $today);
        $this->assertEquals('CHECKED_OUT', $att2->status);
        $this->assertFalse($this->guide->fresh()->isCheckedInOn($today));

        // 2nd Check-in on same day (no limits)
        $att3 = $attendanceService->checkIn($this->guide, $today);
        $this->assertEquals('CHECKED_IN', $att3->status);
        $this->assertNull($att3->check_out_at);
        $this->assertTrue($this->guide->fresh()->isCheckedInOn($today));

        // 2nd Check-out on same day
        $att4 = $attendanceService->checkOut($this->guide, $today);
        $this->assertEquals('CHECKED_OUT', $att4->status);
        $this->assertFalse($this->guide->fresh()->isCheckedInOn($today));
    }

    public function test_admin_dashboard_can_filter_by_month()
    {
        $pkg = TourPackage::create([
            'name' => 'Paket Kintamani',
            'duration' => '8 Jam',
            'default_price' => 250000,
            'status' => 'ACTIVE',
        ]);

        // Create booking in previous month
        $pastDate = Carbon::today()->subMonths(1)->startOfMonth()->addDays(5)->toDateString();
        Booking::create([
            'booking_code' => 'BK-PAST',
            'customer_name' => 'John Past',
            'guest_count' => 4,
            'tour_package_id' => $pkg->id,
            'booking_date' => $pastDate,
            'start_time' => '08:00',
            'end_time' => '16:00',
            'total_price' => 1000000,
            'guide_fee_total' => 200000,
            'status' => 'COMPLETED',
        ]);

        $pastMonth = Carbon::parse($pastDate)->month;
        $pastYear = Carbon::parse($pastDate)->year;

        $response = $this->actingAs($this->admin)->get(route('dashboard', [
            'period' => 'month',
            'month' => $pastMonth,
            'year' => $pastYear,
        ]));

        $response->assertStatus(200);
        $response->assertSee('BK-PAST');
        $response->assertSee('John Past');
        $response->assertSee('Export CSV');
    }

    public function test_admin_dashboard_can_export_csv_for_today_and_month()
    {
        $pkg = TourPackage::create([
            'name' => 'Paket Bedugul',
            'duration' => '8 Jam',
            'default_price' => 300000,
            'status' => 'ACTIVE',
        ]);

        $today = Carbon::today()->toDateString();
        Booking::create([
            'booking_code' => 'BK-TODAY-01',
            'customer_name' => 'Michael Smith',
            'guest_count' => 2,
            'tour_package_id' => $pkg->id,
            'booking_date' => $today,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'total_price' => 600000,
            'guide_fee_total' => 100000,
            'status' => 'COMPLETED',
        ]);

        Expense::create([
            'category' => 'BBM',
            'description' => 'Bensin Avanza',
            'amount' => 150000,
            'expense_date' => $today,
            'recorded_by' => $this->admin->id,
        ]);

        // Export CSV today
        $responseToday = $this->actingAs($this->admin)->get(route('dashboard.export-csv', ['period' => 'today']));
        $responseToday->assertStatus(200);
        $this->assertStringContainsString('text/csv', $responseToday->headers->get('Content-Type'));
        $contentToday = $responseToday->streamedContent();
        $this->assertStringContainsString('LAPORAN OPERASIONAL & KEUANGAN', $contentToday);
        $this->assertStringContainsString('BK-TODAY-01', $contentToday);
        $this->assertStringContainsString('Michael Smith', $contentToday);
        $this->assertStringContainsString('Bensin Avanza', $contentToday);

        // Export CSV monthly
        $currentMonth = Carbon::today()->month;
        $currentYear = Carbon::today()->year;
        $responseMonth = $this->actingAs($this->admin)->get(route('dashboard.export-csv', [
            'period' => 'month',
            'month' => $currentMonth,
            'year' => $currentYear,
        ]));
        $responseMonth->assertStatus(200);
        $this->assertStringContainsString('text/csv', $responseMonth->headers->get('Content-Type'));
        $contentMonth = $responseMonth->streamedContent();
        $this->assertStringContainsString('BK-TODAY-01', $contentMonth);
    }

    public function test_revenue_report_includes_staff_payroll_and_exports_csv()
    {
        $pkg = TourPackage::create([
            'name' => 'Paket Ubud',
            'duration' => '6 Jam',
            'default_price' => 200000,
            'status' => 'ACTIVE',
        ]);

        $today = Carbon::today()->toDateString();
        $currentMonth = (int) Carbon::today()->format('n');
        $currentYear = (int) Carbon::today()->format('Y');

        Booking::create([
            'booking_code' => 'BK-REV-01',
            'customer_name' => 'Alice Revenue',
            'guest_count' => 5,
            'tour_package_id' => $pkg->id,
            'booking_date' => $today,
            'start_time' => '08:00',
            'end_time' => '14:00',
            'total_price' => 1000000,
            'guide_fee_total' => 250000,
            'operational_cost' => 150000,
            'company_profit' => 600000,
            'status' => 'COMPLETED',
        ]);

        // Create Staff Member and Payroll
        $staffUser = User::factory()->create([
            'name' => 'Budi Staff',
            'role' => 'STAFF',
            'is_active' => true,
            'approval_status' => 'APPROVED',
        ]);

        \App\Models\StaffPayroll::create([
            'payroll_code' => 'STF-TEST-001',
            'user_id' => $staffUser->id,
            'period_month' => $currentMonth,
            'period_year' => $currentYear,
            'position' => 'Staff Operasional',
            'base_salary' => 100000,
            'allowance' => 0,
            'bonus' => 0,
            'deduction' => 0,
            'net_salary' => 100000,
            'payment_status' => 'PAID',
            'payment_date' => $today,
            'created_by' => $this->admin->id,
        ]);

        // Access revenue report
        $startDate = Carbon::today()->startOfMonth()->toDateString();
        $endDate = Carbon::today()->endOfMonth()->toDateString();

        $response = $this->actingAs($this->admin)->get(route('reports.revenue', [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Beban Gaji Staff');
        $response->assertSee('STF-TEST-001');
        $response->assertSee('Budi Staff');
        // Revenue: 1,000,000 - Fee: 250,000 - Ops: 150,000 - Staff: 100,000 = Profit: 500,000
        $response->assertSee('500.000');

        // Test CSV export for revenue report
        $responseCsv = $this->actingAs($this->admin)->get(route('reports.revenue', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'export' => 'csv',
        ]));
        $responseCsv->assertStatus(200);
        $contentCsv = $responseCsv->streamedContent();
        $this->assertStringContainsString('LAPORAN PENDAPATAN & PROFITABILITAS', $contentCsv);
        $this->assertStringContainsString('Total Beban Gaji Staff (Payroll)', $contentCsv);
        $this->assertStringContainsString('STF-TEST-001', $contentCsv);
        $this->assertStringContainsString('Budi Staff', $contentCsv);
        $this->assertStringContainsString('BK-REV-01', $contentCsv);
    }

    public function test_guide_dashboard_orders_unfinished_assignments_first(): void
    {
        $today = Carbon::today()->toDateString();
        
        $package = TourPackage::create([
            'name' => 'Paket Bedugul',
            'duration_days' => 1,
            'default_price' => 500000,
            'pricing_type' => 'TIERED',
            'status' => 'ACTIVE',
        ]);

        // Booking 1: earlier assigned, but already COMPLETED
        $booking1 = Booking::create([
            'booking_code' => 'BK-DONE',
            'tour_package_id' => $package->id,
            'customer_name' => 'Tamu Pagi',
            'guest_count' => 2,
            'booking_date' => $today,
            'start_time' => '08:00',
            'end_time' => '12:00',
            'location' => 'Bedugul',
            'total_price' => 1000000,
            'guide_fee_total' => 100000,
            'operational_cost' => 50000,
            'company_profit' => 850000,
            'status' => 'COMPLETED',
        ]);
        $assignCompleted = GuideAssignment::create([
            'booking_id' => $booking1->id,
            'guide_id' => $this->guide->id,
            'guest_count' => 2,
            'fee_type' => 'TIERED',
            'fee_rate' => 50000,
            'fee_amount' => 100000,
            'queue_position' => 1,
            'assigned_at' => Carbon::now()->subHours(4),
            'status' => 'COMPLETED',
        ]);

        // Booking 2: later assigned, but still ASSIGNED (unfinished)
        $booking2 = Booking::create([
            'booking_code' => 'BK-PENDING',
            'tour_package_id' => $package->id,
            'customer_name' => 'Tamu Siang',
            'guest_count' => 3,
            'booking_date' => $today,
            'start_time' => '13:00',
            'end_time' => '17:00',
            'location' => 'Kintamani',
            'total_price' => 1500000,
            'guide_fee_total' => 150000,
            'operational_cost' => 50000,
            'company_profit' => 1300000,
            'status' => 'FULLY_ASSIGNED',
        ]);
        $assignPending = GuideAssignment::create([
            'booking_id' => $booking2->id,
            'guide_id' => $this->guide->id,
            'guest_count' => 3,
            'fee_type' => 'TIERED',
            'fee_rate' => 50000,
            'fee_amount' => 150000,
            'queue_position' => 2,
            'assigned_at' => Carbon::now()->subHours(2),
            'status' => 'ASSIGNED',
        ]);

        $response = $this->actingAs($this->guideUser)->get(route('guide.dashboard'));
        $response->assertStatus(200);

        // Verify that in todayAssignments passed to view, unfinished is first
        $todayAssignments = $response->viewData('todayAssignments');
        $this->assertCount(2, $todayAssignments);
        $this->assertEquals($assignPending->id, $todayAssignments->first()->id);
        $this->assertEquals($assignCompleted->id, $todayAssignments->last()->id);
    }
}
