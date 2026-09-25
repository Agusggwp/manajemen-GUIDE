<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Guide;
use App\Models\GuideAssignment;
use App\Models\GuideQueueState;
use App\Models\QueueHistory;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class GuideQueueService
{
    /**
     * Get the next available guide according to the circular continuous queue.
     * Skips guides who are OFF, INACTIVE, NOT_CHECKED_IN, CHECKED_OUT, ON_TOUR,
     * or have a conflicting booking schedule.
     */
    public function findNextAvailableGuide(
        ?Booking $booking = null,
        ?string $date = null,
        ?string $startTime = null,
        ?string $endTime = null,
        array $excludeGuideIds = []
    ): array {
        $state = GuideQueueState::getState();
        $previousPosition = (int) $state->current_position;

        // Fetch all active guides ordered by queue_number
        $allGuides = Guide::whereNotIn('status', ['INACTIVE', 'SUSPENDED'])
            ->orderBy('queue_number', 'asc')
            ->get();

        if ($allGuides->isEmpty()) {
            return [
                'guide' => null,
                'skipped' => [],
                'previous_position' => $previousPosition,
                'new_position' => null,
                'message' => 'Belum ada guide yang terdaftar.',
            ];
        }

        $targetDate = $date ?? ($booking ? (is_string($booking->booking_date) ? substr($booking->booking_date, 0, 10) : $booking->booking_date->format('Y-m-d')) : Carbon::today()->format('Y-m-d'));
        $targetStartTime = $startTime ?? ($booking ? $booking->start_time : null);
        $targetEndTime = $endTime ?? ($booking ? $booking->end_time : null);

        // Build the circular search sequence starting immediately AFTER previousPosition
        // Guides with queue_number > previousPosition, followed by guides with queue_number <= previousPosition
        $higherQueueGuides = $allGuides->filter(fn($g) => $g->queue_number > $previousPosition)->values();
        $wrapAroundGuides = $allGuides->filter(fn($g) => $g->queue_number <= $previousPosition)->values();

        $searchSequence = $higherQueueGuides->concat($wrapAroundGuides);

        $skipped = [];
        $selectedGuide = null;

        foreach ($searchSequence as $candidate) {
            // If already excluded (e.g. already assigned to this booking in a multi-guide operation)
            if (in_array($candidate->id, $excludeGuideIds)) {
                $skipped[] = [
                    'guide_id' => $candidate->id,
                    'queue_number' => $candidate->queue_number,
                    'nama' => $candidate->nama,
                    'reason' => 'Sudah dialokasikan pada booking ini',
                ];
                continue;
            }

            // Check guide permanent/master status
            if ($candidate->status === 'OFF') {
                $skipped[] = [
                    'guide_id' => $candidate->id,
                    'queue_number' => $candidate->queue_number,
                    'nama' => $candidate->nama,
                    'reason' => 'OFF',
                ];
                continue;
            }

            if ($candidate->status === 'INACTIVE') {
                $skipped[] = [
                    'guide_id' => $candidate->id,
                    'queue_number' => $candidate->queue_number,
                    'nama' => $candidate->nama,
                    'reason' => 'INACTIVE',
                ];
                continue;
            }

            if ($candidate->status === 'SUSPENDED') {
                $skipped[] = [
                    'guide_id' => $candidate->id,
                    'queue_number' => $candidate->queue_number,
                    'nama' => $candidate->nama,
                    'reason' => 'SUSPENDED: ' . ($candidate->suspend_reason ?: 'Ditangguhkan'),
                ];
                continue;
            }

            // Check today's attendance for the target date
            $attendance = $candidate->attendances()
                ->whereDate('attendance_date', $targetDate)
                ->first();

            if (!$attendance || $attendance->status !== 'CHECKED_IN') {
                $reason = 'Belum Check-in';
                if ($attendance) {
                    if ($attendance->status === 'CHECKED_OUT') {
                        $reason = 'Sudah Checkout';
                    } elseif ($attendance->status === 'OFF') {
                        $reason = 'OFF Hari Ini';
                    }
                }
                $skipped[] = [
                    'guide_id' => $candidate->id,
                    'queue_number' => $candidate->queue_number,
                    'nama' => $candidate->nama,
                    'reason' => $reason,
                ];
                continue;
            }

            // Check real-time tour status if checking for current moment
            if ($targetDate === Carbon::today()->format('Y-m-d') && $candidate->status === 'ON_TOUR' && !$targetStartTime) {
                $skipped[] = [
                    'guide_id' => $candidate->id,
                    'queue_number' => $candidate->queue_number,
                    'nama' => $candidate->nama,
                    'reason' => 'Sedang On Tour',
                ];
                continue;
            }

            // Check schedule conflict with existing active assignments
            if ($targetStartTime && $targetEndTime) {
                $hasConflict = $this->hasScheduleConflict($candidate->id, $targetDate, $targetStartTime, $targetEndTime, $booking ? $booking->id : null);
                if ($hasConflict) {
                    $skipped[] = [
                        'guide_id' => $candidate->id,
                        'queue_number' => $candidate->queue_number,
                        'nama' => $candidate->nama,
                        'reason' => 'Jadwal Bertabrakan',
                    ];
                    continue;
                }
            }

            // Eligible guide found!
            $selectedGuide = $candidate;
            break;
        }

        return [
            'guide' => $selectedGuide,
            'skipped' => $skipped,
            'previous_position' => $previousPosition,
            'new_position' => $selectedGuide ? $selectedGuide->queue_number : null,
            'message' => $selectedGuide ? 'Guide tersedia' : 'Belum ada guide yang tersedia.',
        ];
    }

    /**
     * Check if a guide has any overlapping active bookings on the given date.
     */
    public function hasScheduleConflict(int $guideId, string $date, string $startTime, string $endTime, ?int $ignoreBookingId = null): bool
    {
        $query = GuideAssignment::where('guide_id', $guideId)
            ->whereIn('status', ['ASSIGNED', 'ON_GOING'])
            ->whereHas('booking', function ($q) use ($date, $startTime, $endTime, $ignoreBookingId) {
                $q->whereDate('booking_date', $date)
                    ->where('status', '!=', 'CANCELLED');
                if ($ignoreBookingId) {
                    $q->where('id', '!=', $ignoreBookingId);
                }
                // Time overlap check: start1 < end2 AND end1 > start2
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            });

        return $query->exists();
    }

    /**
     * Execute Auto Assign Guide to a booking with transaction safety and row-locking.
     */
    public function autoAssign(Booking $booking, int $guestCount, ?User $assignedBy = null): GuideAssignment
    {
        return DB::transaction(function () use ($booking, $guestCount, $assignedBy) {
            // Lock queue state for update to eliminate race condition
            $state = GuideQueueState::where('id', 1)->lockForUpdate()->first();
            if (!$state) {
                $state = GuideQueueState::create(['id' => 1, 'current_position' => 0]);
            }

            $alreadyAssignedCount = $booking->assignedGuestsCount();
            if (($alreadyAssignedCount + $guestCount) > $booking->guest_count) {
                throw new Exception("Jumlah tamu yang dialokasikan ({$guestCount}) melebihi sisa tamu booking (" . ($booking->guest_count - $alreadyAssignedCount) . ").");
            }

            // Fetch already assigned guide IDs on this booking
            $existingGuideIds = $booking->assignments()
                ->where('status', '!=', 'CANCELLED')
                ->pluck('guide_id')
                ->toArray();

            $bookingDate = is_string($booking->booking_date) ? substr($booking->booking_date, 0, 10) : $booking->booking_date->format('Y-m-d');
            $searchResult = $this->findNextAvailableGuide(
                $booking,
                $bookingDate,
                $booking->start_time,
                $booking->end_time,
                $existingGuideIds
            );

            /** @var Guide|null $guide */
            $guide = $searchResult['guide'];
            if (!$guide) {
                throw new Exception("Belum ada guide yang tersedia.");
            }

            // Calculate guide fee snapshot
            $feeCalculation = $guide->calculateFee($guestCount);

            $assignment = GuideAssignment::create([
                'booking_id' => $booking->id,
                'guide_id' => $guide->id,
                'guest_count' => $guestCount,
                'fee_type' => $feeCalculation['type'],
                'fee_rate' => $feeCalculation['rate'],
                'fee_amount' => $feeCalculation['amount'],
                'queue_position' => $guide->queue_number,
                'assigned_at' => now(),
                'status' => 'ASSIGNED',
                'assigned_by' => $assignedBy ? $assignedBy->id : auth()->id(),
            ]);

            // Update queue state with new rotation position
            $previousPosition = (int) $state->current_position;
            $state->current_position = (int) $guide->queue_number;
            $state->last_guide_id = $guide->id;
            $state->last_assignment_id = $assignment->id;
            $state->save();

            // Record rotation audit history
            QueueHistory::create([
                'previous_position' => $previousPosition,
                'next_position' => $guide->queue_number,
                'selected_guide_id' => $guide->id,
                'skipped_guides' => $searchResult['skipped'],
                'booking_id' => $booking->id,
                'assigned_by' => $assignedBy ? $assignedBy->id : auth()->id(),
                'type' => 'AUTO',
                'notes' => "Auto assign {$guestCount} tamu ke Guide {$guide->nama} (#{$guide->queue_number})",
                'created_at' => now(),
            ]);

            // Audit log
            AuditLog::log(
                action: 'AUTO_ASSIGN_GUIDE',
                model: GuideAssignment::class,
                modelId: $assignment->id,
                newValues: [
                    'booking_id' => $booking->id,
                    'guide_id' => $guide->id,
                    'guest_count' => $guestCount,
                    'fee_amount' => $feeCalculation['amount'],
                    'queue_position' => $guide->queue_number,
                ]
            );

            $booking->recalculateTotals();

            return $assignment;
        });
    }

    /**
     * Auto assign multiple guides sequentially following circular queue.
     */
    public function autoAssignMultiple(Booking $booking, array $guestAllocations, ?User $assignedBy = null): array
    {
        return DB::transaction(function () use ($booking, $guestAllocations, $assignedBy) {
            $totalAllocated = array_sum($guestAllocations);
            $alreadyAssignedCount = $booking->assignedGuestsCount();

            if (($alreadyAssignedCount + $totalAllocated) > $booking->guest_count) {
                throw new Exception("Jumlah tamu yang dialokasikan melebihi jumlah tamu booking.");
            }

            $assignments = [];
            foreach ($guestAllocations as $guests) {
                if ($guests <= 0) {
                    continue;
                }
                $assignments[] = $this->autoAssign($booking, $guests, $assignedBy);
            }

            return $assignments;
        });
    }

    /**
     * Manual Guide Assignment by Admin/Staff with full validation.
     */
    public function manualAssign(Booking $booking, Guide $guide, int $guestCount, ?User $assignedBy = null): GuideAssignment
    {
        return DB::transaction(function () use ($booking, $guide, $guestCount, $assignedBy) {
            $alreadyAssignedCount = $booking->assignedGuestsCount();
            if (($alreadyAssignedCount + $guestCount) > $booking->guest_count) {
                throw new Exception("Jumlah tamu yang dialokasikan ({$guestCount}) melebihi sisa tamu booking (" . ($booking->guest_count - $alreadyAssignedCount) . ").");
            }

            // Check if already assigned
            $alreadyAssigned = $booking->assignments()
                ->where('guide_id', $guide->id)
                ->where('status', '!=', 'CANCELLED')
                ->exists();
            if ($alreadyAssigned) {
                throw new Exception("Guide {$guide->nama} sudah ditugaskan pada booking ini.");
            }

            // Check guide master status
            if ($guide->status === 'OFF' || $guide->status === 'INACTIVE' || $guide->status === 'SUSPENDED') {
                $reason = $guide->status === 'SUSPENDED' ? ' (' . ($guide->suspend_reason ?: 'Ditangguhkan') . ')' : '';
                throw new Exception("Guide {$guide->nama} sedang dalam status {$guide->status}{$reason}.");
            }

            // Check attendance for booking date
            $targetDate = is_string($booking->booking_date) ? substr($booking->booking_date, 0, 10) : $booking->booking_date->format('Y-m-d');
            $attendance = $guide->attendances()->whereDate('attendance_date', $targetDate)->first();
            if (!$attendance || $attendance->status !== 'CHECKED_IN') {
                throw new Exception("Guide {$guide->nama} belum melakukan check-in pada tanggal booking ({$targetDate}).");
            }

            // Check schedule conflict
            if ($this->hasScheduleConflict($guide->id, $targetDate, $booking->start_time, $booking->end_time, $booking->id)) {
                throw new Exception("Guide {$guide->nama} memiliki jadwal yang bertabrakan.");
            }

            $feeCalculation = $guide->calculateFee($guestCount);

            $assignment = GuideAssignment::create([
                'booking_id' => $booking->id,
                'guide_id' => $guide->id,
                'guest_count' => $guestCount,
                'fee_type' => $feeCalculation['type'],
                'fee_rate' => $feeCalculation['rate'],
                'fee_amount' => $feeCalculation['amount'],
                'queue_position' => $guide->queue_number,
                'assigned_at' => now(),
                'status' => 'ASSIGNED',
                'assigned_by' => $assignedBy ? $assignedBy->id : auth()->id(),
            ]);

            // Update queue position state so manual assignment also synchronizes queue position
            $state = GuideQueueState::where('id', 1)->lockForUpdate()->first();
            if ($state) {
                $previousPosition = (int) $state->current_position;
                $state->current_position = (int) $guide->queue_number;
                $state->last_guide_id = $guide->id;
                $state->last_assignment_id = $assignment->id;
                $state->save();

                QueueHistory::create([
                    'previous_position' => $previousPosition,
                    'next_position' => $guide->queue_number,
                    'selected_guide_id' => $guide->id,
                    'skipped_guides' => [],
                    'booking_id' => $booking->id,
                    'assigned_by' => $assignedBy ? $assignedBy->id : auth()->id(),
                    'type' => 'MANUAL',
                    'notes' => "Manual assign {$guestCount} tamu ke Guide {$guide->nama} (#{$guide->queue_number})",
                    'created_at' => now(),
                ]);
            }

            AuditLog::log(
                action: 'MANUAL_ASSIGN_GUIDE',
                model: GuideAssignment::class,
                modelId: $assignment->id,
                newValues: [
                    'booking_id' => $booking->id,
                    'guide_id' => $guide->id,
                    'guest_count' => $guestCount,
                    'fee_amount' => $feeCalculation['amount'],
                ]
            );

            $booking->recalculateTotals();

            return $assignment;
        });
    }
}
