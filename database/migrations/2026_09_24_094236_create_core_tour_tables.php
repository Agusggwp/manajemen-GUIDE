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
        // 1. Guides table
        Schema::create('guides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kode_guide', 30)->unique();
            $table->string('nama', 100);
            $table->string('nomor_telepon', 30);
            $table->string('email', 100)->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto')->nullable();
            $table->string('bahasa', 150)->nullable();
            $table->string('spesialisasi', 150)->nullable();
            $table->unsignedInteger('queue_number')->default(1)->index();
            $table->enum('status', ['AVAILABLE', 'ASSIGNED', 'ON_TOUR', 'OFF', 'INACTIVE'])->default('AVAILABLE')->index();
            $table->date('tanggal_gabung')->nullable();
            $table->enum('tipe_fee', ['PER_GUEST', 'PER_BOOKING', 'TIERED'])->default('PER_GUEST');
            $table->decimal('fee_per_guest', 12, 2)->default(50000);
            $table->decimal('fee_per_booking', 12, 2)->default(300000);
            $table->decimal('fee_minimum', 12, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 2. Guide Attendances
        Schema::create('guide_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_id')->constrained('guides')->cascadeOnDelete();
            $table->date('attendance_date')->index();
            $table->dateTime('check_in_at')->nullable();
            $table->dateTime('check_out_at')->nullable();
            $table->enum('status', ['NOT_CHECKED_IN', 'CHECKED_IN', 'CHECKED_OUT', 'OFF'])->default('NOT_CHECKED_IN')->index();
            $table->text('notes')->nullable();
            $table->decimal('check_in_latitude', 10, 7)->nullable();
            $table->decimal('check_in_longitude', 10, 7)->nullable();
            $table->decimal('check_out_latitude', 10, 7)->nullable();
            $table->decimal('check_out_longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->unique(['guide_id', 'attendance_date'], 'unique_guide_daily_attendance');
        });

        // 3. Guide Queue State
        Schema::create('guide_queue_state', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('current_position')->default(0);
            $table->foreignId('last_guide_id')->nullable()->constrained('guides')->nullOnDelete();
            $table->unsignedBigInteger('last_assignment_id')->nullable();
            $table->timestamps();
        });

        // 4. Tour Packages
        Schema::create('tour_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('duration', 50)->default('Full Day');
            $table->decimal('default_price', 12, 2)->default(150000);
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Tour Package Prices (Pricing Tiers)
        Schema::create('tour_package_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_package_id')->constrained('tour_packages')->cascadeOnDelete();
            $table->unsignedInteger('min_guests')->default(1);
            $table->unsignedInteger('max_guests')->default(999);
            $table->decimal('price_per_guest', 12, 2);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // 6. Bookings
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 40)->unique();
            $table->date('booking_date')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('customer_name', 100);
            $table->string('customer_phone', 40)->nullable();
            $table->string('nationality', 60)->nullable();
            $table->unsignedInteger('guest_count')->default(1);
            $table->foreignId('tour_package_id')->nullable()->constrained('tour_packages')->nullOnDelete();
            $table->string('location', 150)->nullable();
            $table->string('pickup_location', 150)->nullable();
            $table->text('notes')->nullable();
            $table->decimal('price_per_guest', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->decimal('guide_fee_total', 12, 2)->default(0);
            $table->decimal('operational_cost', 12, 2)->default(0);
            $table->decimal('company_profit', 12, 2)->default(0);
            $table->enum('status', ['WAITING', 'READY', 'PARTIALLY_ASSIGNED', 'FULLY_ASSIGNED', 'ON_GOING', 'COMPLETED', 'CANCELLED'])->default('WAITING')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 7. Guests
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->unsignedSmallInteger('umur')->nullable();
            $table->string('negara', 60)->nullable();
            $table->string('nomor_telepon', 40)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 8. Guide Assignments
        Schema::create('guide_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('guide_id')->constrained('guides')->cascadeOnDelete();
            $table->unsignedInteger('guest_count')->default(1);
            $table->string('fee_type', 30)->default('PER_GUEST');
            $table->decimal('fee_rate', 12, 2)->default(0);
            $table->decimal('fee_amount', 12, 2)->default(0);
            $table->unsignedInteger('queue_position')->nullable();
            $table->dateTime('assigned_at');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->enum('status', ['ASSIGNED', 'ON_GOING', 'COMPLETED', 'CANCELLED'])->default('ASSIGNED')->index();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 9. Queue Histories / Rotation Audit
        Schema::create('queue_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('previous_position');
            $table->unsignedInteger('next_position');
            $table->foreignId('selected_guide_id')->nullable()->constrained('guides')->nullOnDelete();
            $table->json('skipped_guides')->nullable();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 20)->default('AUTO');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        // 10. Guide Payments
        Schema::create('guide_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_id')->constrained('guides')->cascadeOnDelete();
            $table->string('payment_code', 40)->unique();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date')->index();
            $table->string('payment_method', 50)->default('Cash');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 11. Expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('category', 60)->index();
            $table->text('description');
            $table->decimal('amount', 12, 2);
            $table->date('expense_date')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 12. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60)->index();
            $table->string('model', 80)->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        // 13. Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('guide_payments');
        Schema::dropIfExists('queue_histories');
        Schema::dropIfExists('guide_assignments');
        Schema::dropIfExists('guests');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('tour_package_prices');
        Schema::dropIfExists('tour_packages');
        Schema::dropIfExists('guide_queue_state');
        Schema::dropIfExists('guide_attendances');
        Schema::dropIfExists('guides');
    }
};
