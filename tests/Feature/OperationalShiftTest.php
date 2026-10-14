<?php

namespace Tests\Feature;

use App\Models\Guide;
use App\Models\GuideAttendance;
use App\Models\Setting;
use App\Models\User;
use App\Services\GuideAttendanceService;
use App\Services\OperationalShiftService;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalShiftTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $guideUser;
    private Guide $guide;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'ADMIN', 'is_active' => true]);
        $this->staff = User::factory()->create(['role' => 'STAFF', 'is_active' => true]);
        $this->guideUser = User::factory()->create(['role' => 'GUIDE', 'is_active' => true]);
        $this->guide = Guide::factory()->create([
            'user_id' => $this->guideUser->id,
            'status' => 'AVAILABLE',
            'queue_number' => 1,
        ]);

        Setting::set('gps_required', '0');
        Setting::set('operational_auto_schedule_enabled', '0');
    }

    public function test_guide_cannot_check_in_when_counter_is_closed()
    {
        // Counter is closed by default
        $shiftService = app(OperationalShiftService::class);
        $shiftService->closeShift($this->admin, 'Tutup di awal');

        $this->assertFalse($shiftService->isOpen());

        $attendanceService = app(GuideAttendanceService::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Loket operasional saat ini sedang TUTUP');

        $attendanceService->checkIn($this->guide);
    }

    public function test_staff_can_manually_open_operational_shift()
    {
        $response = $this->actingAs($this->staff)->post(route('operational.open'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $shiftService = app(OperationalShiftService::class);
        $this->assertTrue($shiftService->isOpen());
    }

    public function test_guide_can_check_in_after_shift_is_opened()
    {
        $shiftService = app(OperationalShiftService::class);
        $shiftService->openShift($this->staff);

        $this->actingAs($this->guideUser);
        $response = $this->post(route('guide.check-in'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('guide_attendances', [
            'guide_id' => $this->guide->id,
            'attendance_date' => Carbon::today()->toDateString(),
            'status' => 'CHECKED_IN',
        ]);
    }

    public function test_closing_shift_automatically_checks_out_active_guides()
    {
        $shiftService = app(OperationalShiftService::class);
        $shiftService->openShift($this->admin);

        // Guide checks in
        $attendanceService = app(GuideAttendanceService::class);
        $attendance = $attendanceService->checkIn($this->guide);

        $this->assertEquals('CHECKED_IN', $attendance->status);
        $this->assertNull($attendance->check_out_at);

        // Staff closes the shift
        $response = $this->actingAs($this->staff)->post(route('operational.close'), [
            'reason' => 'Tutup loket sore hari',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify guide was automatically checked out
        $attendance->refresh();
        $this->assertEquals('CHECKED_OUT', $attendance->status);
        $this->assertNotNull($attendance->check_out_at);
        $this->assertStringContainsString('Otomatis check-out', $attendance->notes);
        $this->assertFalse($shiftService->isOpen());
    }

    public function test_admin_can_update_auto_schedule_settings()
    {
        $response = $this->actingAs($this->admin)->post(route('settings.update'), [
            'company_name' => 'ARTDEVATA',
            'default_guide_fee' => 50000,
            'operational_auto_schedule_enabled' => '1',
            'operational_auto_open_time' => '08:00',
            'operational_auto_close_time' => '17:30',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('1', Setting::get('operational_auto_schedule_enabled'));
        $this->assertEquals('08:00', Setting::get('operational_auto_open_time'));
        $this->assertEquals('17:30', Setting::get('operational_auto_close_time'));
    }

    public function test_auto_schedule_opens_during_hours_and_closes_outside_hours()
    {
        $today = Carbon::today()->toDateString();
        // Clear any manual status
        Setting::set('operational_manual_status_' . $today, '');
        Setting::set('operational_auto_schedule_enabled', '1');
        Setting::set('operational_auto_open_time', '08:00');
        Setting::set('operational_auto_close_time', '17:00');

        $shiftService = app(OperationalShiftService::class);

        // Test before open time (07:00)
        Carbon::setTestNow(Carbon::today()->setTime(7, 0));
        $this->assertFalse($shiftService->isOpen());

        // Test during open hours (10:00)
        Carbon::setTestNow(Carbon::today()->setTime(10, 0));
        $this->assertTrue($shiftService->isOpen());

        // Test after close time (18:00)
        Carbon::setTestNow(Carbon::today()->setTime(18, 0));
        $this->assertFalse($shiftService->isOpen());

        Carbon::setTestNow(); // reset
    }

    public function test_closing_shift_checks_out_guides_even_across_midnight_date()
    {
        $shiftService = app(OperationalShiftService::class);
        $shiftService->openShift($this->admin);

        // Guide checks in on yesterday's date (e.g. before midnight)
        $yesterday = Carbon::yesterday()->toDateString();
        $attendance = GuideAttendance::create([
            'guide_id' => $this->guide->id,
            'attendance_date' => $yesterday,
            'status' => 'CHECKED_IN',
            'check_in_at' => Carbon::yesterday()->setTime(14, 0),
        ]);

        $this->assertEquals('CHECKED_IN', $attendance->status);
        $this->assertEquals(1, $shiftService->getStatus()['active_guides_count']);

        // Staff closes shift today (past midnight)
        $response = $this->actingAs($this->staff)->post(route('operational.close'), [
            'reason' => 'Force tutup loket malam hari',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $attendance->refresh();
        $this->assertEquals('CHECKED_OUT', $attendance->status);
        $this->assertNotNull($attendance->check_out_at);
        $this->assertEquals(0, $shiftService->getStatus()['active_guides_count']);
        $this->assertFalse($shiftService->isOpen());
    }
}
