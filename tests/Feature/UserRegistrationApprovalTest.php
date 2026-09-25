<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\AccountApprovedNotification;
use App\Notifications\AccountRejectedNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class UserRegistrationApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@artdevata.com',
            'role' => 'ADMIN',
            'is_active' => true,
            'approval_status' => 'APPROVED',
            'email_verified_at' => now(),
        ]);

        $this->staff = User::factory()->create([
            'email' => 'staff@artdevata.com',
            'role' => 'STAFF',
            'is_active' => true,
            'approval_status' => 'APPROVED',
            'email_verified_at' => now(),
        ]);
    }

    public function test_registration_page_is_accessible(): void
    {
        $response = $this->get(route('register'));
        $response->assertOk();
        $response->assertSee('Daftar Akun Baru');
        $response->assertSee('Staff Operasional');
        $response->assertSee('Tour Guide');
    }

    public function test_user_can_register_as_staff_and_receives_verification_email(): void
    {
        Notification::fake();

        $payload = [
            'name' => 'Wayan Staff Baru',
            'email' => 'wayan.staff@example.com',
            'role' => 'STAFF',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register'), $payload);

        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', [
            'email' => 'wayan.staff@example.com',
            'role' => 'STAFF',
            'approval_status' => 'PENDING',
            'is_active' => false,
            'email_verified_at' => null,
        ]);

        $user = User::where('email', 'wayan.staff@example.com')->first();
        $this->assertNotNull($user);

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_user_can_register_as_guide_and_receives_verification_email(): void
    {
        Notification::fake();

        $payload = [
            'name' => 'Ketut Guide Baru',
            'email' => 'ketut.guide@example.com',
            'phone' => '081234567890',
            'role' => 'GUIDE',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register'), $payload);

        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', [
            'email' => 'ketut.guide@example.com',
            'role' => 'GUIDE',
            'phone' => '081234567890',
            'approval_status' => 'PENDING',
            'is_active' => false,
        ]);
    }

    public function test_cannot_register_as_admin_publicly(): void
    {
        $payload = [
            'name' => 'Hacker Admin',
            'email' => 'hacker@example.com',
            'role' => 'ADMIN',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register'), $payload);
        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'hacker@example.com']);
    }

    public function test_unverified_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'pending.unverified@example.com',
            'role' => 'STAFF',
            'password' => bcrypt('password123'),
            'email_verified_at' => null,
            'approval_status' => 'PENDING',
            'is_active' => false,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'pending.unverified@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_user_can_verify_email_via_signed_link(): void
    {
        $user = User::factory()->create([
            'email' => 'verify.me@example.com',
            'role' => 'STAFF',
            'email_verified_at' => null,
            'approval_status' => 'PENDING',
            'is_active' => false,
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->get($signedUrl);
        $response->assertOk();
        $response->assertSee('Email Berhasil Diverifikasi!');
        $response->assertSee('Menunggu Persetujuan (ACC) Admin');

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertEquals('PENDING', $user->approval_status);
        $this->assertFalse($user->is_active);
    }

    public function test_verified_but_unapproved_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'verified.unapproved@example.com',
            'role' => 'STAFF',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'approval_status' => 'PENDING',
            'is_active' => false,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'verified.unapproved@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('persetujuan', session('error'));
        $this->assertGuest();
    }

    public function test_admin_can_view_approvals_list(): void
    {
        $pendingUser = User::factory()->create([
            'name' => 'Calon Staff 1',
            'email' => 'calon1@example.com',
            'role' => 'STAFF',
            'approval_status' => 'PENDING',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.user-approvals.index'));
        $response->assertOk();
        $response->assertSee('Calon Staff 1');
        $response->assertSee('calon1@example.com');
        $response->assertSee('Menunggu ACC');
    }

    public function test_non_admin_cannot_access_approvals_list(): void
    {
        $this->actingAs($this->staff)
            ->get(route('admin.user-approvals.index'))
            ->assertForbidden();
    }

    public function test_admin_can_approve_pending_user(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'name' => 'Gede Siap Kerja',
            'email' => 'gede@example.com',
            'role' => 'STAFF',
            'email_verified_at' => now(),
            'approval_status' => 'PENDING',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.user-approvals.approve', $user));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('APPROVED', $user->approval_status);
        $this->assertTrue($user->is_active);
        $this->assertEquals($this->admin->id, $user->approved_by);
        $this->assertNotNull($user->approved_at);

        Notification::assertSentTo($user, AccountApprovedNotification::class);
    }

    public function test_admin_approving_guide_creates_guide_record(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'name' => 'Wayan Pemandu Wisata',
            'email' => 'wayan.guide@example.com',
            'phone' => '08987654321',
            'role' => 'GUIDE',
            'email_verified_at' => now(),
            'approval_status' => 'PENDING',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.user-approvals.approve', $user));

        $response->assertRedirect();
        $this->assertDatabaseHas('guides', [
            'user_id' => $user->id,
            'nama' => 'Wayan Pemandu Wisata',
            'email' => 'wayan.guide@example.com',
            'status' => 'AVAILABLE',
        ]);
    }

    public function test_approved_user_can_login_successfully(): void
    {
        $user = User::factory()->create([
            'email' => 'approved.user@example.com',
            'password' => bcrypt('password123'),
            'role' => 'STAFF',
            'email_verified_at' => now(),
            'approval_status' => 'APPROVED',
            'is_active' => true,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'approved.user@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_reject_user_and_rejected_user_cannot_login(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'name' => 'User Ditolak',
            'email' => 'ditolak@example.com',
            'role' => 'STAFF',
            'email_verified_at' => now(),
            'approval_status' => 'PENDING',
            'is_active' => false,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.user-approvals.reject', $user), [
            'reason' => 'Identitas tidak sesuai dengan data karyawan.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('REJECTED', $user->approval_status);
        $this->assertFalse($user->is_active);
        $this->assertEquals('Identitas tidak sesuai dengan data karyawan.', $user->rejection_reason);

        Notification::assertSentTo($user, AccountRejectedNotification::class);

        // Attempt login as rejected user
        $loginResponse = $this->post(route('login'), [
            'email' => 'ditolak@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertSessionHas('error');
        $this->assertStringContainsString('ditolak', session('error'));
        $this->assertGuest();
    }

    public function test_user_can_request_resend_verification_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'resend.me@example.com',
            'role' => 'STAFF',
            'email_verified_at' => null,
            'approval_status' => 'PENDING',
            'is_active' => false,
        ]);

        $response = $this->post(route('verification.send'), [
            'email' => 'resend.me@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }
}
