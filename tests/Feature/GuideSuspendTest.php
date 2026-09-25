<?php

namespace Tests\Feature;

use App\Models\Guide;
use App\Models\GuideAttendance;
use App\Models\User;
use App\Services\GuideAttendanceService;
use App\Services\GuideQueueService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuideSuspendTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected Guide $guide;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
            'is_active' => true,
        ]);
        $this->staff = User::factory()->create([
            'role' => 'STAFF',
            'is_active' => true,
        ]);

        $guideUser = User::factory()->create([
            'role' => 'GUIDE',
            'is_active' => true,
        ]);

        $this->guide = Guide::create([
            'user_id' => $guideUser->id,
            'kode_guide' => 'GD-001',
            'nama' => 'Wayan Test Guide',
            'nomor_telepon' => '081234567890',
            'bahasa' => 'INDONESIA, ENGLISH',
            'queue_number' => 1,
            'status' => 'AVAILABLE',
        ]);
    }

    public function test_admin_can_suspend_a_guide_with_reason(): void
    {
        $response = $this->actingAs($this->admin)->post(route('guides.suspend', $this->guide), [
            'suspend_reason' => 'Melanggar SOP pelayanan tamu',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->guide->refresh();
        $this->assertEquals('SUSPENDED', $this->guide->status);
        $this->assertEquals('Melanggar SOP pelayanan tamu', $this->guide->suspend_reason);
        $this->assertNotNull($this->guide->suspended_at);
        $this->assertEquals($this->admin->id, $this->guide->suspended_by);
        $this->assertTrue($this->guide->isSuspended());
    }

    public function test_suspend_requires_reason(): void
    {
        $response = $this->actingAs($this->admin)->post(route('guides.suspend', $this->guide), [
            'suspend_reason' => '',
        ]);

        $response->assertSessionHasErrors('reason');
        $this->guide->refresh();
        $this->assertEquals('AVAILABLE', $this->guide->status);
    }

    public function test_staff_cannot_suspend_guide(): void
    {
        $response = $this->actingAs($this->staff)->post(route('guides.suspend', $this->guide), [
            'suspend_reason' => 'Unauthorized attempt',
        ]);

        $response->assertForbidden();
        $this->guide->refresh();
        $this->assertEquals('AVAILABLE', $this->guide->status);
    }

    public function test_admin_can_unsuspend_a_guide(): void
    {
        $this->guide->suspend('Pelanggaran sementara', $this->admin->id);
        $this->assertTrue($this->guide->isSuspended());

        $response = $this->actingAs($this->admin)->post(route('guides.unsuspend', $this->guide));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->guide->refresh();
        $this->assertEquals('AVAILABLE', $this->guide->status);
        $this->assertNull($this->guide->suspend_reason);
        $this->assertNull($this->guide->suspended_at);
        $this->assertNull($this->guide->suspended_by);
        $this->assertFalse($this->guide->isSuspended());
    }

    public function test_suspended_guide_cannot_check_in(): void
    {
        $this->guide->suspend('Sedang masa skorsing', $this->admin->id);

        $attendanceService = app(GuideAttendanceService::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('sedang ditangguhkan (SUSPENDED)');

        $attendanceService->checkIn($this->guide, Carbon::today()->toDateString());
    }

    public function test_suspended_guide_is_skipped_during_queue_auto_assignment(): void
    {
        $today = Carbon::today()->toDateString();
        $attendanceService = app(GuideAttendanceService::class);
        $queueService = app(GuideQueueService::class);

        // Guide 1 (queue 1) is suspended
        $this->guide->suspend('Skorsing', $this->admin->id);

        // Guide 2 (queue 2) is active and checked in
        $guide2User = User::factory()->create([
            'role' => 'GUIDE',
            'is_active' => true,
        ]);
        $guide2 = Guide::create([
            'user_id' => $guide2User->id,
            'kode_guide' => 'GD-002',
            'nama' => 'Made Second Guide',
            'nomor_telepon' => '081234567891',
            'bahasa' => 'INDONESIA',
            'queue_number' => 2,
            'status' => 'AVAILABLE',
        ]);
        $attendanceService->checkIn($guide2, $today);

        // Auto assign search sequence should skip Guide 1 and select Guide 2
        $searchResult = $queueService->findNextAvailableGuide(null, $today);

        $this->assertNotNull($searchResult['guide']);
        $this->assertEquals($guide2->id, $searchResult['guide']->id);
        $this->assertEquals(2, $searchResult['guide']->queue_number);
    }
}
