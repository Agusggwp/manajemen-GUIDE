<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\Guest;
use App\Models\Guide;
use App\Models\GuideAssignment;
use App\Models\GuideAttendance;
use App\Models\GuidePayment;
use App\Models\GuideQueueState;
use App\Models\QueueHistory;
use App\Models\Role;
use App\Models\Setting;
use App\Models\TourPackage;
use App\Models\TourPackagePrice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $today = Carbon::today()->toDateString();
        $password = Hash::make('password');

        // 1. Roles
        Role::create(['name' => 'ADMIN', 'label' => 'Administrator', 'description' => 'Full access to manage guides, queues, pricing, bookings, payroll, and settings']);
        Role::create(['name' => 'STAFF', 'label' => 'Operations Staff', 'description' => 'Create bookings, manage guests, assign guides, view queues']);
        Role::create(['name' => 'GUIDE', 'label' => 'Tour Guide', 'description' => 'Mobile portal for check-in/out, viewing own assignments and earnings']);

        // 2. Settings
        Setting::set('company_name', 'ARTDEVATA');
        Setting::set('company_tagline', 'Guide Management');
        Setting::set('company_logo', 'uploads/logos/artdevata_logo.svg');
        Setting::set('company_phone', '+62 812-3456-7890');
        Setting::set('company_address', 'Jl. Sunset Road No. 88, Seminyak, Bali');
        Setting::set('gps_required', '0');
        Setting::set('default_guide_fee', '50000');

        // 3. Admin & Staff Users
        $adminUser = User::create([
            'name' => 'Admin Operasional',
            'email' => 'admin@example.com',
            'password' => $password,
            'role' => 'ADMIN',
            'phone' => '081234567890',
            'is_active' => true,
            'approval_status' => 'APPROVED',
            'email_verified_at' => now(),
        ]);

        $staffUser = User::create([
            'name' => 'Staff Reservasi',
            'email' => 'staff@example.com',
            'password' => $password,
            'role' => 'STAFF',
            'phone' => '081234567891',
            'is_active' => true,
            'approval_status' => 'APPROVED',
            'email_verified_at' => now(),
        ]);

        // 4. Tour Guides: Budi, Made, Komang, Wayan, Agus
        $guideData = [
            [
                'name' => 'Budi',
                'email' => 'budi@example.com',
                'kode' => 'GD-001',
                'queue' => 1,
                'phone' => '08130000001',
                'bahasa' => 'Inggris, Indonesia',
                'spesialisasi' => 'Culture, Temple & Heritage',
            ],
            [
                'name' => 'Made',
                'email' => 'made@example.com',
                'kode' => 'GD-002',
                'queue' => 2,
                'phone' => '08130000002',
                'bahasa' => 'Inggris, Mandarin, Indonesia',
                'spesialisasi' => 'Nature, Trekking & Volcano',
            ],
            [
                'name' => 'Komang',
                'email' => 'komang@example.com',
                'kode' => 'GD-003',
                'queue' => 3,
                'phone' => '08130000003',
                'bahasa' => 'Inggris, Jepang, Indonesia',
                'spesialisasi' => 'Culinary, Water Sport & Beach',
            ],
            [
                'name' => 'Wayan',
                'email' => 'wayan@example.com',
                'kode' => 'GD-004',
                'queue' => 4,
                'phone' => '08130000004',
                'bahasa' => 'Inggris, Perancis, Indonesia',
                'spesialisasi' => 'Photography, Rice Terrace & Waterfalls',
            ],
            [
                'name' => 'Agus',
                'email' => 'agus@example.com',
                'kode' => 'GD-005',
                'queue' => 5,
                'phone' => '08130000005',
                'bahasa' => 'Inggris, Jerman, Indonesia',
                'spesialisasi' => 'Art & Craft, Custom Private Tour',
            ],
        ];

        $guides = [];
        foreach ($guideData as $g) {
            $user = User::create([
                'name' => $g['name'],
                'email' => $g['email'],
                'password' => $password,
                'role' => 'GUIDE',
                'phone' => $g['phone'],
                'is_active' => true,
                'approval_status' => 'APPROVED',
                'email_verified_at' => now(),
            ]);

            $guide = Guide::create([
                'user_id' => $user->id,
                'kode_guide' => $g['kode'],
                'nama' => $g['name'],
                'nomor_telepon' => $g['phone'],
                'email' => $g['email'],
                'alamat' => 'Denpasar, Bali',
                'bahasa' => $g['bahasa'],
                'spesialisasi' => $g['spesialisasi'],
                'queue_number' => $g['queue'],
                'status' => 'AVAILABLE',
                'tanggal_gabung' => '2024-01-15',
                'tipe_fee' => 'PER_GUEST',
                'fee_per_guest' => 50000,
                'fee_per_booking' => 300000,
                'fee_minimum' => 150000,
                'catatan' => 'Guide bersertifikat HPI Bali.',
            ]);

            $guides[$g['queue']] = $guide;
        }

        // 5. Tour Packages & Tiered Pricing
        $packages = [
            [
                'name' => 'Ubud Cultural Tour',
                'duration' => '8 Jam',
                'default_price' => 150000,
                'description' => 'Mengunjungi Monkey Forest, Puri Saren Ubud, Goa Gajah, dan Pasar Seni Ubud.',
            ],
            [
                'name' => 'Kintamani Volcano & Coffee Tour',
                'duration' => '9 Jam',
                'default_price' => 160000,
                'description' => 'Pemandangan spektakuler Gunung & Danau Batur, perkebunan kopi luwak, dan Tegalalang.',
            ],
            [
                'name' => 'Temple & Uluwatu Sunset Tour',
                'duration' => '7 Jam',
                'default_price' => 140000,
                'description' => 'Pura Taman Ayun, Tanah Lot, Pantai Padang-Padang, dan Tari Kecak Uluwatu.',
            ],
            [
                'name' => 'Bali Cultural & Heritage Tour',
                'duration' => '10 Jam',
                'default_price' => 170000,
                'description' => 'Desa Penglipuran, Tirta Empul Tampaksiring, dan Pura Besakih Ibu dari segala pura.',
            ],
            [
                'name' => 'Nusa Penida Island Explorer',
                'duration' => 'Full Day',
                'default_price' => 250000,
                'description' => 'Kelingking Beach, Broken Beach, Angel Billabong, dan Crystal Bay.',
            ],
        ];

        $createdPackages = [];
        foreach ($packages as $pkg) {
            $tp = TourPackage::create([
                'name' => $pkg['name'],
                'description' => $pkg['description'],
                'duration' => $pkg['duration'],
                'default_price' => $pkg['default_price'],
                'status' => 'ACTIVE',
            ]);

            // Add tiered pricing per guest count
            TourPackagePrice::create(['tour_package_id' => $tp->id, 'min_guests' => 1, 'max_guests' => 5, 'price_per_guest' => 150000, 'active' => true]);
            TourPackagePrice::create(['tour_package_id' => $tp->id, 'min_guests' => 6, 'max_guests' => 10, 'price_per_guest' => 130000, 'active' => true]);
            TourPackagePrice::create(['tour_package_id' => $tp->id, 'min_guests' => 11, 'max_guests' => 20, 'price_per_guest' => 110000, 'active' => true]);
            TourPackagePrice::create(['tour_package_id' => $tp->id, 'min_guests' => 21, 'max_guests' => 100, 'price_per_guest' => 90000, 'active' => true]);

            $createdPackages[] = $tp;
        }

        // 6. Attendances for Today
        // Budi: Checked In
        GuideAttendance::create([
            'guide_id' => $guides[1]->id,
            'attendance_date' => $today,
            'check_in_at' => Carbon::parse($today . ' 07:30:00'),
            'status' => 'CHECKED_IN',
            'notes' => 'Hadir tepat waktu di kantor Sanur.',
            'check_in_latitude' => -8.670458,
            'check_in_longitude' => 115.212629,
        ]);
        // Made: Checked In & On Tour
        GuideAttendance::create([
            'guide_id' => $guides[2]->id,
            'attendance_date' => $today,
            'check_in_at' => Carbon::parse($today . ' 07:45:00'),
            'status' => 'CHECKED_IN',
            'notes' => 'Siap handle tamu grup Australia.',
            'check_in_latitude' => -8.670458,
            'check_in_longitude' => 115.212629,
        ]);
        $guides[2]->update(['status' => 'ON_TOUR']);

        // Komang: Checked In
        GuideAttendance::create([
            'guide_id' => $guides[3]->id,
            'attendance_date' => $today,
            'check_in_at' => Carbon::parse($today . ' 08:00:00'),
            'status' => 'CHECKED_IN',
            'notes' => 'Standby di Ubud meeting point.',
            'check_in_latitude' => -8.506854,
            'check_in_longitude' => 115.262474,
        ]);

        // Wayan: OFF
        GuideAttendance::create([
            'guide_id' => $guides[4]->id,
            'attendance_date' => $today,
            'status' => 'OFF',
            'notes' => 'Izin upacara adat / piodalan.',
        ]);
        $guides[4]->update(['status' => 'OFF']);

        // Agus: Checked In
        GuideAttendance::create([
            'guide_id' => $guides[5]->id,
            'attendance_date' => $today,
            'check_in_at' => Carbon::parse($today . ' 08:15:00'),
            'status' => 'CHECKED_IN',
            'notes' => 'Hadir pickup Kuta.',
            'check_in_latitude' => -8.721532,
            'check_in_longitude' => 115.176435,
        ]);

        // Yesterday Attendances for realistic reporting
        $yesterday = Carbon::yesterday()->toDateString();
        foreach ($guides as $g) {
            GuideAttendance::create([
                'guide_id' => $g->id,
                'attendance_date' => $yesterday,
                'check_in_at' => Carbon::parse($yesterday . ' 07:45:00'),
                'check_out_at' => Carbon::parse($yesterday . ' 17:15:00'),
                'status' => 'CHECKED_OUT',
                'notes' => 'Selesai tugas full day.',
            ]);
        }

        // 7. Seed 22 Bookings across past, today, and upcoming dates
        $sampleCustomers = [
            ['name' => 'Johnathan Smith', 'phone' => '+61 412 345 678', 'country' => 'Australia'],
            ['name' => 'Emma Watson', 'phone' => '+44 7911 123456', 'country' => 'United Kingdom'],
            ['name' => 'Siti Rahmawati', 'phone' => '081298765432', 'country' => 'Indonesia'],
            ['name' => 'Kenji Tanaka', 'phone' => '+81 90 1234 5678', 'country' => 'Japan'],
            ['name' => 'Michael Miller', 'phone' => '+1 415 555 2671', 'country' => 'United States'],
            ['name' => 'Bambang Soediro', 'phone' => '081822334455', 'country' => 'Indonesia'],
            ['name' => 'Chloe Dupont', 'phone' => '+33 6 12 34 56 78', 'country' => 'France'],
            ['name' => 'Lukas Schneider', 'phone' => '+49 151 1234567', 'country' => 'Germany'],
            ['name' => 'Dewi Lestari', 'phone' => '081399887766', 'country' => 'Indonesia'],
            ['name' => 'Chen Wei', 'phone' => '+86 138 0013 8000', 'country' => 'China'],
        ];

        // Past Bookings (Yesterday & earlier)
        for ($i = 1; $i <= 10; $i++) {
            $bDate = Carbon::today()->subDays(12 - $i)->toDateString();
            $cust = $sampleCustomers[($i - 1) % count($sampleCustomers)];
            $pkg = $createdPackages[$i % count($createdPackages)];
            $guests = ($i % 3 == 0) ? 15 : (($i % 2 == 0) ? 8 : 4);
            $pricePerGuest = $pkg->getPriceForGuestCount($guests);
            $totalPrice = $guests * $pricePerGuest;

            $assignedGuide = $guides[(($i % 5) + 1)];
            $feeAmount = $guests * 50000;

            $booking = Booking::create([
                'booking_code' => sprintf('BALI-%s-%03d', Carbon::parse($bDate)->format('ymd'), $i),
                'booking_date' => $bDate,
                'start_time' => '08:30:00',
                'end_time' => '16:30:00',
                'customer_name' => $cust['name'],
                'customer_phone' => $cust['phone'],
                'nationality' => $cust['country'],
                'guest_count' => $guests,
                'tour_package_id' => $pkg->id,
                'location' => 'Bali Tour Area',
                'pickup_location' => 'Hotel Lobby Seminyak',
                'price_per_guest' => $pricePerGuest,
                'total_price' => $totalPrice,
                'guide_fee_total' => $feeAmount,
                'operational_cost' => 100000,
                'company_profit' => $totalPrice - $feeAmount - 100000,
                'status' => 'COMPLETED',
                'created_by' => $adminUser->id,
            ]);

            GuideAssignment::create([
                'booking_id' => $booking->id,
                'guide_id' => $assignedGuide->id,
                'guest_count' => $guests,
                'fee_type' => 'PER_GUEST',
                'fee_rate' => 50000,
                'fee_amount' => $feeAmount,
                'queue_position' => $assignedGuide->queue_number,
                'assigned_at' => Carbon::parse($bDate . ' 07:00:00'),
                'started_at' => Carbon::parse($bDate . ' 08:30:00'),
                'completed_at' => Carbon::parse($bDate . ' 16:30:00'),
                'status' => 'COMPLETED',
                'assigned_by' => $adminUser->id,
            ]);

            Expense::create([
                'booking_id' => $booking->id,
                'category' => 'Tiket Masuk & Parkir',
                'description' => 'Tiket masuk obyek wisata dan retribusi',
                'amount' => 100000,
                'expense_date' => $bDate,
                'created_by' => $adminUser->id,
            ]);
        }

        // Today's Bookings:
        // Booking 1: On Going with Guide Made (12 guests)
        $tBooking1 = Booking::create([
            'booking_code' => sprintf('BALI-%s-001', Carbon::parse($today)->format('ymd')),
            'booking_date' => $today,
            'start_time' => '08:30:00',
            'end_time' => '16:30:00',
            'customer_name' => 'Johnathon Smith Group',
            'customer_phone' => '+61 412 345 678',
            'nationality' => 'Australia',
            'guest_count' => 12,
            'tour_package_id' => $createdPackages[0]->id,
            'location' => 'Ubud & Kintamani',
            'pickup_location' => 'The Mulia Nusa Dua',
            'price_per_guest' => 110000,
            'total_price' => 12 * 110000,
            'guide_fee_total' => 12 * 50000,
            'operational_cost' => 150000,
            'company_profit' => (12 * 110000) - (12 * 50000) - 150000,
            'status' => 'ON_GOING',
            'created_by' => $adminUser->id,
        ]);

        GuideAssignment::create([
            'booking_id' => $tBooking1->id,
            'guide_id' => $guides[2]->id, // Made
            'guest_count' => 12,
            'fee_type' => 'PER_GUEST',
            'fee_rate' => 50000,
            'fee_amount' => 600000,
            'queue_position' => 2,
            'assigned_at' => Carbon::parse($today . ' 07:50:00'),
            'started_at' => Carbon::parse($today . ' 08:30:00'),
            'status' => 'ON_GOING',
            'assigned_by' => $adminUser->id,
        ]);

        // Booking 2: Multi-Guide Demo booking for today (50 guests!)
        // As in example requirement 23 & 52:
        // 50 guests: Budi (20), Agus (15), Komang (15)
        $tBooking2 = Booking::create([
            'booking_code' => sprintf('BALI-%s-002', Carbon::parse($today)->format('ymd')),
            'booking_date' => $today,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'customer_name' => 'PT Mandiri Corporate Bali Gathering',
            'customer_phone' => '081299887766',
            'nationality' => 'Indonesia',
            'guest_count' => 50,
            'tour_package_id' => $createdPackages[3]->id,
            'location' => 'Bali Cultural & Heritage Tour',
            'pickup_location' => 'Ayana Resort Jimbaran',
            'price_per_guest' => 90000,
            'total_price' => 50 * 90000, // 4,500,000
            'guide_fee_total' => (20 * 50000) + (15 * 50000) + (15 * 50000), // 2,500,000
            'operational_cost' => 500000,
            'company_profit' => 4500000 - 2500000 - 500000, // 1,500,000
            'status' => 'FULLY_ASSIGNED',
            'created_by' => $staffUser->id,
        ]);

        GuideAssignment::create([
            'booking_id' => $tBooking2->id,
            'guide_id' => $guides[1]->id, // Budi
            'guest_count' => 20,
            'fee_type' => 'PER_GUEST',
            'fee_rate' => 50000,
            'fee_amount' => 1000000,
            'queue_position' => 1,
            'assigned_at' => Carbon::parse($today . ' 08:05:00'),
            'status' => 'ASSIGNED',
            'assigned_by' => $staffUser->id,
        ]);

        GuideAssignment::create([
            'booking_id' => $tBooking2->id,
            'guide_id' => $guides[5]->id, // Agus
            'guest_count' => 15,
            'fee_type' => 'PER_GUEST',
            'fee_rate' => 50000,
            'fee_amount' => 750000,
            'queue_position' => 5,
            'assigned_at' => Carbon::parse($today . ' 08:06:00'),
            'status' => 'ASSIGNED',
            'assigned_by' => $staffUser->id,
        ]);

        GuideAssignment::create([
            'booking_id' => $tBooking2->id,
            'guide_id' => $guides[3]->id, // Komang
            'guest_count' => 15,
            'fee_type' => 'PER_GUEST',
            'fee_rate' => 50000,
            'fee_amount' => 750000,
            'queue_position' => 3,
            'assigned_at' => Carbon::parse($today . ' 08:07:00'),
            'status' => 'ASSIGNED',
            'assigned_by' => $staffUser->id,
        ]);

        Expense::create([
            'booking_id' => $tBooking2->id,
            'category' => 'Sewa Bus Pariwisata',
            'description' => 'Sewa bus pariwisata 50 seat gathering',
            'amount' => 500000,
            'expense_date' => $today,
            'created_by' => $adminUser->id,
        ]);

        // Booking 3: Today Partially Assigned (25 guests, 15 assigned, 10 remaining)
        $tBooking3 = Booking::create([
            'booking_code' => sprintf('BALI-%s-003', Carbon::parse($today)->format('ymd')),
            'booking_date' => $today,
            'start_time' => '13:00:00',
            'end_time' => '19:00:00',
            'customer_name' => 'Chloe Dupont & Family',
            'customer_phone' => '+33 6 12 34 56 78',
            'nationality' => 'France',
            'guest_count' => 25,
            'tour_package_id' => $createdPackages[2]->id,
            'location' => 'Temple & Sunset Tour',
            'pickup_location' => 'W Bali Seminyak',
            'price_per_guest' => 90000,
            'total_price' => 25 * 90000,
            'guide_fee_total' => 0,
            'operational_cost' => 0,
            'company_profit' => 25 * 90000,
            'status' => 'WAITING',
            'created_by' => $staffUser->id,
        ]);

        // Booking 4: Today Waiting for assignment (8 guests)
        Booking::create([
            'booking_code' => sprintf('BALI-%s-004', Carbon::parse($today)->format('ymd')),
            'booking_date' => $today,
            'start_time' => '14:00:00',
            'end_time' => '20:00:00',
            'customer_name' => 'Kenji Tanaka',
            'customer_phone' => '+81 90 1234 5678',
            'nationality' => 'Japan',
            'guest_count' => 8,
            'tour_package_id' => $createdPackages[1]->id,
            'location' => 'Kintamani Volcano & Coffee Tour',
            'pickup_location' => 'Padma Resort Legian',
            'price_per_guest' => 130000,
            'total_price' => 8 * 130000,
            'guide_fee_total' => 0,
            'operational_cost' => 0,
            'company_profit' => 8 * 130000,
            'status' => 'WAITING',
            'created_by' => $staffUser->id,
        ]);

        // Upcoming Bookings for tomorrow and next week (8 bookings)
        for ($k = 1; $k <= 8; $k++) {
            $uDate = Carbon::today()->addDays($k)->toDateString();
            $uCust = $sampleCustomers[($k + 2) % count($sampleCustomers)];
            $uPkg = $createdPackages[$k % count($createdPackages)];
            $uGuests = 6 + ($k * 2);
            $uPrice = $uPkg->getPriceForGuestCount($uGuests);

            Booking::create([
                'booking_code' => sprintf('BALI-%s-%03d', Carbon::parse($uDate)->format('ymd'), $k),
                'booking_date' => $uDate,
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'customer_name' => $uCust['name'],
                'customer_phone' => $uCust['phone'],
                'nationality' => $uCust['country'],
                'guest_count' => $uGuests,
                'tour_package_id' => $uPkg->id,
                'location' => $uPkg->name,
                'pickup_location' => 'Hotel Sanur / Nusa Dua',
                'price_per_guest' => $uPrice,
                'total_price' => $uGuests * $uPrice,
                'guide_fee_total' => 0,
                'operational_cost' => 0,
                'company_profit' => $uGuests * $uPrice,
                'status' => 'WAITING',
                'created_by' => $adminUser->id,
            ]);
        }

        // 8. Queue State Initialized
        // As in example requirement 2 & 51:
        // Last position = #3 (Komang). Next in line is #4 Wayan (if OFF, #5 Agus, etc.)
        GuideQueueState::updateOrCreate(
            ['id' => 1],
            [
                'current_position' => 3,
                'last_guide_id' => $guides[3]->id,
                'last_assignment_id' => null,
            ]
        );

        // 9. Queue History Samples
        QueueHistory::create([
            'previous_position' => 2,
            'next_position' => 3,
            'selected_guide_id' => $guides[3]->id,
            'skipped_guides' => [],
            'booking_id' => $tBooking2->id,
            'assigned_by' => $staffUser->id,
            'type' => 'AUTO',
            'notes' => 'Auto assignment #3 Komang (15 tamu)',
            'created_at' => Carbon::parse($today . ' 08:07:00'),
        ]);

        QueueHistory::create([
            'previous_position' => 5,
            'next_position' => 1,
            'selected_guide_id' => $guides[1]->id,
            'skipped_guides' => [],
            'booking_id' => $tBooking2->id,
            'assigned_by' => $staffUser->id,
            'type' => 'AUTO',
            'notes' => 'Rotation wrap around #5 -> #1 Budi (20 tamu)',
            'created_at' => Carbon::parse($today . ' 08:05:00'),
        ]);

        // 10. Guide Payments Samples (Payroll demo data)
        // Budi: Paid 3,000,000 out of fees
        GuidePayment::create([
            'guide_id' => $guides[1]->id,
            'payment_code' => 'PAY-' . Carbon::parse($yesterday)->format('ymd') . '-001',
            'amount' => 3000000,
            'payment_date' => $yesterday,
            'payment_method' => 'Bank Transfer BCA',
            'notes' => 'Pembayaran fee periode awal bulan',
            'created_by' => $adminUser->id,
        ]);

        // Made: Paid 2,500,000
        GuidePayment::create([
            'guide_id' => $guides[2]->id,
            'payment_code' => 'PAY-' . Carbon::parse($yesterday)->format('ymd') . '-002',
            'amount' => 2500000,
            'payment_date' => $yesterday,
            'payment_method' => 'Bank Transfer Mandiri',
            'notes' => 'Pembayaran fee tour',
            'created_by' => $adminUser->id,
        ]);

        // 11. Audit Log samples
        AuditLog::log('SYSTEM_SEED', 'System', 1, null, ['status' => 'Demo data seeded successfully']);
    }
}
