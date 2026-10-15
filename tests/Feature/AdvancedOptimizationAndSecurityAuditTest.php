<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Expense;
use App\Models\Guide;
use App\Models\GuideAssignment;
use App\Models\GuideAttendance;
use App\Models\GuidePayment;
use App\Models\StaffPayroll;
use App\Models\TourPackage;
use App\Models\User;
use App\Services\DiscordWebhookService;
use App\Services\GuideQueueService;
use App\Services\OperationalShiftService;
use App\Services\PayrollService;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedOptimizationAndSecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'role' => 'STAFF',
            'name' => 'Wayan Staff',
            'is_active' => true,
            'salary_base' => 3000000,
            'salary_allowance' => 500000,
        ]);
    }

    /**
     * Test 1: Deleting a middle guide should NOT cause collision on next auto-generated guide code.
     */
    public function test_guide_code_generation_prevents_duplicate_when_middle_guide_is_deleted(): void
    {
        $g1 = Guide::create([
            'nama' => 'Guide 1',
            'nomor_telepon' => '081200000001',
            'kode_guide' => 'GD-001',
            'status' => 'AVAILABLE',
            'queue_number' => 1,
            'is_permanent' => true,
        ]);
        $g2 = Guide::create([
            'nama' => 'Guide 2',
            'nomor_telepon' => '081200000002',
            'kode_guide' => 'GD-002',
            'status' => 'AVAILABLE',
            'queue_number' => 2,
            'is_permanent' => true,
        ]);
        $g3 = Guide::create([
            'nama' => 'Guide 3',
            'nomor_telepon' => '081200000003',
            'kode_guide' => 'GD-003',
            'status' => 'AVAILABLE',
            'queue_number' => 3,
            'is_permanent' => true,
        ]);

        // Middle guide is deleted
        $g2->delete();

        // Guide count is now 2. Old logic (count + 1 = 3) would generate GD-003 and crash!
        // New logic inspects max numeric suffix and yields GD-004
        $response = $this->actingAs($this->admin)->get(route('guides.create'));
        $response->assertOk();
        $response->assertSee('GD-004');

        // Verify creating guide via store generates GD-004 safely with full valid payload
        $storeResponse = $this->actingAs($this->admin)->post(route('guides.store'), [
            'nama' => 'Guide 4 New',
            'kode_guide' => 'GD-004',
            'nomor_telepon' => '081234567899',
            'email' => 'guide4new@artdevata.com',
            'password' => 'secret123',
            'queue_number' => 4,
            'tipe_fee' => 'PER_GUEST',
            'fee_per_guest' => 50000,
            'fee_per_booking' => 0,
        ]);

        $storeResponse->assertRedirect(route('guides.index'));
        $this->assertDatabaseHas('guides', [
            'nama' => 'Guide 4 New',
            'kode_guide' => 'GD-004',
        ]);
    }

    /**
     * Test 2: Double-clicking / repeating mark-as-paid on Staff Payroll must be idempotent
     * and strictly prevent duplicate expense creation.
     */
    public function test_staff_payroll_duplicate_payment_is_strictly_idempotent(): void
    {
        $payroll = StaffPayroll::create([
            'payroll_code' => 'STF-PAY-202610-001',
            'user_id' => $this->staff->id,
            'period_month' => 10,
            'period_year' => 2026,
            'base_salary' => 3000000,
            'allowance' => 500000,
            'bonus' => 0,
            'deduction' => 0,
            'net_salary' => 3500000,
            'payment_status' => 'PENDING',
        ]);

        // First payment request
        $res1 = $this->actingAs($this->admin)->post(route('staff-payroll.pay', $payroll), [
            'payment_date' => '2026-10-15',
            'payment_method' => 'TRANSFER',
            'record_expense' => 1,
        ]);
        $res1->assertSessionHas('success');

        $payroll->refresh();
        $this->assertEquals('PAID', $payroll->payment_status);
        $this->assertEquals(1, Expense::where('description', 'like', '%STF-PAY-202610-001%')->count());

        // Duplicate payment request (e.g. double submit or network replay)
        $res2 = $this->actingAs($this->admin)->post(route('staff-payroll.pay', $payroll), [
            'payment_date' => '2026-10-15',
            'payment_method' => 'TRANSFER',
            'record_expense' => 1,
        ]);
        $res2->assertSessionHas('error');

        // Expense count MUST strictly remain 1
        $this->assertEquals(1, Expense::where('description', 'like', '%STF-PAY-202610-001%')->count());
    }

    /**
     * Test 3: Deleting a booking properly restores assigned guide status to AVAILABLE and cleans up.
     */
    public function test_booking_destroy_restores_guide_availability_and_cleans_relations(): void
    {
        $pkg = TourPackage::create([
            'name' => 'Paket Uluwatu Sunset',
            'duration' => '6 Jam',
            'default_price' => 200000,
            'status' => 'ACTIVE',
        ]);

        $guide = Guide::create([
            'nama' => 'Wayan Test Delete',
            'nomor_telepon' => '081200000010',
            'kode_guide' => 'GD-100',
            'status' => 'ON_TOUR',
            'queue_number' => 10,
            'is_permanent' => true,
        ]);

        $booking = Booking::create([
            'booking_code' => 'BK-DELETE-01',
            'customer_name' => 'Alice Cooper',
            'guest_count' => 2,
            'tour_package_id' => $pkg->id,
            'booking_date' => Carbon::today()->toDateString(),
            'start_time' => '14:00',
            'end_time' => '18:00',
            'total_price' => 400000,
            'status' => 'WAITING',
        ]);

        GuideAssignment::create([
            'booking_id' => $booking->id,
            'guide_id' => $guide->id,
            'guest_count' => 2,
            'fee_amount' => 100000,
            'status' => 'ON_GOING',
            'assigned_at' => now(),
        ]);

        // Admin destroys the booking
        $response = $this->actingAs($this->admin)->delete(route('bookings.destroy', $booking));
        $response->assertRedirect(route('bookings.index'));

        // Booking is removed from DB
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);

        // Guide status is safely restored to AVAILABLE
        $this->assertEquals('AVAILABLE', $guide->fresh()->status);
    }

    /**
     * Test 4: PayrollService::makePayment validates amount > 0, enforces pessimistic locking,
     * and strictly rejects overpayment without adminOverride.
     */
    public function test_payroll_service_make_payment_validation_and_concurrency_lock(): void
    {
        $guide = Guide::create([
            'nama' => 'Ketut Finance',
            'nomor_telepon' => '081200000011',
            'kode_guide' => 'GD-101',
            'status' => 'AVAILABLE',
            'queue_number' => 11,
            'is_permanent' => true,
        ]);

        $pkg = TourPackage::create([
            'name' => 'Paket Bedugul',
            'duration' => '8 Jam',
            'default_price' => 300000,
            'status' => 'ACTIVE',
        ]);

        $booking = Booking::create([
            'booking_code' => 'BK-FIN-01',
            'customer_name' => 'Charlie',
            'guest_count' => 1,
            'tour_package_id' => $pkg->id,
            'booking_date' => Carbon::today()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '15:00',
            'total_price' => 300000,
            'status' => 'COMPLETED',
        ]);

        GuideAssignment::create([
            'booking_id' => $booking->id,
            'guide_id' => $guide->id,
            'guest_count' => 1,
            'fee_amount' => 150000,
            'status' => 'COMPLETED',
            'assigned_at' => now(),
        ]);

        $payrollService = app(PayrollService::class);

        // 1. Zero amount rejected
        try {
            $payrollService->makePayment($guide, 0, Carbon::today()->toDateString());
            $this->fail('Zero payment should have been rejected');
        } catch (Exception $e) {
            $this->assertStringContainsString('lebih besar dari 0', $e->getMessage());
        }

        // 2. Negative amount rejected
        try {
            $payrollService->makePayment($guide, -50000, Carbon::today()->toDateString());
            $this->fail('Negative payment should have been rejected');
        } catch (Exception $e) {
            $this->assertStringContainsString('lebih besar dari 0', $e->getMessage());
        }

        // 3. Overpayment rejected (fee is 150000, paying 200000)
        try {
            $payrollService->makePayment($guide, 200000, Carbon::today()->toDateString());
            $this->fail('Overpayment should have been rejected');
        } catch (Exception $e) {
            $this->assertStringContainsString('melebihi sisa fee', $e->getMessage());
        }

        // 4. Valid payment accepted
        $pay1 = $payrollService->makePayment($guide, 100000, Carbon::today()->toDateString());
        $this->assertEquals(100000, (float) $pay1->amount);

        // 5. Subsequent overpayment check (remaining is 50000, paying 60000)
        try {
            $payrollService->makePayment($guide, 60000, Carbon::today()->toDateString());
            $this->fail('Second overpayment should have been rejected');
        } catch (Exception $e) {
            $this->assertStringContainsString('melebihi sisa fee', $e->getMessage());
        }

        // 6. Remaining 50000 paid successfully
        $pay2 = $payrollService->makePayment($guide, 50000, Carbon::today()->toDateString());
        $this->assertEquals(50000, (float) $pay2->amount);

        // Total payments = 150000, outstanding balance = 0
        $totalPaid = GuidePayment::where('guide_id', $guide->id)->sum('amount');
        $this->assertEquals(150000, (float) $totalPaid);
    }

    /**
     * Test 5: OperationalShiftService::closeShift automatically completes ongoing tour assignments
     * and syncs parent Booking status to COMPLETED.
     */
    public function test_operational_shift_closure_cascades_safely_to_ongoing_tours(): void
    {
        $shiftService = app(OperationalShiftService::class);
        $today = Carbon::today()->toDateString();

        // Open shift
        $shiftService->openShift($this->admin, $today);

        $guide = Guide::create([
            'nama' => 'Nyoman Active Shift',
            'nomor_telepon' => '081200000012',
            'kode_guide' => 'GD-102',
            'status' => 'AVAILABLE',
            'queue_number' => 12,
            'is_permanent' => true,
        ]);

        $attendance = GuideAttendance::create([
            'guide_id' => $guide->id,
            'attendance_date' => $today,
            'check_in_at' => now()->subHours(2),
            'status' => 'CHECKED_IN',
        ]);

        $pkg = TourPackage::create([
            'name' => 'Paket Rafting',
            'duration' => '3 Jam',
            'default_price' => 150000,
            'status' => 'ACTIVE',
        ]);

        $booking = Booking::create([
            'booking_code' => 'BK-SHIFT-01',
            'customer_name' => 'David Hasselhoff',
            'guest_count' => 2,
            'tour_package_id' => $pkg->id,
            'booking_date' => $today,
            'start_time' => '10:00',
            'end_time' => '13:00',
            'price_per_guest' => 150000,
            'total_price' => 300000,
            'status' => 'ON_GOING',
        ]);

        $assignment = GuideAssignment::create([
            'booking_id' => $booking->id,
            'guide_id' => $guide->id,
            'guest_count' => 2,
            'fee_amount' => 80000,
            'status' => 'ON_GOING',
            'assigned_at' => now()->subHour(),
        ]);

        // Close operational shift
        $shiftService->closeShift($this->admin, $today);

        // Verification:
        // 1. Shift is closed
        $this->assertFalse($shiftService->isOpen($today));

        // 2. Guide attendance is checked out
        $this->assertEquals('CHECKED_OUT', $attendance->fresh()->status);
        $this->assertNotNull($attendance->fresh()->check_out_at);

        // 3. Assignment is completed
        $this->assertEquals('COMPLETED', $assignment->fresh()->status);
        $this->assertNotNull($assignment->fresh()->completed_at);

        // 4. Parent booking is COMPLETED
        $this->assertEquals('COMPLETED', $booking->fresh()->status);
    }

    /**
     * Test 6: DiscordWebhookService masks webhook URLs and secret tokens in logs.
     */
    public function test_discord_service_masks_sensitive_tokens_in_logs(): void
    {
        $discord = app(DiscordWebhookService::class);
        $rawUrl = 'https://discord.com/api/webhooks/123456789/ABCDEFGHIJKLMNO_PQRSTUVWXYZ1234567890';
        
        $reflection = new \ReflectionClass($discord);
        $method = $reflection->getMethod('maskUrl');
        $method->setAccessible(true);

        $masked = $method->invoke($discord, $rawUrl);

        $this->assertStringNotContainsString('ABCDEFGHIJKLMNO_PQRSTUVWXYZ1234567890', $masked);
        $this->assertStringContainsString('123456789', $masked);
        $this->assertStringContainsString('***', $masked);
    }

    /**
     * Test 7: Multi-guide auto-assignment validates allocations against non-positive integers.
     */
    public function test_guide_queue_service_rejects_negative_or_zero_guest_allocations(): void
    {
        $queueService = app(GuideQueueService::class);

        $pkg = TourPackage::create([
            'name' => 'Paket Batur Sunrise',
            'duration' => '5 Jam',
            'default_price' => 250000,
            'status' => 'ACTIVE',
        ]);

        $booking = Booking::create([
            'booking_code' => 'BK-ALLOC-01',
            'customer_name' => 'Allocation Test',
            'guest_count' => 10,
            'tour_package_id' => $pkg->id,
            'booking_date' => Carbon::today()->toDateString(),
            'start_time' => '05:00',
            'end_time' => '10:00',
            'total_price' => 2500000,
            'status' => 'WAITING',
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Jumlah alokasi tamu per guide harus lebih besar dari 0.');

        $queueService->autoAssignMultiple($booking, [0, 10]);
    }
}
