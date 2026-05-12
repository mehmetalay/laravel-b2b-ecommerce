<!DOCTYPE html>
<html lang="tr">
<head>
    @include('frontend.layouts.meta')
    <link rel="preconnect" href="https://fonts.gstatic.com/">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">
    <link id="rtl-link" rel="stylesheet" type="text/css" href="/assets/css/vendors/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/feather-icon.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/slick/slick.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/slick/slick-theme.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/bulk-style.css">
    <link rel="stylesheet" href="/assets/css/sweetalert2.min.css">
    <link id="color-link" rel="stylesheet" type="text/css" href="/assets/css/style.css?v={{ filemtime('assets/css/style.css') }}">
    <x-asey.css />
</head>
<body>
    <div class="fullpage-loader">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
    @yield('content')
    <div class="bg-overlay"></div>
    @include('frontend.layouts.modal')
    <script src="/assets/js/jquery-3.6.0.min.js"></script>
    <script src="/assets/js/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bootstrap/popper.min.js"></script>
    <script src="/assets/js/feather/feather.min.js"></script>
    <script src="/assets/js/feather/feather-icon.js"></script>
    <script src="/assets/js/slick/slick.js"></script>
    <script src="/assets/js/slick/slick-animation.min.js"></script>
    <script src="/assets/js/lazysizes.min.js"></script>
    <script src="/assets/js/sweetalert2.js"></script>
    <script src="/assets/js/script.js"></script>
    <x-asey.js />
    <script>
        @if (session()->has('swal-success'))
            Swal.fire({title: '{{ trans('translations.swal_js.basarili') }}', text: '{{ session()->get('swal-success') }}', icon: 'success', showConfirmButton: false, timer: 3000});
        @endif
        @if (session()->has('swal-warning'))
            Swal.fire({title: '{{ trans('translations.swal_js.uyari') }}', text: '{{ session()->get('swal-warning') }}', icon: 'warning', showConfirmButton: false, timer: 5000});
        @endif
    </script>
    @yield('js')
    @stack('scripts')
</body>
</html>
