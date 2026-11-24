# PRD Backend: Arsitektur & Logika Bisnis Reservasi Hotel

Dokumen ini mendetailkan spesifikasi teknis sisi server (backend) untuk mendukung fungsionalitas yang didefinisikan dalam `PRD.md` dan `PRD-Frontend.md`. Fokus dokumen ini pada skema database, service layer, middleware, background processing, dan integrasi pihak ketiga. Acuan tampilan ada di `PRD-Frontend.md`.

---

## 1. Arsitektur Backend

Menggunakan pola **Controller -> Service -> Model** untuk memisahkan logika HTTP, logika bisnis, dan akses data.

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Public/            # HomeController, RoomController
│   │   ├── Customer/          # BookingController, PaymentController, ProfileController, ReviewController
│   │   ├── Staff/             # TapeChart, WalkIn, FrontDesk, PaymentVerification, Housekeeping
│   │   └── Admin/             # RoomType, Room, Amenity, Addon, Pricing, Season, Voucher, User, Report
│   ├── Requests/              # FormRequest validasi
│   └── Middleware/            # RoleMiddleware
├── Models/
├── Services/                  # AvailabilityService, PricingService, BookingService, PaymentService, ReportService
├── Jobs/                      # SendBookingVoucher, SendWhatsAppNotification, SendPaymentReminder
├── Mail/
├── Notifications/
└── Console/Commands/          # ExpirePendingBookings, DailyHousekeepingReset
```

- **Controllers:** Menangani request HTTP, memanggil FormRequest untuk validasi, mendelegasikan logika ke Service, dan mengembalikan Blade view / redirect / JSON.
- **Services:** Jantung aplikasi. Menangani kalkulasi harga dinamis, pengecekan kuota, transaksi pemesanan, reschedule, dan verifikasi pembayaran. Tidak boleh berisi kode HTTP.
- **Models:** Representasi data dengan Eloquent ORM, termasuk relasi, `$casts`, scope query, dan accessor (mis. `total_nights`, `is_reschedulable`).
- **FormRequest:** Validasi input terpusat (`StoreBookingRequest`, `WalkInBookingRequest`, `RescheduleRequest`, `ManualPaymentRequest`, `RoomTypeRequest`, dst).

---

## 2. Skema Database (Entitas Utama)

### 2.1 Manajemen Kamar

| Tabel | Kolom Penting |
|-------|---------------|
| `room_types` | `id`, `name`, `slug`, `description`, `base_price`, `weekend_price`, `capacity`, `size`, `bed_type`, `is_active` |
| `rooms` | `id`, `room_type_id`, `room_number` (unique), `floor`, `status` (`available`, `occupied`, `dirty`, `cleaning`, `maintenance`), `notes` |
| `amenities` | `id`, `name`, `icon` |
| `room_type_amenity` | `room_type_id`, `amenity_id` (pivot) |
| `room_type_images` | `id`, `room_type_id`, `image_path`, `is_primary`, `sort_order` |
| `addons` | `id`, `name`, `price`, `unit_type` (`per_night`, `per_person`, `fixed`), `is_active` |

### 2.2 Reservasi & Transaksi

| Tabel | Kolom Penting |
|-------|---------------|
| `bookings` | `id`, `booking_code` (unique, mis. `BK-20261124-XXXX`), `user_id` (**nullable** untuk walk-in), `customer_name`, `customer_email`, `customer_phone`, `identity_type`, `identity_number`, `check_in_date`, `check_out_date`, `total_nights`, `adults`, `children`, `status`, `payment_status`, `payment_scheme` (`full`/`dp_50`), `subtotal`, `addon_total`, `discount_amount`, `tax_amount`, `total_amount`, `dp_amount`, `remaining_amount`, `reschedule_count`, `expires_at`, `notes` |
| `booking_details` | `id`, `booking_id`, `room_type_id`, `room_id` (nullable sampai check-in), `price_per_night` (JSON breakdown per tanggal) |
| `booking_addons` | `id`, `booking_id`, `addon_id`, `quantity`, `unit_price`, `total_price` |
| `payments` | `id`, `booking_id`, `transaction_id`, `payment_type` (`gateway`/`manual`), `payment_method` (VA/QRIS/EWallet/Transfer/Cash/EDC), `amount`, `proof_file`, `status` (`pending`, `success`, `failed`, `rejected`), `verified_by`, `verified_at`, `notes` |
| `booking_status_logs` | `id`, `booking_id`, `old_status`, `new_status`, `changed_by`, `notes` (timeline di dasbor tamu) |
| `extra_charges` | `id`, `booking_id`, `description`, `amount`, `added_by` (minibar, kerusakan, late check-out) |
| `reschedules` | `id`, `booking_id`, `old_check_in`, `old_check_out`, `new_check_in`, `new_check_out`, `price_difference`, `requested_by` |
| `cancellations` | `id`, `booking_id`, `reason`, `refund_eligible`, `refund_status`, `processed_by` |

**Enum status booking:** `pending_payment`, `waiting_verification`, `confirmed`, `checked_in`, `checked_out`, `cancelled`, `expired`
**Enum payment status:** `unpaid`, `dp_paid`, `paid`, `refunded`, `rejected`

### 2.3 Harga & Promo

| Tabel | Kolom Penting |
|-------|---------------|
| `seasonal_prices` | `id`, `room_type_id`, `name`, `start_date`, `end_date`, `price`, `priority` |
| `vouchers` | `id`, `code` (unique), `type` (`fixed`/`percent`), `value`, `min_spend`, `max_discount`, `start_date`, `end_date`, `quota`, `used_count`, `is_active` |
| `voucher_usages` | `id`, `voucher_id`, `booking_id`, `user_id`, `discount_applied` |

### 2.4 Operasional & Feedback

| Tabel | Kolom Penting |
|-------|---------------|
| `reviews` | `id`, `booking_id` (unique), `user_id`, `rating` (1–5), `comment`, `is_published` |
| `room_status_logs` | `id`, `room_id`, `user_id`, `old_status`, `new_status`, `notes`, `created_at` |
| `daily_occupancy_summaries` | `id`, `date`, `rooms_sold`, `rooms_available`, `occupancy_rate`, `revenue` (hasil agregasi scheduler) |

**Catatan index:** `bookings(check_in_date, check_out_date, status)`, `rooms(room_type_id, status)`, `payments(booking_id, status)`, `seasonal_prices(room_type_id, start_date, end_date)`.

---

## 3. Service Layer (Logika Bisnis Inti)

### 3.1 `AvailabilityService`

| Method | Tanggung Jawab |
|--------|----------------|
| `checkAvailability(int $roomTypeId, Carbon $checkIn, Carbon $checkOut): int` | Sisa kuota = total unit tipe kamar − booking aktif (`pending_payment`, `waiting_verification`, `confirmed`, `checked_in`) yang tanggalnya beririsan |
| `getAvailablePhysicalRooms(int $roomTypeId, Carbon $checkIn, Carbon $checkOut): Collection` | Unit `rooms` yang tidak terpakai pada rentang tanggal tersebut |
| `lockAndValidateQuota(int $roomTypeId, Carbon $checkIn, Carbon $checkOut): void` | Dipanggil di dalam `DB::transaction` + `lockForUpdate()`; melempar `RoomUnavailableException` bila kuota habis |
| `isDateRangeValid($checkIn, $checkOut): bool` | `check_out > check_in`, minimal 1 malam, `check_in >= today` |

**Aturan irisan tanggal:** booking A beririsan dengan B jika `A.check_in < B.check_out && A.check_out > B.check_in`. Tanggal check-out tidak dihitung sebagai malam menginap.

### 3.2 `PricingService`

| Method | Tanggung Jawab |
|--------|----------------|
| `calculateNightlyRate(RoomType $type, Carbon $date): float` | Prioritas: `seasonal_prices` (rentang tanggal cocok) > weekend (`dayOfWeek` Jumat/Sabtu) > `base_price` |
| `calculateBookingTotal(...): PriceBreakdown` | Loop tiap malam, jumlahkan tarif, hitung add-on, terapkan voucher, hitung pajak & service charge, tentukan DP 50% dan sisa |
| `getDailyRateBreakdown(RoomType $type, Carbon $checkIn, Carbon $checkOut): array` | Rincian `[tanggal, tipe_tarif, harga]` untuk halaman detail & checkout |
| `applyVoucher(Voucher $voucher, float $subtotal): float` | Validasi masa berlaku, kuota, `min_spend`; hitung diskon fixed/percent dengan `max_discount` |

**Urutan kalkulasi baku:**
1. Subtotal kamar = Σ tarif per malam
2. Subtotal add-on
3. Diskon voucher (dihitung dari subtotal kamar + add-on)
4. Pajak & service charge (dari nilai setelah diskon)
5. `total_amount`
6. `dp_amount` = 50% × `total_amount` (jika skema DP), `remaining_amount` = sisanya

### 3.3 `BookingService`

| Method | Tanggung Jawab |
|--------|----------------|
| `createBooking(array $data): Booking` | `DB::transaction` → lock kuota → simpan `bookings`, `booking_details`, `booking_addons` → increment `vouchers.used_count` → set `expires_at = now()->addHours(2)` → log status → dispatch job notifikasi |
| `createWalkInBooking(array $data): Booking` | Sama seperti di atas, `user_id = null`, langsung alokasi `room_id`, status dapat langsung `checked_in` |
| `processReschedule(Booking $booking, Carbon $newIn, Carbon $newOut): Booking` | Validasi `reschedule_count < 1`, `now() <= check_in - 72 jam`, status `confirmed`; cek kuota tanggal baru; hitung selisih harga; simpan ke `reschedules`; increment counter |
| `processCancellation(Booking $booking, string $reason): Booking` | Validasi status; tentukan `refund_eligible` (≥ H-3); set `cancelled`; lepas kuota & voucher |
| `checkIn(Booking $booking, Room $room, array $identity): Booking` | Validasi status `confirmed`, kamar berstatus `available`, kamar belum dipakai booking lain; set `checked_in`, `room_id`, `rooms.status = occupied` |
| `checkOut(Booking $booking, array $extraCharges): Booking` | Hitung total tagihan akhir; set `checked_out`; `rooms.status = dirty`; catat `room_status_logs` |
| `expirePendingBookings(): int` | Query booking `pending_payment` dengan `expires_at < now()` → set `expired`, lepas kuota (dipanggil scheduler) |

### 3.4 `PaymentService`

| Method | Tanggung Jawab |
|--------|----------------|
| `createGatewayTransaction(Booking $booking): object` | Request ke Midtrans/Xendit, simpan `transaction_id`, kembalikan token/redirect URL |
| `handleWebhook(array $payload): void` | Verifikasi signature, cocokkan `order_id` → `booking_code`, update `payments` + `bookings.payment_status`/`status`, dispatch E-Voucher |
| `submitManualPayment(Booking $booking, UploadedFile $proof, array $meta): Payment` | Simpan file ke storage privat, buat record `payments` status `pending`, set booking `waiting_verification` |
| `verifyManualPayment(Payment $payment, string $decision, ?string $notes): Payment` | Setujui → `success` + booking `confirmed`; tolak → `rejected` + booking kembali `pending_payment` (timer tetap) |
| `recordExtraCharge(Booking $booking, string $desc, float $amount)` | Menambah tagihan akhir saat check-out |
| `markRefund(Booking $booking, float $amount)` | Mencatat refund manual oleh admin |

---

## 4. Keamanan & Role Access

Gunakan middleware `role:admin|receptionist|housekeeping` (custom middleware atau Spatie Permission).

| Role | Hak Akses Utama |
|------|-----------------|
| **guest** (tamu) | Kelola profil sendiri, membuat booking, unggah bukti bayar, lihat booking milik sendiri (`user_id`), reschedule, cancel, beri ulasan |
| **receptionist** | Tape chart, walk-in, check-in/out, alokasi nomor kamar, verifikasi pembayaran manual |
| **housekeeping** | Papan status kamar, ubah status `dirty → cleaning → available`, lapor `maintenance` |
| **admin** | Full access: master data, tarif & voucher, manajemen staf, laporan |

**Aturan otorisasi tambahan:**
- Tamu hanya bisa mengakses booking dengan `user_id` miliknya (`Policy: view/update`).
- Walk-in booking (`user_id = null`) hanya bisa diakses staf.
- Aksi reschedule/cancel/ulasan divalidasi di Service, bukan hanya di tampilan.

---

## 5. Pemrosesan Latar Belakang (Queue & Scheduler)

### 5.1 Laravel Queue (Jobs)

| Job | Trigger | Isi |
|-----|---------|-----|
| `SendBookingVoucherJob` | Booking `confirmed` | Generate PDF E-Voucher + Invoice, kirim email |
| `SendWhatsAppNotificationJob` | Booking dibuat / sukses bayar / H-1 / setelah check-out | Kirim pesan via Fonnte/Wablas |
| `SendPaymentReminderJob` | 30 menit sebelum `expires_at` | Pengingat pembayaran |
| `GenerateInvoicePdfJob` | Permintaan unduh invoice | Generate & simpan PDF |

Semua job memakai `retry`, `backoff`, dan `failed()` handler agar kegagalan pengiriman tidak memblokir transaksi utama.

### 5.2 Laravel Scheduler

| Command | Jadwal | Aksi |
|---------|--------|------|
| `booking:auto-expire` | Setiap 5 menit | `pending_payment` melewati `expires_at` → `expired`, lepas kuota & voucher |
| `booking:reminder-h1` | Setiap hari 09.00 | Kirim reminder WhatsApp ke tamu check-in besok |
| `report:daily-summary` | Setiap hari 00.05 | Agregasi pendapatan & okupansi ke `daily_occupancy_summaries` |
| `housekeeping:daily-reset` | Setiap hari 12.00 | Kamar lewat jam check-out → `dirty` |

---

## 6. Integrasi Pihak Ketiga

### 6.1 Payment Gateway (Midtrans / Xendit)
- Mode: Snap popup atau redirect.
- Endpoint webhook: `POST /api/payments/webhook` (dikecualikan dari CSRF, diverifikasi signature key).
- Pemetaan status gateway → sistem:
  - `settlement` / `capture` → `success`, booking `confirmed`, `payment_status` `paid` atau `dp_paid`.
  - `pending` → biarkan `pending_payment`.
  - `deny` / `expire` / `cancel` → `failed`, booking tetap `pending_payment` sampai scheduler expire.
- Webhook bersifat idempoten (cek `transaction_id` sebelum update).

### 6.2 WhatsApp Gateway (Fonnte / Wablas)
- HTTP Client dengan timeout & retry; kredensial di `.env` (`WA_GATEWAY_TOKEN`, `WA_GATEWAY_URL`).
- Template pesan memakai placeholder: `{nama}`, `{kode_booking}`, `{check_in}`, `{total}`, `{link_pembayaran}`.

### 6.3 Konfigurasi `.env` tambahan
```
PAYMENT_GATEWAY=midtrans
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
WA_GATEWAY=fonnte
WA_GATEWAY_TOKEN=
WA_GATEWAY_URL=
BOOKING_PAYMENT_EXPIRY_HOURS=2
RESCHEDULE_MIN_HOURS=72
```

---

## 7. Validasi Data & Constraint

### 7.1 Pencegahan Race Condition
```php
DB::transaction(function () use ($data) {
    $roomType = RoomType::whereKey($data['room_type_id'])->lockForUpdate()->firstOrFail();

    if (! $this->availability->checkAvailability(...) >= $data['rooms']) {
        throw new RoomUnavailableException();
    }

    return $this->createBookingRecords($data);
});
```

### 7.2 Aturan Bisnis yang Wajib Divalidasi di Backend

| Aturan | Titik Validasi |
|--------|----------------|
| `check_out_date > check_in_date`, min 1 malam | `StoreBookingRequest`, `AvailabilityService` |
| Kuota tipe kamar mencukupi | `BookingService@createBooking` (dalam transaction) |
| Booking hanya untuk user yang login (kecuali walk-in) | `auth` middleware + `user_id` dari `Auth::id()` |
| Reschedule maksimal 1x dan ≥ H-3 | `BookingService@processReschedule` |
| Check-in hanya ke kamar `available` | `BookingService@checkIn` |
| Voucher valid: aktif, dalam periode, `used_count < quota`, `min_spend` terpenuhi | `PricingService@applyVoucher` |
| Booking `pending_payment` kedaluwarsa setelah 2 jam | `ExpirePendingBookings` command |
| Refund hanya bila pembatalan ≥ H-3 | `BookingService@processCancellation` |
| Bukti bayar manual hanya file gambar/PDF, maks 5 MB | `ManualPaymentRequest` |

---

## 8. Laporan & Analitik (Logic)

- **Occupancy Rate** = `(kamar terjual / total kamar tersedia) × 100` per hari, dirata-rata pada rentang laporan.
- **Revenue** = Σ `total_amount` booking dengan status `confirmed`, `checked_in`, `checked_out` (dikelompokkan per tanggal check-in atau tanggal pembayaran sesuai kebutuhan laporan).
- **Export:** `maatwebsite/excel` untuk `.xlsx`, `barryvdh/laravel-dompdf` untuk PDF.
- **ReportService:** `dailyRevenue($from, $to)`, `occupancyRate($from, $to)`, `revenueByRoomType($from, $to)`, `exportExcel()`, `exportPdf()`.

---

## 9. Testing & Kualitas

- **Feature test wajib:** pembuatan booking bersamaan (race condition), auto-expire, reschedule melewati batas, check-in ke kamar `dirty`, verifikasi pembayaran manual, kalkulasi harga weekend/high season.
- **Unit test:** `PricingService` (prioritas tarif & voucher), `AvailabilityService` (irisan tanggal).
- **Seeder demo:** tipe kamar, unit kamar, tarif seasonal, voucher contoh, akun staf per role.

---

## 10. Urutan Implementasi Backend

| Fase | Cakupan |
|------|---------|
| **1. Fondasi** | Migrasi & model, auth + role middleware, master data CRUD, seeder |
| **2. Pricing & Availability** | `PricingService`, `AvailabilityService`, unit test kalkulasi |
| **3. Booking** | `BookingService` (create, walk-in), checkout, kode booking, auto-expire |
| **4. Payment** | Gateway (Midtrans/Xendit), webhook, transfer manual, verifikasi staf |
| **5. Operasional** | Check-in/out, alokasi kamar, extra charge, status housekeeping |
| **6. Notifikasi & Laporan** | Queue email/WA, scheduler, `ReportService`, ekspor Excel/PDF |
