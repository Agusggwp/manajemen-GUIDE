<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guide;
use App\Models\GuideAssignment;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
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
            'email' => 'admin@artdevata.com',
            'role' => 'ADMIN',
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'email' => 'staff@artdevata.com',
            'role' => 'STAFF',
            'is_active' => true,
        ]);

        $this->guideUser1 = User::factory()->create([
            'email' => 'budi@artdevata.com',
            'role' => 'GUIDE',
            'is_active' => true,
        ]);

        $this->guide1 = Guide::factory()->create([
            'user_id' => $this->guideUser1->id,
            'nama' => 'Budi Antara',
            'queue_number' => 1,
            'status' => 'AVAILABLE',
        ]);

        $this->guideUser2 = User::factory()->create([
            'email' => 'made@artdevata.com',
            'role' => 'GUIDE',
            'is_active' => true,
        ]);

        $this->guide2 = Guide::factory()->create([
            'user_id' => $this->guideUser2->id,
            'nama' => 'Made Sujana',
            'queue_number' => 2,
            'status' => 'AVAILABLE',
        ]);
    }

    public function test_admin_can_access_all_modules(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('guides.index'))->assertOk();
        $this->get(route('bookings.index'))->assertOk();
        $this->get(route('packages.index'))->assertOk();
        $this->get(route('payroll.index'))->assertOk();
        $this->get(route('settings.index'))->assertOk();
        $this->get(route('audit.index'))->assertOk();
    }

    public function test_staff_cannot_access_settings_or_audit_or_delete_guides(): void
    {
        $this->actingAs($this->staff);

        // Staff can access bookings and queue
        $this->get(route('bookings.index'))->assertOk();
        $this->get(route('queue.board'))->assertOk();

        // Staff is forbidden from settings and audit logs
        $this->get(route('settings.index'))->assertForbidden();
        $this->get(route('audit.index'))->assertForbidden();

        // Staff cannot delete guides
        $this->delete(route('guides.destroy', $this->guide1->id))->assertForbidden();
    }

    public function test_guide_cannot_access_admin_dashboard_or_other_guides(): void
    {
        $this->actingAs($this->guideUser1);

        // Cannot view admin pages
        $this->get(route('dashboard'))->assertForbidden();
        $this->get(route('payroll.index'))->assertForbidden();
        $this->get(route('guides.index'))->assertForbidden();
        $this->get(route('settings.index'))->assertForbidden();

        // Guide can access own guide portal
        $this->get(route('guide.dashboard'))->assertOk();
        $this->get(route('guide.jobs'))->assertOk();
        $this->get(route('guide.earnings'))->assertOk();
        $this->get(route('guide.profile'))->assertOk();
    }

    public function test_guide_cannot_view_financials_of_another_guide(): void
    {
        $this->actingAs($this->guideUser1);

        // Guide 1 attempting to view Guide 2's payroll breakdown or admin payroll
        $this->get(route('payroll.show', $this->guide2->id))->assertForbidden();
    }

    public function test_admin_can_delete_tour_package_and_prices(): void
    {
        $this->actingAs($this->admin);

        $pkg = \App\Models\TourPackage::create([
            'name' => 'Paket Hapus Test',
            'duration' => '5 Jam',
            'default_price' => 120000,
            'status' => 'ACTIVE',
        ]);
        \App\Models\TourPackagePrice::create([
            'tour_package_id' => $pkg->id,
            'min_guests' => 1,
            'max_guests' => 5,
            'price_per_guest' => 120000,
        ]);

        $response = $this->delete(route('packages.destroy', $pkg));
        $response->assertRedirect(route('packages.index'));
        $this->assertDatabaseMissing('tour_packages', ['id' => $pkg->id]);
        $this->assertDatabaseMissing('tour_package_prices', ['tour_package_id' => $pkg->id]);
    }

    public function test_staff_cannot_delete_tour_package(): void
    {
        $this->actingAs($this->staff);

        $pkg = \App\Models\TourPackage::create([
            'name' => 'Paket Tidak Boleh Dihapus Staff',
            'duration' => '5 Jam',
            'default_price' => 120000,
            'status' => 'ACTIVE',
        ]);

        $this->delete(route('packages.destroy', $pkg))->assertForbidden();
        $this->assertDatabaseHas('tour_packages', ['id' => $pkg->id]);
    }
}
