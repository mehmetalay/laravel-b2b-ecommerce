<!DOCTYPE html>
<html lang="tr">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="content-type" content="text/html;charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
        <title>@yield('title', config('app.name'))</title>
        <link rel="icon" type="image/x-icon" href="/admin/assets/img/favicon.ico"/>
        <link href="/admin/assets/css/loader.css" rel="stylesheet" type="text/css" />
        <script src="/admin/assets/js/loader.js"></script>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700&amp;display=swap" rel="stylesheet">
        <link href="/admin/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="/admin/assets/css/main.css" rel="stylesheet" type="text/css" />
        <link href="/admin/assets/css/structure.css" rel="stylesheet" type="text/css" />
        <link href="/admin/plugins/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" type="text/css" />
        <link href="/admin/plugins/highlight/styles/monokai-sublime.css" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
        <link href="/admin/assets/css/authentication/auth_3.css" rel="stylesheet" type="text/css">
        <x-asey.css />
    </head>
    <body class="login-three">
        <div id="load_screen">
            <div class="boxes">
                <div class="box">
                    <div></div><div></div><div></div><div></div>
                </div>
                <div class="box">
                    <div></div><div></div><div></div><div></div>
                </div>
                <div class="box">
                    <div></div><div></div><div></div><div></div>
                </div>
                <div class="box">
                    <div></div><div></div><div></div><div></div>
                </div>
            </div>
            <p class="xato-loader-heading">ÖZDOĞAN HIRDAVAT</p>
        </div>
        <div class="container-fluid login-three-container">
            <div class="row main-login-three">
                <div class="col-xl-3 col-lg-3 col-md-2 d-none d-md-block p-0">
                    <div class="login-bg"></div>
                </div>
                @yield('content')
            </div>
        </div>
        <script src="/admin/assets/js/libs/jquery-3.1.1.min.js"></script>
        <script src="/admin/bootstrap/js/bootstrap.min.js"></script>
        <script src="/admin/assets/js/authentication/auth_2.js"></script>
        <x-asey.js />
        <script src="/admin/assets/js/app.js?v={{ filectime('admin/assets/js/app.js') }}"></script>
        @yield('js')
        @stack('scripts')
    </body>
</html>
