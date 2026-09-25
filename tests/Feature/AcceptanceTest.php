<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guide;
use App\Models\GuideAssignment;
use App\Models\GuideAttendance;
use App\Models\GuideQueueState;
use App\Models\TourPackage;
use App\Models\User;
use App\Services\GuideAttendanceService;
use App\Services\GuideQueueService;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcceptanceTest extends TestCase
{
    use RefreshDatabase;

    protected GuideQueueService $queueService;
    protected GuideAttendanceService $attendanceService;
    protected TourPackage $tourPackage;
    protected User $admin;
    protected array $guides = [];
    protected string $today;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queueService = new GuideQueueService();
        $this->attendanceService = new GuideAttendanceService();
        $this->today = Carbon::today()->toDateString();

        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
            'is_active' => true,
        ]);

        $this->tourPackage = TourPackage::create([
            'name' => 'Ubud Tour',
            'duration' => '8 Jam',
            'default_price' => 150000,
            'status' => 'ACTIVE',
        ]);

        // Create 5 guides with permanent queue numbers:
        // 1=Budi, 2=Made, 3=Komang, 4=Wayan, 5=Agus
        $names = [1 => 'Budi', 2 => 'Made', 3 => 'Komang', 4 => 'Wayan', 5 => 'Agus'];
        foreach ($names as $q => $name) {
            $user = User::factory()->create(['role' => 'GUIDE']);
            $this->guides[$q] = Guide::create([
                'user_id' => $user->id,
                'kode_guide' => 'GD-00' . $q,
                'nama' => $name,
                'nomor_telepon' => '081200000' . $q,
                'queue_number' => $q,
                'status' => 'AVAILABLE',
                'tipe_fee' => 'PER_GUEST',
                'fee_per_guest' => 50000,
            ]);
        }
    }

    private function setGuideCheckedIn(int $queueNum, string $date = null): void
    {
        $targetDate = $date ?? $this->today;
        GuideAttendance::updateOrCreate(
            ['guide_id' => $this->guides[$queueNum]->id, 'attendance_date' => $targetDate],
            ['status' => 'CHECKED_IN', 'check_in_at' => now()]
        );
        $this->guides[$queueNum]->update(['status' => 'AVAILABLE']);
    }

    private function setGuideOff(int $queueNum, string $date = null): void
    {
        $targetDate = $date ?? $this->today;
        GuideAttendance::updateOrCreate(
            ['guide_id' => $this->guides[$queueNum]->id, 'attendance_date' => $targetDate],
            ['status' => 'OFF']
        );
        $this->guides[$queueNum]->update(['status' => 'OFF']);
    }

    private function setQueuePosition(int $pos): void
    {
        GuideQueueState::updateOrCreate(
            ['id' => 1],
            ['current_position' => $pos]
        );
    }

    private function createBooking(int $guestCount = 10, string $date = null, string $start = '08:00', string $end = '16:00'): Booking
    {
        return Booking::create([
            'booking_code' => 'BOOK-' . uniqid(),
            'booking_date' => $date ?? $this->today,
            'start_time' => $start,
            'end_time' => $end,
            'customer_name' => 'John Doe',
            'guest_count' => $guestCount,
            'tour_package_id' => $this->tourPackage->id,
            'price_per_guest' => 150000,
            'total_price' => $guestCount * 150000,
            'status' => 'WAITING',
            'created_by' => $this->admin->id,
        ]);
    }

    /**
     * Test 1: 5 guide. Last position #3. #4 available. Expected: #4.
     */
    public function test_1_five_guides_last_3_four_available_expects_four(): void
    {
        $this->setQueuePosition(3);
        for ($i = 1; $i <= 5; $i++) {
            $this->setGuideCheckedIn($i);
        }

        $booking = $this->createBooking();
        $assignment = $this->queueService->autoAssign($booking, 10, $this->admin);

        $this->assertEquals(4, $assignment->guide->queue_number);
        $this->assertEquals('Wayan', $assignment->guide->nama);
        $this->assertEquals(4, GuideQueueState::first()->current_position);
    }

    /**
     * Test 2: Last #3. #4 OFF. #5 available. Expected: #5.
     */
    public function test_2_last_3_four_off_five_available_expects_five(): void
    {
        $this->setQueuePosition(3);
        $this->setGuideCheckedIn(1);
        $this->setGuideCheckedIn(2);
        $this->setGuideCheckedIn(3);
        $this->setGuideOff(4); // #4 OFF
        $this->setGuideCheckedIn(5); // #5 available

        $booking = $this->createBooking();
        $assignment = $this->queueService->autoAssign($booking, 10, $this->admin);

        $this->assertEquals(5, $assignment->guide->queue_number);
        $this->assertEquals('Agus', $assignment->guide->nama);
        $this->assertEquals(5, GuideQueueState::first()->current_position);
    }

    /**
     * Test 3: Last #5. Expected: #1 (wrap around).
     */
    public function test_3_last_5_expects_one_wrap_around(): void
    {
        $this->setQueuePosition(5);
        for ($i = 1; $i <= 5; $i++) {
            $this->setGuideCheckedIn($i);
        }

        $booking = $this->createBooking();
        $assignment = $this->queueService->autoAssign($booking, 10, $this->admin);

        $this->assertEquals(1, $assignment->guide->queue_number);
        $this->assertEquals('Budi', $assignment->guide->nama);
        $this->assertEquals(1, GuideQueueState::first()->current_position);
    }

    /**
     * Test 4: Last #3. #4 OFF, #5 OFF, #1 OFF, #2 available. Expected: #2.
     */
    public function test_4_last_3_four_five_one_off_two_available_expects_two(): void
    {
        $this->setQueuePosition(3);
        $this->setGuideOff(4);
        $this->setGuideOff(5);
        $this->setGuideOff(1);
        $this->setGuideCheckedIn(2); // #2 available

        $booking = $this->createBooking();
        $assignment = $this->queueService->autoAssign($booking, 10, $this->admin);

        $this->assertEquals(2, $assignment->guide->queue_number);
        $this->assertEquals('Made', $assignment->guide->nama);
        $this->assertEquals(2, GuideQueueState::first()->current_position);
    }

    /**
     * Test 5: Semua guide OFF. Expected: No assignment. Position tidak berubah.
     */
    public function test_5_all_guides_off_expects_no_assignment_position_unchanged(): void
    {
        $this->setQueuePosition(3);
        for ($i = 1; $i <= 5; $i++) {
            $this->setGuideOff($i);
        }

        $booking = $this->createBooking();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Belum ada guide yang tersedia.");

        try {
            $this->queueService->autoAssign($booking, 10, $this->admin);
        } finally {
            // Position must remain #3
            $this->assertEquals(3, GuideQueueState::first()->current_position);
            $this->assertEquals(0, $booking->assignments()->count());
        }
    }

    /**
     * Test 6: Last #3. Butuh 3 guide. #4, #5, #1 available. Expected: #4 -> #5 -> #1. Last: #1.
     */
    public function test_6_last_3_needs_3_guides_expects_four_five_one_and_last_one(): void
    {
        $this->setQueuePosition(3);
        for ($i = 1; $i <= 5; $i++) {
            $this->setGuideCheckedIn($i);
        }

        $booking = $this->createBooking(50); // 50 guests
        $allocations = [20, 15, 15]; // 3 guides

        $assignments = $this->queueService->autoAssignMultiple($booking, $allocations, $this->admin);

        $this->assertCount(3, $assignments);
        $this->assertEquals(4, $assignments[0]->guide->queue_number); // #4 Wayan
        $this->assertEquals(20, $assignments[0]->guest_count);

        $this->assertEquals(5, $assignments[1]->guide->queue_number); // #5 Agus
        $this->assertEquals(15, $assignments[1]->guest_count);

        $this->assertEquals(1, $assignments[2]->guide->queue_number); // #1 Budi
        $this->assertEquals(15, $assignments[2]->guest_count);

        $this->assertEquals(1, GuideQueueState::first()->current_position);
        $this->assertEquals('FULLY_ASSIGNED', $booking->fresh()->status);
    }

    /**
     * Test 7: Dua admin melakukan AUTO ASSIGN bersamaan (Race condition lock).
     */
    public function test_7_concurrent_auto_assign_race_condition_protection(): void
    {
        $this->setQueuePosition(3);
        for ($i = 1; $i <= 5; $i++) {
            $this->setGuideCheckedIn($i);
        }

        $booking1 = $this->createBooking(10);
        $booking2 = $this->createBooking(10);

        // First assignment takes #4
        $assign1 = $this->queueService->autoAssign($booking1, 10, $this->admin);
        // Next assignment immediately takes #5 without duplicate
        $assign2 = $this->queueService->autoAssign($booking2, 10, $this->admin);

        $this->assertNotEquals($assign1->guide_id, $assign2->guide_id);
        $this->assertEquals(4, $assign1->guide->queue_number);
        $this->assertEquals(5, $assign2->guide->queue_number);
        $this->assertEquals(5, GuideQueueState::first()->current_position);
    }

    /**
     * Test 8: Hari berubah. Last position hari sebelumnya: #4.
     * Hari berikutnya search: #5 -> #1 -> #2 -> #3 -> #4. Bukan reset ke #1.
     */
    public function test_8_day_changes_last_4_next_day_searches_five_one_two_three_four_not_reset_to_one(): void
    {
        // Yesterday ended at #4
        $this->setQueuePosition(4);

        $tomorrow = Carbon::tomorrow()->toDateString();
        // Check in all guides for tomorrow
        for ($i = 1; $i <= 5; $i++) {
            $this->setGuideCheckedIn($i, $tomorrow);
        }

        $nextResult = $this->queueService->findNextAvailableGuide(null, $tomorrow);

        // Next candidate must be #5 Agus, NOT reset to #1 Budi
        $this->assertNotNull($nextResult['guide']);
        $this->assertEquals(5, $nextResult['guide']->queue_number);
        $this->assertEquals('Agus', $nextResult['guide']->nama);
    }

    /**
     * Test 9: Guide check-in. Expected: Guide dapat masuk queue.
     */
    public function test_9_guide_check_in_enters_queue(): void
    {
        $this->setQueuePosition(0);
        $guide = $this->guides[1]; // Budi

        // Initially not checked in
        $resultBefore = $this->queueService->findNextAvailableGuide(null, $this->today);
        $this->assertNull($resultBefore['guide']);

        // Check in
        $this->attendanceService->checkIn($guide, $this->today);

        // Now guide is in queue and eligible
        $resultAfter = $this->queueService->findNextAvailableGuide(null, $this->today);
        $this->assertNotNull($resultAfter['guide']);
        $this->assertEquals(1, $resultAfter['guide']->queue_number);
    }

    /**
     * Test 10: Guide checkout. Expected: Guide tidak dapat menerima assignment baru.
     */
    public function test_10_guide_checkout_cannot_receive_new_assignment(): void
    {
        $this->setQueuePosition(0);
        $guide = $this->guides[1]; // Budi
        $this->attendanceService->checkIn($guide, $this->today);

        // Guide checks out
        $this->attendanceService->checkOut($guide, $this->today);

        $result = $this->queueService->findNextAvailableGuide(null, $this->today);
        $this->assertNull($result['guide']);
    }

    /**
     * Test 11: Guide masih memiliki tour aktif. Expected: Checkout ditolak.
     */
    public function test_11_guide_with_active_tour_checkout_rejected(): void
    {
        $guide = $this->guides[1];
        $this->attendanceService->checkIn($guide, $this->today);

        $booking = $this->createBooking(5);
        $assignment = GuideAssignment::create([
            'booking_id' => $booking->id,
            'guide_id' => $guide->id,
            'guest_count' => 5,
            'fee_type' => 'PER_GUEST',
            'fee_rate' => 50000,
            'fee_amount' => 250000,
            'assigned_at' => now(),
            'status' => 'ON_GOING',
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Anda masih memiliki tour aktif. Selesaikan tour terlebih dahulu sebelum checkout.");

        $this->attendanceService->checkOut($guide, $this->today);
    }

    /**
     * Test 12: Booking 50 tamu. Assignments: 20 + 15 + 15. Expected: Fully Assigned.
     */
    public function test_12_booking_50_guests_assignments_20_15_15_fully_assigned(): void
    {
        $booking = $this->createBooking(50);
        for ($i = 1; $i <= 5; $i++) {
            $this->setGuideCheckedIn($i);
        }

        $this->queueService->autoAssignMultiple($booking, [20, 15, 15], $this->admin);

        $booking->refresh();
        $this->assertEquals(50, $booking->assignedGuestsCount());
        $this->assertEquals(0, $booking->remainingGuestsCount());
        $this->assertEquals('FULLY_ASSIGNED', $booking->status);
    }

    /**
     * Test 13: Booking 30 tamu. Assignments: 20 + 15. Expected: Rejected karena 35 > 30.
     */
    public function test_13_booking_30_guests_assignments_20_15_rejected_exceeds_guest_count(): void
    {
        $booking = $this->createBooking(30);
        for ($i = 1; $i <= 5; $i++) {
            $this->setGuideCheckedIn($i);
        }

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Jumlah tamu yang dialokasikan melebihi jumlah tamu booking.");

        $this->queueService->autoAssignMultiple($booking, [20, 15], $this->admin);
    }
}
