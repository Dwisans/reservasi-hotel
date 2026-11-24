<!DOCTYPE html>
<!-- 
Jampack
Author: Hencework
Contact: contact@hencework.com
-->
<html lang="en">
@extends('admin.layouts.head')
<body>
   	<!-- Wrapper -->
	<div class="hk-wrapper" data-layout="vertical" data-layout-style="default" data-menu="light" data-footer="simple">
		<!-- Top Navbar -->
		@include('admin.layouts.topbar')
		<!-- /Top Navbar -->

        <!-- Vertical Nav -->
        @include('admin.layouts.sidebar')
        <!-- /Vertical Nav -->

		<!-- Chat Popup -->
		@include('admin.layouts.chat')
		<!-- /Chat Popup -->

		<!-- Main Content -->
		<div class="hk-pg-wrapper">
			@yield('content')
			
			<!-- Page Footer -->
			@extends('admin.layouts.footer')
            <!-- / Page Footer -->

		</div>
		<!-- /Main Content -->
	</div>
    <!-- /Wrapper -->

	@extends('admin.layouts.scripts')
</body>
</html>