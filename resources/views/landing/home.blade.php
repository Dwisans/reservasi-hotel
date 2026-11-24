@extends('landing.layouts.app')
@section('content')
    <main>
        <div id="top"></div>

        {{-- Hero & Pencarian Ketersediaan --}}
        <section class="jarallax relative">
            <img src="{{ asset('landing/images/background/1.webp') }}" class="jarallax-img" alt="Suasana penginapan">

            <div class="container relative z-2">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-7 text-light">
                        <div class="subtitle wow fadeInUp mb-3">Reservasi Langsung</div>
                        <h1 class="split">Menginap Nyaman, Pesan Langsung Tanpa Perantara</h1>
                        <div class="spacer-single"></div>
                        <p class="mb-0 wow fadeInUp" data-wow-delay=".2s">Pilih kamar terbaik untuk perjalanan Anda dan
                            dapatkan harga resmi langsung dari hotel. Proses pemesanan cepat, pembayaran aman, dan
                            konfirmasi instan.</p>
                        <div class="spacer-single"></div>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-5 wow fadeInUp" data-wow-delay=".4s">
                                <a class="btn-main fx-slide w-100" href="#cek-ketersediaan"><span>Cek
                                        Ketersediaan</span></a>
                            </div>
                            <div class="col-md-5 wow fadeInUp" data-wow-delay=".5s">
                                <a class="btn-main btn-line fx-slide w-100" href="#kamar"><span>Lihat
                                        Kamar</span></a>
                            </div>
                        </div>
                        <div class="spacer-single"></div>
                        <div class="row g-4">
                            <div class="col-4 wow fadeInUp" data-wow-delay=".6s">
                                <h3 class="fs-32 mb-0">50+</h3>
                                <small>Kamar Siap Huni</small>
                            </div>
                            <div class="col-4 wow fadeInUp" data-wow-delay=".7s">
                                <h3 class="fs-32 mb-0">4.8/5</h3>
                                <small>Rating Tamu</small>
                            </div>
                            <div class="col-4 wow fadeInUp" data-wow-delay=".8s">
                                <h3 class="fs-32 mb-0">24 Jam</h3>
                                <small>Layanan Resepsionis</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="spacer-double"></div>

                {{-- Widget Pencarian --}}
                <div id="cek-ketersediaan" class="bg-white text-dark p-40 rounded-1 shadow-soft wow fadeInUp">
                    <div class="row g-3 align-items-end mb-3">
                        <div class="col-lg-8">
                            <div class="subtitle s2 mb-2">Cek Ketersediaan</div>
                            <h3 class="mb-0">Temukan Kamar untuk Tanggal Menginap Anda</h3>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <small class="text-muted"><i class="icofont-clock-time me-1 id-color"></i>Check-in 14.00 ·
                                Check-out 12.00</small>
                        </div>
                    </div>
                    <form action="{{ url('/rooms') }}" method="GET" class="row g-3">
                        <div class="col-lg-2 col-md-4 col-6">
                            <label for="check_in_date" class="fw-600 mb-2 d-block fs-14">Check-in</label>
                            <input type="date" id="check_in_date" name="check_in_date" class="form-control"
                                min="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6">
                            <label for="check_out_date" class="fw-600 mb-2 d-block fs-14">Check-out</label>
                            <input type="date" id="check_out_date" name="check_out_date" class="form-control"
                                min="{{ now()->addDay()->toDateString() }}"
                                value="{{ now()->addDay()->toDateString() }}" required>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6">
                            <label for="rooms" class="fw-600 mb-2 d-block fs-14">Jumlah Kamar</label>
                            <select id="rooms" name="rooms" class="form-control">
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">{{ $i }} Kamar</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6">
                            <label for="adults" class="fw-600 mb-2 d-block fs-14">Dewasa</label>
                            <select id="adults" name="adults" class="form-control">
                                @for ($i = 1; $i <= 8; $i++)
                                    <option value="{{ $i }}">{{ $i }} Orang</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6">
                            <label for="children" class="fw-600 mb-2 d-block fs-14">Anak</label>
                            <select id="children" name="children" class="form-control">
                                @for ($i = 0; $i <= 6; $i++)
                                    <option value="{{ $i }}">{{ $i }} Anak</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6">
                            <button type="submit" class="btn-main fx-slide w-100"><span>Cari Kamar</span></button>
                        </div>
                    </form>
                </div>

                <div class="spacer-double"></div>
            </div>

            <div class="gradient-edge-bottom h-100 color"></div>
            <div class="sw-overlay op-3"></div>
        </section>

        {{-- Highlight Tipe Kamar --}}
        <section id="kamar" class="relative">
            <div class="container relative z-2">
                <div class="row g-4 mb-2 align-items-end">
                    <div class="col-lg-8">
                        <div class="subtitle s2 wow fadeInUp mb-2">Tipe Kamar</div>
                        <h2 class="split" data-wow-delay=".2s">Pilih Kamar Sesuai Kebutuhan Anda</h2>
                    </div>
                    <div class="col-lg-4 text-lg-end wow fadeInUp" data-wow-delay=".3s">
                        <a class="btn-main btn-line fx-slide" href="#cek-ketersediaan"><span>Lihat Semua
                                Kamar</span></a>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-lg-4 col-sm-6 wow fadeInUp" data-wow-delay="0s">
                        <div class="hover">
                            <div class="relative overflow-hidden rounded-1">
                                <a href="#cek-ketersediaan" class="d-block hover">
                                    <div class="relative overflow-hidden rounded-1">
                                        <img src="{{ asset('landing/images/services/1.webp') }}"
                                            class="w-100 hover-scale-1-2" alt="Deluxe Room">
                                        <div class="gradient-edge-bottom color h-90 op-8"></div>
                                    </div>

                                    <div class="p-4 relative bg-white rounded-1 mx-4 mt-min-100 z-2">
                                        <div class="abs top-0 end-0 mt-min-30 me-4 circle bg-color w-60px h-60px">
                                            <img src="{{ asset('landing/images/misc/up-right-arrow.webp') }}"
                                                class="w-60px p-20" alt="">
                                        </div>
                                        <h4>Deluxe Room</h4>
                                        <p class="mb-2">Kamar nyaman dengan tempat tidur queen, AC, dan pemandangan
                                            taman.</p>
                                        <div class="d-flex align-items-center fs-14 text-muted mb-2">
                                            <span class="me-3"><i class="icofont-user me-1 id-color"></i>2 Tamu</span>
                                            <span><i class="icofont-expand me-1 id-color"></i>24 m&sup2;</span>
                                        </div>
                                        <div class="fw-bold fs-20 id-color">Rp 650.000 <span
                                                class="fs-14 text-muted fw-normal">/malam</span></div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 wow fadeInUp" data-wow-delay="0.2s">
                        <div class="hover">
                            <div class="relative overflow-hidden rounded-1">
                                <a href="#cek-ketersediaan" class="d-block hover">
                                    <div class="relative overflow-hidden rounded-1">
                                        <img src="{{ asset('landing/images/services/2.webp') }}"
                                            class="w-100 hover-scale-1-2" alt="Executive Room">
                                        <div class="gradient-edge-bottom color h-90 op-8"></div>
                                    </div>

                                    <div class="p-4 relative bg-white rounded-1 mx-4 mt-min-100 z-2">
                                        <div class="abs top-0 end-0 mt-min-30 me-4 circle bg-color w-60px h-60px">
                                            <img src="{{ asset('landing/images/misc/up-right-arrow.webp') }}"
                                                class="w-60px p-20" alt="">
                                        </div>
                                        <h4>Executive Room</h4>
                                        <p class="mb-2">Ruang lebih luas dengan area kerja, sofa, dan sarapan untuk dua
                                            orang.</p>
                                        <div class="d-flex align-items-center fs-14 text-muted mb-2">
                                            <span class="me-3"><i class="icofont-user me-1 id-color"></i>2 Tamu</span>
                                            <span><i class="icofont-expand me-1 id-color"></i>32 m&sup2;</span>
                                        </div>
                                        <div class="fw-bold fs-20 id-color">Rp 850.000 <span
                                                class="fs-14 text-muted fw-normal">/malam</span></div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 wow fadeInUp" data-wow-delay="0.4s">
                        <div class="hover">
                            <div class="relative overflow-hidden rounded-1">
                                <a href="#cek-ketersediaan" class="d-block hover">
                                    <div class="relative overflow-hidden rounded-1">
                                        <img src="{{ asset('landing/images/services/3.webp') }}"
                                            class="w-100 hover-scale-1-2" alt="Family Suite">
                                        <div class="gradient-edge-bottom color h-90 op-8"></div>
                                    </div>

                                    <div class="p-4 relative bg-white rounded-1 mx-4 mt-min-100 z-2">
                                        <div class="abs top-0 end-0 mt-min-30 me-4 circle bg-color w-60px h-60px">
                                            <img src="{{ asset('landing/images/misc/up-right-arrow.webp') }}"
                                                class="w-60px p-20" alt="">
                                        </div>
                                        <h4>Family Suite</h4>
                                        <p class="mb-2">Dua kamar terhubung, ruang keluarga, dan kapasitas hingga empat
                                            tamu.</p>
                                        <div class="d-flex align-items-center fs-14 text-muted mb-2">
                                            <span class="me-3"><i class="icofont-user me-1 id-color"></i>4 Tamu</span>
                                            <span><i class="icofont-expand me-1 id-color"></i>48 m&sup2;</span>
                                        </div>
                                        <div class="fw-bold fs-20 id-color">Rp 1.250.000 <span
                                                class="fs-14 text-muted fw-normal">/malam</span></div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Fasilitas Hotel --}}
        <section id="fasilitas" class="bg-color-op-2 relative text-light">
            <div class="gradient-edge-top color op-8 h-100"></div>
            <div class="container relative z-2">
                <div class="row g-4 mb-2 justify-content-center">
                    <div class="col-lg-7 text-center">
                        <div class="uptitle wow fadeInUp">Fasilitas</div>
                        <h2 class="split" data-wow-delay=".2s">Semua yang Anda Butuhkan untuk Menginap</h2>
                    </div>
                </div>
                <div class="row g-4 justify-content-center">
                    <div class="col-6 col-md-4 col-lg-2 de-step wow fadeInUp" data-wow-delay="0s">
                        <div class="de-step-icon bg-white id-color">
                            <i class="fas fa-wifi"></i>
                        </div>
                        <h5 class="fw-bold mb-1">Wi-Fi Gratis</h5>
                        <p class="mb-0 fs-14">Internet cepat di seluruh area hotel.</p>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 de-step wow fadeInUp" data-wow-delay=".1s">
                        <div class="de-step-icon bg-white id-color">
                            <i class="fas fa-water-ladder"></i>
                        </div>
                        <h5 class="fw-bold mb-1">Kolam Renang</h5>
                        <p class="mb-0 fs-14">Kolam outdoor untuk dewasa dan anak.</p>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 de-step wow fadeInUp" data-wow-delay=".2s">
                        <div class="de-step-icon bg-white id-color">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h5 class="fw-bold mb-1">Restoran</h5>
                        <p class="mb-0 fs-14">Sarapan dan menu lokal sepanjang hari.</p>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 de-step wow fadeInUp" data-wow-delay=".3s">
                        <div class="de-step-icon bg-white id-color">
                            <i class="fas fa-square-parking"></i>
                        </div>
                        <h5 class="fw-bold mb-1">Parkir Luas</h5>
                        <p class="mb-0 fs-14">Area parkir aman untuk tamu menginap.</p>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 de-step wow fadeInUp" data-wow-delay=".4s">
                        <div class="de-step-icon bg-white id-color">
                            <i class="fas fa-bell-concierge"></i>
                        </div>
                        <h5 class="fw-bold mb-1">Resepsionis 24 Jam</h5>
                        <p class="mb-0 fs-14">Siap membantu kapan pun Anda butuh.</p>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 de-step wow fadeInUp" data-wow-delay=".5s">
                        <div class="de-step-icon bg-white id-color">
                            <i class="fas fa-spa"></i>
                        </div>
                        <h5 class="fw-bold mb-1">Spa & Relaksasi</h5>
                        <p class="mb-0 fs-14">Layanan pijat untuk tubuh yang lebih segar.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Keunggulan Memesan Langsung --}}
        <section class="relative">
            <div class="gradient-edge-top color op-3"></div>
            <div class="gradient-edge-bottom color op-3"></div>
            <div class="container relative z-2">
                <div class="row gy-4 gx-5 align-items-center">
                    <div class="col-lg-6">
                        <div class="me-lg-3">
                            <div class="subtitle s2 mb-3 wow fadeInUp" data-wow-delay=".0s">Mengapa Memesan Langsung
                            </div>
                            <h2 class="split" data-wow-delay=".2s">Harga Terbaik, Konfirmasi Instan, Tanpa Perantara</h2>
                            <p class="wow fadeInUp" data-wow-delay=".4s">Pemesanan melalui website resmi memberi Anda
                                akses ke tarif terbaik, ketersediaan kamar yang selalu terbaru, serta layanan resepsionis
                                yang siap membantu sebelum dan selama menginap.</p>
                            <ul class="ul-check text-dark cols-2 fw-600 mb-4 wow fadeInUp" data-wow-delay=".6s">
                                <li>Harga resmi langsung dari hotel</li>
                                <li>Konfirmasi pemesanan lebih cepat</li>
                                <li>Pembayaran aman via VA, QRIS, atau transfer</li>
                                <li>Bisa ajukan reschedule maksimal 1x</li>
                            </ul>
                            <a class="btn-main fx-slide wow fadeInUp" data-wow-delay=".8s" href="#cek-ketersediaan"><span>Pesan
                                    Sekarang</span></a>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="relative">
                            <div class="rounded-1 w-90 overflow-hidden wow zoomIn">
                                <img src="{{ asset('landing/images/misc/l1.webp') }}" class="w-100 wow scaleIn"
                                    alt="Suasana kamar">
                            </div>
                            <div class="rounded-1 w-50 abs mb-min-50 end-0 bottom-0 z-2 overflow-hidden shadow-soft wow zoomIn"
                                data-wow-delay=".2s">
                                <img src="{{ asset('landing/images/misc/s1.webp') }}" class="w-100 wow scaleIn"
                                    data-wow-delay=".2s" alt="Detail kamar">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Ulasan Tamu --}}
        <section id="ulasan" class="bg-color-op-2 relative">
            <div class="gradient-edge-top color op-8 h-100"></div>
            <div class="container relative z-2">
                <div class="row mb-3 g-4 align-items-center justify-content-between">
                    <div class="col-lg-6 text-light">
                        <div class="uptitle wow fadeInUp">Ulasan Tamu</div>
                        <h2 class="split">Cerita Tamu yang Menginap Bersama Kami</h2>
                        <p>Kepuasan tamu adalah prioritas kami. Berikut sebagian pengalaman mereka selama menginap.</p>
                    </div>
                    <div class="col-lg-6">
                        <div class="relative">
                            <div class="de-custom-nav d-flex flex-end" data-target="#testimonial-carousel">
                                <div class="d-prev circle"></div>
                                <div class="d-next circle"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-12">
                        <div class="row">
                            <div id="testimonial-carousel" class="owl-carousel owl-theme owl-2-dots wow fadeInUp">
                                <div class="item">
                                    <div class="bg-white relative rounded-1 p-40">
                                        <i class="fs-32 icofont-quote-left absolute start-40px id-color"></i>
                                        <div class="ps-5">
                                            <p>"Proses pemesanan sangat mudah dan konfirmasinya cepat. Kamar bersih,
                                                sarapan enak, dan staf resepsionis ramah saat kami tiba lebih awal."</p>
                                            <div class="de-rating-ext mb-2">
                                                <span class="d-stars">
                                                    <i class="fa fa-star"></i><i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i><i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                </span>
                                                <span class="ms-2 text-white">5.0</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex mt-4 align-items-center">
                                        <img class="w-50px circle me-3" alt=""
                                            src="{{ asset('landing/images/testimonial/1.webp') }}">
                                        <div class="mt-2">
                                            <div class="text-dark fw-bold lh-1">Budi Santoso</div>
                                            <small>12 Maret 2026</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="item">
                                    <div class="bg-white relative rounded-1 p-40">
                                        <i class="fs-32 icofont-quote-left absolute start-40px id-color"></i>
                                        <div class="ps-5">
                                            <p>"Kami memesan untuk liburan keluarga dan mendapat kamar yang luas. Anak-anak
                                                senang dengan kolam renang, sementara kami menikmati suasana yang tenang."</p>
                                            <div class="de-rating-ext mb-2">
                                                <span class="d-stars">
                                                    <i class="fa fa-star"></i><i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i><i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                </span>
                                                <span class="ms-2 text-white">5.0</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex mt-4 align-items-center">
                                        <img class="w-50px circle me-3" alt=""
                                            src="{{ asset('landing/images/testimonial/2.webp') }}">
                                        <div class="mt-2">
                                            <div class="text-dark fw-bold lh-1">Siti Rahma</div>
                                            <small>28 Februari 2026</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="item">
                                    <div class="bg-white relative rounded-1 p-40">
                                        <i class="fs-32 icofont-quote-left absolute start-40px id-color"></i>
                                        <div class="ps-5">
                                            <p>"Lokasi strategis dan kamar sesuai foto. Pembayaran lewat virtual account
                                                langsung terkonfirmasi, jadi tidak perlu menunggu lama saat check-in."</p>
                                            <div class="de-rating-ext mb-2">
                                                <span class="d-stars">
                                                    <i class="fa fa-star"></i><i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i><i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                </span>
                                                <span class="ms-2 text-white">5.0</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex mt-4 align-items-center">
                                        <img class="w-50px circle me-3" alt=""
                                            src="{{ asset('landing/images/testimonial/3.webp') }}">
                                        <div class="mt-2">
                                            <div class="text-dark fw-bold lh-1">Andi Pratama</div>
                                            <small>20 Januari 2026</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="item">
                                    <div class="bg-white relative rounded-1 p-40">
                                        <i class="fs-32 icofont-quote-left absolute start-40px id-color"></i>
                                        <div class="ps-5">
                                            <p>"Pelayanan resepsionis 24 jam sangat membantu karena kami tiba malam hari.
                                                Kamar nyaman dan sarapannya lengkap. Pasti akan menginap lagi."</p>
                                            <div class="de-rating-ext mb-2">
                                                <span class="d-stars">
                                                    <i class="fa fa-star"></i><i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i><i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                </span>
                                                <span class="ms-2 text-white">5.0</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex mt-4 align-items-center">
                                        <img class="w-50px circle me-3" alt=""
                                            src="{{ asset('landing/images/testimonial/4.webp') }}">
                                        <div class="mt-2">
                                            <div class="text-dark fw-bold lh-1">Maya Lestari</div>
                                            <small>8 Desember 2025</small>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end carousel -->
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- CTA Promo --}}
        <section class="bg-color relative text-light pt-50 pb-50">
            <div class="container relative z-2">
                <div class="row g-4 align-items-center">
                    <div class="col-md-9">
                        <div class="uptitle">Promo Spesial</div>
                        <h3 class="mb-2 fs-32 split">Pesan Langsung dan Hemat dengan Kode STAYCATION10</h3>
                        <p class="mb-0">Dapatkan diskon 10% untuk pemesanan langsung melalui website. Berlaku untuk semua
                            tipe kamar selama periode promo, kuota terbatas.</p>
                    </div>
                    <div class="col-lg-3 text-lg-end">
                        <a class="btn-main btn-line fx-slide" href="#cek-ketersediaan"><span>Pesan Sekarang</span></a>
                    </div>
                </div>
            </div>
        </section>

    </main>
@endsection
