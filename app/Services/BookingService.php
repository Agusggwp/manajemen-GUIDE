<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Guide;
use App\Models\TourPackage;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Create a new booking with pricing snapshot and guest allocation.
     */
    public function createBooking(array $data, ?int $userId = null): Booking
    {
        return DB::transaction(function () use ($data, $userId) {
            $tourPackage = TourPackage::findOrFail($data['tour_package_id']);
            $guestCount = (int) $data['guest_count'];

            if ($guestCount <= 0) {
                throw new Exception("Jumlah tamu minimal 1 orang.");
            }

            // Determine snapshot price per guest
            $pricePerGuest = $tourPackage->getPriceForGuestCount($guestCount);
            $totalPrice = $guestCount * $pricePerGuest;

            // Generate unique booking code
            $prefix = 'BALI-' . Carbon::parse($data['booking_date'])->format('ymd');
            $latest = Booking::where('booking_code', 'like', $prefix . '-%')->count();
            $bookingCode = sprintf('%s-%03d', $prefix, $latest + 1);

            $booking = Booking::create([
                'booking_code' => $bookingCode,
                'booking_date' => $data['booking_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'nationality' => $data['nationality'] ?? 'Indonesia',
                'guest_count' => $guestCount,
                'tour_package_id' => $tourPackage->id,
                'location' => $data['location'] ?? $tourPackage->name,
                'pickup_location' => $data['pickup_location'] ?? null,
                'notes' => $data['notes'] ?? null,
                'price_per_guest' => $pricePerGuest,
                'total_price' => $totalPrice,
                'guide_fee_total' => 0,
                'operational_cost' => 0,
                'company_profit' => $totalPrice,
                'status' => 'WAITING',
                'created_by' => $userId ?? auth()->id(),
            ]);

            // Save individual guests if provided
            if (!empty($data['guests']) && is_array($data['guests'])) {
                foreach ($data['guests'] as $guestData) {
                    if (!empty($guestData['nama'])) {
                        Guest::create([
                            'booking_id' => $booking->id,
                            'nama' => $guestData['nama'],
                            'jenis_kelamin' => $guestData['jenis_kelamin'] ?? null,
                            'umur' => $guestData['umur'] ?? null,
                            'negara' => $guestData['negara'] ?? $booking->nationality,
                            'nomor_telepon' => $guestData['nomor_telepon'] ?? null,
                            'email' => $guestData['email'] ?? null,
                            'catatan' => $guestData['catatan'] ?? null,
                        ]);
                    }
                }
            }

            AuditLog::log('CREATE_BOOKING', Booking::class, $booking->id, null, [
                'code' => $booking->booking_code,
                'guest_count' => $guestCount,
                'total_price' => $totalPrice,
            ]);

            return $booking;
        });
    }

    /**
     * Update an existing booking.
     */
    public function updateBooking(Booking $booking, array $data): Booking
    {
        return DB::transaction(function () use ($booking, $data) {
            $oldValues = $booking->only(['guest_count', 'start_time', 'end_time', 'booking_date', 'customer_name', 'status']);

            $newGuestCount = isset($data['guest_count']) ? (int) $data['guest_count'] : $booking->guest_count;
            if ($newGuestCount < $booking->assignedGuestsCount()) {
                throw new Exception("Jumlah tamu tidak boleh lebih kecil dari jumlah tamu yang sudah dialokasikan ke guide ({$booking->assignedGuestsCount()}).");
            }

            $booking->update([
                'booking_date' => $data['booking_date'] ?? $booking->booking_date,
                'start_time' => $data['start_time'] ?? $booking->start_time,
                'end_time' => $data['end_time'] ?? $booking->end_time,
                'customer_name' => $data['customer_name'] ?? $booking->customer_name,
                'customer_phone' => $data['customer_phone'] ?? $booking->customer_phone,
                'nationality' => $data['nationality'] ?? $booking->nationality,
                'guest_count' => $newGuestCount,
                'location' => $data['location'] ?? $booking->location,
                'pickup_location' => $data['pickup_location'] ?? $booking->pickup_location,
                'notes' => $data['notes'] ?? $booking->notes,
            ]);

            $booking->recalculateTotals();

            AuditLog::log('UPDATE_BOOKING', Booking::class, $booking->id, $oldValues, $booking->only(array_keys($oldValues)));

            return $booking;
        });
    }

    /**
     * Create a direct/walk-in tour without advance booking.
     * Instantly creates booking record, assigns guide (auto circular queue or specific guide),
     * and optionally starts the tour immediately.
     */
    public function createDirectTour(array $data, GuideQueueService $queueService, ?User $user = null): array
    {
        return DB::transaction(function () use ($data, $queueService, $user) {
            $tourPackage = TourPackage::findOrFail($data['tour_package_id']);
            $guestCount = (int) ($data['guest_count'] ?? 1);

            if ($guestCount <= 0) {
                throw new Exception("Jumlah tamu minimal 1 orang.");
            }

            $bookingDate = $data['booking_date'] ?? Carbon::today()->toDateString();
            $startTime = $data['start_time'] ?? Carbon::now()->format('H:i:s');
            $endTime = $data['end_time'] ?? Carbon::now()->addHours(3)->format('H:i:s');

            // Determine snapshot price per guest
            $pricePerGuest = $tourPackage->getPriceForGuestCount($guestCount);
            $totalPrice = $guestCount * $pricePerGuest;

            // Generate unique walk-in tour code
            $prefix = 'WALKIN-' . Carbon::parse($bookingDate)->format('ymd');
            $latest = Booking::where('booking_code', 'like', $prefix . '-%')->count();
            $bookingCode = sprintf('%s-%03d', $prefix, $latest + 1);

            $customerName = !empty($data['customer_name']) ? trim($data['customer_name']) : 'Tamu Walk-in';
            $startImmediately = !empty($data['start_immediately']);

            $booking = Booking::create([
                'booking_code' => $bookingCode,
                'booking_date' => $bookingDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'customer_name' => $customerName,
                'customer_phone' => $data['customer_phone'] ?? null,
                'nationality' => $data['nationality'] ?? 'Indonesia',
                'guest_count' => $guestCount,
                'tour_package_id' => $tourPackage->id,
                'location' => $data['location'] ?? $tourPackage->name,
                'pickup_location' => $data['pickup_location'] ?? 'Lokasi Langsung / On the spot',
                'notes' => $data['notes'] ?? 'Tour langsung tanpa reservasi (Walk-in).',
                'price_per_guest' => $pricePerGuest,
                'total_price' => $totalPrice,
                'guide_fee_total' => 0,
                'operational_cost' => 0,
                'company_profit' => $totalPrice,
                'status' => $startImmediately ? 'ON_GOING' : 'FULLY_ASSIGNED',
                'is_walkin' => true,
                'created_by' => $user ? $user->id : auth()->id(),
            ]);

            // Guide assignment
            $guideChoice = $data['guide_id'] ?? 'auto';
            if ($guideChoice === 'auto' || empty($guideChoice)) {
                $assignment = $queueService->autoAssign($booking, $guestCount, $user);
            } else {
                $guide = Guide::findOrFail($guideChoice);
                
                // If guide is not checked in today, perform automatic attendance check-in for walk-in tour
                $todayAttendance = $guide->attendances()->whereDate('attendance_date', $bookingDate)->first();
                if (!$todayAttendance || $todayAttendance->status !== 'CHECKED_IN') {
                    app(GuideAttendanceService::class)->checkIn(
                        guide: $guide,
                        date: $bookingDate,
                        notes: 'Check-in otomatis untuk Tour Langsung (Walk-in)',
                        isForce: true
                    );
                }

                $assignment = $queueService->manualAssign($booking, $guide, $guestCount, $user);
            }

            // If start immediately is requested
            if ($startImmediately) {
                $assignment->status = 'ON_GOING';
                $assignment->started_at = now();
                $assignment->save();

                $assignedGuide = $assignment->guide;
                $assignedGuide->status = 'ON_TOUR';
                $assignedGuide->save();

                $booking->status = 'ON_GOING';
                $booking->save();
            }

            $booking->recalculateTotals();

            AuditLog::log('CREATE_DIRECT_TOUR', Booking::class, $booking->id, null, [
                'booking_code' => $booking->booking_code,
                'guide_id' => $assignment->guide_id,
                'guest_count' => $guestCount,
                'start_immediately' => $startImmediately,
                'total_price' => $totalPrice,
            ]);

            return [
                'booking' => $booking,
                'assignment' => $assignment,
                'guide' => $assignment->guide,
            ];
        });
    }
}
