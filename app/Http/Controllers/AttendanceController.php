<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use App\Models\GuideAttendance;
use App\Services\GuideAttendanceService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->query('date', Carbon::today()->toDateString());

        $guides = Guide::where('status', '!=', 'INACTIVE')
            ->orderBy('queue_number', 'asc')
            ->with(['attendances' => function ($q) use ($selectedDate) {
                $q->where('attendance_date', $selectedDate);
            }])
            ->get();

        $attendances = $guides->map(function (Guide $guide) use ($selectedDate) {
            $attendance = $guide->attendances->first();
            return (object) [
                'guide' => $guide,
                'attendance' => $attendance,
                'status' => $attendance ? $attendance->status : 'NOT_CHECKED_IN',
                'check_in_at' => $attendance?->check_in_at,
                'check_out_at' => $attendance?->check_out_at,
                'notes' => $attendance?->notes,
                'latitude' => $attendance?->check_in_latitude,
                'longitude' => $attendance?->check_in_longitude,
            ];
        });

        $checkedInCount = $attendances->where('status', 'CHECKED_IN')->count();
        $checkedOutCount = $attendances->where('status', 'CHECKED_OUT')->count();
        $notCheckedInCount = $attendances->where('status', 'NOT_CHECKED_IN')->count();

        return view('attendance.index', compact('attendances', 'selectedDate', 'checkedInCount', 'checkedOutCount', 'notCheckedInCount'));
    }

    public function forceCheckIn(Request $request, Guide $guide, GuideAttendanceService $attendanceService)
    {
        try {
            $date = $request->input('date', Carbon::today()->toDateString());
            $attendanceService->checkIn($guide, $date, null, null, 'Force Check-in oleh Admin', true);

            return back()->with('success', "Force Check-in berhasil untuk Guide {$guide->nama}.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function forceCheckOut(Request $request, Guide $guide, GuideAttendanceService $attendanceService)
    {
        try {
            $date = $request->input('date', Carbon::today()->toDateString());
            $attendanceService->checkOut($guide, $date, null, null, 'Force Check-out oleh Admin', true);

            return back()->with('success', "Force Check-out berhasil untuk Guide {$guide->nama}.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
