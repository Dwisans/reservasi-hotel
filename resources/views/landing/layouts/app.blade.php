<!DOCTYPE html>
<html lang="en">

@extends('landing.layouts.head')

<body>
    <div id="wrapper">
        <a href="#" id="back-to-top"></a>

        <!-- page preloader begin -->
        <div id="de-loader"></div>
        <!-- page preloader close -->

        <!-- header begin -->
        @include('landing.layouts.header')
        <!-- header close -->
        <!-- content begin -->

        <!-- content close -->
        @yield('content')
        <!-- footer begin -->
        @extends('landing.layouts.footer')
        <!-- footer close -->
    </div>



    <!-- Javascript Files
    ================================================== -->
    @extends('landing.layouts.scripts')

</body>

</html>
