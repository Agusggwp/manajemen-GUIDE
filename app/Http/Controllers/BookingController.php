<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Guide;
use App\Models\GuideAssignment;
use App\Models\TourPackage;
use App\Services\BookingService;
use App\Services\GuideQueueService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['tourPackage', 'assignments.guide', 'creator'])
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'asc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->where('booking_date', $request->date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $bookings = $query->paginate(15)->withQueryString();

        return view('bookings.index', compact('bookings'));
    }

    public function create(GuideQueueService $queueService)
    {
        $tourPackages = TourPackage::where('status', 'ACTIVE')->with('prices')->get();
        $today = Carbon::today()->toDateString();
        $nextGuideResult = $queueService->findNextAvailableGuide(null, $today);

        return view('bookings.create', compact('tourPackages', 'today', 'nextGuideResult'));
    }

    public function store(Request $request, BookingService $bookingService)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'nullable|string|max:40',
            'nationality' => 'nullable|string|max:60',
            'tour_package_id' => 'required|exists:tour_packages,id',
            'booking_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'guest_count' => 'required|integer|min:1',
            'location' => 'nullable|string|max:150',
            'pickup_location' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
            'guests' => 'nullable|array',
            'guests.*.nama' => 'nullable|string|max:100',
            'guests.*.jenis_kelamin' => 'nullable|in:L,P',
            'guests.*.umur' => 'nullable|integer',
            'guests.*.nomor_telepon' => 'nullable|string|max:40',
        ]);

        try {
            $booking = $bookingService->createBooking($validated);
            return redirect()->route('bookings.show', $booking)
                ->with('success', "Booking {$booking->booking_code} berhasil dibuat!");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Booking $booking, GuideQueueService $queueService)
    {
        $booking->load(['tourPackage', 'guests', 'assignments.guide', 'expenses.creator', 'creator']);

        $today = $booking->booking_date->toDateString();
        
        // Exclude guides already assigned
        $assignedGuideIds = $booking->assignments()
            ->where('status', '!=', 'CANCELLED')
            ->pluck('guide_id')
            ->toArray();

        // Check next guide candidates
        $nextCandidateResult = $queueService->findNextAvailableGuide(
            $booking,
            $today,
            $booking->start_time,
            $booking->end_time,
            $assignedGuideIds
        );

        // All active guides for smart manual assignment selector
        $allGuides = Guide::where('status', '!=', 'INACTIVE')
            ->orderBy('queue_number', 'asc')
            ->get()
            ->map(function (Guide $guide) use ($booking, $queueService, $today) {
                $attendance = $guide->attendances()->where('attendance_date', $today)->first();
                $isCheckedIn = $attendance && $attendance->status === 'CHECKED_IN';
                $isAlreadyAssigned = $booking->assignments()->where('guide_id', $guide->id)->where('status', '!=', 'CANCELLED')->exists();
                $hasConflict = $queueService->hasScheduleConflict($guide->id, $today, $booking->start_time, $booking->end_time, $booking->id);

                $todayAssignments = $guide->assignments()
                    ->whereHas('booking', fn($b) => $b->where('booking_date', $today))
                    ->where('status', '!=', 'CANCELLED')
                    ->get();

                $isAvailable = $isCheckedIn && !$hasConflict && !$isAlreadyAssigned && $guide->status !== 'OFF';

                return (object) [
                    'guide' => $guide,
                    'is_available' => $isAvailable,
                    'is_checked_in' => $isCheckedIn,
                    'has_conflict' => $hasConflict,
                    'is_already_assigned' => $isAlreadyAssigned,
                    'today_jobs' => $todayAssignments->count(),
                    'today_guests' => $todayAssignments->sum('guest_count'),
                    'today_earnings' => $todayAssignments->sum('fee_amount'),
                ];
            });

        return view('bookings.show', compact('booking', 'nextCandidateResult', 'allGuides'));
    }

    public function edit(Booking $booking)
    {
        $tourPackages = TourPackage::where('status', 'ACTIVE')->get();
        return view('bookings.edit', compact('booking', 'tourPackages'));
    }

    public function update(Request $request, Booking $booking, BookingService $bookingService)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'nullable|string|max:40',
            'nationality' => 'nullable|string|max:60',
            'booking_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'guest_count' => 'required|integer|min:1',
            'location' => 'nullable|string|max:150',
            'pickup_location' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
        ]);

        try {
            $bookingService->updateBooking($booking, $validated);
            return redirect()->route('bookings.show', $booking)
                ->with('success', "Booking {$booking->booking_code} berhasil diperbarui.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function autoAssign(Request $request, Booking $booking, GuideQueueService $queueService)
    {
        $request->validate([
            'guest_count' => 'nullable|integer|min:1',
            'multi_allocations' => 'nullable|string', // e.g. "20,15,15"
        ]);

        try {
            if ($request->filled('multi_allocations')) {
                // Multi-guide allocations
                $raw = explode(',', $request->multi_allocations);
                $allocations = array_map('intval', array_map('trim', $raw));
                $allocations = array_filter($allocations, fn($v) => $v > 0);

                if (empty($allocations)) {
                    throw new Exception("Format alokasi multi-guide tidak valid.");
                }

                $assignments = $queueService->autoAssignMultiple($booking, $allocations);
                $count = count($assignments);
                return back()->with('success', "Berhasil melakukan auto assignment untuk {$count} guide secara berurutan!");
            }

            // Single auto-assignment
            $guestCount = $request->filled('guest_count')
                ? (int) $request->guest_count
                : $booking->remainingGuestsCount();

            if ($guestCount <= 0) {
                throw new Exception("Semua tamu untuk booking ini sudah dialokasikan ke guide.");
            }

            $assignment = $queueService->autoAssign($booking, $guestCount);
            return back()->with('success', "Guide {$assignment->guide->nama} (#{$assignment->guide->queue_number}) berhasil di-assign untuk {$assignment->guest_count} tamu.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function manualAssign(Request $request, Booking $booking, GuideQueueService $queueService)
    {
        $validated = $request->validate([
            'guide_id' => 'required|exists:guides,id',
            'guest_count' => 'required|integer|min:1',
        ]);

        try {
            $guide = Guide::findOrFail($validated['guide_id']);
            $assignment = $queueService->manualAssign($booking, $guide, (int) $validated['guest_count']);

            return back()->with('success', "Guide {$guide->nama} berhasil ditugaskan untuk {$assignment->guest_count} tamu.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function removeAssignment(GuideAssignment $assignment)
    {
        try {
            $booking = $assignment->booking;
            $guide = $assignment->guide;

            $assignment->status = 'CANCELLED';
            $assignment->save();

            AuditLog::log('REMOVE_ASSIGNMENT', GuideAssignment::class, $assignment->id, null, [
                'booking_id' => $booking->id,
                'guide_id' => $guide->id,
            ]);

            $booking->recalculateTotals();

            return back()->with('success', "Penugasan Guide {$guide->nama} berhasil dibatalkan.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function calculateEstimate(Request $request)
    {
        $packageId = $request->query('package_id');
        $guestCount = (int) $request->query('guest_count', 1);

        $tourPackage = TourPackage::find($packageId);
        if (!$tourPackage) {
            return response()->json(['success' => false]);
        }

        $pricePerGuest = $tourPackage->getPriceForGuestCount($guestCount);
        $totalPrice = $guestCount * $pricePerGuest;
        $estGuideFee = $guestCount * 50000;
        $estProfit = $totalPrice - $estGuideFee;

        return response()->json([
            'success' => true,
            'price_per_guest' => $pricePerGuest,
            'price_per_guest_formatted' => 'Rp ' . number_format($pricePerGuest, 0, ',', '.'),
            'total_price' => $totalPrice,
            'total_price_formatted' => 'Rp ' . number_format($totalPrice, 0, ',', '.'),
            'estimated_guide_fee' => $estGuideFee,
            'estimated_guide_fee_formatted' => 'Rp ' . number_format($estGuideFee, 0, ',', '.'),
            'estimated_profit' => $estProfit,
            'estimated_profit_formatted' => 'Rp ' . number_format($estProfit, 0, ',', '.'),
        ]);
    }
}
