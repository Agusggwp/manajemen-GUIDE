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
use App\Services\OperationalShiftService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DiscordWebhookTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private Guide $guide;
    private TourPackage $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'ADMIN', 'is_active' => true]);
        $this->staff = User::factory()->create(['role' => 'STAFF', 'is_active' => true]);

        $guideUser = User::factory()->create(['role' => 'GUIDE', 'is_active' => true]);
        $this->guide = Guide::factory()->create([
            'user_id' => $guideUser->id,
            'nama' => 'Wayan Sudarta',
            'status' => 'AVAILABLE',
            'queue_number' => 1,
        ]);

        $this->package = TourPackage::create([
            'name' => 'Ayung Rafting Adventure',
            'duration' => '8 Jam',
            'default_price' => 200000,
            'status' => 'ACTIVE',
        ]);

        Setting::set('company_name', 'ARTDEVATA');
        Setting::set('operational_auto_schedule_enabled', '0');
    }

    public function test_webhook_url_is_sourced_from_config_or_env()
    {
        config(['services.discord.webhook_url' => 'https://discord.com/api/webhooks/12345/env-url']);

        $discordService = app(\App\Services\DiscordWebhookService::class);
        $this->assertTrue($discordService->isEnabled());
        $this->assertEquals('https://discord.com/api/webhooks/12345/env-url', $discordService->getWebhookUrl());
    }

    public function test_admin_can_send_test_discord_webhook_using_env()
    {
        config(['services.discord.webhook_url' => 'https://discord.com/api/webhooks/12345/env-url']);

        Http::fake([
            'https://discord.com/api/webhooks/12345/*' => Http::response(null, 204),
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('settings.test-discord'), []);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '12345/env-url') &&
                   str_contains($request['embeds'][0]['title'], 'UJI COBA WEBHOOK DISCORD');
        });
    }

    public function test_shift_open_sends_discord_notification_when_enabled()
    {
        config(['services.discord.webhook_url' => 'https://discord.com/api/webhooks/12345/test']);

        Http::fake([
            'https://discord.com/api/webhooks/12345/test' => Http::response(null, 204),
        ]);

        $shiftService = app(OperationalShiftService::class);
        $shiftService->openShift($this->staff);

        Http::assertSent(function ($request) {
            $embed = $request['embeds'][0];
            return str_contains($embed['title'], 'LOKET OPERASIONAL RESMI DIBUKA') &&
                   $embed['color'] === 0x2ECC71;
        });
    }

    public function test_shift_close_sends_detailed_operational_and_financial_summary_to_discord()
    {
        config(['services.discord.webhook_url' => 'https://discord.com/api/webhooks/12345/test']);

        Http::fake([
            'https://discord.com/api/webhooks/12345/test' => Http::response(null, 204),
        ]);

        $today = Carbon::today()->toDateString();

        // Guide checks in
        GuideAttendance::create([
            'guide_id' => $this->guide->id,
            'attendance_date' => $today,
            'check_in_at' => Carbon::now()->subHours(4),
            'status' => 'CHECKED_IN',
        ]);

        // Create booking with pax, cash, and QRIS payments
        $booking = Booking::create([
            'booking_code' => 'BK-TEST-001',
            'booking_date' => $today,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'customer_name' => 'John Doe',
            'customer_phone' => '08123456789',
            'guest_count' => 4,
            'tour_package_id' => $this->package->id,
            'price_per_guest' => 200000,
            'total_price' => 800000,
            'amount_paid' => 800000,
            'payment_method' => 'CASH',
            'payment_status' => 'PAID',
            'is_walkin' => true,
            'status' => 'COMPLETED',
        ]);

        // Assign guide with fee
        GuideAssignment::create([
            'booking_id' => $booking->id,
            'guide_id' => $this->guide->id,
            'guest_count' => 4,
            'fee_amount' => 100000,
            'status' => 'COMPLETED',
            'assigned_at' => Carbon::now()->subHours(3),
        ]);

        // Record operational expense
        Expense::create([
            'category' => 'Bahan Bakar & Tol',
            'description' => 'Bensin Kendaraan Operasional',
            'amount' => 150000,
            'expense_date' => $today,
        ]);

        // Close shift
        $shiftService = app(OperationalShiftService::class);
        $shiftService->closeShift($this->staff, 'Penutupan sore');

        Http::assertSent(function ($request) {
            $embed = $request['embeds'][0];
            $fields = collect($embed['fields'])->keyBy('name');

            // Assert title & color
            $isTitleCorrect = str_contains($embed['title'], 'REKAP OPERASIONAL LOKET DITUTUP') &&
                              $embed['color'] === 0xE74C3C;

            // Assert Pax (4 Pax sold)
            $paxField = $fields->get('Wisatawan (Pax Laku)')['value'] ?? '';
            $hasPax = str_contains($paxField, '4 Orang (Pax)');

            // Assert Money Received (800.000)
            $omsetField = $fields->get('Total Uang Masuk (Omset)')['value'] ?? '';
            $hasOmset = str_contains($omsetField, '800.000');

            // Assert Payment method breakdown (Cash: 800.000)
            $paymentField = $fields->get('Rincian Metode Pembayaran')['value'] ?? '';
            $hasCash = str_contains($paymentField, 'Tunai (Cash): **Rp 800.000**');

            // Assert Guide Attendance (1 Guide, Wayan Sudarta)
            $guideField = $fields->get('Absensi Pemandu (Guide)')['value'] ?? '';
            $hasGuide = str_contains($guideField, 'Wayan Sudarta') && str_contains($guideField, 'Total Hadir: **1 Guide**');

            // Assert Expenses (150.000) & Guide Fee (100.000)
            $expenseField = $fields->get('Biaya & Pengeluaran')['value'] ?? '';
            $hasExpense = str_contains($expenseField, '150.000') && str_contains($expenseField, '100.000');

            return $isTitleCorrect && $hasPax && $hasOmset && $hasCash && $hasGuide && $hasExpense;
        });
    }

    public function test_shift_operations_succeed_gracefully_when_discord_fails()
    {
        config(['services.discord.webhook_url' => 'https://discord.com/api/webhooks/invalid']);

        Http::fake([
            'https://discord.com/api/webhooks/invalid' => Http::response('Unauthorized', 401),
        ]);

        $shiftService = app(OperationalShiftService::class);

        // Open shift should not fail
        $openResult = $shiftService->openShift($this->staff);
        $this->assertEquals('OPEN', $openResult['status']);

        // Close shift should not fail
        $closeResult = $shiftService->closeShift($this->staff);
        $this->assertEquals('CLOSED', $closeResult['status']);
    }

    public function test_activity_webhook_url_is_sourced_from_config_or_env_or_setting()
    {
        config(['services.discord.activity_webhook_url' => 'https://discord.com/api/webhooks/99999/activity-url']);

        $discordService = app(\App\Services\DiscordWebhookService::class);
        $this->assertTrue($discordService->isActivityEnabled());
        $this->assertEquals('https://discord.com/api/webhooks/99999/activity-url', $discordService->getActivityWebhookUrl());

        // Test fallback to database Setting
        config(['services.discord.activity_webhook_url' => null]);
        Setting::set('discord_activity_webhook_url', 'https://discord.com/api/webhooks/88888/db-activity');
        $this->assertEquals('https://discord.com/api/webhooks/88888/db-activity', $discordService->getActivityWebhookUrl());
    }

    public function test_admin_can_send_test_activity_discord_webhook()
    {
        config(['services.discord.activity_webhook_url' => 'https://discord.com/api/webhooks/99999/activity-url']);

        Http::fake([
            'https://discord.com/api/webhooks/99999/*' => Http::response(null, 204),
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('settings.test-discord'), [
            'type' => 'activity',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '99999/activity-url') &&
                   str_contains($request['embeds'][0]['title'], 'AKTIVITAS & AUDIT');
        });
    }

    public function test_login_and_logout_triggers_activity_discord_webhook()
    {
        config(['services.discord.activity_webhook_url' => 'https://discord.com/api/webhooks/99999/activity-url']);

        Http::fake([
            'https://discord.com/api/webhooks/99999/*' => Http::response(null, 204),
        ]);

        $user = User::factory()->create([
            'email' => 'staff_activity@artdevata.com',
            'password' => bcrypt('password123'),
            'role' => 'STAFF',
            'is_active' => true,
            'approval_status' => 'APPROVED',
        ]);

        // 1. Perform Login
        $this->post('/login', [
            'email' => 'staff_activity@artdevata.com',
            'password' => 'password123',
        ]);

        Http::assertSent(function ($request) {
            $embed = $request['embeds'][0];
            return str_contains($request->url(), '99999/activity-url') &&
                   str_contains($embed['title'], 'LOGIN');
        });

        // 2. Perform Logout
        $this->actingAs($user)->post('/logout');

        Http::assertSent(function ($request) {
            $embed = $request['embeds'][0];
            return str_contains($request->url(), '99999/activity-url') &&
                   str_contains($embed['title'], 'LOGOUT');
        });
    }

    public function test_crud_operations_trigger_activity_discord_webhook()
    {
        config(['services.discord.activity_webhook_url' => 'https://discord.com/api/webhooks/99999/activity-url']);

        Http::fake([
            'https://discord.com/api/webhooks/99999/*' => Http::response(null, 204),
        ]);

        // Create an expense (CRUD operation)
        $this->actingAs($this->admin)->post(route('expenses.store'), [
            'category' => 'Bahan Bakar & Tol',
            'description' => 'Bensin Kijang Innova',
            'amount' => 175000,
            'expense_date' => Carbon::today()->toDateString(),
        ]);

        Http::assertSent(function ($request) {
            $embed = $request['embeds'][0];
            return str_contains($request->url(), '99999/activity-url') &&
                   str_contains($embed['title'], 'BIAYA OPERASIONAL');
        });
    }

    public function test_operational_and_activity_webhooks_use_different_urls_independently()
    {
        config([
            'services.discord.webhook_url' => 'https://discord.com/api/webhooks/11111/operational-channel',
            'services.discord.activity_webhook_url' => 'https://discord.com/api/webhooks/22222/activity-channel',
        ]);

        Http::fake([
            'https://discord.com/api/webhooks/11111/*' => Http::response(null, 204),
            'https://discord.com/api/webhooks/22222/*' => Http::response(null, 204),
        ]);

        // Action 1: Open operational shift (Should ONLY send to operational webhook 11111)
        $shiftService = app(OperationalShiftService::class);
        $shiftService->openShift($this->staff);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '11111/operational-channel') &&
                   str_contains($request['embeds'][0]['title'], 'LOKET OPERASIONAL RESMI DIBUKA');
        });

        // Action 2: Trigger Audit Log (Should ONLY send to activity webhook 22222)
        \App\Models\AuditLog::log('CREATE_BOOKING', Booking::class, 99, null, [
            'booking_code' => 'BK-SEPARATE-001',
            'total_price' => 500000,
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '22222/activity-channel') &&
                   str_contains($request['embeds'][0]['title'], 'PEMESANAN TUR BARU DIBUAT');
        });
    }

    public function test_access_denied_triggers_security_alert_on_activity_webhook()
    {
        config(['services.discord.activity_webhook_url' => 'https://discord.com/api/webhooks/99999/activity-url']);

        Http::fake([
            'https://discord.com/api/webhooks/99999/*' => Http::response(null, 204),
        ]);

        // Non-admin attempting to access restricted admin settings
        $response = $this->actingAs($this->staff)->get(route('settings.index'));
        $response->assertStatus(403);

        Http::assertSent(function ($request) {
            $embed = $request['embeds'][0];
            return str_contains($request->url(), '99999/activity-url') &&
                   str_contains($embed['title'], 'AKSES WEB DITOLAK (403)');
        });
    }
}
