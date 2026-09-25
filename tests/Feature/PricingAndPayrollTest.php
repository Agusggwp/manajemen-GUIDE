<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guide;
use App\Models\GuideAssignment;
use App\Models\GuideAttendance;
use App\Models\GuidePayment;
use App\Models\TourPackage;
use App\Models\TourPackagePrice;
use App\Models\User;
use App\Services\BookingService;
use App\Services\GuideQueueService;
use App\Services\PayrollService;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingAndPayrollTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private TourPackage $package;
    private Guide $guide;
    private BookingService $bookingService;
    private GuideQueueService $queueService;
    private PayrollService $payrollService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@artdevata.com',
            'role' => 'ADMIN',
            'is_active' => true,
        ]);

        $this->bookingService = new BookingService();
        $this->queueService = new GuideQueueService();
        $this->payrollService = new PayrollService();

        // Create Tour Package with tiered pricing
        $this->package = TourPackage::create([
            'name' => 'Ubud Cultural Tour',
            'duration' => '8 Jam',
            'default_price' => 150000,
            'status' => 'ACTIVE',
        ]);

        TourPackagePrice::create(['tour_package_id' => $this->package->id, 'min_guests' => 1, 'max_guests' => 5, 'price_per_guest' => 150000, 'active' => true]);
        TourPackagePrice::create(['tour_package_id' => $this->package->id, 'min_guests' => 6, 'max_guests' => 10, 'price_per_guest' => 130000, 'active' => true]);
        TourPackagePrice::create(['tour_package_id' => $this->package->id, 'min_guests' => 11, 'max_guests' => 20, 'price_per_guest' => 110000, 'active' => true]);
        TourPackagePrice::create(['tour_package_id' => $this->package->id, 'min_guests' => 21, 'max_guests' => 50, 'price_per_guest' => 90000, 'active' => true]);

        // Create Guide
        $guideUser = User::factory()->create(['role' => 'GUIDE']);
        $this->guide = Guide::create([
            'user_id' => $guideUser->id,
            'kode_guide' => 'GD-001',
            'nama' => 'Budi Antara',
            'nomor_telepon' => '081234567890',
            'queue_number' => 1,
            'status' => 'AVAILABLE',
            'tipe_fee' => 'PER_GUEST',
            'fee_per_guest' => 50000,
            'fee_minimum' => 0,
        ]);
    }

    public function test_automatic_tiered_pricing_selection(): void
    {
        // 3 guests -> tier 1-5 -> 150,000
        $priceTier1 = $this->package->getPriceForGuests(3);
        $this->assertEquals(150000, $priceTier1);

        // 8 guests -> tier 6-10 -> 130,000
        $priceTier2 = $this->package->getPriceForGuests(8);
        $this->assertEquals(130000, $priceTier2);

        // 15 guests -> tier 11-20 -> 110,000
        $priceTier3 = $this->package->getPriceForGuests(15);
        $this->assertEquals(110000, $priceTier3);

        // 25 guests -> tier 21-50 -> 90,000
        $priceTier4 = $this->package->getPriceForGuests(25);
        $this->assertEquals(90000, $priceTier4);
    }

    public function test_pricing_snapshot_preserved_when_package_prices_change(): void
    {
        // Create booking with 15 guests
        $booking = $this->bookingService->createBooking([
            'booking_date' => Carbon::today()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '17:00',
            'customer_name' => 'John Doe',
            'guest_count' => 15,
            'tour_package_id' => $this->package->id,
            'location' => 'Ubud',
        ], $this->admin->id);

        $this->assertEquals(110000, $booking->price_per_guest);
        $this->assertEquals(1650000, $booking->total_price);

        // Now modify package pricing
        TourPackagePrice::where('tour_package_id', $this->package->id)
            ->where('min_guests', 11)
            ->update(['price_per_guest' => 200000]);

        // Booking snapshot remains untouched!
        $refreshed = $booking->fresh();
        $this->assertEquals(110000, $refreshed->price_per_guest);
        $this->assertEquals(1650000, $refreshed->total_price);
    }

    public function test_guide_fee_snapshot_preserved_when_guide_fee_rate_changes(): void
    {
        $today = Carbon::today()->toDateString();
        GuideAttendance::create([
            'guide_id' => $this->guide->id,
            'attendance_date' => $today,
            'check_in_at' => '07:30:00',
            'status' => 'CHECKED_IN',
        ]);

        $booking = $this->bookingService->createBooking([
            'booking_date' => $today,
            'start_time' => '08:00',
            'end_time' => '12:00',
            'customer_name' => 'Alice',
            'guest_count' => 10,
            'tour_package_id' => $this->package->id,
        ], $this->admin->id);

        $assignment = $this->queueService->autoAssign($booking, 10, $this->admin);

        // 10 guests * 50,000 = 500,000
        $this->assertEquals(50000, $assignment->fee_rate);
        $this->assertEquals(500000, $assignment->fee_amount);

        // Change guide master fee
        $this->guide->update(['fee_per_guest' => 100000]);

        // Assignment fee remains 500,000
        $this->assertEquals(500000, $assignment->fresh()->fee_amount);
    }

    public function test_payment_cannot_exceed_outstanding_balance(): void
    {
        $today = Carbon::today()->toDateString();
        GuideAttendance::create([
            'guide_id' => $this->guide->id,
            'attendance_date' => $today,
            'status' => 'CHECKED_IN',
        ]);

        $booking = $this->bookingService->createBooking([
            'booking_date' => $today,
            'start_time' => '08:00',
            'end_time' => '12:00',
            'customer_name' => 'Alice',
            'guest_count' => 10,
            'tour_package_id' => $this->package->id,
        ], $this->admin->id);

        $assignment = $this->queueService->autoAssign($booking, 10, $this->admin);
        $assignment->update(['status' => 'COMPLETED']);

        // Outstanding balance is 500,000
        // Attempting to pay 600,000 should throw Exception
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('melebihi sisa fee yang belum dibayar');

        $this->payrollService->makePayment($this->guide, 600000, $today, 'TRANSFER', 'Overpayment attempt', false, $this->admin->id);
    }

    public function test_schedule_conflict_detection(): void
    {
        $today = Carbon::today()->toDateString();
        GuideAttendance::create([
            'guide_id' => $this->guide->id,
            'attendance_date' => $today,
            'status' => 'CHECKED_IN',
        ]);

        // Booking 1: 08:00 - 12:00
        $booking1 = $this->bookingService->createBooking([
            'booking_date' => $today,
            'start_time' => '08:00',
            'end_time' => '12:00',
            'customer_name' => 'Alice',
            'guest_count' => 5,
            'tour_package_id' => $this->package->id,
        ], $this->admin->id);
        $this->queueService->autoAssign($booking1, 5, $this->admin);

        // Overlapping check: 10:00 - 13:00 on same day -> Conflict!
        $hasConflict = $this->queueService->hasScheduleConflict($this->guide->id, $today, '10:00', '13:00');
        $this->assertTrue($hasConflict);

        // Non-overlapping check: 13:00 - 17:00 on same day -> No Conflict!
        $noConflict = $this->queueService->hasScheduleConflict($this->guide->id, $today, '13:00', '17:00');
        $this->assertFalse($noConflict);
    }

    public function test_standard_package_uses_single_1_pax_price_for_any_guest_count(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('packages.store'), [
            'name' => 'Snorkeling Nusa Penida Single Price',
            'duration' => '6 Jam',
            'default_price' => 175000,
            'has_special_tiers' => 0,
        ]);

        $response->assertRedirect(route('packages.index'));

        $pkg = TourPackage::where('name', 'Snorkeling Nusa Penida Single Price')->firstOrFail();
        $this->assertEquals(175000, (float)$pkg->default_price);
        $this->assertCount(0, $pkg->prices);

        // For 1 pax, 5 pax, 20 pax, price per guest is always 175,000
        $this->assertEquals(175000, $pkg->getPriceForGuestCount(1));
        $this->assertEquals(175000, $pkg->getPriceForGuestCount(5));
        $this->assertEquals(175000, $pkg->getPriceForGuestCount(20));
    }

    public function test_special_package_creates_tiered_prices_and_can_be_converted_back_to_single_price(): void
    {
        $this->actingAs($this->admin);

        // 1. Create special package with tiers
        $this->post(route('packages.store'), [
            'name' => 'Rafting Ayung Tiered',
            'duration' => '4 Jam',
            'default_price' => 200000,
            'has_special_tiers' => 1,
            'tiers' => [
                ['min' => 1, 'max' => 5, 'price' => 200000],
                ['min' => 6, 'max' => 10, 'price' => 180000],
            ],
        ])->assertRedirect(route('packages.index'));

        $pkg = TourPackage::where('name', 'Rafting Ayung Tiered')->firstOrFail();
        $this->assertCount(2, $pkg->prices);
        $this->assertEquals(200000, $pkg->getPriceForGuestCount(3));
        $this->assertEquals(180000, $pkg->getPriceForGuestCount(8));

        // 2. Update to turn off special tiers
        $this->put(route('packages.update', $pkg), [
            'name' => 'Rafting Ayung Now Single Price',
            'duration' => '4 Jam',
            'default_price' => 190000,
            'status' => 'ACTIVE',
            'has_special_tiers' => 0,
        ])->assertRedirect(route('packages.index'));

        $pkg->refresh();
        $this->assertEquals('Rafting Ayung Now Single Price', $pkg->name);
        $this->assertEquals(190000, (float)$pkg->default_price);
        $this->assertCount(0, $pkg->prices);

        // Now uses single price 190,000 for any guest count
        $this->assertEquals(190000, $pkg->getPriceForGuestCount(8));
    }
}
