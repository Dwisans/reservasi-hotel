# PRD & Desain Sistem: Aplikasi Reservasi Hotel dan Penginapan

## 1. Informasi Umum & Stack Teknis
- **Nama Proyek:** Sistem Reservasi & Manajemen Operasional Penginapan (Hotel / Guest House / Villa)
- **Arsitektur:** Laravel Full-Stack (Monolith)
- **Frontend / Templating:** Laravel Blade (integrasi template HTML klien)
- **Database:** MySQL / MariaDB
- **Background Processing:** Laravel Queue (Email & WhatsApp Notification) & Laravel Scheduler (Auto-expire pembayaran)
- **Integrasi Pihak Ketiga:** Payment Gateway (Midtrans / Xendit) & WhatsApp Gateway API (Fonnte / Wablas)

---

## 2. Product Requirements Document (PRD)

### 2.1 Ringkasan Produk & Sasaran
Aplikasi web terintegrasi yang menangani pemesanan kamar langsung oleh tamu (*direct booking*) hingga manajemen operasional harian oleh Resepsionis dan *Housekeeping* secara *real-time*.

**Sasaran Utama:**
1. **Zero Overbooking:** Mencegah pemesanan ganda pada tipe kamar yang sama di rentang tanggal yang beririsan.
2. **Otomatisasi Tarif & Pembayaran:** Menghitung tarif dinamis (*weekday*, *weekend*, *high season*), kode promo, serta skema DP 50% atau pelunasan otomatis.
3. **Sinkronisasi Operasional:** Menghubungkan proses *check-out* tamu secara otomatis ke antrean kerja *Housekeeping* agar kesiapan kamar terpantau akurat.

---

### 2.2 Ruang Lingkup Fitur (Scope) Berdasarkan Aktor

#### A. Tamu (Guest)
- **Pencarian Ketersediaan (Publik):** Filter pencarian berdasarkan tanggal *check-in*, *check-out*, jumlah kamar, serta jumlah tamu dewasa dan anak.
- **Katalog Tipe Kamar (Publik):** Menampilkan galeri foto, luas kamar, kapasitas maksimal, fasilitas (*amenities*), dan rincian tarif per malam.
- **Alur Pemesanan & Checkout (Wajib Login):**
  - Memilih tipe kamar, menambahkan layanan tambahan (*Extra Bed*, *Breakfast*, *Airport Pickup*), menerapkan kode promo, dan memilih skema bayar (**DP 50%** atau **Bayar Lunas**).
  - Data pemesan otomatis terisi dari profil akun, namun dapat disesuaikan jika memesan untuk orang lain.
- **Pembayaran:** Mendukung pembayaran otomatis via *Payment Gateway* (Virtual Account, QRIS, E-Wallet) maupun transfer bank manual (unggah bukti bayar) dengan hitung mundur batas waktu bayar.
- **Dasbor Tamu:** Mengunduh *E-Voucher* & *Invoice* (PDF), mengajukan *reschedule* (maksimal 1x), mengajukan pembatalan, dan memberikan ulasan (*review*) setelah *check-out*.

#### B. Resepsionis (Front Office)
- **Kalender Okupansi (Tape Chart):** Tampilan matriks nomor unit kamar terhadap tanggal untuk memantau kamar terisi, kosong, atau jadwal *check-out* hari ini.
- **Walk-in Booking:** Input pemesanan cepat untuk tamu yang datang langsung ke lokasi tanpa harus membuat akun.
- **Proses Check-in:** Alokasi nomor kamar fisik (misal: Kamar 101), pencatatan nomor identitas (KTP/Paspor), dan penagihan pelunasan sisa DP.
- **Proses Check-out:** Pencatatan biaya tambahan (*extra charge* seperti minibar, kerusakan, atau *late check-out*), penyelesaian tagihan akhir, dan mengubah status kamar menjadi *Dirty*.
- **Verifikasi Pembayaran Manual:** Menyetujui atau menolak bukti transfer manual dari tamu.

#### C. Housekeeping
- **Papan Status Kamar (Room Status Board):** Melihat daftar kamar berstatus *Dirty* (baru *check-out*) dan jadwal pembersihan harian kamar yang sedang ditempati.
- **Pembaruan Status Kebersihan:** Mengubah status fisik kamar secara *real-time* (`dirty` -> `cleaning` -> `available`) serta melaporkan kendala teknis/kerusakan (`maintenance`).

#### D. Admin / Manager
- **Manajemen Master Data:** CRUD tipe kamar, nomor unit kamar fisik, fasilitas, dan daftar layanan tambahan (*add-ons*).
- **Manajemen Tarif & Promo:** Mengatur harga dasar (*weekday*), tarif akhir pekan (*weekend*), tarif musiman (*high season*), dan kode voucher diskon.
- **Manajemen Pengguna & Role:** Mengelola akun staf (Resepsionis & Housekeeping) beserta hak aksesnya.
- **Laporan & Analitik:** Laporan pendapatan harian/bulanan, tingkat hunian (*Occupancy Rate*), serta ekspor laporan ke format Excel/PDF.

---

### 2.3 Aturan Bisnis Inti (Business Rules)

1. **Aturan Login & Checkout:**
   - Pengunjung dapat mencari kamar dan melihat rincian harga tanpa login.
   - Saat menekan tombol pesan/checkout, sistem mewajibkan autentikasi (`auth` middleware). Jika belum masuk, pilihan kamar disimpan di *session* dan pengguna diarahkan kembali ke halaman checkout setelah login/registrasi (`redirect()->intended()`).
   - Pengecualian tanpa akun hanya berlaku untuk transaksi *walk-in* yang diinput oleh Resepsionis (`user_id` bersifat *nullable*).
2. **Perhitungan Malam & Jam Operasional:**
   - Durasi menginap dihitung berdasarkan selisih hari (`check_out_date - check_in_date`), minimal 1 malam.
   - Standar waktu *Check-in* pukul **14.00** dan *Check-out* pukul **12.00**.
3. **Prioritas Harga Dinamis (Dynamic Pricing):**
   - Sistem menghitung harga per tanggal menginap secara berurutan dengan urutan prioritas:
     `Tarif High Season > Tarif Weekend (Jumat & Sabtu malam) > Harga Dasar (Weekday)`.
4. **Batas Waktu Pembayaran (Payment Expiry):**
   - Reservasi berstatus `pending_payment` memiliki batas waktu bayar **2 jam**. Jika melewati batas waktu tanpa pembayaran terverifikasi, *Laravel Scheduler* otomatis mengubah status menjadi `expired` dan mengembalikan kuota kamar.
5. **Kebijakan Reschedule & Pembatalan:**
   - Tamu dapat mengajukan *reschedule* maksimal **1 kali** dan paling lambat **H-3 (72 jam)** sebelum *check-in*, selama kuota kamar di tanggal baru tersedia.
   - Pembatalan di atas H-3 berhak atas *refund* (diproses manual oleh Admin). Pembatalan di bawah H-3 atau *No-Show* menyebabkan DP/pembayaran hangus.
6. **Validasi Kesiapan Kamar:**
   - Resepsionis tidak dapat melakukan proses *Check-in* ke nomor kamar fisik yang berstatus `dirty`, `cleaning`, atau `maintenance`.

---

### 2.4 Kebutuhan Non-Fungsional
- **Pencegahan Race Condition:** Menggunakan *Database Transaction* dan *Pessimistic Locking* (`lockForUpdate()`) saat pembuatan reservasi untuk mencegah perebutan kamar terakhir pada detik yang sama.
- **Pemrosesan Asinkron (Queue):** Pengiriman email *E-Voucher* (PDF) dan pengingat WhatsApp H-1 dijalankan di latar belakang menggunakan *Laravel Queue*.
- **Pemisahan Layout Template:** Memisahkan aset dan kerangka tampilan publik (tamu) dengan kerangka dasbor internal (staf/admin).

---

## 3. Desain Sistem (System Design)

### 3.1 Arsitektur Aplikasi (Laravel Blade Full-Stack)
Menggunakan pola **Controller -> Service -> Model** agar logika bisnis (kalkulasi tarif dinamis, pengecekan kuota, dan perubahan status) terisolasi dengan rapi dari lapisan HTTP dan tampilan Blade.

```text
+-------------------------------------------------------------------+
|                        PRESENTATION LAYER                         |
|  Laravel Blade Templates (Layout Publik & Layout Panel Staf)      |
+---------------------------------+---------------------------------+
                                  |
                                  v
+-------------------------------------------------------------------+
|                    HTTP & ROUTING LAYER                           |
|  web.php (Middleware: auth, role:admin|receptionist|housekeeping) |
|  FormRequest Validation (Validasi input tanggal, tamu, upload)    |
+---------------------------------+---------------------------------+
                                  |
                                  v
+-------------------------------------------------------------------+
|                      BUSINESS LOGIC LAYER                         |
|  - AvailabilityService   (Cek kuota tipe kamar & alokasi nomor)   |
|  - PricingService        (Hitung weekday/weekend/season & promo)  |
|  - BookingService        (Transaksi booking, reschedule, cancel)  |
|  - PaymentService        (Webhook Midtrans/Xendit & verifikasi)   |
+---------------------------------+---------------------------------+
                                  |
                 +----------------+----------------+
                 v                                 v
+---------------------------------+   +-----------------------------+
|       DATA ACCESS LAYER         |   |    BACKGROUND WORKERS       |
|  Eloquent ORM + MySQL           |   |  - Queue: Email & WA API    |
|  (Pessimistic Locking)          |   |  - Cron: Auto-expire unpaid |
+---------------------------------+   +-----------------------------+