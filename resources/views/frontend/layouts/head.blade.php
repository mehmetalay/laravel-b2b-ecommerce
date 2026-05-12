<head>
    @include('frontend.layouts.meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.gstatic.com/">
    <link href="https://fonts.googleapis.com/css2?family=Russo+One&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">
    <link id="rtl-link" rel="stylesheet" type="text/css" href="/assets/css/vendors/bootstrap.css">
    <link rel="stylesheet" href="/assets/css/animate.min.css" />
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/feather-icon.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/slick/slick.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/slick/slick-theme.css?v=1.1">
    <link rel="stylesheet" type="text/css" href="/assets/css/bulk-style.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/animate.css">
    <link rel="stylesheet" href="/assets/css/sweetalert2.min.css">
    <link rel="stylesheet" href="/assets/css/fancybox.css">
    <link href="/assets/css/select2.min.css" rel="stylesheet">
    <link id="color-link" rel="stylesheet" type="text/css" href="/assets/css/style.css?v={{ versioned_asset('assets/css/style.css') }}">
    <x-asey.css />
    <style>
        @media (min-width: 576px) {
            .modal-md {
                max-width: 500px;
            }
        }

        header .search-box {
            position: relative;
        }

        .search-suggestion-box {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            width: 100%;
            background: #fff;
            border: 1px solid #ececec;
            border-radius: 6px;
            z-index: 9999;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            display: none;
            max-height: 430px;
            overflow-y: auto;
        }

        .search-suggestion-box.active {
            display: block;
        }

        .search-suggestion-item {
            display: flex;
            gap: 12px;
            padding: 10px 12px;
            text-decoration: none;
            color: #222;
            align-items: center;
        }

        .search-suggestion-item:hover {
            background: #f8f8f8;
        }

        .search-suggestion-item img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            border: 1px solid #eee;
            border-radius: 4px;
        }

        .search-suggestion-title {
            font-size: 14px;
            font-weight: 500;
            line-height: 1.3;
        }

        .search-suggestion-sku {
            font-size: 12px;
            color: #777;
            margin-top: 3px;
        }

        .search-suggestion-all {
            display: block;
            padding: 12px;
            text-align: center;
            color: #666;
            font-size: 13px;
            border-top: 1px solid #eee;
            text-decoration: none;
        }
    </style>
    @yield('css')
</head>
