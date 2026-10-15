<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Guide;
use App\Models\GuideAssignment;
use App\Models\GuideAttendance;
use App\Models\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class GuideAttendanceService
{
    /**
     * Guide Check-In.
     */
    public function checkIn(
        Guide $guide,
        ?string $date = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $notes = null,
        bool $isForce = false
    ): GuideAttendance {
        return DB::transaction(function () use ($guide, $date, $latitude, $longitude, $notes, $isForce) {
            $attendanceDate = $date ?? Carbon::today()->toDateString();

            if ($guide->status === 'SUSPENDED') {
                $reason = $guide->suspend_reason ? " (Alasan: {$guide->suspend_reason})" : '';
                throw new Exception("Guide {$guide->nama} sedang ditangguhkan (SUSPENDED){$reason}. Tidak dapat melakukan absensi.");
            }

            if (!$isForce && $guide->status === 'INACTIVE') {
                throw new Exception("Guide dalam status tidak aktif.");
            }

            // Check if operational counter is currently open (unless forced by admin/system)
            if (!$isForce) {
                $shiftService = app(OperationalShiftService::class);
                if (!$shiftService->isOpen($attendanceDate)) {
                    throw new Exception("Loket operasional saat ini sedang TUTUP. Pemandu (Guide) belum dapat melakukan check-in sampai operasional dibuka oleh staf/admin.");
                }
            }

            // Check if GPS is required by settings
            $gpsRequired = Setting::get('gps_required', '0') === '1';
            if ($gpsRequired && (!$latitude || !$longitude)) {
                throw new Exception("Lokasi GPS wajib diaktifkan untuk melakukan absensi.");
            }

            $attendance = GuideAttendance::firstOrNew([
                'guide_id' => $guide->id,
                'attendance_date' => $attendanceDate,
            ]);

            if ($attendance->exists && $attendance->status === 'CHECKED_IN' && !$isForce) {
                $checkInTime = $attendance->check_in_at ? $attendance->check_in_at->format('H:i') : '-';
                throw new Exception("Anda sudah melakukan check-in hari ini pada pukul {$checkInTime} WITA.");
            }

            $oldStatus = $attendance->status;

            $attendance->check_in_at = $attendance->check_in_at ?? now();
            $attendance->check_out_at = null;
            $attendance->status = 'CHECKED_IN';
            $attendance->notes = $notes ?? $attendance->notes;

            if ($latitude !== null && $longitude !== null) {
                $attendance->check_in_latitude = $latitude;
                $attendance->check_in_longitude = $longitude;
            }

            $attendance->save();

            // Update guide real-time status if not currently ON_TOUR and not SUSPENDED
            if ($guide->status !== 'ON_TOUR' && $guide->status !== 'SUSPENDED') {
                $guide->status = 'AVAILABLE';
                $guide->save();
            }

            AuditLog::log(
                action: $isForce ? 'FORCE_CHECK_IN' : 'CHECK_IN',
                model: GuideAttendance::class,
                modelId: $attendance->id,
                oldValues: ['status' => $oldStatus],
                newValues: [
                    'guide_id' => $guide->id,
                    'status' => 'CHECKED_IN',
                    'check_in_at' => $attendance->check_in_at->toDateTimeString(),
                ]
            );

            return $attendance;
        });
    }

    /**
     * Guide Check-Out.
     */
    public function checkOut(
        Guide $guide,
        ?string $date = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $notes = null,
        bool $isForce = false
    ): GuideAttendance {
        return DB::transaction(function () use ($guide, $date, $latitude, $longitude, $notes, $isForce) {
            $attendanceDate = $date ?? Carbon::today()->toDateString();

            // Check if guide has ongoing tours
            $hasActiveTour = GuideAssignment::where('guide_id', $guide->id)
                ->where('status', 'ON_GOING')
                ->exists();

            if ($hasActiveTour && !$isForce) {
                throw new Exception("Anda masih memiliki tour aktif. Selesaikan tour terlebih dahulu sebelum checkout.");
            }

            $attendance = GuideAttendance::where('guide_id', $guide->id)
                ->where('attendance_date', $attendanceDate)
                ->first();

            if (!$attendance && !$isForce) {
                throw new Exception("Guide belum melakukan check-in hari ini.");
            }

            if ($attendance && $attendance->status === 'CHECKED_OUT' && !$isForce) {
                $checkOutTime = $attendance->check_out_at ? $attendance->check_out_at->format('H:i') : '-';
                throw new Exception("Anda sudah melakukan checkout hari ini pada pukul {$checkOutTime} WITA.");
            }

            if (!$attendance) {
                $attendance = new GuideAttendance([
                    'guide_id' => $guide->id,
                    'attendance_date' => $attendanceDate,
                    'check_in_at' => now(),
                ]);
            }

            $oldStatus = $attendance->status;

            $now = now();
            if ($attendance->check_in_at && $now->lt($attendance->check_in_at)) {
                $now = $attendance->check_in_at->copy();
            }

            $attendance->check_out_at = $now;
            $attendance->status = 'CHECKED_OUT';
            if ($notes) {
                $attendance->notes = $attendance->notes ? $attendance->notes . ' | ' . $notes : $notes;
            }

            if ($latitude !== null && $longitude !== null) {
                $attendance->check_out_latitude = $latitude;
                $attendance->check_out_longitude = $longitude;
            }

            $attendance->save();

            // Set guide master status to AVAILABLE or checked out state
            if ($guide->status === 'ON_TOUR' && $isForce) {
                // If forced, complete active assignments
                GuideAssignment::where('guide_id', $guide->id)
                    ->where('status', 'ON_GOING')
                    ->update([
                        'status' => 'COMPLETED',
                        'completed_at' => now(),
                    ]);
            }

            if ($guide->status !== 'SUSPENDED') {
                $guide->status = 'AVAILABLE';
                $guide->save();
            }

            AuditLog::log(
                action: $isForce ? 'FORCE_CHECK_OUT' : 'CHECK_OUT',
                model: GuideAttendance::class,
                modelId: $attendance->id,
                oldValues: ['status' => $oldStatus],
                newValues: [
                    'guide_id' => $guide->id,
                    'status' => 'CHECKED_OUT',
                    'check_out_at' => $attendance->check_out_at->toDateTimeString(),
                ]
            );

            return $attendance;
        });
    }

    /**
     * Start Tour action by Guide.
     */
    public function startTour(GuideAssignment $assignment, Guide $guide): void
    {
        DB::transaction(function () use ($assignment, $guide) {
            if ($assignment->guide_id !== $guide->id) {
                throw new Exception("Anda tidak memiliki hak akses untuk tour ini.");
            }

            if ($assignment->status === 'CANCELLED' || $assignment->booking?->status === 'CANCELLED') {
                throw new Exception("Tour ini telah dibatalkan.");
            }

            if ($assignment->status === 'COMPLETED') {
                throw new Exception("Tour sudah selesai.");
            }

            $assignment->status = 'ON_GOING';
            $assignment->started_at = now();
            $assignment->save();

            $guide->status = 'ON_TOUR';
            $guide->save();

            $booking = $assignment->booking;
            $booking->status = 'ON_GOING';
            $booking->save();

            AuditLog::log('START_TOUR', GuideAssignment::class, $assignment->id, null, ['status' => 'ON_GOING']);
        });
    }

    /**
     * Complete Tour action by Guide.
     */
    public function completeTour(GuideAssignment $assignment, Guide $guide): void
    {
        DB::transaction(function () use ($assignment, $guide) {
            if ($assignment->guide_id !== $guide->id) {
                throw new Exception("Anda tidak memiliki hak akses untuk tour ini.");
            }

            if ($assignment->status === 'CANCELLED' || $assignment->booking?->status === 'CANCELLED') {
                throw new Exception("Tour ini telah dibatalkan.");
            }

            if ($assignment->status === 'COMPLETED') {
                throw new Exception("Tour sudah selesai.");
            }

            $assignment->status = 'COMPLETED';
            $assignment->completed_at = now();
            $assignment->save();

            // Check if guide has other ongoing assignments right now
            $otherOnGoing = GuideAssignment::where('guide_id', $guide->id)
                ->where('status', 'ON_GOING')
                ->where('id', '!=', $assignment->id)
                ->exists();

            if (!$otherOnGoing) {
                $guide->status = 'AVAILABLE';
                $guide->save();
            }

            $booking = $assignment->booking;
            if ($booking) {
                $allCompleted = !$booking->assignments()->whereNotIn('status', ['COMPLETED', 'CANCELLED'])->exists();
                if ($allCompleted && $booking->assignedGuestsCount() >= $booking->guest_count) {
                    $booking->status = 'COMPLETED';
                    $booking->save();
                }

                $booking->recalculateTotals();
            }

            AuditLog::log('COMPLETE_TOUR', GuideAssignment::class, $assignment->id, null, ['status' => 'COMPLETED']);
        });
    }
}
