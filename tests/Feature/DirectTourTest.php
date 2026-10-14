<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Expense;
use App\Models\Guide;
use App\Models\GuideAssignment;
use App\Models\GuideAttendance;
use App\Models\Setting;
use App\Models\TourPackage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectTourTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $guideUser;
    private Guide $guide;
    private TourPackage $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
            'is_active' => true,
            'email' => 'admin@example.com',
        ]);

        $this->staff = User::factory()->create([
            'role' => 'STAFF',
            'is_active' => true,
            'email' => 'staff@example.com',
        ]);

        $this->guideUser = User::factory()->create([
            'role' => 'GUIDE',
            'is_active' => true,
            'email' => 'guide@example.com',
        ]);

        $this->guide = Guide::create([
            'user_id' => $this->guideUser->id,
            'kode_guide' => 'GD-001',
            'nama' => 'Wayan Sukadana',
            'nomor_telepon' => '08123456789',
            'queue_number' => 1,
            'status' => 'AVAILABLE',
            'fee_type' => 'PER_GUEST',
            'fee_rate' => 50000,
        ]);

        $this->package = TourPackage::create([
            'name' => 'Snorkeling Nusa Penida',
            'duration' => 'Full Day',
            'default_price' => 200000,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_admin_and_staff_can_view_direct_tour_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('direct-tours.create'));
        $response->assertStatus(200);
        $response->assertSee('Tour Langsung (Tanpa Booking)');

        $responseStaff = $this->actingAs($this->staff)->get(route('direct-tours.create'));
        $responseStaff->assertStatus(200);
    }

    public function test_admin_can_create_direct_tour_with_auto_assign(): void
    {
        // Guide checks in today
        $today = Carbon::today()->toDateString();
        GuideAttendance::create([
            'guide_id' => $this->guide->id,
            'attendance_date' => $today,
            'check_in_at' => now(),
            'status' => 'CHECKED_IN',
        ]);

        $response = $this->actingAs($this->admin)->post(route('direct-tours.store'), [
            'tour_package_id' => $this->package->id,
            'guest_count' => 3,
            'customer_name' => 'Keluarga Bpk. Made',
            'customer_phone' => '081999888777',
            'nationality' => 'Indonesia',
            'guide_id' => 'auto',
            'start_immediately' => '1',
            'notes' => 'Tamu walk-in langsung berangkat',
        ]);

        $booking = Booking::where('customer_name', 'Keluarga Bpk. Made')->first();
        $this->assertNotNull($booking);
        $this->assertTrue($booking->is_walkin);
        $this->assertStringStartsWith('WALKIN-', $booking->booking_code);
        $this->assertEquals(3, $booking->guest_count);
        $this->assertEquals(600000, (float) $booking->total_price); // 3 * 200.000
        $this->assertEquals('ON_GOING', $booking->status);

        $assignment = GuideAssignment::where('booking_id', $booking->id)->first();
        $this->assertNotNull($assignment);
        $this->assertEquals($this->guide->id, $assignment->guide_id);
        $this->assertEquals(3, $assignment->guest_count);
        $this->assertEquals('ON_GOING', $assignment->status);
        $this->assertEquals(150000, (float) $assignment->fee_amount); // 3 * 50.000

        // Guide real-time status is ON_TOUR
        $this->assertEquals('ON_TOUR', $this->guide->fresh()->status);

        $response->assertRedirect(route('direct-tours.receipt', ['booking' => $booking, 'autoprint' => 1]));
    }

    public function test_admin_can_create_direct_tour_with_specific_guide_manual_selection(): void
    {
        $response = $this->actingAs($this->admin)->post(route('direct-tours.store'), [
            'tour_package_id' => $this->package->id,
            'guest_count' => 2,
            'customer_name' => 'Mr. David Smith',
            'guide_id' => $this->guide->id,
            'start_immediately' => '1',
        ]);

        $booking = Booking::where('customer_name', 'Mr. David Smith')->first();
        $this->assertNotNull($booking);
        $this->assertTrue($booking->is_walkin);
        $this->assertEquals('ON_GOING', $booking->status);

        // Guide should have been automatically checked in for direct tour and assigned
        $assignment = GuideAssignment::where('booking_id', $booking->id)->first();
        $this->assertNotNull($assignment);
        $this->assertEquals($this->guide->id, $assignment->guide_id);
        $this->assertEquals('ON_GOING', $assignment->status);
    }

    public function test_guide_can_create_walk_in_tour_directly_from_portal(): void
    {
        $today = Carbon::today()->toDateString();

        // Guide checks in first
        GuideAttendance::create([
            'guide_id' => $this->guide->id,
            'attendance_date' => $today,
            'check_in_at' => now(),
            'status' => 'CHECKED_IN',
        ]);

        $response = $this->actingAs($this->guideUser)->post(route('guide.direct-tour.store'), [
            'tour_package_id' => $this->package->id,
            'guest_count' => 4,
            'customer_name' => 'Walk-in Guests',
            'customer_phone' => '087766554433',
        ]);

        $booking = Booking::where('customer_name', 'Walk-in Guests')->first();
        $this->assertNotNull($booking);
        $this->assertTrue($booking->is_walkin);
        $this->assertEquals('ON_GOING', $booking->status);

        $assignment = GuideAssignment::where('booking_id', $booking->id)->first();
        $this->assertNotNull($assignment);
        $this->assertEquals($this->guide->id, $assignment->guide_id);
        $this->assertEquals(4, $assignment->guest_count);
        $this->assertEquals('ON_GOING', $assignment->status);
        $this->assertEquals(200000, (float) $assignment->fee_amount); // 4 * 50.000

        $this->assertEquals('ON_TOUR', $this->guide->fresh()->status);
        $response->assertSessionHas('success');
    }

    public function test_guide_can_view_queue_number_and_online_guides(): void
    {
        $today = Carbon::today()->toDateString();

        // Guide 1 (current user) checks in
        GuideAttendance::create([
            'guide_id' => $this->guide->id,
            'attendance_date' => $today,
            'check_in_at' => now(),
            'status' => 'CHECKED_IN',
        ]);

        // Guide 2
        $guideUser2 = User::factory()->create(['role' => 'GUIDE', 'is_active' => true]);
        $guide2 = Guide::create([
            'user_id' => $guideUser2->id,
            'kode_guide' => 'GD-002',
            'nama' => 'Made Sudarma',
            'nomor_telepon' => '08987654321',
            'queue_number' => 2,
            'status' => 'AVAILABLE',
            'fee_type' => 'PER_GUEST',
            'fee_rate' => 50000,
        ]);
        GuideAttendance::create([
            'guide_id' => $guide2->id,
            'attendance_date' => $today,
            'check_in_at' => now(),
            'status' => 'CHECKED_IN',
        ]);

        // Check Dashboard
        $responseDash = $this->actingAs($this->guideUser)->get(route('guide.dashboard'));
        $responseDash->assertStatus(200);
        $responseDash->assertSee('Posisi #1');
        $responseDash->assertSee('Antrean & Rekan Guide ON', false);
        $responseDash->assertSee('Made Sudarma');

        // Check Dedicated Mobile Queue Page
        $responseQueue = $this->actingAs($this->guideUser)->get(route('guide.queue'));
        $responseQueue->assertStatus(200);
        $responseQueue->assertSee('Papan Antrean Guide');
        $responseQueue->assertSee('STATUS ANTREAN ANDA');
        $responseQueue->assertSee('#1');
        $responseQueue->assertSee('Made Sudarma');
    }

    public function test_guide_can_view_queue_page_when_not_checked_in(): void
    {
        $responseQueue = $this->actingAs($this->guideUser)->get(route('guide.queue'));
        $responseQueue->assertStatus(200);
        $responseQueue->assertSee('Papan Antrean Guide');
        $responseQueue->assertSee('Check-in sekarang agar masuk ke dalam antrean harian.');
    }

    public function test_admin_can_create_direct_tour_with_phone_fee_deduction(): void
    {
        $today = Carbon::today()->toDateString();
        GuideAttendance::create([
            'guide_id' => $this->guide->id,
            'attendance_date' => $today,
            'check_in_at' => now(),
            'status' => 'CHECKED_IN',
        ]);

        Setting::set('default_phone_fee', '20000');

        // Phone fee is deducted automatically from Setting without manual input
        $response = $this->actingAs($this->admin)->post(route('direct-tours.store'), [
            'tour_package_id' => $this->package->id,
            'guest_count' => 2,
            'customer_name' => 'Bpk. Hendra',
            'guide_id' => 'auto',
            'start_immediately' => '1',
        ]);

        $booking = Booking::where('customer_name', 'Bpk. Hendra')->first();
        $this->assertNotNull($booking);
        $this->assertEquals(400000, (float) $booking->total_price); // 2 * 200.000
        $this->assertEquals(100000, (float) $booking->guide_fee_total); // 2 * 50.000
        $this->assertEquals(20000, (float) $booking->operational_cost); // Phone fee
        $this->assertEquals(280000, (float) $booking->company_profit); // 400.000 - 100.000 - 20.000

        // Assert Expense created
        $expense = Expense::where('booking_id', $booking->id)->first();
        $this->assertNotNull($expense);
        $this->assertEquals(20000, (float) $expense->amount);
        $this->assertEquals('Biaya Telepon / Komunikasi', $expense->category);
    }

    public function test_admin_can_create_direct_tour_with_driver_fee_option(): void
    {
        $today = Carbon::today()->toDateString();
        GuideAttendance::create([
            'guide_id' => $this->guide->id,
            'attendance_date' => $today,
            'check_in_at' => now(),
            'status' => 'CHECKED_IN',
        ]);

        Setting::set('default_phone_fee', '15000');

        $response = $this->actingAs($this->admin)->post(route('direct-tours.store'), [
            'tour_package_id' => $this->package->id,
            'guest_count' => 2,
            'customer_name' => 'Keluarga Bpk. Made',
            'guide_id' => 'auto',
            'phone_fee' => 15000,
            'give_driver_fee' => '1',
            'driver_fee' => 50000,
            'driver_notes' => 'Pak Ketut - Avanza DK 1234 XY',
            'start_immediately' => '1',
        ]);

        $booking = Booking::where('customer_name', 'Keluarga Bpk. Made')->first();
        $this->assertNotNull($booking);
        $this->assertEquals(400000, (float) $booking->total_price); // 2 * 200.000
        $this->assertEquals(100000, (float) $booking->guide_fee_total); // 2 * 50.000
        $this->assertEquals(65000, (float) $booking->operational_cost); // 15.000 phone + 50.000 driver
        $this->assertEquals(235000, (float) $booking->company_profit); // 400.000 - 100.000 - 65.000

        // Assert Driver Expense created
        $driverExpense = Expense::where('booking_id', $booking->id)->where('category', 'Uang Driver')->first();
        $this->assertNotNull($driverExpense);
        $this->assertEquals(50000, (float) $driverExpense->amount);
        $this->assertStringContainsString('Pak Ketut', $driverExpense->description);
    }

    public function test_direct_tour_without_driver_fee_does_not_create_driver_expense(): void
    {
        $today = Carbon::today()->toDateString();
        GuideAttendance::create([
            'guide_id' => $this->guide->id,
            'attendance_date' => $today,
            'check_in_at' => now(),
            'status' => 'CHECKED_IN',
        ]);

        $response = $this->actingAs($this->admin)->post(route('direct-tours.store'), [
            'tour_package_id' => $this->package->id,
            'guest_count' => 2,
            'customer_name' => 'Tamu Solo',
            'guide_id' => 'auto',
            'phone_fee' => 0,
            'give_driver_fee' => '0',
            'start_immediately' => '1',
        ]);

        $booking = Booking::where('customer_name', 'Tamu Solo')->first();
        $this->assertNotNull($booking);
        $hasDriverExpense = Expense::where('booking_id', $booking->id)->where('category', 'Uang Driver')->exists();
        $this->assertFalse($hasDriverExpense);
    }

    public function test_direct_tour_driver_fee_strictly_uses_admin_setting_and_cannot_be_overridden(): void
    {
        \App\Models\Setting::set('default_driver_fee', '75000');

        $today = Carbon::today()->toDateString();
        GuideAttendance::create([
            'guide_id' => $this->guide->id,
            'attendance_date' => $today,
            'check_in_at' => now(),
            'status' => 'CHECKED_IN',
        ]);

        // Attempt to pass arbitrary driver_fee of 999999
        $response = $this->actingAs($this->admin)->post(route('direct-tours.store'), [
            'tour_package_id' => $this->package->id,
            'guest_count' => 2,
            'customer_name' => 'Tamu Tampered Price',
            'guide_id' => 'auto',
            'give_driver_fee' => '1',
            'driver_fee' => 999999,
            'start_immediately' => '1',
        ]);

        $booking = Booking::where('customer_name', 'Tamu Tampered Price')->first();
        $this->assertNotNull($booking);

        // Must strictly use 75000 from Setting, not 999999
        $driverExpense = Expense::where('booking_id', $booking->id)->where('category', 'Uang Driver')->first();
        $this->assertNotNull($driverExpense);
        $this->assertEquals(75000, (float) $driverExpense->amount);
    }

    public function test_direct_tour_receipt_can_be_viewed_by_admin_and_staff(): void
    {
        $booking = Booking::create([
            'booking_code' => 'WALKIN-261014-001',
            'booking_date' => Carbon::today()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '14:00:00',
            'customer_name' => 'Bpk. Hendra Wijaya',
            'customer_phone' => '081234567890',
            'guest_count' => 2,
            'tour_package_id' => $this->package->id,
            'price_per_guest' => 200000,
            'total_price' => 400000,
            'guide_fee_total' => 100000,
            'operational_cost' => 52000,
            'company_profit' => 248000,
            'status' => 'ON_GOING',
            'is_walkin' => true,
            'created_by' => $this->admin->id,
        ]);

        // Admin can view receipt
        $responseAdmin = $this->actingAs($this->admin)->get(route('direct-tours.receipt', $booking));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('WALKIN-261014-001');
        $responseAdmin->assertSee('Bpk. Hendra Wijaya');
        $responseAdmin->assertSee('Cetak Nota Thermal');

        // Staff can also view receipt
        $responseStaff = $this->actingAs($this->staff)->get(route('direct-tours.receipt', $booking));
        $responseStaff->assertStatus(200);
        $responseStaff->assertSee('WALKIN-261014-001');

        // Reusable route bookings.receipt
        $responseBookingReceipt = $this->actingAs($this->admin)->get(route('bookings.receipt', $booking));
        $responseBookingReceipt->assertStatus(200);
    }

    public function test_direct_tour_receipt_shows_only_customer_total_bill_and_no_deductions(): void
    {
        Setting::set('company_name', 'ARTDEVATA TOURS');

        $booking = Booking::create([
            'booking_code' => 'WALKIN-261014-002',
            'booking_date' => Carbon::today()->toDateString(),
            'start_time' => '11:00:00',
            'end_time' => '15:00:00',
            'customer_name' => 'Ibu Ketut Sari',
            'customer_phone' => '081987654321',
            'guest_count' => 3,
            'tour_package_id' => $this->package->id,
            'price_per_guest' => 200000,
            'total_price' => 600000,
            'guide_fee_total' => 150000,
            'operational_cost' => 52000, // e.g. 2000 phone + 50000 driver
            'company_profit' => 398000,
            'status' => 'ON_GOING',
            'is_walkin' => true,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('direct-tours.receipt', $booking));

        $response->assertStatus(200);

        // MUST SEE: Customer billing information
        $response->assertSee('ARTDEVATA TOURS');
        $response->assertSee('WALKIN-261014-002');
        $response->assertSee('Ibu Ketut Sari');
        $response->assertSee('Snorkeling Nusa Penida');
        $response->assertSee('3 Pax x Rp 200.000');
        $response->assertSee('TOTAL TAGIHAN');
        $response->assertSee('Rp 600.000');
        $response->assertSee('LUNAS');

        // MUST NOT SEE: Internal deductions & costs
        $response->assertDontSee('Fee Guide');
        $response->assertDontSee('Estimasi Fee Guide');
        $response->assertDontSee('Pengurangan Biaya Telpon');
        $response->assertDontSee('Biaya Telepon');
        $response->assertDontSee('Uang Driver');
        $response->assertDontSee('Estimasi Margin');
        $response->assertDontSee('Margin Usaha');
        $response->assertDontSee('150.000'); // Guide fee amount must not be shown
        $response->assertDontSee('52.000');  // Operational cost must not be shown
        $response->assertDontSee('398.000'); // Company profit must not be shown
    }

    public function test_admin_can_create_direct_tour_with_custom_payment_method(): void
    {
        GuideAttendance::create([
            'guide_id' => $this->guide->id,
            'attendance_date' => Carbon::today()->toDateString(),
            'status' => 'CHECKED_IN',
            'check_in_time' => '08:00:00',
        ]);

        $response = $this->actingAs($this->admin)->post(route('direct-tours.store'), [
            'tour_package_id' => $this->package->id,
            'guest_count' => 2,
            'customer_name' => 'Michael Brown',
            'guide_id' => 'auto',
            'payment_method' => 'QRIS',
            'payment_reference' => 'RRN-99887766',
        ]);

        $booking = Booking::where('customer_name', 'Michael Brown')->first();
        $this->assertNotNull($booking);
        $this->assertEquals('QRIS', $booking->payment_method);
        $this->assertEquals('QRIS Scan', $booking->payment_method_label);
        $this->assertEquals('RRN-99887766', $booking->payment_reference);

        $resReceipt = $this->actingAs($this->admin)->get(route('direct-tours.receipt', $booking));
        $resReceipt->assertStatus(200);
        $resReceipt->assertSee('QRIS Scan');
        $resReceipt->assertSee('RRN-99887766');
    }

    public function test_admin_can_create_direct_tour_with_cash_and_change_calculation(): void
    {
        GuideAttendance::create([
            'guide_id' => $this->guide->id,
            'attendance_date' => Carbon::today()->toDateString(),
            'status' => 'CHECKED_IN',
            'check_in_time' => '08:00:00',
        ]);

        // 2 guests x 200.000 = 400.000
        $response = $this->actingAs($this->admin)->post(route('direct-tours.store'), [
            'tour_package_id' => $this->package->id,
            'guest_count' => 2,
            'customer_name' => 'Sarah Connor',
            'guide_id' => 'auto',
            'payment_method' => 'CASH',
            'amount_paid' => 500000,
        ]);

        $booking = Booking::where('customer_name', 'Sarah Connor')->first();
        $this->assertNotNull($booking);
        $this->assertEquals('CASH', $booking->payment_method);
        $this->assertEquals(400000, (float) $booking->total_price);
        $this->assertEquals(500000, (float) $booking->amount_paid);
        $this->assertEquals(100000, (float) $booking->change_amount);

        $resReceipt = $this->actingAs($this->admin)->get(route('direct-tours.receipt', $booking));
        $resReceipt->assertStatus(200);
        $resReceipt->assertSee('Tunai (Cash)');
        $resReceipt->assertSee('Rp 500.000');
        $resReceipt->assertSee('Rp 100.000');
    }
}

