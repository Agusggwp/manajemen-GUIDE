<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'label',
        'description',
        'permissions',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system' => 'boolean',
        ];
    }

    /**
     * Relationship to Users holding this role.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role', 'name');
    }

    /**
     * Check if this role has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        $perms = $this->permissions ?? [];

        if (in_array('*', $perms)) {
            return true;
        }

        return in_array($permission, $perms);
    }

    /**
     * Check if this role is a protected system role that cannot be deleted.
     */
    public function isProtected(): bool
    {
        return (bool) $this->is_system || in_array($this->name, ['ADMIN', 'GUIDE', 'STAFF']);
    }

    /**
     * Comprehensive dictionary of available system permissions grouped by module.
     */
    public static function availablePermissions(): array
    {
        return [
            'Dashboard & Antrean' => [
                'dashboard.view' => 'Melihat Dashboard Utama & Ringkasan Metrik',
                'dashboard.export' => 'Ekspor Data Dashboard ke CSV',
                'queue.view' => 'Melihat Papan Antrean Pemandu (Queue Board)',
                'queue.reorder' => 'Mengatur Ulang Posisi Antrean Pemandu',
                'queue.history' => 'Melihat Riwayat Rotasi Antrean Pemandu',
            ],
            'Pemesanan & Tur (Bookings)' => [
                'bookings.view' => 'Melihat Daftar Pemesanan & Tur',
                'bookings.create' => 'Membuat Pemesanan Baru (Termasuk Direct/Walk-In Tour)',
                'bookings.edit' => 'Mengubah Data Pemesanan & Jadwal',
                'bookings.delete' => 'Membatalkan / Menghapus Pemesanan',
                'bookings.assign' => 'Menugaskan Pemandu (Auto / Manual Assignment)',
            ],
            'Absensi & Operasional Shift' => [
                'attendance.view' => 'Melihat Daftar Absensi Harian Pemandu',
                'attendance.manage' => 'Force Check-In & Force Check-Out Pemandu',
                'shift.manage' => 'Buka & Tutup Loket Operasional Kasir',
            ],
            'Pemandu Wisata (Guides)' => [
                'guides.view' => 'Melihat Data & Daftar Pemandu',
                'guides.create' => 'Menambah Pemandu Baru',
                'guides.edit' => 'Mengubah Profil & Tarif Fee Pemandu',
                'guides.delete' => 'Menghapus Data Pemandu',
                'guides.suspend' => 'Menangguhkan (Suspend) / Mengaktifkan Pemandu',
            ],
            'Paket Wisata & Harga' => [
                'packages.view' => 'Melihat Daftar Paket Wisata',
                'packages.manage' => 'Menambah, Mengubah, & Menghapus Paket serta Tier Harga',
            ],
            'Keuangan & Penggajian' => [
                'payroll.view' => 'Melihat Laporan Fee Pemandu & Saldo',
                'payroll.pay' => 'Melakukan Pembayaran Fee Pemandu',
                'staff_payroll.view' => 'Melihat Daftar Gaji Staf',
                'staff_payroll.manage' => 'Membuat Slip Gaji Staf & Pelunasan',
                'expenses.view' => 'Melihat Daftar Biaya Operasional',
                'expenses.manage' => 'Mencatat & Menghapus Biaya Operasional',
            ],
            'Laporan & Analitik' => [
                'reports.revenue' => 'Melihat Laporan Pendapatan & Omset',
                'reports.payroll' => 'Melihat Rekap Gaji & Fee Pemandu',
                'reports.attendance' => 'Melihat Laporan Kehadiran Pemandu',
            ],
            'Pengaturan & Keamanan' => [
                'settings.manage' => 'Mengubah Identitas Aplikasi, Discord & Parameter Loket',
                'roles.manage' => 'Mengelola Role Pengguna & Hak Akses (Permissions)',
                'audit.view' => 'Melihat Log Audit Aktivitas Sistem',
                'approvals.manage' => 'Menyetujui / Menolak Pendaftaran Pengguna Baru',
            ],
        ];
    }
}
