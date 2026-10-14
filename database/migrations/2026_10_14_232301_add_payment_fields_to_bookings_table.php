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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_method', 50)->default('CASH')->after('is_walkin');
            $table->string('payment_status', 20)->default('PAID')->after('payment_method');
            $table->decimal('amount_paid', 12, 2)->nullable()->after('payment_status');
            $table->decimal('change_amount', 12, 2)->nullable()->after('amount_paid');
            $table->string('payment_reference', 100)->nullable()->after('change_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'payment_status',
                'amount_paid',
                'change_amount',
                'payment_reference',
            ]);
        });
    }
};
