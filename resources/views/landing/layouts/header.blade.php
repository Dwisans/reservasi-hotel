<header class="transparent header-light header-float">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="header-inner">
                    <div class="de-flex">
                        <div class="de-flex-col">
                            <!-- logo begin -->
                            <div id="logo">
                                <a href="{{ route('home') }}">
                                    <img class="logo-main" src="{{ asset('landing/images/logo-dark.webp') }}" alt="">
                                    <img class="logo-scroll" src="{{ asset('landing/images/logo-dark.webp') }}" alt="">
                                    <img class="logo-mobile" src="{{ asset('landing/images/logo-dark.webp') }}" alt="">
                                </a>
                            </div>
                            <!-- logo close -->
                        </div>

                        <div class="de-flex-col">
                            <div class="de-flex-col header-col-mid">
                                <ul id="mainmenu">
                                    <li class="active"><a class="menu-item" href="{{ route('home') }}">Beranda</a></li>
                                    <li><a class="menu-item" href="{{ route('home') }}#kamar">Kamar</a></li>
                                    <li><a class="menu-item" href="{{ route('home') }}#fasilitas">Fasilitas</a></li>
                                    <li><a class="menu-item" href="{{ route('home') }}#ulasan">Ulasan</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="de-flex-col">
                            <div class="d-flex align-items-center">
                                @auth
                                    <a class="fw-600 me-3" href="{{ url('/my/bookings') }}">Dasbor Saya</a>
                                @else
                                    <a class="fw-600 me-3" href="{{ url('/login') }}">Masuk</a>
                                @endauth
                                <a class="btn-main fx-slide" href="{{ route('home') }}#cek-ketersediaan"><span>Pesan
                                        Sekarang</span></a>
                            </div>
                            <div class="menu_side_area">
                                <span id="menu-btn"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
