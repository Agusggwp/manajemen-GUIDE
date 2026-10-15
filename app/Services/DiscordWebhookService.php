<?php

namespace App\Services;

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
     * Check if Discord webhook notifications are configured in .env.
     */
    public function isEnabled(): bool
    {
        return !empty($this->getWebhookUrl());
    }

    /**
     * Get configured Discord Webhook URL from .env / config.
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
                    'description' => "Loket operasional **{$appName}** telah dibuka. Pemandu wisata (guide) sekarang dapat melakukan absensi check-in dan sistem siap menerima pesanan tur.",
                    'color' => 0x2ECC71, // Emerald Green
                    'fields' => [
                        [
                            'name' => 'Tanggal Operasional',
                            'value' => $dateFormatted,
                            'inline' => true,
                        ],
                        [
                            'name' => 'Waktu Buka',
                            'value' => $timeNow,
                            'inline' => true,
                        ],
                        [
                            'name' => 'Dibuka Oleh',
                            'value' => $openedBy,
                            'inline' => true,
                        ],
                        [
                            'name' => 'Guide Terdaftar',
                            'value' => "{$activeGuidesTotal} Guide Aktif",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Urutan Antrean',
                            'value' => "Posisi Antrean: #{$queueState->current_position}",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Mode Penutupan',
                            'value' => $autoScheduleText,
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
        $packagesText = !empty($packagesLines) ? implode("\n", array_slice($packagesLines, 0, 5)) : "Tidak ada tur terjual";
        if (count($packagesLines) > 5) {
            $packagesText .= "\n• ...dan " . (count($packagesLines) - 5) . " paket lainnya";
        }

        // 7. Guide Attendances
        $attendances = GuideAttendance::where('attendance_date', $targetDate)
            ->with('guide')
            ->get();
        $totalGuidesAttended = $attendances->count();
        $manualCheckedOut = $attendances->where('status', 'CHECKED_OUT')->count() - $autoCheckedOutCount;
        if ($manualCheckedOut < 0) {
            $manualCheckedOut = 0;
        }

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
                           . "• Auto Checkout: **{$autoCheckedOutCount} Guide**\n"
                           . "• Checkout Mandiri: **{$manualCheckedOut} Guide**\n"
                           . "• Daftar Pemandu: {$guideNamesText}";

        $payload = [
            'username' => "{$appName} Operasional",
            'avatar_url' => 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png',
            'embeds' => [
                [
                    'title' => 'REKAP OPERASIONAL LOKET DITUTUP',
                    'description' => "Laporan resmi penutupan shift loket kasir & operasional **{$appName}** pada tanggal **{$dateFormatted}**.\nSemua pemandu aktif telah otomatis di-checkout oleh sistem.",
                    'color' => 0xE74C3C, // Alizarin Red
                    'fields' => [
                        [
                            'name' => 'Tanggal & Waktu Tutup',
                            'value' => "{$dateFormatted}\nPukul **{$timeClosed}**",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Ditutup Oleh',
                            'value' => "{$closedBy}\n_{$reason}_",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Durasi Operasional',
                            'value' => $durationText,
                            'inline' => true,
                        ],
                        [
                            'name' => 'Wisatawan (Pax Laku)',
                            'value' => "**{$totalPax} Orang (Pax)**\nTotal: **{$totalBookingsCount} Tur** ({$walkinCount} Walk-in / {$reservationCount} Reservasi)",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Total Uang Masuk (Omset)',
                            'value' => "Diterima: **{$fmtRp($totalAmountPaid)}**\nTagihan: **{$fmtRp($totalRevenue)}**",
                            'inline' => true,
                        ],
                        [
                            'name' => 'Estimasi Laba Bersih',
                            'value' => "**{$fmtRp($netEstimatedProfit)}**\n_(Omset - Fee Guide - Operasional)_",
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
                            'inline' => true,
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
     * Send test notification to verify Discord webhook URL.
     */
    public function sendTestNotification(string $webhookUrl): array
    {
        $appName = Setting::get('company_name', 'ARTDEVATA');
        $currentUser = auth()->check() ? auth()->user()->name : 'Admin';

        $payload = [
            'username' => "{$appName} Operasional",
            'avatar_url' => 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png',
            'embeds' => [
                [
                    'title' => 'UJI COBA WEBHOOK DISCORD ARTDEVATA',
                    'description' => "Pesan uji coba berhasil terkirim. Sistem notifikasi Webhook Discord untuk **{$appName}** telah terhubung dengan baik.",
                    'color' => 0x3498DB, // Info Blue
                    'fields' => [
                        [
                            'name' => 'Status Koneksi',
                            'value' => 'Terhubung & Siap Digunakan',
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
                    ],
                    'footer' => [
                        'text' => "{$appName} Management System • Notifikasi Discord Webhook",
                    ],
                    'timestamp' => Carbon::now()->toISOString(),
                ],
            ],
        ];

        try {
            $masked = $this->maskUrl($webhookUrl);
            $response = Http::timeout(6)->post($webhookUrl, $payload);
            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Notifikasi uji coba berhasil dikirim ke channel Discord!',
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
