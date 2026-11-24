# PRD Frontend: Tampilan Website Reservasi Hotel & Penginapan

Dokumen ini menurunkan kebutuhan pada `PRD.md` menjadi daftar tampilan (halaman, komponen, dan state UI) yang akan dibangun dengan **Laravel Blade**. Fokus dokumen ini hanya pada lapisan presentasi; logika bisnis berada di Service Layer (AvailabilityService, PricingService, BookingService, PaymentService).

---

## 1. Prinsip Desain Tampilan

1. **Dua kerangka terpisah**
   - **Layout Publik** (`resources/views/landing/layouts/`) untuk tamu: beranda, katalog kamar, checkout, pembayaran, dasbor tamu. Nuansa marketing (hero, galeri, CTA).
   - **Layout Panel Staf** (`resources/views/admin/layouts/`) untuk admin, resepsionis, housekeeping. Nuansa aplikasi (sidebar, topbar, tabel, form padat data).
2. **Satu komponen, banyak role.** Halaman panel memakai layout yang sama; menu sidebar ditampilkan bersyarat berdasarkan `role` (`admin`, `receptionist`, `housekeeping`).
3. **Mobile-first untuk operasional.** Papan status housekeeping dan proses check-in/check-out harus nyaman dipakai dari ponsel/tablet di lapangan.
4. **Status selalu terlihat.** Setiap entitas punya representasi visual status yang konsisten (badge warna): reservasi, pembayaran, dan kondisi kamar fisik.
5. **Akses tanpa login untuk eksplorasi.** Pencarian dan katalog kamar terbuka; tombol "Pesan" memicu `auth` middleware dan kembali ke checkout lewat `redirect()->intended()`.

---

## 2. Arsitektur Tampilan

```text
resources/views/
├── landing/                      # Layout publik (tamu)
│   ├── layouts/
│   │   ├── app.blade.php         # kerangka utama publik
│   │   ├── head.blade.php        # meta, CSS, font
│   │   ├── header.blade.php      # navbar publik + tombol Login/Dasbor
│   │   ├── footer.blade.php
│   │   └── scripts.blade.php
│   ├── home.blade.php
│   ├── rooms/
│   ├── booking/
│   ├── payment/
│   └── customer/
├── admin/                        # Layout panel staf
│   ├── layouts/
│   │   ├── app.blade.php         # kerangka panel (sidebar + topbar + konten)
│   │   ├── head.blade.php
│   │   ├── sidebar.blade.php     # menu bersyarat per role
│   │   ├── topbar.blade.php      # profil, notifikasi, toggle sidebar
│   │   ├── chat.blade.php
│   │   ├── footer.blade.php
│   │   └── scripts.blade.php
│   ├── dashboard.blade.php
│   ├── master/
│   ├── pricing/
│   ├── users/
│   ├── receptionist/
│   ├── housekeeping/
│   └── reports/
├── auth/                         # login, register, lupa password
└── components/                   # komponen Blade reusable (lihat bagian 7)
```

**Pemisahan aset:** `public/landing/` untuk CSS/JS/gambar publik, `public/admin/` (atau `dist/`) untuk aset panel. Tidak ada aset panel yang dimuat di halaman publik.

---

## 3. Peta Halaman

| # | Halaman | Route (usulan) | Controller@method | Blade | Akses |
|---|---------|----------------|-------------------|-------|-------|
| 1 | Beranda | `GET /` | `HomeController@index` | `landing/home` | Publik |
| 2 | Katalog & Pencarian Kamar | `GET /rooms` | `RoomController@index` | `landing/rooms/index` | Publik |
| 3 | Detail Tipe Kamar | `GET /rooms/{roomType}` | `RoomController@show` | `landing/rooms/show` | Publik |
| 4 | Login | `GET/POST /login` | `AuthController` | `auth/login` | Publik |
| 5 | Registrasi | `GET/POST /register` | `AuthController` | `auth/register` | Publik |
| 6 | Checkout Pemesanan | `GET /booking/checkout` | `BookingController@checkout` | `landing/booking/checkout` | Tamu (login) |
| 7 | Konfirmasi & Buat Pesanan | `POST /booking` | `BookingController@store` | redirect ke pembayaran | Tamu |
| 8 | Pembayaran & Unggah Bukti | `GET /booking/{code}/payment` | `PaymentController@show` | `landing/payment/show` | Tamu (pemilik) |
| 9 | Status Pembayaran Berhasil | `GET /booking/{code}/success` | `PaymentController@success` | `landing/payment/success` | Tamu |
| 10 | Dasbor Tamu — Reservasi | `GET /my/bookings` | `CustomerBookingController@index` | `landing/customer/bookings` | Tamu |
| 11 | Detail Reservasi + E-Voucher | `GET /my/bookings/{code}` | `CustomerBookingController@show` | `landing/customer/booking-detail` | Tamu |
| 12 | Form Reschedule | `GET/POST /my/bookings/{code}/reschedule` | `CustomerBookingController@reschedule` | `landing/customer/reschedule` | Tamu |
| 13 | Form Pembatalan | `POST /my/bookings/{code}/cancel` | `CustomerBookingController@cancel` | modal di `booking-detail` | Tamu |
| 14 | Form Ulasan | `POST /my/bookings/{code}/review` | `ReviewController@store` | modal di `booking-detail` | Tamu |
| 15 | Profil Akun | `GET/PUT /my/profile` | `ProfileController` | `landing/customer/profile` | Tamu |
| 16 | Dashboard Staf | `GET /dashboard` | `AdminController@index` | `admin/dashboard` | admin, receptionist, housekeeping (varian) |
| 17 | Kalender Okupansi (Tape Chart) | `GET /receptionist/tape-chart` | `TapeChartController@index` | `admin/receptionist/tape-chart` | admin, receptionist |
| 18 | Walk-in Booking | `GET/POST /receptionist/walk-in` | `WalkInController` | `admin/receptionist/walk-in` | admin, receptionist |
| 19 | Daftar Check-in / Check-out | `GET /receptionist/front-desk` | `FrontDeskController@index` | `admin/receptionist/front-desk` | admin, receptionist |
| 20 | Verifikasi Pembayaran Manual | `GET /receptionist/payments` | `PaymentVerificationController@index` | `admin/receptionist/payments` | admin, receptionist |
| 21 | Papan Status Kamar | `GET /housekeeping/rooms` | `HousekeepingController@index` | `admin/housekeeping/room-board` | admin, housekeeping |
| 22 | Master Tipe Kamar | `GET /admin/room-types` | `RoomTypeController@index` | `admin/master/room-types/index` | admin |
| 23 | Form Tipe Kamar | `GET/POST /admin/room-types/{id}` | `RoomTypeController` | `admin/master/room-types/form` | admin |
| 24 | Master Unit Kamar Fisik | `GET /admin/rooms` | `RoomController@index` | `admin/master/rooms/index` | admin |
| 25 | Master Fasilitas (Amenities) | `GET /admin/amenities` | `AmenityController@index` | `admin/master/amenities/index` | admin |
| 26 | Master Add-ons | `GET /admin/addons` | `AddonController@index` | `admin/master/addons/index` | admin |
| 27 | Manajemen Tarif | `GET /admin/pricing` | `PricingController@index` | `admin/pricing/index` | admin |
| 28 | Tarif High Season | `GET /admin/pricing/seasons` | `SeasonRateController@index` | `admin/pricing/seasons` | admin |
| 29 | Manajemen Voucher / Promo | `GET /admin/vouchers` | `VoucherController@index` | `admin/pricing/vouchers` | admin |
| 30 | Manajemen Pengguna & Role | `GET /admin/users` | `UserController@index` | `admin/users/index` | admin |
| 31 | Laporan & Analitik | `GET /admin/reports` | `ReportController@index` | `admin/reports/index` | admin |

> Route akhir dapat menyesuaikan; tabel ini menjadi acuan struktur file Blade.

---

## 4. Detail Halaman — Area Publik

### 4.1 Beranda (`landing/home.blade.php`)

| Blok | Isi |
|------|-----|
| Hero | Banner utama, headline, subheadline, CTA "Cek Ketersediaan" |
| Search Widget | Input `check_in_date`, `check_out_date`, `rooms`, `adults`, `children` + tombol cari. Sticky di bawah hero saat scroll |
| Highlight Kamar | 3–4 kartu tipe kamar unggulan: foto, nama, kapasitas, "mulai dari Rp X/malam", tombol detail |
| Fasilitas Hotel | Grid ikon fasilitas umum (kolam renang, restoran, Wi-Fi, parkir, resepsionis 24 jam) |
| Keunggulan / Why Us | 3 poin: harga langsung, tanpa biaya agen, konfirmasi instan |
| Ulasan Tamu | Slider kartu ulasan (nama, rating bintang, kutipan) |
| CTA Promo | Banner kode promo aktif (opsional, dari data voucher) |
| Footer | Alamat, kontak, jam check-in 14.00 / check-out 12.00, tautan navigasi, sosial media |

**State:** default; jika tidak ada tipe kamar aktif → sembunyikan blok highlight.

### 4.2 Katalog & Pencarian Kamar (`landing/rooms/index.blade.php`)

- **Header Pencarian (sticky):** tanggal check-in/out, jumlah kamar, dewasa, anak, tombol "Ubah Pencarian".
- **Filter Panel** (sidebar desktop / drawer mobile):
  - Rentang harga (slider min–max)
  - Fasilitas (checkbox: AC, TV, Water Heater, Bathtub, Balkon, dll)
  - Kapasitas (jumlah tamu)
  - Urutkan: harga terendah, tertinggi, rekomendasi
- **Kartu Kamar:** foto utama + badge sisa kamar, nama tipe, luas m², kapasitas, 3 fasilitas teratas, **rincian tarif per malam** (dasar/weekend/high season sesuai tanggal), harga total untuk durasi terpilih, tombol "Pesan Sekarang".
- **State:**
  - `loading` — skeleton card saat filter dijalankan.
  - `empty` — ilustrasi "Kamar tidak tersedia untuk tanggal ini" + saran tanggal terdekat yang tersedia.
  - `error` — pesan validasi tanggal (check-out harus setelah check-in, minimal 1 malam).

### 4.3 Detail Tipe Kamar (`landing/rooms/show.blade.php`)

- **Galeri Foto:** grid/lightbox dengan thumbnail.
- **Info Kamar:** nama tipe, luas, tipe tempat tidur, kapasitas maksimal, deskripsi.
- **Daftar Fasilitas:** ikon + label lengkap.
- **Rincian Tarif:** tabel per tanggal menginap (menunjukkan mana yang weekday/weekend/high season) + catatan "Tarif akhir pekan berlaku Jumat & Sabtu malam".
- **Sidebar Booking:** pilih tanggal, jumlah tamu, tombol "Pesan Sekarang".
  - Jika belum login → tombol tetap tampil; klik menyimpan pilihan ke session lalu ke halaman login.
- **Kebijakan:** ringkasan reschedule (maks 1x, H-3) dan pembatalan.

### 4.4 Checkout Pemesanan (`landing/booking/checkout.blade.php`)

Halaman bertahap (stepper), semua data di ringkasan kanan (sticky).

| Langkah | Elemen |
|---------|--------|
| 1. Ringkasan Menginap | Tipe kamar, tanggal, jumlah malam, jumlah tamu, nomor kamar (jika sudah dialokasikan) |
| 2. Data Pemesan | Terisi otomatis dari profil; checkbox **"Pesan untuk orang lain"** membuka field nama, telepon, email tamu |
| 3. Layanan Tambahan | Daftar add-on dengan checkbox/counter (Extra Bed, Breakfast, Airport Pickup) + harga satuan |
| 4. Kode Promo | Input kode + tombol "Terapkan"; tampilkan diskon atau pesan error (kode tidak valid/kadaluarsa/kuota habis) |
| 5. Skema Pembayaran | Radio: **DP 50%** atau **Bayar Lunas**, dengan penjelasan sisa tagihan |
| 6. Ringkasan Biaya | Subtotal kamar, total add-on, pajak/layanan, diskon, **total bayar sekarang**, sisa pelunasan |
| Aksi | Tombol "Buat Pesanan" (disabled saat submit) |

**State:** promo berhasil/gagal, error validasi per field, konfirmasi kuota habis (jika kamar terambil di detik yang sama → pesan "Kamar baru saja dipesan, silakan pilih ulang").

### 4.5 Pembayaran (`landing/payment/show.blade.php`)

- **Ringkasan Pesanan:** kode booking, tipe kamar, tanggal, total, jumlah yang harus dibayar sekarang.
- **Countdown Timer:** sisa waktu bayar dari batas 2 jam (server-side `expires_at`), berubah merah saat < 15 menit, dan menampilkan state *expired* saat habis.
- **Metode Pembayaran:**
  - **Otomatis (Payment Gateway):** tab Virtual Account (daftar bank), QRIS (render QR), E-Wallet (GoPay/OVO/Dana). Tombol "Bayar Sekarang" → redirect ke halaman gateway.
  - **Transfer Manual:** instruksi nomor rekening + form unggah bukti (file gambar/PDF), input nama pengirim & bank asal, tombol "Kirim Bukti".
- **State:** menunggu pembayaran, menunggu verifikasi manual, pembayaran berhasil, kedaluwarsa (dengan tombol "Pesan Ulang").
- **Info:** E-Voucher dikirim via email setelah pembayaran terverifikasi.

### 4.6 Status Berhasil (`landing/payment/success.blade.php`)

- Ikon sukses, kode booking, ringkasan, tombol "Unduh E-Voucher", "Lihat Reservasi", dan info pengiriman email/WhatsApp.

### 4.7 Dasbor Tamu (`landing/customer/`)

**Navigasi:** Reservasi · Profil · (Logout)

- **`bookings.blade.php`** — daftar reservasi dalam kartu/tabel dengan filter status:
  - Badge status: `pending_payment`, `waiting_verification`, `confirmed`, `checked_in`, `checked_out`, `cancelled`, `expired`.
  - Tiap kartu: kode booking, tipe kamar, tanggal, total, tombol "Detail".
- **`booking-detail.blade.php`** — detail lengkap + aksi kontekstual:
  - Unduh **E-Voucher (PDF)** dan **Invoice (PDF)** (aktif setelah `confirmed`).
  - **Ajukan Reschedule** (aktif jika belum pernah reschedule dan ≥ H-3).
  - **Ajukan Pembatalan** (modal konfirmasi + penjelasan kebijakan refund).
  - **Beri Ulasan** (modal rating bintang + teks, hanya setelah `checked_out`).
  - Riwayat status (timeline).
- **`reschedule.blade.php`** — pilih tanggal baru, tampilkan ketersediaan, ringkasan selisih harga (jika ada), peringatan "reschedule hanya dapat dilakukan 1 kali".
- **`profile.blade.php`** — form nama, email, telepon, ubah password.

### 4.8 Autentikasi (`auth/`)

- `login.blade.php` — email, password, ingat saya, tautan lupa password & registrasi.
- `register.blade.php` — nama, email, telepon, password, konfirmasi password.
- `forgot-password.blade.php` & `reset-password.blade.php`.
- Menampilkan pesan "Silakan masuk untuk melanjutkan pemesanan" bila diarahkan dari checkout.

---

## 5. Detail Halaman — Panel Staf

Semua halaman memakai `admin/layouts/app.blade.php`. Menu sidebar tampil bersyarat:

| Menu | admin | receptionist | housekeeping |
|------|:-----:|:------------:|:------------:|
| Dashboard | ✓ | ✓ | ✓ |
| Tape Chart | ✓ | ✓ | – |
| Walk-in Booking | ✓ | ✓ | – |
| Front Desk (Check-in/out) | ✓ | ✓ | – |
| Verifikasi Pembayaran | ✓ | ✓ | – |
| Papan Status Kamar | ✓ | – | ✓ |
| Master Data | ✓ | – | – |
| Tarif & Promo | ✓ | – | – |
| Pengguna & Role | ✓ | – | – |
| Laporan | ✓ | – | – |

### 5.1 Dashboard (`admin/dashboard.blade.php`)

- **Kartu Statistik:** pendapatan hari ini, okupansi hari ini (%), check-in hari ini, check-out hari ini, kamar `dirty`, pembayaran menunggu verifikasi.
- **Grafik:** tren pendapatan & okupansi 7/30 hari (Chart.js / ApexCharts).
- **Panel Aktivitas:** reservasi terbaru dan perubahan status kamar terbaru.
- Varian tampilan untuk role housekeeping: kartu statistik diganti ringkasan kamar per status.

### 5.2 Tape Chart (`admin/receptionist/tape-chart.blade.php`)

- **Matriks:** baris = nomor unit kamar fisik (101, 102, …), kolom = tanggal (navigasi minggu/bulan, tombol hari ini, prev/next).
- **Bar reservasi:** blok berwarna per status (confirmed, checked_in, checked_out) dengan nama tamu & kode booking.
- **Legenda warna:** tersedia, terisi, check-out hari ini, maintenance.
- **Interaksi:** klik sel kosong → shortcut walk-in dengan tanggal & kamar terisi; klik bar → drawer detail reservasi.
- **State:** loading skeleton, empty (belum ada unit kamar), filter per tipe kamar.

### 5.3 Walk-in Booking (`admin/receptionist/walk-in.blade.php`)

- Form cepat satu halaman: pilih tipe kamar → pilih nomor kamar fisik yang `available`, data tamu (nama, telepon, identitas), tanggal, jumlah tamu, add-on, metode bayar (tunai/EDC/transfer), skema bayar.
- Ringkasan biaya real-time (menggunakan PricingService).
- Tombol "Simpan & Check-in Langsung" atau "Simpan sebagai Reservasi".
- State: konflik kamar (sudah terisi pada tanggal tsb), validasi identitas wajib.

### 5.4 Front Desk — Check-in / Check-out (`admin/receptionist/front-desk.blade.php`)

- **Dua tab:** "Check-in Hari Ini" dan "Check-out Hari Ini".
- **Tabel Check-in:** kode booking, nama tamu, tipe kamar, status pembayaran (lunas/DP), tombol **Proses Check-in**.
- **Modal Check-in:**
  - Pilih nomor kamar fisik — hanya yang berstatus `available` (kamar `dirty`/`cleaning`/`maintenance` disabled dengan alasan).
  - Input nomor identitas (KTP/Paspor) + upload scan (opsional).
  - Rincian sisa pembayaran DP + tombol "Tagih Pelunasan".
  - Tombol konfirmasi.
- **Modal Check-out:**
  - Daftar tagihan tambahan (minibar, kerusakan, late check-out) — form tambah item.
  - Total tagihan akhir + metode pembayaran.
  - Tombol "Konfirmasi Check-out" → info kamar otomatis menjadi `dirty`.

### 5.5 Verifikasi Pembayaran Manual (`admin/receptionist/payments.blade.php`)

- Tabel pembayaran manual: kode booking, nama tamu, nominal, bank pengirim, waktu unggah, status.
- Tombol **Pratinjau Bukti** (modal gambar/PDF).
- Aksi **Setujui** (→ `confirmed`, antrean email E-Voucher) dan **Tolak** (wajib catatan alasan).
- Filter status: menunggu, disetujui, ditolak.

### 5.6 Papan Status Kamar — Housekeeping (`admin/housekeeping/room-board.blade.php`)

- **Grid kartu per kamar fisik** (mobile-friendly), dikelompokkan per lantai.
- Tiap kartu: nomor kamar, tipe, status saat ini (badge), tombol aksi cepat:
  - `dirty` → **Mulai Bersihkan** (`cleaning`) → **Selesai** (`available`)
  - **Laporkan Kerusakan** → form keterangan → `maintenance`
- **Ringkasan atas:** jumlah kamar per status.
- **Tab tambahan:** "Jadwal Pembersihan Harian" untuk kamar yang sedang ditempati (occupied).
- **State:** optimistic update + toast konfirmasi; error jika status sudah berubah oleh staf lain (refresh data).

### 5.7 Master Data (`admin/master/`)

Pola seragam: halaman **index** (tabel + pencarian + tombol "Tambah") dan halaman **form** (create/edit).

- **`room-types/`** — nama tipe, deskripsi, luas, kapasitas, tipe tempat tidur, fasilitas (multi-select), upload galeri foto (multiple), status aktif.
- **`rooms/`** — nomor kamar fisik, tipe kamar, lantai, status awal, catatan.
- **`amenities/`** — nama fasilitas + ikon.
- **`addons/`** — nama layanan (Extra Bed, Breakfast, Airport Pickup), harga, satuan (per malam/per orang/per sekali), status aktif.
- **`users/`** — daftar staf: nama, email, role, status aktif; form tambah/edit + reset password.
- **State:** konfirmasi hapus (modal), validasi unik (nomor kamar), notifikasi sukses.

### 5.8 Tarif & Promo (`admin/pricing/`)

- **`index.blade.php`** — tarif dasar per tipe kamar: harga weekday, harga weekend (Jumat & Sabtu), preview urutan prioritas `High Season > Weekend > Weekday`.
- **`seasons.blade.php`** — tabel periode high season: nama periode, tanggal mulai–selesai, tipe kamar, harga khusus; form tambah/edit; validasi rentang tanggal tidak tumpang tindih.
- **`vouchers.blade.php`** — tabel kode promo: kode, tipe diskon (nominal/persen), nilai, kuota, terpakai, masa berlaku, status; form tambah/edit; indikator kadaluarsa.

### 5.9 Laporan & Analitik (`admin/reports/index.blade.php`)

- **Filter:** rentang tanggal (preset hari ini/7 hari/bulan ini/kustom), tipe kamar.
- **Kartu ringkasan:** total pendapatan, jumlah reservasi, occupancy rate rata-rata, rata-rata lama menginap.
- **Tabel pendapatan** per hari/bulan dan per tipe kamar.
- **Tombol ekspor:** Excel (`.xlsx`) dan PDF.
- **Grafik:** pendapatan & okupansi pada rentang terpilih.

---

## 6. Matriks Status & Representasi Visual

### Status Reservasi
| Status | Warna Badge | Label |
|--------|-------------|-------|
| `pending_payment` | kuning | Menunggu Pembayaran |
| `waiting_verification` | biru | Menunggu Verifikasi |
| `confirmed` | hijau | Terkonfirmasi |
| `checked_in` | ungu | Sedang Menginap |
| `checked_out` | abu | Selesai |
| `cancelled` | merah | Dibatalkan |
| `expired` | abu gelap | Kedaluwarsa |

### Status Kamar Fisik
| Status | Warna | Aksi tersedia |
|--------|-------|---------------|
| `available` | hijau | Check-in, walk-in |
| `occupied` | biru | — |
| `dirty` | oranye | Mulai bersihkan |
| `cleaning` | kuning | Selesai bersihkan |
| `maintenance` | merah | Tandai selesai perbaikan |

### Status Pembayaran
`unpaid` · `dp_paid` · `paid` · `waiting_verification` · `refunded` · `rejected`

---

## 7. Komponen Blade Reusable

Disimpan di `resources/views/components/`:

| Komponen | Kegunaan |
|----------|----------|
| `x-status-badge` | Badge status reservasi / kamar / pembayaran |
| `x-room-card` | Kartu tipe kamar (katalog & beranda) |
| `x-search-form` | Widget pencarian tanggal & tamu |
| `x-price-summary` | Ringkasan biaya (subtotal, add-on, pajak, diskon, total) |
| `x-countdown` | Timer pembayaran 2 jam |
| `x-form.input`, `x-form.select`, `x-form.file` | Field form dengan label & pesan error seragam |
| `x-modal` | Modal konfirmasi, pratinjau bukti bayar, form ulasan |
| `x-empty-state` | Ilustrasi + pesan untuk data kosong |
| `x-stat-card` | Kartu statistik dashboard |
| `x-table` | Tabel panel dengan header aksi & state kosong |
| `x-flash` | Notifikasi sukses/error dari session |

---

## 8. Kebutuhan UI Non-Fungsional

1. **Responsif:** breakpoint utama 360px (mobile housekeeping), 768px (tablet front desk), 1280px (desktop admin).
2. **Aksesibilitas:** kontras badge memadai, label form eksplisit, navigasi keyboard pada modal, `aria-live` untuk notifikasi.
3. **Feedback:** tombol submit menampilkan spinner & disabled; konfirmasi untuk aksi destruktif; toast untuk perubahan status kamar.
4. **Anti double-submit:** form checkout & walk-in mengunci tombol saat request berjalan.
5. **Format:** tanggal `d M Y` (id), mata uang `Rp 1.250.000`, durasi malam eksplisit.
6. **Keamanan tampilan:** data reservasi hanya tampil untuk pemilik (`user_id`), tombol aksi mengikuti status & aturan bisnis (mis. reschedule disembunyikan setelah dipakai atau < H-3).
7. **Real-time ringan:** papan housekeeping dan verifikasi pembayaran memakai polling ringan (mis. 30 detik) atau refresh manual, bukan websocket, untuk menjaga kesederhanaan.

---

## 9. Urutan Pembangunan (Fase)

| Fase | Cakupan |
|------|---------|
| **1. Fondasi** | Layout publik & panel, komponen dasar (badge, form, modal, flash), autentikasi, profil |
| **2. Katalog & Booking** | Beranda, katalog + filter, detail kamar, checkout, pembayaran (gateway + manual), status |
| **3. Dasbor Tamu** | Daftar reservasi, detail + E-Voucher/Invoice, reschedule, pembatalan, ulasan |
| **4. Operasional** | Tape chart, walk-in, front desk (check-in/out), verifikasi pembayaran |
| **5. Housekeeping** | Papan status kamar, alur dirty → cleaning → available, laporan kerusakan |
| **6. Admin & Laporan** | Master data, tarif & promo, pengguna & role, laporan + ekspor |

---

## 10. Ringkasan Jumlah Tampilan

| Kelompok | Jumlah Halaman |
|----------|----------------|
| Publik (beranda, katalog, detail, auth) | 6 |
| Tamu login (checkout, pembayaran, dasbor) | 8 |
| Panel staf (dashboard, operasional) | 6 |
| Admin (master, tarif, pengguna, laporan) | 11 |
| **Total** | **31 halaman** + ±12 komponen reusable |
