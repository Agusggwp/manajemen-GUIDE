<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use App\Models\GuideAssignment;
use App\Models\GuideAttendance;
use App\Models\GuideQueueState;
use App\Models\TourPackage;
use App\Services\BookingService;
use App\Services\GuideAttendanceService;
use App\Services\GuideQueueService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class GuidePortalController extends Controller
{
    private function getAuthenticatedGuide()
    {
        $user = auth()->user();
        if (!$user->isGuide() || !$user->guide) {
            abort(403, 'Akses ditolak: Anda tidak terdaftar sebagai guide.');
        }
        return $user->guide;
    }

    public function dashboard(GuideQueueService $queueService)
    {
        $guide = $this->getAuthenticatedGuide();
        $today = Carbon::today()->toDateString();

        // Today's attendance
        $todayAttendance = GuideAttendance::where('guide_id', $guide->id)
            ->where('attendance_date', $today)
            ->first();

        // Today's assignments
        $todayAssignments = GuideAssignment::with('booking.tourPackage')
            ->where('guide_id', $guide->id)
            ->whereHas('booking', fn($q) => $q->where('booking_date', $today))
            ->where('status', '!=', 'CANCELLED')
            ->orderBy('assigned_at', 'asc')
            ->get();

        // Upcoming assignments (future dates)
        $upcomingAssignments = GuideAssignment::with('booking.tourPackage')
            ->where('guide_id', $guide->id)
            ->whereHas('booking', fn($q) => $q->where('booking_date', '>', $today))
            ->where('status', '!=', 'CANCELLED')
            ->orderBy('assigned_at', 'asc')
            ->take(5)
            ->get();

        // Quick statistics for today
        $todayJobsCount = $todayAssignments->count();
        $todayGuestsCount = $todayAssignments->sum('guest_count');
        $todayEarnings = $todayAssignments->sum('fee_amount');

        // Check if there are active tours right now
        $activeTour = $todayAssignments->where('status', 'ON_GOING')->first();

        // Tour packages for walk-in instant tours
        $tourPackages = TourPackage::where('status', 'ACTIVE')->get();

        // Queue status and online guides data
        $queueData = $this->getQueueOverviewData($guide, $today, $queueService);

        return view('guide.dashboard', array_merge(compact(
            'guide',
            'today',
            'todayAttendance',
            'todayAssignments',
            'upcomingAssignments',
            'todayJobsCount',
            'todayGuestsCount',
            'todayEarnings',
            'activeTour',
            'tourPackages'
        ), $queueData));
    }

    public function checkIn(Request $request, GuideAttendanceService $attendanceService)
    {
        $guide = $this->getAuthenticatedGuide();

        try {
            $lat = $request->filled('latitude') ? (float) $request->latitude : null;
            $lng = $request->filled('longitude') ? (float) $request->longitude : null;
            $notes = $request->input('notes');

            $attendance = $attendanceService->checkIn($guide, null, $lat, $lng, $notes);

            return back()->with('success', "Check-in berhasil pukul {$attendance->check_in_at->format('H:i')}. Anda sekarang aktif dalam antrean #{$guide->queue_number}!");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function checkOut(Request $request, GuideAttendanceService $attendanceService)
    {
        $guide = $this->getAuthenticatedGuide();

        try {
            $lat = $request->filled('latitude') ? (float) $request->latitude : null;
            $lng = $request->filled('longitude') ? (float) $request->longitude : null;
            $notes = $request->input('notes');

            $attendance = $attendanceService->checkOut($guide, null, $lat, $lng, $notes);

            return back()->with('success', "Check-out berhasil pukul {$attendance->check_out_at->format('H:i')}. Terima kasih atas kerja keras Anda hari ini!");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function startTour(GuideAssignment $assignment, GuideAttendanceService $attendanceService)
    {
        $guide = $this->getAuthenticatedGuide();

        try {
            $attendanceService->startTour($assignment, $guide);
            return back()->with('success', "Tour berhasil dimulai. Selamat bertugas!");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function completeTour(GuideAssignment $assignment, GuideAttendanceService $attendanceService)
    {
        $guide = $this->getAuthenticatedGuide();

        try {
            $attendanceService->completeTour($assignment, $guide);
            return back()->with('success', "Tour telah diselesaikan. Kerja bagus!");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function myJobs(Request $request)
    {
        $guide = $this->getAuthenticatedGuide();

        $query = GuideAssignment::with('booking.tourPackage')
            ->where('guide_id', $guide->id)
            ->where('status', '!=', 'CANCELLED')
            ->orderBy('assigned_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assignments = $query->paginate(15)->withQueryString();

        return view('guide.jobs', compact('guide', 'assignments'));
    }

    public function myEarnings()
    {
        $guide = $this->getAuthenticatedGuide();

        $assignments = GuideAssignment::with('booking')
            ->where('guide_id', $guide->id)
            ->where('status', '!=', 'CANCELLED')
            ->orderBy('assigned_at', 'desc')
            ->get();

        $payments = $guide->payments()->orderBy('payment_date', 'desc')->get();

        $totalFee = (float) $assignments->sum('fee_amount');
        $totalPaid = (float) $payments->sum('amount');
        $remaining = max(0, $totalFee - $totalPaid);

        return view('guide.earnings', compact('guide', 'assignments', 'payments', 'totalFee', 'totalPaid', 'remaining'));
    }

    public function profile()
    {
        $guide = $this->getAuthenticatedGuide();
        return view('guide.profile', compact('guide'));
    }

    public function updateProfile(Request $request)
    {
        $guide = $this->getAuthenticatedGuide();

        $validated = $request->validate([
            'nomor_telepon' => 'required|string|max:30',
            'alamat' => 'nullable|string',
            'bahasa' => 'nullable|string|max:150',
            'spesialisasi' => 'nullable|string|max:150',
        ]);

        $guide->update($validated);
        $guide->user->update(['phone' => $validated['nomor_telepon']]);

        return back()->with('success', 'Profil Anda berhasil diperbarui.');
    }

    /**
     * Start a direct tour on the spot by Guide for walk-in guests.
     */
    public function createDirectTour(Request $request, BookingService $bookingService, GuideQueueService $queueService)
    {
        $guide = $this->getAuthenticatedGuide();

        if ($guide->isSuspended()) {
            return back()->with('error', 'Akun Anda sedang ditangguhkan: ' . ($guide->suspend_reason ?? 'Menunggu review manajemen.'));
        }

        // Must be checked in
        $today = Carbon::today()->toDateString();
        $attendance = $guide->attendances()->whereDate('attendance_date', $today)->first();
        if (!$attendance || $attendance->status !== 'CHECKED_IN') {
            return back()->with('error', 'Anda harus melakukan Check-in terlebih dahulu sebelum melayani tour.');
        }

        // Must not be currently on tour
        if ($guide->status === 'ON_TOUR') {
            return back()->with('error', 'Anda masih memiliki tour aktif yang sedang berjalan.');
        }

        $validated = $request->validate([
            'tour_package_id' => 'required|exists:tour_packages,id',
            'guest_count' => 'required|integer|min:1',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:40',
            'notes' => 'nullable|string',
        ]);

        $validated['guide_id'] = $guide->id;
        $validated['start_immediately'] = true;

        try {
            $result = $bookingService->createDirectTour($validated, $queueService, auth()->user());
            return back()->with('success', "⚡ Tour Langsung ({$result['booking']->booking_code}) berhasil dimulai. Selamat bertugas!");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Dedicated Mobile Queue Board for Guides.
     */
    public function queueBoard(GuideQueueService $queueService)
    {
        $guide = $this->getAuthenticatedGuide();
        $today = Carbon::today()->toDateString();
        $queueData = $this->getQueueOverviewData($guide, $today, $queueService);

        return view('guide.queue', array_merge([
            'guide' => $guide,
            'today' => $today,
        ], $queueData));
    }

    /**
     * Helper to compute real-time queue position and active guides for the guide portal.
     */
    private function getQueueOverviewData(Guide $guide, string $today, GuideQueueService $queueService): array
    {
        $state = GuideQueueState::getState();
        $lastPosition = (int) $state->current_position;

        $nextGuideResult = $queueService->findNextAvailableGuide(null, $today);
        $nextGuide = $nextGuideResult['guide'];

        $allGuides = Guide::with(['attendances' => function ($q) use ($today) {
            $q->whereDate('attendance_date', $today);
        }, 'assignments' => function ($q) use ($today) {
            $q->whereHas('booking', fn($b) => $b->whereDate('booking_date', $today))
              ->where('status', '!=', 'CANCELLED');
        }])
        ->where('status', '!=', 'INACTIVE')
        ->orderBy('queue_number', 'asc')
        ->get();

        $guideCards = $allGuides->map(function (Guide $g) use ($guide, $nextGuide) {
            $attendance = $g->attendances->first();
            $isCheckedIn = $attendance && $attendance->status === 'CHECKED_IN';
            $isCheckedOut = $attendance && $attendance->status === 'CHECKED_OUT';
            $isOnTour = ($g->status === 'ON_TOUR');
            $isAvailable = $isCheckedIn && !$isOnTour && ($g->status !== 'OFF');
            $isNext = $nextGuide && ($g->id === $nextGuide->id);
            $isMe = ($g->id === $guide->id);

            return (object) [
                'id' => $g->id,
                'queue_number' => $g->queue_number,
                'nama' => $g->nama,
                'kode_guide' => $g->kode_guide,
                'nomor_telepon' => $g->nomor_telepon,
                'status' => $g->status,
                'is_checked_in' => $isCheckedIn,
                'is_checked_out' => $isCheckedOut,
                'is_on_tour' => $isOnTour,
                'is_available' => $isAvailable,
                'is_next' => $isNext,
                'is_me' => $isMe,
                'check_in_time' => $attendance?->check_in_at ? $attendance->check_in_at->format('H:i') : null,
                'check_out_time' => $attendance?->check_out_at ? $attendance->check_out_at->format('H:i') : null,
                'today_guests' => (int) $g->assignments->sum('guest_count'),
                'today_jobs' => (int) $g->assignments->count(),
            ];
        });

        // Guides who are currently ON (checked in today)
        $onGuides = $guideCards->filter(fn($g) => $g->is_checked_in)->values();
        $standbyCount = $guideCards->filter(fn($g) => $g->is_available)->count();
        $onTourCount = $guideCards->filter(fn($g) => $g->is_on_tour)->count();

        // Calculate queue position distance for this guide
        $myInfo = $guideCards->firstWhere('id', $guide->id);
        $isMyTurnNext = $nextGuide && ($nextGuide->id === $guide->id);

        $guidesAheadCount = 0;
        if ($myInfo && $myInfo->is_available && !$isMyTurnNext) {
            $higher = $guideCards->filter(fn($g) => $g->queue_number > $lastPosition && $g->is_available);
            $lower = $guideCards->filter(fn($g) => $g->queue_number <= $lastPosition && $g->is_available);
            $sequence = $higher->concat($lower)->values();

            $myIdx = $sequence->search(fn($item) => $item->id === $guide->id);
            if ($myIdx !== false) {
                $guidesAheadCount = (int) $myIdx;
            }
        }

        return [
            'state' => $state,
            'lastPosition' => $lastPosition,
            'nextGuide' => $nextGuide,
            'guideCards' => $guideCards,
            'onGuides' => $onGuides,
            'standbyCount' => $standbyCount,
            'onTourCount' => $onTourCount,
            'myInfo' => $myInfo,
            'isMyTurnNext' => $isMyTurnNext,
            'guidesAheadCount' => $guidesAheadCount,
        ];
    }
}
