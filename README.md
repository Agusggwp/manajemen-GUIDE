# Bali Tour Guide Management System (ARTDEVATA)

Aplikasi Web Tour Guide Management System kelas enterprise untuk operasional usaha wisata di Bali. Sistem ini mengelola seluruh siklus operasional harian: pendaftaran guide, check-in harian, antrean berputar terus-menerus (*continuous circular rotating queue*), booking tamu, pembagian multi-guide per grup, perhitungan fee snapshot, penggajian/payroll guide, kalkulasi laba bersih perusahaan, dan audit log lengkap.

---

## 1. Project Overview & Fitur Utama

- **Continuous Circular Rotating Queue**: Antrean guide tidak di-reset ke #1 setiap hari, melainkan melanjutkan dari posisi assignment terakhir secara berputar (circular $1 \dots N \to 1$).
- **Smart Guide Auto-Assignment**: Algoritma pencarian otomatis guide berikutnya yang melewati guide status `OFF`, `INACTIVE`, `Belum Check-In`, `Sudah Checkout`, `Sedang On Tour`, atau memiliki jadwal booking bertabrakan (*schedule conflict*).
- **Race Condition Safe**: Menggunakan `DB::transaction()` dan `lockForUpdate()` pada state antrean untuk mencegah double-assignment ketika multi-staff menekan tombol assign secara bersamaan.
- **Multi-Guide per Booking**: Satu booking rombongan besar (misal 50 tamu) dapat dibagi ke beberapa guide (misal 20 + 15 + 15) secara sekuensial mengikuti antrean fair rotation dengan validasi kuota tamu ketat.
- **Pricing & Fee Snapshot**: Harga per tamu bertingkat (*tiered pricing*) dan fee guide (*per guest, per booking, tiered*) dikunci sebagai snapshot riil saat transaksi dibuat sehingga perubahan harga master di masa depan tidak merusak laporan finansial lama.
- **Payroll & Pembayaran Guide**: Tracking total booking, jumlah tamu, akumulasi fee, status pembayaran (`UNPAID`, `PARTIAL`, `PAID`), dan validasi pencegahan overpayment.
- **Mobile-First Guide Portal**: Antarmuka khusus untuk guide di smartphone: check-in (opsional GPS), job harian, mulai/selesaikan tour, cek estimasi pendapatan, dan proteksi IDOR (guide tidak bisa melihat pendapatan guide lain).
- **Laporan & Ekspor Lengkap**: Laporan revenue, laporan performa guide, laporan absensi, dan riwayat putaran antrean (*queue rotation history*) dengan opsi filter tanggal serta export ke CSV dan Print.
- **Audit Log Terintegrasi**: Mencatat aksi krusial (login, check-in/out, force checkout, booking create/edit, assignment, payment, queue rotation).
- **Desain Khas ARTDEVATA**: Minimalis, clean, modern, berbasis Tailwind CSS dengan palet warna Primary `#14433b` dan Accent `#21c9a4`.

---

## 2. Kebutuhan Sistem (System Requirements)

- **PHP**: 8.2 atau 8.3+ (ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`)
- **Web Server**: Apache / Nginx / LiteSpeed (Shared Hosting compatible seperti Hostinger)
- **Database**: MySQL 8.0+ atau MariaDB 10.4+
- **Composer**: 2.5+
- **Node.js & NPM**: Node 18+ & NPM 9+ (untuk kompilasi aset Vite)

---

## 3. Instalasi & Setup Lokal

1. **Clone repository dan masuk ke direktori**:
   ```bash
   cd MANAJEMEN-GUIDE
   ```

2. **Install Dependensi PHP**:
   ```bash
   composer install --optimize-autoloader
   ```

3. **Install Dependensi Frontend & Build Aset**:
   ```bash
   npm install
   npm run build
   ```

4. **Konfigurasi Environment File**:
   Salin `.env.example` ke `.env` jika belum ada:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Konfigurasi Database di `.env`**:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=tour_guide_bali
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Jalankan Migrasi & Database Seeder**:
   ```bash
   php artisan migrate:fresh --seed
   ```

7. **Jalankan Server Lokal**:
   ```bash
   php artisan serve
   ```
   Akses aplikasi di browser pada: `http://localhost:8000`

---

## 4. Akun Demo & Hak Akses (Login Credentials)

Sistem sudah dilengkapi seeder data realistis dan tombol **Quick 1-Click Demo Login** pada halaman login:

| Role | Email | Password | Hak Akses Utama |
|---|---|---|---|
| **ADMIN** | `admin@example.com` | `password` | Akses penuh seluruh modul, kelola guide, rotasi queue, pricing, payroll, pembayaran, audit log, konfigurasi sistem, dan force check-out. |
| **STAFF** | `staff@example.com` | `password` | Membuat booking, assign/reassign guide, memantau antrean harian, melihat daftar tamu. Dilarang menghapus guide, mengubah settings, atau manipulasi payroll sembarangan. |
| **GUIDE (Budi)** | `budi@example.com` | `password` | Queue #1. Portal mobile: Check-in, checkout, start/complete tour, lihat earnings pribadi. |
| **GUIDE (Made)** | `made@example.com` | `password` | Queue #2. Portal mobile personal. |
| **GUIDE (Komang)** | `komang@example.com` | `password` | Queue #3. Portal mobile personal. |
| **GUIDE (Wayan)** | `wayan@example.com` | `password` | Queue #4. Portal mobile personal. |
| **GUIDE (Agus)** | `agus@example.com` | `password` | Queue #5. Portal mobile personal. |

---

## 5. Arsitektur Sistem & Struktur Database

### Service Layer Pattern
Seluruh logika bisnis murni dipisahkan dari Controller ke dalam `app/Services/`:
- `App\Services\GuideQueueService`: Algoritma circular queue, pencarian guide aktif, auto-assign atomik ber-locking, multi-guide sequential rotation, dan deteksi konflik jadwal (*schedule conflict*).
- `App\Services\GuideAttendanceService`: Check-in harian (dengan opsional lat/long), checkout aman dengan verifikasi tour aktif, start tour, dan complete tour.
- `App\Services\BookingService`: Perhitungan harga bertingkat (*tiered pricing snapshot*), kalkulasi laba perusahaan, alokasi kuota tamu, dan sinkronisasi status booking.
- `App\Services\PayrollService`: Agregasi upah guide, riwayat penugasan, kalkulasi sisa piutang (*remaining fee*), dan pencatatan pembayaran anti-overpayment.

### Tabel Utama:
1. `users`: Menyimpan kredensial dan role (`ADMIN`, `STAFF`, `GUIDE`).
2. `guides`: Data master guide (kode, nama, telepon, queue number permanen, status master, tipe fee).
3. `guide_attendances`: Absensi harian (unique per `guide_id` + `attendance_date`, status `NOT_CHECKED_IN`, `CHECKED_IN`, `CHECKED_OUT`, `OFF`).
4. `guide_queue_state`: Tabel 1 baris yang menyimpan posisi putaran terakhir (`current_position`), `last_guide_id`, dan `last_assignment_id`.
5. `queue_histories`: Riwayat audit perpindahan nomor antrean, guide yang dilewati beserta alasannya.
6. `tour_packages` & `tour_package_prices`: Paket wisata dengan harga per tamu berdasarkan tier kapasitas rombongan.
7. `bookings`: Data reservasi tamu, waktu mulai/selesai, snapshot harga per tamu, total revenue, dan estimasi laba.
8. `guide_assignments`: Rekaman penugasan guide, porsi jumlah tamu yang dipegang, snapshot tarif upah, posisi queue saat penugasan, dan status tour.
9. `guide_payments`: Bukti pembayaran fee guide yang diaudit.
10. `expenses`: Beban operasional wisata untuk kalkulasi laba bersih akurat.
11. `audit_logs`: Audit trail untuk seluruh event keamanan dan operasional.

---

## 6. Algoritma Continuous Circular Rotating Queue

### Prinsip Kerja
1. Urutan antrean didasarkan pada nomor permanen `queue_number` guide ($1, 2, 3, 4, 5$).
2. **Tidak Reset Antar Hari**: Jika pada hari Senin assignment terakhir berakhir di **#3 Komang**, maka pada hari Selasa pencarian akan langsung dimulai dari **#4 Wayan**, bukan reset ke #1.
3. **Pencarian Melingkar (Circular Search Sequence)**:
   $$\text{Search Order: } (\text{Position} + 1 \dots N) \to (1 \dots \text{Position})$$
4. **Kriteria Guide Tersedia (Available)**:
   - Terdaftar aktif (bukan `INACTIVE` atau `OFF`).
   - Sudah melakukan **CHECK-IN** pada tanggal booking terkait.
   - Belum melakukan **CHECK-OUT**.
   - Tidak sedang memegang status `ON_TOUR` saat jam tersebut.
   - Tidak memiliki jadwal booking lain yang bertabrakan (*overlap check*):
     $$\text{Conflict: } (\text{Start}_1 < \text{End}_2) \land (\text{End}_1 > \text{Start}_2)$$
5. **Guide yang Dilewati (Skipped)**:
   Guide yang tidak hadir/OFF/bentrok akan dilewati otomatis dan **tidak dianggap mendapatkan giliran**. Posisi antrean hanya berpindah ke guide yang benar-benar berhasil menerima assignment.
6. **Multi-Guide Assignment**:
   Jika booking membutuhkan 3 guide (misal 50 tamu: 20 + 15 + 15), sistem melakukan 3 kali putaran berturut-turut. Setiap guide yang dialokasikan memperbarui `current_position` sehingga booking selanjutnya akan melanjutkan dari guide berikutnya.

---

## 7. Keamanan & Transaction Safety

1. **Pencegahan Race Condition**:
   ```php
   DB::transaction(function () {
       $state = GuideQueueState::where('id', 1)->lockForUpdate()->first();
       // Cari guide, create assignment, update queue state
   });
   ```
2. **Role-Based Access Control (RBAC)**:
   Dilindungi via middleware `RoleMiddleware` dan Gate/Policy Laravel.
3. **Proteksi IDOR**:
   Guide hanya memiliki akses ke rute portal guide miliknya. Rute modul admin, daftar guide lain, dan payroll orang lain mengembalikan respons `403 Forbidden`.
4. **Pencegahan Overpayment & Validasi Server-Side**:
   Nominal fee, kuota tamu, dan harga paket dihitung dan divalidasi ulang di backend. Browser tidak dapat memanipulasi angka perhitungan.

---

## 8. Pengujian Otomatis (Automated Testing)

Aplikasi memiliki rangkaian automated tests komprehensif mencakup 24 test cases (82 assertions):
- **AcceptanceTest (13 Skenario Wajib)**:
  - Test 1: 5 guide, last #3, #4 available $\to$ terpilih #4.
  - Test 2: Last #3, #4 OFF, #5 available $\to$ terpilih #5.
  - Test 3: Last #5 $\to$ wrap around ke #1.
  - Test 4: Last #3, #4 OFF, #5 OFF, #1 OFF, #2 available $\to$ terpilih #2.
  - Test 5: Semua guide OFF $\to$ Tolak penugasan, posisi tidak berubah.
  - Test 6: Last #3, butuh 3 guide $\to$ #4, #5, #1 terpilih, posisi akhir #1.
  - Test 7: Proteksi race condition assignment ganda.
  - Test 8: Perubahan hari tidak mereset queue ke #1.
  - Test 9: Guide check-in dapat masuk queue.
  - Test 10: Guide checkout dilarang menerima assignment baru.
  - Test 11: Guide dengan tour aktif dilarang checkout.
  - Test 12: Booking 50 tamu dialokasikan 20 + 15 + 15 $\to$ status `FULLY_ASSIGNED`.
  - Test 13: Booking 30 tamu dialokasikan 20 + 15 $\to$ ditolak karena $35 > 30$.
- **AuthorizationTest**:
  - Hak akses Admin ke seluruh modul.
  - Pembatasan hak akses Staff.
  - Proteksi portal Guide dan IDOR data finansial guide lain.
- **PricingAndPayrollTest**:
  - Pemilihan otomatis tiered pricing.
  - Preservasi snapshot harga booking.
  - Preservasi snapshot fee guide.
  - Validasi pencegahan overpayment fee guide.
  - Deteksi konflik waktu/overlap tour guide.

Untuk menjalankan seluruh test suite:
```bash
php artisan test
```

---

## 9. Panduan Deploy ke Hostinger (Shared Hosting)

Aplikasi dirancang ringan tanpa server node.js persisten (menggunakan Blade + Tailwind CSS pre-compiled + Alpine.js):

1. **Persiapan di Komputer Lokal**:
   Kompilasi aset produksi:
   ```bash
   npm run build
   composer install --no-dev --optimize-autoloader
   ```
2. **Upload Berkas**:
   - Upload seluruh folder project ke root hosting (misal `/home/u123456/tour_guide_app`).
   - Arahkan Document Root domain/subdomain di hPanel Hostinger ke folder `/home/u123456/tour_guide_app/public`.
   - Atau salin isi folder `public/` ke dalam `public_html/`, lalu sesuaikan path `require __DIR__ . '/../vendor/autoload.php'` dan `bootstrap/app.php` pada `index.php`.
3. **Konfigurasi Database di hPanel**:
   - Buat database MySQL baru dan user database melalui menu *MySQL Databases*.
   - Edit file `.env` di server sesuai nama database, user, dan password Hostinger.
   - Set `APP_ENV=production` dan `APP_DEBUG=false`.
4. **Migrasi Database**:
   Melalui Terminal SSH hPanel:
   ```bash
   php artisan migrate --seed --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
5. **Storage Symlink**:
   ```bash
   php artisan storage:link
   ```

---

## 10. Lisensi

Dikembangkan secara khusus untuk operasional usaha pariwisata di Bali dengan standar arsitektur ARTDEVATA. Hak Cipta dilindungi.
# manajemen-GUIDE
