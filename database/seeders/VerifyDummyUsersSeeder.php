<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VerifyDummyUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $password = Hash::make('password');

        // 1. Mark all existing users as verified and approved
        User::query()->update([
            'email_verified_at' => $now,
            'approval_status' => 'APPROVED',
            'is_active' => true,
        ]);

        // 2. Ensure standard demo accounts exist and are verified
        $standardUsers = [
            [
                'name' => 'Admin Operasional',
                'email' => 'admin@example.com',
                'role' => 'ADMIN',
                'phone' => '081234567890',
            ],
            [
                'name' => 'Staff Reservasi',
                'email' => 'staff@example.com',
                'role' => 'STAFF',
                'phone' => '081234567891',
            ],
            [
                'name' => 'Budi Antara',
                'email' => 'budi@example.com',
                'role' => 'GUIDE',
                'phone' => '08130000001',
            ],
            [
                'name' => 'Made Sujana',
                'email' => 'made@example.com',
                'role' => 'GUIDE',
                'phone' => '08130000002',
            ],
            [
                'name' => 'Komang Widi',
                'email' => 'komang@example.com',
                'role' => 'GUIDE',
                'phone' => '08130000003',
            ],
            [
                'name' => 'Wayan Sudarma',
                'email' => 'wayan@example.com',
                'role' => 'GUIDE',
                'phone' => '08130000004',
            ],
            [
                'name' => 'Agus Devata',
                'email' => 'agus@example.com',
                'role' => 'GUIDE',
                'phone' => '08130000005',
            ],
        ];

        foreach ($standardUsers as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $password,
                    'role' => $userData['role'],
                    'phone' => $userData['phone'],
                    'is_active' => true,
                    'approval_status' => 'APPROVED',
                    'email_verified_at' => $now,
                ]
            );
        }

        // 3. Create sample verified dummy users waiting for Admin ACC approval (for testing /user-approvals)
        User::updateOrCreate(
            ['email' => 'staff.pending@example.com'],
            [
                'name' => 'Kadek Staff Baru',
                'password' => $password,
                'role' => 'STAFF',
                'phone' => '081298765432',
                'is_active' => false,
                'approval_status' => 'PENDING',
                'email_verified_at' => $now, // Sudah di-verify emailnya!
            ]
        );

        User::updateOrCreate(
            ['email' => 'guide.pending@example.com'],
            [
                'name' => 'Nyoman Guide Baru',
                'password' => $password,
                'role' => 'GUIDE',
                'phone' => '081298765433',
                'is_active' => false,
                'approval_status' => 'PENDING',
                'email_verified_at' => $now, // Sudah di-verify emailnya!
            ]
        );

        $this->command->info('Semua akun dummy berhasil di-verifikasi (email_verified_at diisi) dan siap digunakan!');
    }
}
