<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\StaffPayroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffPayrollTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff1;
    protected User $staff2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
            'is_active' => true,
        ]);

        $this->staff1 = User::factory()->create([
            'role' => 'STAFF',
            'name' => 'Kadek Staff Operasional',
            'is_active' => true,
            'position' => 'Staff Operasional',
            'salary_base' => 3000000,
            'salary_allowance' => 500000,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Kadek Staff Operasional',
        ]);

        $this->staff2 = User::factory()->create([
            'role' => 'STAFF',
            'name' => 'Putu Ticketing',
            'is_active' => true,
            'position' => 'Staff Ticketing',
            'salary_base' => 2800000,
            'salary_allowance' => 400000,
        ]);
    }

    public function test_admin_can_view_staff_payroll_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('staff-payroll.index'));
        $response->assertOk();
        $response->assertSee('Penggajian Staff');
    }

    public function test_admin_can_update_staff_salary_settings(): void
    {
        $response = $this->actingAs($this->admin)->post(route('staff-payroll.setting', $this->staff1), [
            'position' => 'Senior Koordinator',
            'salary_base' => 4000000,
            'salary_allowance' => 750000,
            'bank_name' => 'Mandiri',
            'bank_account_number' => '9876543210',
            'bank_account_holder' => 'Kadek Staff',
        ]);

        $response->assertRedirect();
        $this->staff1->refresh();

        $this->assertEquals('Senior Koordinator', $this->staff1->position);
        $this->assertEquals(4000000, (float)$this->staff1->salary_base);
        $this->assertEquals(750000, (float)$this->staff1->salary_allowance);
        $this->assertEquals('Mandiri', $this->staff1->bank_name);
        $this->assertEquals('9876543210', $this->staff1->bank_account_number);
    }

    public function test_admin_can_create_staff_payroll_slip(): void
    {
        $response = $this->actingAs($this->admin)->post(route('staff-payroll.store'), [
            'user_id' => $this->staff1->id,
            'period_month' => 9,
            'period_year' => 2026,
            'position' => 'Staff Operasional',
            'base_salary' => 3000000,
            'allowance' => 500000,
            'bonus' => 200000,
            'deduction' => 100000,
            'payment_status' => 'PENDING',
            'notes' => 'Bonus target tercapai',
        ]);

        $response->assertRedirect();
        
        $payroll = StaffPayroll::where('user_id', $this->staff1->id)
            ->where('period_month', 9)
            ->where('period_year', 2026)
            ->first();

        $this->assertNotNull($payroll);
        $this->assertStringStartsWith('STF-PAY-202609-', $payroll->payroll_code);
        $this->assertEquals(3000000, (float)$payroll->base_salary);
        $this->assertEquals(500000, (float)$payroll->allowance);
        $this->assertEquals(200000, (float)$payroll->bonus);
        $this->assertEquals(100000, (float)$payroll->deduction);
        // Net salary = 3.000.000 + 500.000 + 200.000 - 100.000 = 3.600.000
        $this->assertEquals(3600000, (float)$payroll->net_salary);
        $this->assertEquals('PENDING', $payroll->payment_status);
        $this->assertFalse($payroll->isPaid());
    }

    public function test_cannot_create_duplicate_payroll_for_same_period(): void
    {
        StaffPayroll::create([
            'payroll_code' => 'STF-PAY-202609-001',
            'user_id' => $this->staff1->id,
            'period_month' => 9,
            'period_year' => 2026,
            'base_salary' => 3000000,
            'net_salary' => 3000000,
            'payment_status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->admin)->post(route('staff-payroll.store'), [
            'user_id' => $this->staff1->id,
            'period_month' => 9,
            'period_year' => 2026,
            'base_salary' => 3000000,
            'payment_status' => 'PENDING',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_admin_can_mark_payroll_as_paid_and_record_expense(): void
    {
        $payroll = StaffPayroll::create([
            'payroll_code' => 'STF-PAY-202609-002',
            'user_id' => $this->staff1->id,
            'period_month' => 9,
            'period_year' => 2026,
            'base_salary' => 3000000,
            'net_salary' => 3000000,
            'payment_status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->admin)->post(route('staff-payroll.pay', $payroll), [
            'payment_date' => '2026-09-25',
            'payment_method' => 'TRANSFER',
            'payment_reference' => 'TRF-123456',
            'notes' => 'Transfer via BCA',
            'record_expense' => 1,
        ]);

        $response->assertRedirect();
        $payroll->refresh();

        $this->assertEquals('PAID', $payroll->payment_status);
        $this->assertEquals('2026-09-25', $payroll->payment_date->toDateString());
        $this->assertEquals('TRANSFER', $payroll->payment_method);
        $this->assertEquals('TRF-123456', $payroll->payment_reference);
        $this->assertEquals($this->admin->id, $payroll->paid_by);
        $this->assertTrue($payroll->isPaid());

        // Verify expense was recorded
        $expense = Expense::where('description', 'like', "%{$this->staff1->name}%")->first();
        $this->assertNotNull($expense);
        $this->assertEquals('Gaji & Upah', $expense->category);
        $this->assertEquals(3000000, (float)$expense->amount);
    }

    public function test_staff_can_view_own_salary_slips_portal(): void
    {
        $payroll = StaffPayroll::create([
            'payroll_code' => 'STF-PAY-202609-003',
            'user_id' => $this->staff1->id,
            'period_month' => 9,
            'period_year' => 2026,
            'base_salary' => 3000000,
            'net_salary' => 3000000,
            'payment_status' => 'PAID',
            'payment_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->staff1)->get(route('staff.my-salary'));
        $response->assertOk();
        $response->assertSee('STF-PAY-202609-003');

        $responseShow = $this->actingAs($this->staff1)->get(route('staff-payroll.show', $payroll));
        $responseShow->assertOk();
        $responseShow->assertSee('SLIP GAJI KARYAWAN');
    }

    public function test_staff_cannot_view_other_staff_salary_slip(): void
    {
        $payrollStaff2 = StaffPayroll::create([
            'payroll_code' => 'STF-PAY-202609-004',
            'user_id' => $this->staff2->id,
            'period_month' => 9,
            'period_year' => 2026,
            'base_salary' => 2800000,
            'net_salary' => 2800000,
            'payment_status' => 'PENDING',
        ]);

        // Staff 1 tries to view Staff 2's slip
        $response = $this->actingAs($this->staff1)->get(route('staff-payroll.show', $payrollStaff2));
        $response->assertForbidden();
    }

    public function test_staff_cannot_access_staff_payroll_admin_routes(): void
    {
        $responseIndex = $this->actingAs($this->staff1)->get(route('staff-payroll.index'));
        $responseIndex->assertForbidden();

        $responseCreate = $this->actingAs($this->staff1)->get(route('staff-payroll.create'));
        $responseCreate->assertForbidden();
    }

    public function test_admin_can_delete_staff_payroll(): void
    {
        $payroll = StaffPayroll::create([
            'payroll_code' => 'STF-PAY-202609-005',
            'user_id' => $this->staff1->id,
            'period_month' => 9,
            'period_year' => 2026,
            'base_salary' => 3000000,
            'net_salary' => 3000000,
            'payment_status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('staff-payroll.destroy', $payroll));
        $response->assertRedirect(route('staff-payroll.index'));

        $this->assertDatabaseMissing('staff_payrolls', ['id' => $payroll->id]);
    }
}
