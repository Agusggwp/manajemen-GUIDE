<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add salary and banking info to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('position', 100)->nullable()->after('phone');
            $table->decimal('salary_base', 12, 2)->default(0)->after('position');
            $table->decimal('salary_allowance', 12, 2)->default(0)->after('salary_base');
            $table->string('bank_name', 50)->nullable()->after('salary_allowance');
            $table->string('bank_account_number', 50)->nullable()->after('bank_name');
            $table->string('bank_account_holder', 100)->nullable()->after('bank_account_number');
        });

        // Create staff_payrolls table
        Schema::create('staff_payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_code', 50)->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->string('position', 100)->nullable();
            
            // Salary components
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->decimal('allowance', 12, 2)->default(0); // Tunjangan Makan / Transport
            $table->decimal('bonus', 12, 2)->default(0); // Bonus / Lembur / Insentif
            $table->decimal('deduction', 12, 2)->default(0); // Potongan (BPJS, Kasbon, Terlambat)
            $table->decimal('net_salary', 12, 2)->default(0); // Gaji Bersih Diterima
            
            // Payment info
            $table->string('payment_status', 20)->default('PENDING')->index(); // PENDING, PAID
            $table->date('payment_date')->nullable();
            $table->string('payment_method', 50)->nullable(); // TRANSFER, CASH, LAINNYA
            $table->string('payment_reference', 100)->nullable(); // No Ref Transfer
            $table->text('notes')->nullable();

            // Tracking
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Unique constraint: one payroll per staff per month/year
            $table->unique(['user_id', 'period_month', 'period_year'], 'staff_month_year_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_payrolls');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'position',
                'salary_base',
                'salary_allowance',
                'bank_name',
                'bank_account_number',
                'bank_account_holder',
            ]);
        });
    }
};
