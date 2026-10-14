<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guide;
use App\Models\Setting;
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

        $defaultGuideFee = (float) Setting::get('default_guide_fee', 50000);
        $defaultPhoneFee = (float) Setting::get('default_phone_fee', 0);
        $defaultDriverFee = (float) Setting::get('default_driver_fee', 50000);

        return view('direct_tours.create', compact('tourPackages', 'guides', 'nextCandidateResult', 'today', 'defaultGuideFee', 'defaultPhoneFee', 'defaultDriverFee'));
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
            'phone_fee' => 'nullable|numeric|min:0',
            'give_driver_fee' => 'nullable|boolean',
            'driver_fee' => 'nullable|numeric|min:0',
            'driver_notes' => 'nullable|string|max:150',
            'start_immediately' => 'nullable|boolean',
            'location' => 'nullable|string|max:150',
            'pickup_location' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
            'payment_method' => 'nullable|string|max:50',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        // Enforce phone fee and driver fee strictly from Admin Settings
        $validated['phone_fee'] = (float) Setting::get('default_phone_fee', 0);
        if (!empty($validated['give_driver_fee'])) {
            $validated['driver_fee'] = (float) Setting::get('default_driver_fee', 50000);
        } else {
            $validated['driver_fee'] = 0;
        }

        try {
            $result = $bookingService->createDirectTour($validated, $queueService, auth()->user());
            $booking = $result['booking'];
            $guide = $result['guide'];

            $statusText = $result['assignment']->status === 'ON_GOING' ? 'dan langsung BERJALAN' : 'dan siap bertugas';

            return redirect()->route('direct-tours.receipt', ['booking' => $booking, 'autoprint' => 1]);
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display thermal printer receipt for walk-in tour / booking.
     * Only displays guest billing without internal deductions.
     */
    public function receipt(Booking $booking)
    {
        $booking->load(['tourPackage', 'assignments.guide', 'creator']);

        $settings = [
            'company_name' => Setting::get('company_name', Setting::get('app_name', 'ARTDEVATA')),
            'company_tagline' => Setting::get('company_tagline', Setting::get('app_tagline', 'Guide Management')),
            'company_phone' => Setting::get('company_phone', '+62 812-3456-7890'),
            'company_address' => Setting::get('company_address', 'Jl. Sunset Road, Seminyak, Kuta, Bali'),
            'company_logo' => Setting::get('company_logo', null),
        ];

        $assignedGuide = $booking->assignments->first()?->guide;

        return view('direct_tours.receipt', compact('booking', 'settings', 'assignedGuide'));
    }
}
