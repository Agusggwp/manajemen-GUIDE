<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\Guide;
use App\Models\GuideAssignment;
use App\Models\GuideAttendance;
use App\Models\GuideQueueState;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordWebhookService
{
    /**
     * Check if Discord operational webhook notifications are configured in .env / config.
     */
    public function isEnabled(): bool
    {
        return !empty($this->getWebhookUrl());
    }

    /**
     * Get configured Discord Operational Webhook URL from .env / config.
     */
    public function getWebhookUrl(): ?string
    {
        $url = config('services.discord.webhook_url') ?: env('DISCORD_WEBHOOK_URL');
        if (!empty($url)) {
            return trim($url);
        }

        $legacy = Setting::get('discord_webhook_url');
        if (!empty($legacy)) {
            return trim($legacy);
        }

        return null;
    }

    /**
     * Check if Discord Activity & Audit Webhook is configured in .env / config.
     */
    public function isActivityEnabled(): bool
    {
        return !empty($this->getActivityWebhookUrl());
    }

    /**
     * Get configured Discord Activity & Audit Webhook URL from .env / config.
     */
    public function getActivityWebhookUrl(): ?string
    {
        $url = config('services.discord.activity_webhook_url') ?: env('DISCORD_ACTIVITY_WEBHOOK_URL');
        if (!empty($url)) {
            return trim($url);
        }

        $fromDb = Setting::get('discord_activity_webhook_url');
        if (!empty($fromDb)) {
            return trim($fromDb);
        }

        return null;
    }

    /**
     * Send notification when operational shift is OPENED.
     */
    public function sendShiftOpened(array $shiftData, ?User $user = null, ?string $date = null): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $url = $this->getWebhookUrl();
        $targetDate = $date ?? Carbon::today()->toDateString();
        $dateFormatted = Carbon::parse($targetDate)->locale('id')->isoFormat('dddd, D MMMM Y');
        $timeNow = Carbon::now()->format('H:i') . ' WITA';
        $openedBy = $user ? "{$user->name} ({$user->role})" : ($shiftData['opened_by'] ?? 'Staf / Admin');

        $autoEnabled = Setting::get('operational_auto_schedule_enabled', '0') === '1';
        $autoCloseTime = Setting::get('operational_auto_close_time', '18:00');
        $autoScheduleText = $autoEnabled 
            ? "Otomatis tutup pukul {$autoCloseTime} WITA" 
            : "Manual (Ditutup mandiri oleh Staf/Admin)";

        $activeGuidesTotal = Guide::where('status', '!=', 'INACTIVE')->count();
        $queueState = GuideQueueState::getState();

        $appName = Setting::get('company_name', 'ARTDEVATA');

        $payload = [
            'username' => "{$appName} Operasional",
            'avatar_url' => 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png',
            'embeds' => [
                [
                    'title' => 'LOKET OPERASIONAL RESMI DIBUKA',
                    'description' => "> 🟢 **Status: LOKET RESMI DIBUKA**\n> Loket operasional **{$appName}** telah dibuka. Pemandu wisata (guide) sekarang dapat melakukan absensi check-in dan sistem siap menerima pesanan tur.",
                    'color' => 0x2ECC71, // Emerald Green
                    'fields' => [
                        [
                            'name' => 'Tanggal Operasional',
                            'value' => "📅 {$dateFormatted}",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Waktu Buka',
                            'value' => "⏰ **{$timeNow}**",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Dibuka Oleh',
                            'value' => "👤 **{$openedBy}**",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Guide Terdaftar',
                            'value' => "👥 **{$activeGuidesTotal} Guide** Aktif",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Urutan Antrean',
                            'value' => "🔄 Posisi Antrean: **#{$queueState->current_position}**",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Mode Penutupan',
                            'value' => "⚙️ {$autoScheduleText}",
                            'inline' => true,
                        ],
                    ],
                    'footer' => [
                        'text' => "{$appName} Management System • Shift Kasir & Operasional",
                    ],
                    'timestamp' => Carbon::now()->toISOString(),
                ],
            ],
        ];

        return $this->postWebhook($url, $payload);
    }

    /**
     * Send notification when operational shift is CLOSED.
     * Includes full operational and financial summary:
     * - Total Pax sold (Wisatawan)
     * - Total Money Received (Pendapatan & breakdown metode pembayaran)
     * - Total Guides Attended & Checked Out
     * - Total Bookings / Tours (Walk-in vs Reservasi)
     * - Operational expenses & net estimated profit
     */
    public function sendShiftClosed(array $shiftData, ?User $user = null, string $reason = 'Penutupan operasional', ?string $date = null): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $url = $this->getWebhookUrl();
        $targetDate = $date ?? Carbon::today()->toDateString();
        $dateFormatted = Carbon::parse($targetDate)->locale('id')->isoFormat('dddd, D MMMM Y');
        $timeClosed = Carbon::now()->format('H:i') . ' WITA';
        $closedBy = $user ? "{$user->name} ({$user->role})" : ($shiftData['closed_by'] ?? 'Sistem Otomatis');
        $autoCheckedOutCount = $shiftData['auto_checked_out_count'] ?? 0;

        $appName = Setting::get('company_name', 'ARTDEVATA');

        // 1. Calculate Bookings & Pax for target date
        $startBound = Carbon::parse($targetDate)->startOfDay()->toDateTimeString();
        $endBound = Carbon::parse($targetDate)->endOfDay()->toDateTimeString();

        $bookings = Booking::where(function ($q) use ($targetDate, $startBound, $endBound) {
            $q->whereDate('booking_date', $targetDate)
              ->orWhereBetween('booking_date', [$startBound, $endBound]);
        })->where('status', '!=', 'CANCELLED')
          ->with(['tourPackage', 'assignments.guide'])
          ->get();

        $totalPax = (int) $bookings->sum('guest_count');
        $totalBookingsCount = $bookings->count();
        $walkinCount = $bookings->where('is_walkin', true)->count();
        $reservationCount = $totalBookingsCount - $walkinCount;

        // 2. Financial Metrics
        $totalRevenue = (float) $bookings->sum('total_price');
        $totalAmountPaid = (float) $bookings->sum('amount_paid');

        // Payment method breakdown
        $cashPaid = (float) $bookings->filter(fn($b) => strtoupper($b->payment_method ?? '') === 'CASH' || empty($b->payment_method))->sum('amount_paid');
        $qrisPaid = (float) $bookings->filter(fn($b) => strtoupper($b->payment_method ?? '') === 'QRIS')->sum('amount_paid');
        $transferPaid = (float) $bookings->filter(fn($b) => strtoupper($b->payment_method ?? '') === 'TRANSFER')->sum('amount_paid');
        $cardPaid = (float) $bookings->filter(fn($b) => in_array(strtoupper($b->payment_method ?? ''), ['CARD', 'CREDIT_CARD', 'DEBIT']))->sum('amount_paid');

        // 3. Operational Expenses for target date
        $expenses = Expense::where(function ($q) use ($targetDate, $startBound, $endBound) {
            $q->whereDate('expense_date', $targetDate)
              ->orWhereBetween('expense_date', [$startBound, $endBound]);
        })->get();
        $totalExpenses = (float) $expenses->sum('amount');

        // 4. Guide Fees for target date bookings
        $totalGuideFee = (float) GuideAssignment::whereHas('booking', function ($q) use ($targetDate, $startBound, $endBound) {
            $q->whereDate('booking_date', $targetDate)
              ->orWhereBetween('booking_date', [$startBound, $endBound]);
        })->where('status', '!=', 'CANCELLED')->sum('fee_amount');

        // 5. Estimated Net Profit
        $netEstimatedProfit = $totalRevenue - $totalGuideFee - $totalExpenses;

        // 6. Packages Breakdown
        $packagesLines = [];
        foreach ($bookings->groupBy('tour_package_id') as $pkgId => $pkgBookings) {
            $pkgName = $pkgBookings->first()->tourPackage?->name ?? 'Paket Kustom';
            $paxCount = $pkgBookings->sum('guest_count');
            $tourCount = $pkgBookings->count();
            $packagesLines[] = "• **{$pkgName}**: {$paxCount} pax ({$tourCount} tur)";
        }
        $packagesText = !empty($packagesLines) ? implode("\n", array_slice($packagesLines, 0, 5)) : "• *Tidak ada tur terjual pada shift ini.*";
        if (count($packagesLines) > 5) {
            $packagesText .= "\n• *...dan " . (count($packagesLines) - 5) . " paket lainnya*";
        }

        // 7. Guide Attendances
        $attendances = GuideAttendance::where('attendance_date', $targetDate)
            ->with('guide')
            ->get();
        $totalGuidesAttended = max($attendances->count(), $autoCheckedOutCount);
        $manualCheckedOut = max(0, $totalGuidesAttended - $autoCheckedOutCount);

        $guideNames = $attendances->map(fn($a) => $a->guide?->nama)->filter()->unique()->values()->all();
        $guideNamesText = !empty($guideNames) ? implode(', ', array_slice($guideNames, 0, 8)) : 'Tidak ada guide hadir';
        if (count($guideNames) > 8) {
            $guideNamesText .= ' (+ ' . (count($guideNames) - 8) . ' lainnya)';
        }

        // Duration of Shift
        $openedAt = Setting::get('operational_opened_at');
        $durationText = '-';
        if ($openedAt) {
            $openTimeObj = Carbon::parse($openedAt);
            $durationMinutes = $openTimeObj->diffInMinutes(Carbon::now());
            $hours = floor($durationMinutes / 60);
            $mins = $durationMinutes % 60;
            $durationText = "{$openTimeObj->format('H:i')} - {$timeClosed} ({$hours}j {$mins}m)";
        }

        // Format helpers
        $fmtRp = fn($num) => 'Rp ' . number_format($num, 0, ',', '.');

        $paymentDetails = "• Tunai (Cash): **{$fmtRp($cashPaid)}**\n"
                        . "• QRIS: **{$fmtRp($qrisPaid)}**\n"
                        . "• Transfer: **{$fmtRp($transferPaid)}**\n"
                        . "• Kartu / EDC: **{$fmtRp($cardPaid)}**";

        $expenseDetails = "• Operasional: **{$fmtRp($totalExpenses)}**\n"
                        . "• Fee Guide: **{$fmtRp($totalGuideFee)}**\n"
                        . "• Total Beban: **{$fmtRp($totalExpenses + $totalGuideFee)}**";

        $attendanceDetails = "• Total Hadir: **{$totalGuidesAttended} Guide**\n"
                           . "• Auto Checkout: **{$autoCheckedOutCount} Guide** • Checkout Mandiri: **{$manualCheckedOut} Guide**\n"
                           . "• Daftar Pemandu: {$guideNamesText}";

        $payload = [
            'username' => "{$appName} Operasional",
            'avatar_url' => 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png',
            'embeds' => [
                [
                    'title' => 'REKAP OPERASIONAL LOKET DITUTUP',
                    'description' => "> 🔴 **Status: LOKET RESMI DITUTUP**\n> Laporan resmi penutupan shift loket kasir & operasional **{$appName}** pada tanggal **{$dateFormatted}**.\n> Semua pemandu aktif telah otomatis di-checkout oleh sistem.",
                    'color' => 0xE74C3C, // Alizarin Red
                    'fields' => [
                        [
                            'name' => 'Tanggal & Waktu Tutup',
                            'value' => "📅 {$dateFormatted}\n⏰ Pukul **{$timeClosed}**",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Ditutup Oleh',
                            'value' => "👤 **{$closedBy}**\n📝 _{$reason}_",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Durasi Operasional',
                            'value' => "⏱️ **{$durationText}**",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Wisatawan (Pax Laku)',
                            'value' => "🎟️ **{$totalPax} Orang (Pax)**\n📊 Total: **{$totalBookingsCount} Tur** ({$walkinCount} Walk-in / {$reservationCount} Reservasi)",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Total Uang Masuk (Omset)',
                            'value' => "💰 Diterima: **{$fmtRp($totalAmountPaid)}**\n🧾 Tagihan: **{$fmtRp($totalRevenue)}**",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Estimasi Laba Bersih',
                            'value' => "📈 **{$fmtRp($netEstimatedProfit)}**\n*(Omset − Fee Guide − Operasional)*",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Rincian Metode Pembayaran',
                            'value' => $paymentDetails,
                            'inline' => true,
                        ],
                        [
                            'name' => 'Biaya & Pengeluaran',
                            'value' => $expenseDetails,
                            'inline' => true,
                        ],
                        [
                            'name' => 'Paket Wisata Terjual',
                            'value' => $packagesText,
                            'inline' => false,
                        ],
                        [
                            'name' => 'Absensi Pemandu (Guide)',
                            'value' => $attendanceDetails,
                            'inline' => false,
                        ],
                    ],
                    'footer' => [
                        'text' => "{$appName} Management System • Rekap Harian Loket Operasional",
                    ],
                    'timestamp' => Carbon::now()->toISOString(),
                ],
            ],
        ];

        return $this->postWebhook($url, $payload);
    }

    /**
     * Send activity & audit trail notification to Discord Activity Webhook.
     */
    public function sendActivityLog(AuditLog $auditLog): bool
    {
        if (!$this->isActivityEnabled()) {
            return false;
        }

        $url = $this->getActivityWebhookUrl();
        if (empty($url)) {
            return false;
        }

        $user = $auditLog->user ?? ($auditLog->user_id ? User::find($auditLog->user_id) : null);
        $actorName = $user ? $user->name : 'Sistem Otomatis / Publik';
        $actorRole = $user ? $user->role : 'SYSTEM';
        $actorEmail = $user ? $user->email : '-';

        $action = strtoupper($auditLog->action);
        $meta = $this->resolveActionMetadata($action, $auditLog);

        $appName = Setting::get('company_name', 'ARTDEVATA');
        $timeWita = Carbon::parse($auditLog->created_at ?? now())->format('H:i:s') . ' WITA';
        $dateFormatted = Carbon::parse($auditLog->created_at ?? now())->locale('id')->isoFormat('dddd, D MMMM Y');

        $targetObject = $auditLog->model 
            ? (class_basename($auditLog->model) . ($auditLog->model_id ? " #{$auditLog->model_id}" : ''))
            : ($meta['target'] ?? 'Sistem');

        $fields = [
            [
                'name' => '👤 Pelaksana (Aktor)',
                'value' => "**{$actorName}**\nRole: `{$actorRole}`\nEmail: `{$actorEmail}`",
                'inline' => true,
            ],
            [
                'name' => '⏰ Waktu & Tanggal',
                'value' => "**{$timeWita}**\n{$dateFormatted}",
                'inline' => true,
            ],
            [
                'name' => '🌐 IP & Perangkat',
                'value' => "IP: `{$auditLog->ip_address}`\n" . $this->formatUserAgent($auditLog->user_agent),
                'inline' => true,
            ],
            [
                'name' => '🏷️ Modul / Objek Target',
                'value' => "`{$targetObject}`",
                'inline' => true,
            ],
        ];

        if (!empty($auditLog->new_values) && is_array($auditLog->new_values)) {
            $formattedNew = $this->formatValuesForDiscord($auditLog->new_values);
            if (!empty($formattedNew)) {
                $fields[] = [
                    'name' => '📋 Rincian Nilai / Perubahan Baru',
                    'value' => $formattedNew,
                    'inline' => false,
                ];
            }
        }

        if (!empty($auditLog->old_values) && is_array($auditLog->old_values)) {
            $formattedOld = $this->formatValuesForDiscord($auditLog->old_values);
            if (!empty($formattedOld)) {
                $fields[] = [
                    'name' => '⏮️ Nilai Sebelumnya',
                    'value' => $formattedOld,
                    'inline' => false,
                ];
            }
        }

        $payload = [
            'username' => "{$appName} Audit Trail",
            'avatar_url' => 'https://cdn-icons-png.flaticon.com/512/942/942748.png',
            'embeds' => [
                [
                    'title' => $meta['title'],
                    'description' => "> **{$meta['description']}**",
                    'color' => $meta['color'],
                    'fields' => $fields,
                    'footer' => [
                        'text' => "{$appName} Management System • Notifikasi Audit Trail & Aktivitas Web",
                    ],
                    'timestamp' => Carbon::parse($auditLog->created_at ?? now())->toISOString(),
                ],
            ],
        ];

        return $this->postWebhook($url, $payload);
    }

    /**
     * Resolve title, color, and description for each Audit Log action.
     */
    protected function resolveActionMetadata(string $action, AuditLog $log): array
    {
        $map = [
            'LOGIN' => [
                'title' => '🟢 PENGGUNA BERHASIL LOGIN',
                'color' => 0x2ECC71, // Emerald
                'description' => 'Pengguna telah berhasil login ke portal web ARTDEVATA.',
                'target' => 'Sesi Pengguna',
            ],
            'LOGOUT' => [
                'title' => '⚪ PENGGUNA TELAH LOGOUT',
                'color' => 0x95A5A6, // Silver
                'description' => 'Pengguna telah mengakhiri sesi dan keluar dari sistem.',
                'target' => 'Sesi Pengguna',
            ],
            'REGISTER' => [
                'title' => '📝 PENDAFTARAN PENGGUNA BARU',
                'color' => 0x3498DB, // Blue
                'description' => 'Akun pengguna baru telah mendaftar dan menunggu verifikasi/persetujuan.',
                'target' => 'User Account',
            ],
            'VERIFY_EMAIL' => [
                'title' => '✉️ EMAIL PENGGUNA TERVERIFIKASI',
                'color' => 0x1ABC9C, // Teal
                'description' => 'Alamat email pengguna telah berhasil diverifikasi melalui signed URL.',
                'target' => 'User Account',
            ],
            'APPROVE_USER' => [
                'title' => '✅ AKUN PENGGUNA DISETUJUI (ACC)',
                'color' => 0x27AE60, // Green
                'description' => 'Administrator telah menyetujui akun pengguna. Pengguna kini berstatus AKTIF.',
                'target' => 'User Account',
            ],
            'REJECT_USER' => [
                'title' => '🚫 PENDAFTARAN PENGGUNA DITOLAK',
                'color' => 0xE74C3C, // Red
                'description' => 'Pendaftaran akun pengguna telah ditolak oleh Administrator.',
                'target' => 'User Account',
            ],
            'ACCESS_DENIED' => [
                'title' => '🚨 PERINGATAN: AKSES WEB DITOLAK (403)',
                'color' => 0xE74C3C, // Red
                'description' => 'Terdeteksi upaya akses halaman terlarang tanpa izin hak akses yang sesuai.',
                'target' => 'Keamanan Web',
            ],
            'CREATE_BOOKING' => [
                'title' => '🎫 PEMESANAN TUR BARU DIBUAT',
                'color' => 0x3498DB,
                'description' => 'Pemesanan paket tur wisata baru telah berhasil dicatat.',
                'target' => 'Pemesanan Tur',
            ],
            'CREATE_DIRECT_TOUR' => [
                'title' => '⚡ TUR LANGSUNG (WALK-IN) DIBUAT',
                'color' => 0x2ECC71,
                'description' => 'Pemesanan tiket tur langsung di loket kasir telah diterbitkan.',
                'target' => 'Pemesanan Tur',
            ],
            'UPDATE_BOOKING' => [
                'title' => '✏️ DATA PEMESANAN DIUBAH',
                'color' => 0xF39C12,
                'description' => 'Perubahan informasi jadwal, pemandu, atau tarif pemesanan tur.',
                'target' => 'Pemesanan Tur',
            ],
            'DELETE_BOOKING' => [
                'title' => '🗑️ PEMESANAN TUR DIBATALKAN / DIHAPUS',
                'color' => 0xE74C3C,
                'description' => 'Pemesanan tur telah dibatalkan dari sistem operasional.',
                'target' => 'Pemesanan Tur',
            ],
            'REMOVE_ASSIGNMENT' => [
                'title' => '🔄 PENUGASAN PEMANDU DICABUT',
                'color' => 0xE67E22,
                'description' => 'Penugasan pemandu pada tur telah dibatalkan dan dikembalikan ke antrean.',
                'target' => 'Penugasan Guide',
            ],
            'START_TOUR' => [
                'title' => '🚀 KEBERANGKATAN: TUR DIMULAI',
                'color' => 0x2ECC71,
                'description' => 'Pemandu wisata telah mengonfirmasi bahwa tur telah resmi dimulai.',
                'target' => 'Operasional Tur',
            ],
            'COMPLETE_TOUR' => [
                'title' => '🏁 TUR TELAH SELESAI',
                'color' => 0x9B59B6,
                'description' => 'Pemandu wisata telah menyelesaikan tur dan kembali ke loket.',
                'target' => 'Operasional Tur',
            ],
            'CHECK_IN' => [
                'title' => '📍 ABSENSI: PEMANDU CHECK-IN',
                'color' => 0x2ECC71,
                'description' => 'Pemandu wisata telah check-in absensi masuk loket operasional.',
                'target' => 'Absensi Guide',
            ],
            'CHECK_OUT' => [
                'title' => '🚪 ABSENSI: PEMANDU CHECK-OUT',
                'color' => 0xE67E22,
                'description' => 'Pemandu wisata telah melakukan absensi pulang loket operasional.',
                'target' => 'Absensi Guide',
            ],
            'CREATE_GUIDE' => [
                'title' => '👤 PEMANDU WISATA BARU DIDAFTARKAN',
                'color' => 0x3498DB,
                'description' => 'Data pemandu wisata baru telah ditambahkan ke sistem.',
                'target' => 'Master Guide',
            ],
            'UPDATE_GUIDE' => [
                'title' => '📝 DATA PEMANDU DIPERBARUI',
                'color' => 0xF39C12,
                'description' => 'Profil, nomor telepon, atau tarif fee pemandu telah diubah.',
                'target' => 'Master Guide',
            ],
            'SUSPEND_GUIDE' => [
                'title' => '⚠️ PEMANDU DITANGGUHKAN (SUSPEND)',
                'color' => 0xE74C3C,
                'description' => 'Pemandu wisata disuspend sementara oleh Administrator.',
                'target' => 'Master Guide',
            ],
            'UNSUSPEND_GUIDE' => [
                'title' => '🔄 STATUS SUSPEND PEMANDU DICABUT',
                'color' => 0x2ECC71,
                'description' => 'Pemandu wisata telah diaktifkan kembali ke daftar operasional.',
                'target' => 'Master Guide',
            ],
            'DELETE_GUIDE' => [
                'title' => '🗑️ DATA PEMANDU DIHAPUS',
                'color' => 0xE74C3C,
                'description' => 'Data pemandu wisata telah dihapus permanen dari sistem.',
                'target' => 'Master Guide',
            ],
            'REORDER_QUEUE' => [
                'title' => '🔀 ANTREAN PEMANDU DIATUR ULANG',
                'color' => 0x3498DB,
                'description' => 'Urutan giliran antrean pemandu telah diubah manual oleh staf/admin.',
                'target' => 'Antrean Guide',
            ],
            'SET_QUEUE_POSITION' => [
                'title' => '🔢 POSISI POINTER ANTREAN DIUBAH',
                'color' => 0x3498DB,
                'description' => 'Penunjuk nomor antrean aktif berikutnya telah diatur manual.',
                'target' => 'Antrean Guide',
            ],
            'PAYMENT_GUIDE' => [
                'title' => '💰 PEMBAYARAN FEE PEMANDU',
                'color' => 0x2ECC71,
                'description' => 'Pembayaran fee komisi tur pemandu wisata telah berhasil dicatat.',
                'target' => 'Fee Guide',
            ],
            'CREATE_STAFF_PAYROLL' => [
                'title' => '📄 SLIP GAJI STAF DITERBITKAN',
                'color' => 0x3498DB,
                'description' => 'Slip gaji bulanan staf baru telah diterbitkan.',
                'target' => 'Gaji Staf',
            ],
            'UPDATE_STAFF_PAYROLL' => [
                'title' => '✏️ SLIP GAJI STAF DIPERBARUI',
                'color' => 0xF39C12,
                'description' => 'Komponen gaji staf telah diperbarui.',
                'target' => 'Gaji Staf',
            ],
            'PAY_STAFF_PAYROLL' => [
                'title' => '💵 PELUNASAN GAJI STAF',
                'color' => 0x2ECC71,
                'description' => 'Gaji staf telah dibayarkan lunas dan tercatat di pengeluaran kas.',
                'target' => 'Gaji Staf',
            ],
            'DELETE_STAFF_PAYROLL' => [
                'title' => '🗑️ SLIP GAJI STAF DIHAPUS',
                'color' => 0xE74C3C,
                'description' => 'Data slip gaji staf telah dihapus.',
                'target' => 'Gaji Staf',
            ],
            'UPDATE_STAFF_SALARY_SETTING' => [
                'title' => '⚙️ PENGATURAN GAJI STAF DIUBAH',
                'color' => 0xF39C12,
                'description' => 'Standar gaji pokok atau tunjangan staf telah dimodifikasi.',
                'target' => 'Gaji Staf',
            ],
            'CREATE_EXPENSE' => [
                'title' => '📉 PENCATATAN BIAYA OPERASIONAL',
                'color' => 0xF39C12,
                'description' => 'Pengeluaran kas operasional baru telah dicatat.',
                'target' => 'Biaya Operasional',
            ],
            'DELETE_EXPENSE' => [
                'title' => '🗑️ BIAYA OPERASIONAL DIHAPUS',
                'color' => 0xE74C3C,
                'description' => 'Catatan pengeluaran operasional telah dihapus.',
                'target' => 'Biaya Operasional',
            ],
            'CREATE_TOUR_PACKAGE' => [
                'title' => '📦 PAKET WISATA BARU DITAMBAHKAN',
                'color' => 0x3498DB,
                'description' => 'Paket wisata baru telah tersedia di katalog pemesanan.',
                'target' => 'Paket Wisata',
            ],
            'UPDATE_TOUR_PACKAGE' => [
                'title' => '✏️ PAKET WISATA DIPERBARUI',
                'color' => 0xF39C12,
                'description' => 'Harga atau durasi paket wisata telah diubah.',
                'target' => 'Paket Wisata',
            ],
            'DELETE_TOUR_PACKAGE' => [
                'title' => '🗑️ PAKET WISATA DIHAPUS',
                'color' => 0xE74C3C,
                'description' => 'Paket wisata telah dihapus dari katalog.',
                'target' => 'Paket Wisata',
            ],
            'CREATE_ROLE' => [
                'title' => '🛡️ ROLE PENGGUNA BARU DIBUAT',
                'color' => 0x6C63FF,
                'description' => 'Jabatan pengguna baru dengan hak akses granular telah dibuat.',
                'target' => 'Role & Hak Akses',
            ],
            'UPDATE_ROLE_PERMISSIONS' => [
                'title' => '🔑 HAK AKSES ROLE DIPERBARUI',
                'color' => 0x6C63FF,
                'description' => 'Daftar wewenang operasional role telah dimodifikasi.',
                'target' => 'Role & Hak Akses',
            ],
            'DELETE_ROLE' => [
                'title' => '🗑️ ROLE PENGGUNA DIHAPUS',
                'color' => 0xE74C3C,
                'description' => 'Role custom telah dihapus dari sistem.',
                'target' => 'Role & Hak Akses',
            ],
            'UPDATE_SETTINGS' => [
                'title' => '⚙️ PENGATURAN SISTEM DIUBAH',
                'color' => 0x34495E,
                'description' => 'Parameter konfigurasi sistem, logo, atau tarif telah diperbarui.',
                'target' => 'Pengaturan Master',
            ],
            'EXPORT_REPORT' => [
                'title' => '📊 EKSPOR LAPORAN CSV',
                'color' => 0x1ABC9C,
                'description' => 'Data laporan telah diunduh oleh pengguna dalam format CSV.',
                'target' => 'Laporan Operasional',
            ],
            'EXPORT_DASHBOARD' => [
                'title' => '📊 EKSPOR DASHBOARD CSV',
                'color' => 0x1ABC9C,
                'description' => 'Ringkasan operasional dashboard telah diekspor ke format CSV.',
                'target' => 'Dashboard Utama',
            ],
            'OPEN_SHIFT' => [
                'title' => '🟢 LOKET OPERASIONAL RESMI DIBUKA',
                'color' => 0x2ECC71,
                'description' => 'Loket operasional harian telah dibuka.',
                'target' => 'Operasional Loket',
            ],
            'CLOSE_SHIFT' => [
                'title' => '🔴 LOKET OPERASIONAL DITUTUP',
                'color' => 0xE74C3C,
                'description' => 'Loket operasional harian telah resmi ditutup.',
                'target' => 'Operasional Loket',
            ],
        ];

        if (isset($map[$action])) {
            return $map[$action];
        }

        $cleanAction = ucwords(strtolower(str_replace('_', ' ', $action)));
        if (str_contains($action, 'DELETE') || str_contains($action, 'REMOVE')) {
            return [
                'title' => "🗑️ HAPUS DATA: {$cleanAction}",
                'color' => 0xE74C3C,
                'description' => "Operasi penghapusan data ({$action}) telah dilakukan.",
                'target' => 'Sistem Data',
            ];
        } elseif (str_contains($action, 'UPDATE') || str_contains($action, 'EDIT')) {
            return [
                'title' => "✏️ PERBARUI DATA: {$cleanAction}",
                'color' => 0xF39C12,
                'description' => "Operasi perubahan data ({$action}) telah dilakukan.",
                'target' => 'Sistem Data',
            ];
        } elseif (str_contains($action, 'CREATE') || str_contains($action, 'ADD')) {
            return [
                'title' => "➕ TAMBAH DATA: {$cleanAction}",
                'color' => 0x2ECC71,
                'description' => "Operasi penambahan data baru ({$action}) telah dilakukan.",
                'target' => 'Sistem Data',
            ];
        }

        return [
            'title' => "ℹ️ AKTIVITAS SISTEM: {$cleanAction}",
            'color' => 0x3498DB,
            'description' => "Aktivitas ({$action}) telah dicatat ke audit log sistem.",
            'target' => 'Sistem',
        ];
    }

    /**
     * Format values array into readable bullets for Discord embeds.
     */
    protected function formatValuesForDiscord(array $values): string
    {
        $lines = [];
        $count = 0;
        foreach ($values as $k => $v) {
            if (is_array($v)) {
                $vStr = count($v) . ' item [' . implode(', ', array_slice($v, 0, 4)) . (count($v) > 4 ? '...' : '') . ']';
            } elseif (is_bool($v)) {
                $vStr = $v ? 'Ya (True)' : 'Tidak (False)';
            } elseif (is_null($v)) {
                $vStr = '-';
            } else {
                $vStr = (string) $v;
                if (mb_strlen($vStr) > 100) {
                    $vStr = mb_substr($vStr, 0, 97) . '...';
                }
            }
            $cleanKey = ucwords(str_replace(['_', '-'], ' ', $k));
            $lines[] = "• **{$cleanKey}:** `{$vStr}`";
            $count++;
            if ($count >= 8) {
                $remaining = count($values) - 8;
                if ($remaining > 0) {
                    $lines[] = "• *...dan {$remaining} kolom lainnya.*";
                }
                break;
            }
        }
        return implode("\n", $lines);
    }

    /**
     * Shorten User Agent into simple device/browser summary.
     */
    protected function formatUserAgent(?string $ua): string
    {
        if (empty($ua)) {
            return 'Browser Umum';
        }

        $os = 'Lainnya';
        if (str_contains($ua, 'Windows')) $os = 'Windows';
        elseif (str_contains($ua, 'Macintosh') || str_contains($ua, 'Mac OS')) $os = 'Mac';
        elseif (str_contains($ua, 'iPhone')) $os = 'iPhone';
        elseif (str_contains($ua, 'Android')) $os = 'Android';
        elseif (str_contains($ua, 'Linux')) $os = 'Linux';

        $browser = 'Browser';
        if (str_contains($ua, 'Edg/')) $browser = 'Edge';
        elseif (str_contains($ua, 'Chrome/')) $browser = 'Chrome';
        elseif (str_contains($ua, 'Firefox/')) $browser = 'Firefox';
        elseif (str_contains($ua, 'Safari/')) $browser = 'Safari';

        return "`{$browser} / {$os}`";
    }

    /**
     * Send test notification to verify Discord webhook URL.
     */
    public function sendTestNotification(string $webhookUrl, string $type = 'operational'): array
    {
        $appName = Setting::get('company_name', 'ARTDEVATA');
        $currentUser = auth()->check() ? auth()->user()->name : 'Admin';

        if ($type === 'activity') {
            $payload = [
                'username' => "{$appName} Audit Trail",
                'avatar_url' => 'https://cdn-icons-png.flaticon.com/512/942/942748.png',
                'embeds' => [
                    [
                        'title' => 'UJI COBA WEBHOOK DISCORD - AKTIVITAS & AUDIT ARTDEVATA',
                        'description' => "Pesan uji coba berhasil terkirim. Webhook ini terhubung khusus untuk memantau **Aktivitas Login, Logout, Akses Web, dan seluruh operasi CRUD** pada sistem **{$appName}**.",
                        'color' => 0x6C63FF, // Indigo / Purple
                        'fields' => [
                            [
                                'name' => 'Jenis Saluran',
                                'value' => '🛡️ Log Aktivitas, Keamanan & CRUD',
                                'inline' => true,
                            ],
                            [
                                'name' => 'Waktu Pengujian',
                                'value' => Carbon::now()->format('d M Y, H:i:s') . ' WITA',
                                'inline' => true,
                            ],
                            [
                                'name' => 'Penguji',
                                'value' => $currentUser,
                                'inline' => true,
                            ],
                            [
                                'name' => 'Status Koneksi',
                                'value' => '✅ Terhubung & Siap Merekam Log',
                                'inline' => false,
                            ],
                        ],
                        'footer' => [
                            'text' => "{$appName} Management System • Notifikasi Audit Trail & Aktivitas Web",
                        ],
                        'timestamp' => Carbon::now()->toISOString(),
                    ],
                ],
            ];
        } else {
            $payload = [
                'username' => "{$appName} Operasional",
                'avatar_url' => 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png',
                'embeds' => [
                    [
                        'title' => 'UJI COBA WEBHOOK DISCORD - OPERASIONAL LOKET ARTDEVATA',
                        'description' => "Pesan uji coba berhasil terkirim. Webhook ini terhubung khusus untuk **Rekap Pembukaan & Penutupan Loket Harian** pada sistem **{$appName}**.",
                        'color' => 0x3498DB, // Info Blue
                        'fields' => [
                            [
                                'name' => 'Jenis Saluran',
                                'value' => '🏪 Operasional Buka/Tutup Loket',
                                'inline' => true,
                            ],
                            [
                                'name' => 'Waktu Pengujian',
                                'value' => Carbon::now()->format('d M Y, H:i:s') . ' WITA',
                                'inline' => true,
                            ],
                            [
                                'name' => 'Penguji',
                                'value' => $currentUser,
                                'inline' => true,
                            ],
                            [
                                'name' => 'Status Koneksi',
                                'value' => '✅ Terhubung & Siap Menerima Rekap',
                                'inline' => false,
                            ],
                        ],
                        'footer' => [
                            'text' => "{$appName} Management System • Notifikasi Webhook Loket Operasional",
                        ],
                        'timestamp' => Carbon::now()->toISOString(),
                    ],
                ],
            ];
        }

        try {
            $masked = $this->maskUrl($webhookUrl);
            $response = Http::timeout(6)->post($webhookUrl, $payload);
            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => ($type === 'activity' ? 'Webhook Aktivitas & Audit' : 'Webhook Operasional Loket') . ' berhasil terhubung ke Discord!',
                ];
            }

            return [
                'success' => false,
                'message' => 'Discord menolak permintaan: ' . $response->status() . ' - ' . $response->body(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal menghubungi Discord: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Perform HTTP POST request to Discord Webhook with error catching, rate-limit retry, and URL masking.
     */
    protected function postWebhook(string $url, array $payload): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            Log::warning('Discord Webhook URL tidak valid: ' . $this->maskUrl($url));
            return false;
        }

        // Sanitize embed fields so none exceed Discord's 1024 char limit
        if (!empty($payload['embeds'])) {
            foreach ($payload['embeds'] as &$embed) {
                if (!empty($embed['fields'])) {
                    foreach ($embed['fields'] as &$field) {
                        if (isset($field['value']) && mb_strlen($field['value']) > 1000) {
                            $field['value'] = mb_substr($field['value'], 0, 996) . '...';
                        }
                    }
                }
            }
        }

        $maskedUrl = $this->maskUrl($url);

        try {
            $response = Http::timeout(6)->post($url, $payload);

            // Handle HTTP 429 Rate Limiting with single safe retry
            if ($response->status() === 429) {
                $retryAfter = (int) $response->header('Retry-After', 1);
                $delay = min(2, max(1, $retryAfter));
                sleep($delay);
                $response = Http::timeout(6)->post($url, $payload);
            }

            if (!$response->successful()) {
                Log::warning('Discord Webhook notification failed', [
                    'url' => $maskedUrl,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (Exception $e) {
            Log::error('Exception while posting to Discord Webhook: ' . $e->getMessage(), [
                'url' => $maskedUrl,
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Mask secret token from webhook URL for safe logging.
     */
    protected function maskUrl(string $url): string
    {
        return preg_replace('/(\/api\/webhooks\/\d+\/)[^\/\s]+/', '$1***', $url) ?? $url;
    }
}
