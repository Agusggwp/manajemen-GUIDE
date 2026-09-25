<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guide;
use App\Models\GuideAssignment;
use App\Models\GuideAttendance;
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

        $response->assertRedirect(route('bookings.show', $booking));
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
}
