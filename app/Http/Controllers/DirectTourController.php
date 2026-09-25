<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use App\Models\TourPackage;
use App\Services\BookingService;
use App\Services\GuideQueueService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class DirectTourController extends Controller
{
    /**
     * Show the form to create a direct tour (walk-in tour without advance booking).
     */
    public function create(GuideQueueService $queueService)
    {
        $today = Carbon::today()->toDateString();
        $tourPackages = TourPackage::where('status', 'ACTIVE')->with('prices')->get();

        // Get circular queue next available candidate
        $nextCandidateResult = $queueService->findNextAvailableGuide(null, $today);

        // List all active guides with attendance & current status
        $guides = Guide::where('status', '!=', 'INACTIVE')
            ->orderBy('queue_number', 'asc')
            ->get()
            ->map(function (Guide $guide) use ($today) {
                $attendance = $guide->attendances()->whereDate('attendance_date', $today)->first();
                $isCheckedIn = $attendance && $attendance->status === 'CHECKED_IN';
                return (object) [
                    'guide' => $guide,
                    'is_checked_in' => $isCheckedIn,
                    'is_available' => $isCheckedIn && $guide->status === 'AVAILABLE',
                    'status' => $guide->status,
                ];
            });

        return view('direct_tours.create', compact('tourPackages', 'guides', 'nextCandidateResult', 'today'));
    }

    /**
     * Store the direct tour and assign guide immediately.
     */
    public function store(Request $request, BookingService $bookingService, GuideQueueService $queueService)
    {
        $validated = $request->validate([
            'tour_package_id' => 'required|exists:tour_packages,id',
            'guest_count' => 'required|integer|min:1',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:40',
            'nationality' => 'nullable|string|max:60',
            'guide_id' => 'required', // 'auto' or integer guide_id
            'start_immediately' => 'nullable|boolean',
            'location' => 'nullable|string|max:150',
            'pickup_location' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
        ]);

        try {
            $result = $bookingService->createDirectTour($validated, $queueService, auth()->user());
            $booking = $result['booking'];
            $guide = $result['guide'];

            $statusText = $result['assignment']->status === 'ON_GOING' ? 'dan langsung BERJALAN' : 'dan siap bertugas';

            return redirect()->route('bookings.show', $booking)
                ->with('success', "⚡ Tour Langsung {$booking->booking_code} berhasil dibuat {$statusText}! Guide ditugaskan: {$guide->nama} (#{$guide->queue_number}).");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
