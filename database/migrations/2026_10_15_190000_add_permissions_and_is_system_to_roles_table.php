<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('description');
            $table->boolean('is_system')->default(false)->after('permissions');
        });

        // Initialize default system roles with their base permissions
        $adminPermissions = ['*'];
        $staffPermissions = [
            'dashboard.view',
            'dashboard.export',
            'queue.view',
            'queue.reorder',
            'queue.history',
            'bookings.view',
            'bookings.create',
            'bookings.assign',
            'bookings.edit',
            'attendance.view',
            'attendance.manage',
            'shift.manage',
            'guides.view',
            'packages.view',
            'reports.revenue',
            'reports.payroll',
            'reports.attendance',
        ];

        DB::table('roles')->updateOrInsert(
            ['name' => 'ADMIN'],
            [
                'label' => 'Administrator',
                'description' => 'Akses penuh ke semua modul sistem, keuangan, antrean, dan konfigurasi master.',
                'permissions' => json_encode($adminPermissions),
                'is_system' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('roles')->updateOrInsert(
            ['name' => 'STAFF'],
            [
                'label' => 'Staff Operasional & Kasir',
                'description' => 'Operasional loket harian, pemesanan tiket tur, penugasan pemandu, dan absensi.',
                'permissions' => json_encode($staffPermissions),
                'is_system' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('roles')->updateOrInsert(
            ['name' => 'GUIDE'],
            [
                'label' => 'Pemandu Wisata (Guide)',
                'description' => 'Akses khusus portal mobile pemandu untuk absensi, menerima tur, dan melihat riwayat fee.',
                'permissions' => json_encode([]),
                'is_system' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['permissions', 'is_system']);
        });
    }
};
