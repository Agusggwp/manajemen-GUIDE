<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guide;
use App\Models\GuideAssignment;
use App\Models\GuidePayment;
use App\Models\TourPackage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuidePayrollReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $guideUser;
    private Guide $guide;
    private TourPackage $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin_payroll_test@artdevata.com',
            'role' => 'ADMIN',
            'is_active' => true,
        ]);

        $this->guideUser = User::factory()->create([
            'name' => 'Wayan Sukadana',
            'role' => 'GUIDE',
            'is_active' => true,
        ]);

        $this->guide = Guide::create([
            'user_id' => $this->guideUser->id,
            'kode_guide' => 'GD-TEST-01',
            'nama' => 'Wayan Sukadana',
            'nomor_telepon' => '081234567890',
            'queue_number' => 1,
            'status' => 'AVAILABLE',
        ]);

        $this->package = TourPackage::create([
            'name' => 'Kintamani Highland Tour',
            'duration' => '8 Jam',
            'default_price' => 200000,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_guide_payroll_report()
    {
        $response = $this->get(route('reports.guide-payroll'));
        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_guide_payroll_report()
    {
        $response = $this->actingAs($this->admin)->get(route('reports.guide-payroll'));

        $response->assertStatus(200);
        $response->assertSee('Laporan Gaji &amp; Fee Guide', false);
        $response->assertSee('GD-TEST-01');
        $response->assertSee('Wayan Sukadana');
    }

    public function test_guide_payroll_report_calculates_fees_payments_and_balance_correctly()
    {
        // Create 2 bookings for the guide
        $booking1 = Booking::create([
            'booking_code' => 'BK-PAY-001',
            'customer_name' => 'Mr. John Doe',
            'customer_phone' => '08123456789',
            'tour_package_id' => $this->package->id,
            'booking_date' => Carbon::now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'guest_count' => 4,
            'total_price' => 800000,
            'status' => 'COMPLETED',
        ]);

        GuideAssignment::create([
            'booking_id' => $booking1->id,
            'guide_id' => $this->guide->id,
            'assigned_at' => Carbon::now(),
            'guest_count' => 4,
            'fee_amount' => 150000,
            'status' => 'COMPLETED',
        ]);

        $booking2 = Booking::create([
            'booking_code' => 'BK-PAY-002',
            'customer_name' => 'Mrs. Jane Smith',
            'customer_phone' => '08987654321',
            'tour_package_id' => $this->package->id,
            'booking_date' => Carbon::now()->toDateString(),
            'start_time' => '13:00:00',
            'end_time' => '18:00:00',
            'guest_count' => 2,
            'total_price' => 400000,
            'status' => 'COMPLETED',
        ]);

        GuideAssignment::create([
            'booking_id' => $booking2->id,
            'guide_id' => $this->guide->id,
            'assigned_at' => Carbon::now(),
            'guest_count' => 2,
            'fee_amount' => 100000,
            'status' => 'COMPLETED',
        ]);

        // Total fee = 250,000. Create partial payment of 100,000
        GuidePayment::create([
            'payment_code' => 'PAY-TEST-001',
            'guide_id' => $this->guide->id,
            'payment_date' => Carbon::now()->toDateString(),
            'amount' => 100000,
            'payment_method' => 'TRANSFER',
            'reference_number' => 'TRF-PAY-001',
            'notes' => 'Pembayaran termin 1',
            'recorded_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('reports.guide-payroll', [
            'filter_type' => 'month',
            'month' => Carbon::now()->month,
            'year' => Carbon::now()->year,
        ]));

        $response->assertStatus(200);
        // Total Hak: 250.000, Terbayar: 100.000, Sisa: 150.000
        $response->assertSee('250.000');
        $response->assertSee('100.000');
        $response->assertSee('150.000');
        $response->assertSee('SEBAGIAN');
    }

    public function test_guide_payroll_report_filters_by_month_and_year()
    {
        // Booking last month
        $lastMonth = Carbon::now()->subMonth();

        $pastBooking = Booking::create([
            'booking_code' => 'BK-PAST-001',
            'customer_name' => 'Past Customer',
            'customer_phone' => '08123456789',
            'tour_package_id' => $this->package->id,
            'booking_date' => $lastMonth->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'guest_count' => 3,
            'total_price' => 600000,
            'status' => 'COMPLETED',
        ]);

        GuideAssignment::create([
            'booking_id' => $pastBooking->id,
            'guide_id' => $this->guide->id,
            'assigned_at' => $lastMonth,
            'guest_count' => 3,
            'fee_amount' => 200000,
            'status' => 'COMPLETED',
        ]);

        // When viewing current month, past month fee shouldn't appear
        $responseCurrentMonth = $this->actingAs($this->admin)->get(route('reports.guide-payroll', [
            'filter_type' => 'month',
            'month' => Carbon::now()->month,
            'year' => Carbon::now()->year,
        ]));
        $responseCurrentMonth->assertStatus(200);
        $responseCurrentMonth->assertDontSee('BK-PAST-001');

        // When viewing last month, past month fee should appear
        $responseLastMonth = $this->actingAs($this->admin)->get(route('reports.guide-payroll', [
            'filter_type' => 'month',
            'month' => $lastMonth->month,
            'year' => $lastMonth->year,
        ]));
        $responseLastMonth->assertStatus(200);
        $responseLastMonth->assertSee('200.000');
    }

    public function test_guide_payroll_report_filters_by_guide_and_status()
    {
        // Create second guide
        $guideUser2 = User::factory()->create(['name' => 'Made Sudarsana', 'role' => 'GUIDE']);
        $guide2 = Guide::create([
            'user_id' => $guideUser2->id,
            'kode_guide' => 'GD-TEST-02',
            'nama' => 'Made Sudarsana',
            'nomor_telepon' => '081234567891',
            'queue_number' => 2,
            'status' => 'AVAILABLE',
        ]);

        // Filter by guide 1 only
        $response = $this->actingAs($this->admin)->get(route('reports.guide-payroll', [
            'guide_id' => $this->guide->id,
        ]));
        $response->assertStatus(200);
        $response->assertViewHas('guidesData', function ($data) {
            return $data->count() === 1 && $data->first()->guide->id === $this->guide->id;
        });

        // Filter by status UNPAID
        $responseUnpaid = $this->actingAs($this->admin)->get(route('reports.guide-payroll', [
            'payment_status' => 'UNPAID',
        ]));
        $responseUnpaid->assertStatus(200);
        $responseUnpaid->assertViewHas('guidesData', function ($data) {
            return $data->every(fn($item) => $item->status === 'UNPAID');
        });
    }

    public function test_guide_payroll_report_exports_csv_successfully()
    {
        $response = $this->actingAs($this->admin)->get(route('reports.guide-payroll', [
            'export' => 'csv',
        ]));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), 'laporan-gaji-fee-guide-'));
    }
}
