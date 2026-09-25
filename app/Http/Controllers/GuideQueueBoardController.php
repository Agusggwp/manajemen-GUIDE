<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guide;
use App\Models\GuideAttendance;
use App\Models\GuideQueueState;
use App\Services\GuideQueueService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GuideQueueBoardController extends Controller
{
    public function index(GuideQueueService $queueService)
    {
        $today = Carbon::today()->toDateString();
        $state = GuideQueueState::getState();

        $guides = Guide::with(['attendances' => function ($q) use ($today) {
            $q->where('attendance_date', $today);
        }, 'assignments' => function ($q) use ($today) {
            $q->whereHas('booking', fn($b) => $b->where('booking_date', $today))
              ->where('status', '!=', 'CANCELLED');
        }])
        ->where('status', '!=', 'INACTIVE')
        ->orderBy('queue_number', 'asc')
        ->get();

        $guideBoardData = $guides->map(function (Guide $guide) use ($today) {
            $attendance = $guide->attendances->first();
            $attStatus = $attendance ? $attendance->status : 'NOT_CHECKED_IN';

            // Determine board category
            $boardCategory = 'OFF';
            if ($attStatus === 'CHECKED_OUT') {
                $boardCategory = 'CHECKED_OUT';
            } elseif ($attStatus === 'CHECKED_IN') {
                if ($guide->status === 'ON_TOUR') {
                    $boardCategory = 'ON_TOUR';
                } elseif ($guide->assignments->where('status', 'ASSIGNED')->count() > 0) {
                    $boardCategory = 'ASSIGNED';
                } elseif ($guide->status === 'AVAILABLE') {
                    $boardCategory = 'AVAILABLE';
                } else {
                    $boardCategory = 'WAITING';
                }
            } else {
                $boardCategory = 'OFF';
            }

            $todayJobsCount = $guide->assignments->count();
            $todayGuestsCount = $guide->assignments->sum('guest_count');
            $todayEarnings = $guide->assignments->sum('fee_amount');

            return (object) [
                'guide' => $guide,
                'queue_number' => $guide->queue_number,
                'nama' => $guide->nama,
                'attendance_status' => $attStatus,
                'guide_status' => $guide->status,
                'board_category' => $boardCategory,
                'check_in_at' => $attendance?->check_in_at,
                'check_out_at' => $attendance?->check_out_at,
                'today_jobs' => $todayJobsCount,
                'today_guests' => $todayGuestsCount,
                'today_earnings' => $todayEarnings,
                'assignments' => $guide->assignments,
            ];
        });

        // Group into columns
        $boardColumns = [
            'WAITING' => $guideBoardData->where('board_category', 'WAITING'),
            'AVAILABLE' => $guideBoardData->where('board_category', 'AVAILABLE'),
            'ASSIGNED' => $guideBoardData->where('board_category', 'ASSIGNED'),
            'ON_TOUR' => $guideBoardData->where('board_category', 'ON_TOUR'),
            'CHECKED_OUT' => $guideBoardData->where('board_category', 'CHECKED_OUT'),
            'OFF' => $guideBoardData->where('board_category', 'OFF'),
        ];

        // Next Guide preview
        $nextGuideResult = $queueService->findNextAvailableGuide(null, $today);

        // Pending bookings needing assignment
        $pendingBookings = Booking::with('tourPackage')
            ->where('booking_date', '>=', $today)
            ->whereIn('status', ['WAITING', 'PARTIALLY_ASSIGNED'])
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();

        return view('admin.queue-board', compact(
            'today',
            'state',
            'guideBoardData',
            'boardColumns',
            'nextGuideResult',
            'pendingBookings'
        ));
    }
}
