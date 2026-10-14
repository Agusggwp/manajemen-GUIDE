<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\GuideAttendance;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OperationalShiftService
{
    /**
     * Determine if operational counter is currently OPEN.
     */
    public function isOpen(?string $date = null): bool
    {
        $targetDate = $date ?? Carbon::today()->toDateString();
        $isToday = ($targetDate === Carbon::today()->toDateString());

        // Check if there is a manual override for this specific date
        $manualStatus = Setting::get('operational_manual_status_' . $targetDate);
        if ($manualStatus === 'OPEN') {
            return true;
        }
        if ($manualStatus === 'CLOSED') {
            return false;
        }

        // If no manual status for this date, check if auto-schedule is enabled
        $autoEnabled = Setting::get('operational_auto_schedule_enabled', '0') === '1';
        if ($autoEnabled && $isToday) {
            $openTime = Setting::get('operational_auto_open_time', '07:00');
            $closeTime = Setting::get('operational_auto_close_time', '18:00');
            $currentTime = Carbon::now()->format('H:i');

            if ($currentTime >= $openTime && $currentTime < $closeTime) {
                return true;
            }

            // If time is past close time, check if auto-close needs to execute
            if ($currentTime >= $closeTime) {
                $this->checkAndRunAutoClose($targetDate);
                return false;
            }

            // Before open time
            return false;
        }

        // Check general operational_status setting (manual mode)
        $generalStatus = Setting::get('operational_status', 'CLOSED');
        if ($generalStatus === 'OPEN') {
            return true;
        }

        // If any guides are still checked in and not checked out, counter is considered active
        if (GuideAttendance::where('status', 'CHECKED_IN')->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Get detailed status of the operational shift.
     */
    public function getStatus(?string $date = null): array
    {
        $targetDate = $date ?? Carbon::today()->toDateString();
        $open = $this->isOpen($targetDate);

        // Count ALL active guides who haven't checked out yet across all dates
        $activeGuidesCount = GuideAttendance::where('status', 'CHECKED_IN')->count();

        $autoEnabled = Setting::get('operational_auto_schedule_enabled', '0') === '1';
        $openTime = Setting::get('operational_auto_open_time', '07:00');
        $closeTime = Setting::get('operational_auto_close_time', '18:00');

        return [
            'is_open' => $open,
            'status' => $open ? 'OPEN' : 'CLOSED',
            'status_label' => $open ? 'Loket Buka' : 'Loket Tutup',
            'auto_schedule_enabled' => $autoEnabled,
            'auto_open_time' => $openTime,
            'auto_close_time' => $closeTime,
            'opened_at' => Setting::get('operational_opened_at'),
            'opened_by' => Setting::get('operational_opened_by'),
            'closed_at' => Setting::get('operational_closed_at'),
            'closed_by' => Setting::get('operational_closed_by'),
            'active_guides_count' => $activeGuidesCount,
        ];
    }

    /**
     * Manually Open the Operational Shift.
     */
    public function openShift(?User $user = null, ?string $date = null): array
    {
        $today = Carbon::today()->toDateString();
        $targetDate = $date ?? $today;

        Setting::set('operational_manual_status_' . $today, 'OPEN');
        if ($targetDate !== $today) {
            Setting::set('operational_manual_status_' . $targetDate, 'OPEN');
        }
        Setting::set('operational_status', 'OPEN');
        Setting::set('operational_status_date', $today);
        Setting::set('operational_opened_at', Carbon::now()->toDateTimeString());
        Setting::set('operational_opened_by', $user ? $user->name : 'Staff / Admin');

        AuditLog::log(
            action: 'OPEN_OPERATIONAL_SHIFT',
            model: Setting::class,
            modelId: null,
            oldValues: ['status' => 'CLOSED'],
            newValues: [
                'status' => 'OPEN',
                'opened_by' => $user ? $user->name : 'Staff / Admin',
                'date' => $targetDate,
            ]
        );

        $result = [
            'status' => 'OPEN',
            'message' => 'Loket operasional berhasil DIBUKA. Pemandu wisata (guide) sekarang dapat melakukan absensi check-in.',
        ];

        // Send Discord Webhook notification if enabled
        try {
            app(DiscordWebhookService::class)->sendShiftOpened($this->getStatus($targetDate), $user, $targetDate);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Discord Webhook on openShift failed: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Manually or Automatically Close the Operational Shift.
     * Automatically checks out ALL checked-in guides (force checkout)!
     */
    public function closeShift(?User $user = null, string $reason = 'Penutupan operasional', ?string $date = null): array
    {
        $today = Carbon::today()->toDateString();
        $targetDate = $date ?? $today;

        $result = DB::transaction(function () use ($user, $reason, $today, $targetDate) {
            Setting::set('operational_manual_status_' . $today, 'CLOSED');
            if ($targetDate !== $today) {
                Setting::set('operational_manual_status_' . $targetDate, 'CLOSED');
            }
            $statusDate = Setting::get('operational_status_date');
            if ($statusDate && $statusDate !== $today && $statusDate !== $targetDate) {
                Setting::set('operational_manual_status_' . $statusDate, 'CLOSED');
            }
            Setting::set('operational_status', 'CLOSED');
            Setting::set('operational_status_date', $today);
            Setting::set('operational_closed_at', Carbon::now()->toDateTimeString());
            Setting::set('operational_closed_by', $user ? $user->name : 'Sistem Otomatis');

            // Find ALL guides currently checked in who haven't checked out yet
            $activeAttendances = GuideAttendance::where('status', 'CHECKED_IN')
                ->with('guide')
                ->get();

            $autoCheckoutCount = 0;
            $closedByText = $user ? $user->name : 'Sistem Otomatis';

            foreach ($activeAttendances as $attendance) {
                $attendance->check_out_at = Carbon::now();
                $attendance->status = 'CHECKED_OUT';
                $attendance->notes = ($attendance->notes ? $attendance->notes . ' | ' : '')
                    . "Otomatis check-out saat operasional ditutup oleh {$closedByText} ({$reason})";
                $attendance->save();

                if ($attendance->guide && $attendance->guide->status !== 'SUSPENDED') {
                    // Complete any ongoing tour assignments if any
                    \App\Models\GuideAssignment::where('guide_id', $attendance->guide->id)
                        ->where('status', 'ON_GOING')
                        ->update([
                            'status' => 'COMPLETED',
                            'completed_at' => Carbon::now(),
                        ]);

                    $attendance->guide->status = 'AVAILABLE';
                    $attendance->guide->save();
                }

                $autoCheckoutCount++;
            }

            AuditLog::log(
                action: 'CLOSE_OPERATIONAL_SHIFT',
                model: Setting::class,
                modelId: null,
                oldValues: ['status' => 'OPEN'],
                newValues: [
                    'status' => 'CLOSED',
                    'closed_by' => $closedByText,
                    'reason' => $reason,
                    'auto_checked_out_guides' => $autoCheckoutCount,
                    'date' => $targetDate,
                ]
            );

            return [
                'status' => 'CLOSED',
                'auto_checked_out_count' => $autoCheckoutCount,
                'message' => "Loket operasional berhasil DITUTUP. Sebanyak {$autoCheckoutCount} pemandu (guide) yang masih aktif telah otomatis di-checkout.",
            ];
        });

        // Send Discord Webhook notification with complete daily operational & financial summary
        try {
            app(DiscordWebhookService::class)->sendShiftClosed($result, $user, $reason, $targetDate);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Discord Webhook on closeShift failed: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Check and run auto-close checkout if scheduled closing time has passed.
     */
    public function checkAndRunAutoClose(?string $date = null): bool
    {
        $today = $date ?? Carbon::today()->toDateString();
        $autoCloseExecuted = Setting::get('operational_auto_close_ran_' . $today, '0') === '1';

        if (!$autoCloseExecuted) {
            Setting::set('operational_auto_close_ran_' . $today, '1');
            $this->closeShift(null, 'Jadwal tutup otomatis sistem');
            return true;
        }

        return false;
    }
}
