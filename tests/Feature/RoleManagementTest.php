<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed system default roles
        Role::firstOrCreate(
            ['name' => 'ADMIN'],
            ['label' => 'Administrator', 'description' => 'Akses penuh ke semua modul sistem', 'permissions' => ['*'], 'is_system' => true]
        );
        Role::firstOrCreate(
            ['name' => 'STAFF'],
            ['label' => 'Staff Operasional', 'description' => 'Operasional pemesanan dan antrean harian', 'permissions' => ['dashboard.view', 'bookings.view', 'bookings.create'], 'is_system' => true]
        );
        Role::firstOrCreate(
            ['name' => 'GUIDE'],
            ['label' => 'Pemandu Wisata', 'description' => 'Akses portal pemandu mandiri', 'permissions' => [], 'is_system' => true]
        );
    }

    public function test_admin_can_view_role_settings_tab(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'approval_status' => 'APPROVED',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('settings.index', ['tab' => 'roles']));

        $response->assertStatus(200);
        $response->assertSee('Hak Akses Pengguna');
        $response->assertSee('Role Terdaftar');
        $response->assertSee('ADMIN');
        $response->assertSee('STAFF');
        $response->assertSee('GUIDE');
        $response->assertSee('+ Tambah Role Baru');
    }

    public function test_non_admin_cannot_access_role_settings(): void
    {
        $staff = User::factory()->create([
            'role' => 'STAFF',
            'approval_status' => 'APPROVED',
            'is_active' => true,
        ]);

        // Non-admin attempting to access settings
        $response = $this->actingAs($staff)->get(route('settings.index', ['tab' => 'roles']));
        $response->assertStatus(403);

        // Non-admin attempting to create a role
        $postResponse = $this->actingAs($staff)->post(route('roles.store'), [
            'name' => 'HACKER',
            'label' => 'Hacker Role',
        ]);
        $postResponse->assertStatus(403);
    }

    public function test_admin_can_create_a_new_role_with_granular_permissions(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'approval_status' => 'APPROVED',
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'KASIR',
            'label' => 'Staff Kasir Tiket',
            'description' => 'Bertanggung jawab atas loket dan tiket masuk.',
            'permissions' => [
                'dashboard.view',
                'bookings.view',
                'bookings.create',
                'shift.manage',
            ],
        ];

        $response = $this->actingAs($admin)->post(route('roles.store'), $payload);

        $response->assertRedirect(route('settings.index', ['tab' => 'roles']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('roles', [
            'name' => 'KASIR',
            'label' => 'Staff Kasir Tiket',
            'is_system' => false,
        ]);

        $kasirRole = Role::where('name', 'KASIR')->first();
        $this->assertNotNull($kasirRole);
        $this->assertEquals(['dashboard.view', 'bookings.view', 'bookings.create', 'shift.manage'], $kasirRole->permissions);
        $this->assertTrue($kasirRole->hasPermission('bookings.create'));
        $this->assertFalse($kasirRole->hasPermission('roles.manage'));

        // Check Audit Log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'CREATE_ROLE',
            'model' => Role::class,
            'model_id' => $kasirRole->id,
        ]);
    }

    public function test_role_creation_validates_unique_code_and_alphanumeric_format(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'approval_status' => 'APPROVED',
            'is_active' => true,
        ]);

        // Attempt duplicate role code
        $duplicateResponse = $this->actingAs($admin)->post(route('roles.store'), [
            'name' => 'ADMIN',
            'label' => 'Another Admin',
        ]);
        $duplicateResponse->assertSessionHasErrors('name');

        // Attempt invalid characters
        $invalidResponse = $this->actingAs($admin)->post(route('roles.store'), [
            'name' => 'ROLE WITH SPACES!',
            'label' => 'Invalid Role',
        ]);
        $invalidResponse->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_role_permissions_and_label(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'approval_status' => 'APPROVED',
            'is_active' => true,
        ]);

        $role = Role::create([
            'name' => 'FINANCE',
            'label' => 'Staff Akuntansi',
            'description' => 'Pengelola laporan keuangan',
            'permissions' => ['reports.revenue'],
            'is_system' => false,
        ]);

        $updatePayload = [
            'label' => 'Manager Keuangan & Payroll',
            'description' => 'Memegang kendali penuh atas penggajian dan pengeluaran',
            'permissions' => [
                'reports.revenue',
                'payroll.view',
                'payroll.pay',
                'staff_payroll.view',
                'staff_payroll.manage',
            ],
        ];

        $response = $this->actingAs($admin)->put(route('roles.update', $role), $updatePayload);

        $response->assertRedirect(route('settings.index', ['tab' => 'roles']));
        $response->assertSessionHas('success');

        $role->refresh();
        $this->assertEquals('Manager Keuangan & Payroll', $role->label);
        $this->assertTrue($role->hasPermission('staff_payroll.manage'));
        $this->assertFalse($role->hasPermission('roles.manage'));

        // Check Audit Log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'UPDATE_ROLE_PERMISSIONS',
            'model' => Role::class,
            'model_id' => $role->id,
        ]);
    }

    public function test_system_roles_cannot_be_deleted(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'approval_status' => 'APPROVED',
            'is_active' => true,
        ]);

        $adminRole = Role::where('name', 'ADMIN')->first();
        $guideRole = Role::where('name', 'GUIDE')->first();

        // Attempt to delete ADMIN role
        $response = $this->actingAs($admin)->delete(route('roles.destroy', $adminRole));
        $response->assertRedirect(route('settings.index', ['tab' => 'roles']));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['name' => 'ADMIN']);

        // Attempt to delete GUIDE role
        $guideResponse = $this->actingAs($admin)->delete(route('roles.destroy', $guideRole));
        $guideResponse->assertRedirect(route('settings.index', ['tab' => 'roles']));
        $guideResponse->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['name' => 'GUIDE']);
    }

    public function test_custom_role_assigned_to_active_users_cannot_be_deleted(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'approval_status' => 'APPROVED',
            'is_active' => true,
        ]);

        $customRole = Role::create([
            'name' => 'OPERATOR',
            'label' => 'Operator Lapangan',
            'description' => 'Tim pendukung operasional',
            'permissions' => ['queue.view'],
            'is_system' => false,
        ]);

        // Assign to a user
        User::factory()->create([
            'role' => 'OPERATOR',
            'approval_status' => 'APPROVED',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('roles.destroy', $customRole));
        $response->assertRedirect(route('settings.index', ['tab' => 'roles']));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['name' => 'OPERATOR']);
    }

    public function test_unassigned_custom_role_can_be_deleted_cleanly(): void
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'approval_status' => 'APPROVED',
            'is_active' => true,
        ]);

        $role = Role::create([
            'name' => 'TEMPORARY',
            'label' => 'Role Sementara',
            'description' => 'Akan segera dihapus',
            'permissions' => ['dashboard.view'],
            'is_system' => false,
        ]);

        $roleId = $role->id;

        $response = $this->actingAs($admin)->delete(route('roles.destroy', $role));
        $response->assertRedirect(route('settings.index', ['tab' => 'roles']));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', ['id' => $roleId]);

        // Check Audit Log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'DELETE_ROLE',
            'model' => Role::class,
            'model_id' => $roleId,
        ]);
    }

    public function test_user_has_permission_helper_integration(): void
    {
        Role::create([
            'name' => 'SUPERVISOR',
            'label' => 'Supervisor Lapangan',
            'description' => 'Pengawas pemandu',
            'permissions' => ['queue.view', 'queue.reorder', 'guides.view'],
            'is_system' => false,
        ]);

        $supervisor = User::factory()->create([
            'role' => 'SUPERVISOR',
            'approval_status' => 'APPROVED',
            'is_active' => true,
        ]);

        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'approval_status' => 'APPROVED',
            'is_active' => true,
        ]);

        // Admin has all permissions automatically
        $this->assertTrue($admin->hasPermission('queue.view'));
        $this->assertTrue($admin->hasPermission('random.nonexistent.permission'));

        // Supervisor has specific permissions
        $this->assertTrue($supervisor->hasPermission('queue.view'));
        $this->assertTrue($supervisor->hasPermission('queue.reorder'));
        $this->assertFalse($supervisor->hasPermission('roles.manage'));
        $this->assertFalse($supervisor->hasPermission('packages.manage'));
    }
}
