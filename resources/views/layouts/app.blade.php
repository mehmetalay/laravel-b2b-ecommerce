@if (additional_setting('site_status', true))
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
        @include('layouts.head')

        <body class="bg-effect">

            <div class="fullpage-loader bg-transparent">
                <div class="loader-content">
                    <img src="{{ image_url(config('images.default.logo'), 'images') }}" alt="Özdoğan" />
                </div>
            </div>

            <header class="pb-md-4 pb-0">
                @include('layouts.partials.header-top')
                <div class="top-nav top-header sticky-header">
                    <div class="container-fluid-lg">
                        <div class="row">
                            <div class="col-12">
                                <div class="navbar-top">
                                    <button class="navbar-toggler d-xl-none d-inline navbar-menu-button" type="button" data-bs-toggle="offcanvas" data-bs-target="#primaryMenu">
                                        <span class="navbar-toggler-icon">
                                            <i class="fa-solid fa-bars"></i>
                                        </span>
                                    </button>

                                    <a href="/" class="web-logo nav-logo">
                                        <img src="{{ image_url(config('images.default.logo'), 'images') }}" class="img-fluid blur-up lazyload" alt="logo">
                                    </a>

                                    <div class="middle-box">
                                        <form class="search-box js-search-autocomplete"
                                            action="{{ route('product.search') }}"
                                            method="GET"
                                            accept-charset="utf-8"
                                            data-suggestion-url="/ajax/product-suggestions">

                                            <div class="input-group">
                                                <input type="search"
                                                    name="q"
                                                    class="form-control js-search-input"
                                                    placeholder="{{ trans('translations.header.urun_ara') }}"
                                                    autocomplete="off">

                                                <button class="btn" type="submit" id="button-addon2">
                                                    <i data-feather="search"></i>
                                                </button>
                                            </div>

                                            <div class="search-suggestion-box js-search-suggestion-box"></div>

                                            @if (Route::is('product.all') || Route::is('product.list') || Route::is('product.search'))
                                                <input type="hidden" name="category_id" value="{{ isset($category) ? $category->id : request()->get('category_id') }}">
                                            @endif
                                        </form>
                                    </div>

                                    <div class="rightside-box">
                                        <form class="search-full" action="{{ route('product.search') }}" method="GET" accept-charset="utf-8">
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i data-feather="search" class="font-light"></i>
                                                </span>
                                                <input type="search" class="form-control search-type" name="q" placeholder="{{ trans('translations.header.urun_ara') }}">
                                                <button type="submit" class="input-group-text theme-color">{{ trans('translations.header.ara') }}</button>
                                                <span class="input-group-text close-search">
                                                    <i data-feather="x" class="font-light"></i>
                                                </span>
                                            </div>

                                            @if (Route::is('product.all') || Route::is('product.list') || Route::is('product.search'))
                                                <input type="hidden" name="category_id" value="{{ isset($category) ? $category->id : request()->get('category_id') }}">
                                            @endif
                                        </form>

                                        <ul class="right-side-menu">

                                            <li class="right-side">
                                                <div class="delivery-login-box">
                                                    <div class="delivery-icon">
                                                        <div class="search-box">
                                                            <i data-feather="search"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>

                                            @if (auth('web')->check() && auth('web')->user()->role === 'salesman')
                                                <li class="right-side">
                                                    <a href="javascript:;" class="btn deal-button" data-action="current-account">
                                                        <span>{{ trans('translations.header.bayi_sec') }}</span>
                                                    </a>
                                                </li>
                                            @endif

                                            <li class="right-side">
                                                <div class="onhover-dropdown header-badge">
                                                    <a href="{{ route('cart.index') }}" class="btn p-0 position-relative header-wishlist">
                                                        <i data-feather="shopping-cart"></i>
                                                        <span class="position-absolute start-100 translate-middle badge" data-js="cart-count"></span>
                                                    </a>
                                                    <div class="onhover-div" data-js="cart-header">
                                                        <div class="text-center">
                                                            <div class="spinner-border text-danger" role="status">
                                                                <span class="visually-hidden">Loading...</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>

                                            <li class="right-side onhover-dropdown">
                                                <div class="delivery-login-box">
                                                    <div class="delivery-icon">
                                                        <i data-feather="user"></i>
                                                    </div>

                                                    <div class="delivery-detail">
                                                        <h6>{{ auth('web')->check() ? auth('web')->user()->name : (auth('subdealer')->check() ? auth('subdealer')->user()->name : '') }}</h6>
                                                        <h5>{{ trans('translations.header.hesabim') }}</h5>
                                                    </div>
                                                </div>

                                                <div class="onhover-div onhover-div-login">
                                                    <ul class="user-box-name">

                                                        <li class="product-box-contain">
                                                            <i></i>
                                                            <a href="/account/dashboard">{{ trans('translations.header.hesabim') }}</a>
                                                        </li>

                                                        <li class="product-box-contain">
                                                            <i></i>
                                                            <a href="{{ route('orders.index') }}">{{ trans('translations.header.siparisler') }}</a>
                                                        </li>

                                                        @if (auth('web')->check() && auth('web')->user()->role === 'dealer')
                                                            @if ($switchAccountId = $currentAccountService->findTargetUserForCurrencySwitch())
                                                                <li class="product-box-contain">
                                                                    <i></i>
                                                                    <a href="{{ route('switch.account', [$switchAccountId]) }}" onclick="showLoader()">{{ trans('translations.header.diger_hesaba_gec') }}</a>
                                                                </li>
                                                            @endif

                                                            <li class="product-box-contain">
                                                                <i></i>
                                                                <a href="javascript:;" data-action="current-account">{{ trans('translations.header.bayi_sec') }}</a>
                                                            </li>

                                                            <li>
                                                                <i></i>
                                                                <a href="{{ route('dealers.sub-dealers.index') }}">{{ trans('translations.header.bayiler') }}</a>
                                                            </li>
                                                        @endif

                                                        <li class="product-box-contain">
                                                            <a href="#" data-selector="logout" data-url="{{ auth('web')->check() ? '/logout' : '/sub-dealer/logout' }}">{{ trans('translations.header.guvenli_cikis') }}</a>
                                                        </li>

                                                    </ul>
                                                </div>
                                            </li>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container-fluid-lg">
                    <div class="row">
                        <div class="col-12">
                            <div class="header-nav">
                                <div class="header-nav-middle">
                                    <div class="main-nav navbar navbar-expand-xl navbar-light navbar-sticky">
                                        <div class="offcanvas offcanvas-collapse order-xl-2" id="primaryMenu">
                                            <div class="offcanvas-header navbar-shadow">
                                                <h5>{{ trans('translations.header.menu') }}</h5>
                                                <button class="btn-close lead" type="button" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                            </div>
                                            <div class="offcanvas-body">
                                                <ul class="navbar-nav">
                                                    <li class="nav-item dropdown">
                                                        <a class="nav-link dropdown-toggle" href="javascript:;" data-bs-toggle="dropdown">{{ trans('translations.menu.tum_kategoriler') }}</a>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('product.all') }}">
                                                                    TÜM ÜRÜNLER
                                                                </a>
                                                            </li>
                                                            <x-category-menu :categories="$categories" />
                                                        </ul>
                                                    </li>
                                                    @if (auth('web')->check() && auth('web')->user()->role === 'salesman' && auth('web')->user()->report_access == 1)
                                                        <li class="nav-item dropdown">
                                                            <a class="nav-link dropdown-toggle" href="javascript:;" data-bs-toggle="dropdown">{{ trans('translations.menu.plasiyer') }}</a>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <a class="dropdown-item" href="{{ route('reports.customer-statement') }}">{{ trans('translations.menu.musteri_ekstresi') }}</a>
                                                                </li>
                                                            </ul>
                                                        </li>
                                                    @endif
                                                    @if (auth('web')->check() && auth('web')->user()->role === 'salesman' && auth('web')->user()->report_access == 1 || auth('web')->check() && auth('web')->user()->role === 'dealer' && $currentAccountService->currentAccount()->report_access == 1)
                                                        <li class="nav-item dropdown">
                                                            <a class="nav-link dropdown-toggle" href="javascript:;" data-bs-toggle="dropdown">{{ trans('translations.menu.bayi') }}</a>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <a class="dropdown-item" href="{{ route('reports.customer-statement') }}">{{ trans('translations.menu.musteri_ekstresi') }}</a>
                                                                </li>
                                                            </ul>
                                                        </li>
                                                    @endif
                                                    <li class="nav-item dropdown">
                                                        <a class="nav-link dropdown-toggle" href="javascript:;" data-bs-toggle="dropdown">{{ trans('translations.menu.online_odeme') }}</a>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('payments.page') }}">{{ trans('translations.menu.odeme_sayfasi') }}</a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('payments.index') }}">{{ trans('translations.menu.odeme_raporlari') }}</a>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                    @if (auth('web')->check() && auth('web')->user()->role === 'salesman')
                                                        <li class="nav-item dropdown">
                                                            <a class="nav-link dropdown-toggle" href="javascript:;" data-bs-toggle="dropdown">{{ trans('translations.menu.tahsilatlar') }}</a>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <a class="dropdown-item" href="{{ route('collections.cashes.index') }}">{{ trans('translations.menu.nakit_tahsilati') }}</a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item" href="{{ route('collections.cheques.index') }}">{{ trans('translations.menu.cek_tahsilati') }}</a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item" href="{{ route('collections.promissories.index') }}">{{ trans('translations.menu.senet_tahsilati') }}</a>
                                                                </li>
                                                            </ul>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="mobile-menu d-md-none d-block mobile-cart">
                <ul>
                    <li class="active">
                        <a href="/">
                            <i class="iconly-Home icli"></i>
                            <span>{{ trans('translations.anasayfa') }}</span>
                        </a>
                    </li>
                    @if (auth('web')->check() && auth('web')->user()->role === 'salesman')
                        <li>
                            <a href="javascript:;" data-action="current-account">
                                <i class="iconly-Category icli"></i>
                                <span>{{ trans('translations.header.bayi_sec') }}</span>
                            </a>
                        </li>
                    @endif
                    <li class="mobile-category">
                        <a href="{{ route('orders.index') }}">
                            <i class="iconly-Bag-2 icli"></i>
                            <span>{{ trans('translations.header.siparisler') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('cart.index') }}">
                            <i class="iconly-Buy icli"></i>
                            <span>{{ trans('translations.header.sepet') }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            @yield('content')

            <footer class="section-t-space">
                <div class="container-fluid">
                    <div class="main-footer section-b-space section-t-space">
                        <div class="row g-md-4 g-3">
                            <div class="col-xl-3 col-lg-12 col-sm-6">
                                <div class="footer-logo">
                                    <div class="theme-logo">
                                        <a href="/">
                                            <img src="{{ image_url(config('images.default.footer_logo'), 'images') }}" class="blur-up lazyload" alt="footer-logo">
                                        </a>
                                    </div>
                                    <div class="footer-logo-contain">
                                        @if (app()->getLocale() == 'tr')
                                            <p>{{ $themeSetting->footer_about_us_text }}</p>
                                        @endif
                                        @if (app()->getLocale() == 'en')
                                            <p>{{ $themeSetting->footer_about_us_text_en }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-sm-6">
                                <div class="footer-title">
                                    <h4>{{ trans('translations.footer.kategoriler') }}</h4>
                                </div>
                                <div class="footer-contain">
                                    <ul>
                                        @foreach ($categories as $category)
                                            <li>
                                                <a href="{{ route('product.list', [$category->slug]) }}" class="text-content">{{ $category->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-sm-6">
                                <div class="footer-title">
                                    <h4>{{ trans('translations.footer.kurumsal') }}</h4>
                                </div>
                                <div class="footer-contain">
                                    <ul>
                                        <li>
                                            <a href="{{ route('page.about-us') }}" class="text-content">{{ trans('translations.footer.hakkimizda') }}</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('page.contact-us') }}" class="text-content">{{ trans('translations.footer.iletisim') }}</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('page.ourBankInformation') }}" class="text-content">{{ trans('translations.footer.banka_bilgilerimiz') }}</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 col-sm-6">
                                <div class="footer-title">
                                    <h4>{{ trans('translations.footer.iletisim') }}</h4>
                                </div>
                                <div class="footer-contact">
                                    <ul>
                                        <li>
                                            <div class="footer-number">
                                                <i data-feather="home"></i>
                                                <div class="contact-number">
                                                    <h6 class="text-content">{{ trans('translations.footer.adres') }}:</h6>
                                                    <h5>
                                                        <a href="{{ general_info('google_maps_link') }}" target="_blank">
                                                            {{ general_info('company_full_address') }}
                                                        </a>
                                                    </h5>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="footer-number">
                                                <i data-feather="phone"></i>
                                                <div class="contact-number">
                                                    <h6 class="text-content">{{ trans('translations.footer.tel') }}:</h6>
                                                    <h5><a href="tel:{{ general_info('company_phone_number') }}">{{ general_info('company_phone_number') }}</a></h5>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="footer-number">
                                                <i data-feather="mail"></i>
                                                <div class="contact-number">
                                                    <h6 class="text-content">{{ trans('translations.footer.e_posta') }}:</h6>
                                                    <h5><a href="mailto:{{ general_info('email_address') }}">{{ general_info('email_address') }}</a></h5>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-6 col-sm-6">
                                <div class="footer-map">
                                    {!! general_info('google_maps_embed') !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sub-footer section-small-space">
                        <div class="reserve">
                            <h6 class="text-content">
                                @if (app()->getLocale() == 'tr')
                                    <p>{{ $themeSetting->copyright }}</p>
                                @endif
                                @if (app()->getLocale() == 'en')
                                    <p>{{ $themeSetting->copyright_en }}</p>
                                @endif
                            </h6>
                        </div>
                        <div class="payment">
                            <img src="{{ image_url(config('images.default.footer_ssl_image'), 'images') }}" class="blur-up lazyload img-fluid" alt="SSL Logo">
                        </div>
                        <div class="social-link">
                            <h6 class="text-content">{{ trans('translations.footer.bizi_takip_edin') }}:</h6>
                            <ul>
                                @if ($themeSetting->instagram)
                                    <li>
                                        <a href="{{ $themeSetting->instagram }}" target="_blank">
                                            <i class="fa-brands fa-instagram"></i>
                                        </a>
                                    </li>
                                @endif
                                @if ($themeSetting->twitter)
                                    <li>
                                        <a href="{{ $themeSetting->twitter }}" target="_blank">
                                            <i class="fa-brands fa-twitter"></i>
                                        </a>
                                    </li>
                                @endif
                                @if ($themeSetting->whatsapp)
                                    <li>
                                        <a href="{{ $themeSetting->whatsapp }}" target="_blank">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </a>
                                    </li>
                                @endif
                                @if ($themeSetting->facebook)
                                    <li>
                                        <a href="{{ $themeSetting->facebook }}" target="_blank">
                                            <i class="fa-brands fa-facebook-f"></i>
                                        </a>
                                    </li>
                                @endif
                                @if ($themeSetting->linkedin)
                                    <li>
                                        <a href="{{ $themeSetting->linkedin }}" target="_blank">
                                            <i class="fa-brands fa-linkedin"></i>
                                        </a>
                                    </li>
                                @endif
                                @if ($themeSetting->youtube)
                                    <li>
                                        <a href="{{ $themeSetting->youtube }}" target="_blank">
                                            <i class="fa-brands fa-youtube"></i>
                                        </a>
                                    </li>
                                @endif
                                @if ($themeSetting->pinterest)
                                    <li>
                                        <a href="{{ $themeSetting->pinterest }}" target="_blank">
                                            <i class="fa-brands fa-pinterest"></i>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    <div class="section-small-space">
                        <x-company-name />
                    </div>
                </div>
            </footer>

            <div class="theme-option">
                <div class="setting-box">
                    <button class="btn setting-button">
                        <i class="fa-solid fa-clock"></i>
                    </button>
                    <div class="theme-setting-2">
                        <div class="theme-box">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td colspan="2">
                                            <b>ERP Güncelleme Tarihleri</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ trans('translations.app.urun') }}</td>
                                        <td>{{ format_date_time($productLast) }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ trans('translations.app.cari') }}</td>
                                        <td>{{ format_date_time($customerLast) }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ trans('translations.app.resim') }}</td>
                                        <td>{{ format_date_time($imageLast) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="back-to-top">
                    <a id="back-to-top" href="#">
                        <i class="fas fa-chevron-up"></i>
                    </a>
                </div>
            </div>

            @include('layouts.modal')

            <div class="bg-overlay"></div>

            @include('layouts.js')
            @stack('scripts')

            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    updateAllCarts();
                });

                $(document).on('click', '[cancel-sub-dealer]', async function () {
                    const btn = this;

                    setLoading(btn, true);

                    $.post('/dealers/sub-dealers/cancel-selection', {}, function (response) {
                        if (response.success) {
                            notify('success', response.message);
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        }
                    }).fail(function (xhr) {
                        notify('error', xhr.responseJSON?.error || 'Bir hata oluştu');
                    }).always(function () {
                        setLoading(btn, false);
                    });
                });
            </script>
            
            <script src="{{ mix('js/frontend/modules/search/autocomplete.js') }}"></script>
        </body>
    </html>
@else
    <!DOCTYPE html>
    <html lang="tr">
        <head>
            @include('layouts.meta')
            <link rel="preconnect" href="https://fonts.gstatic.com/">
            <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">
            <link id="rtl-link" rel="stylesheet" type="text/css" href="/assets/css/vendors/bootstrap.css">
            <link rel="stylesheet" type="text/css" href="/assets/css/vendors/font-awesome.css">
            <link rel="stylesheet" type="text/css" href="/assets/css/vendors/feather-icon.css">
            <link rel="stylesheet" type="text/css" href="/assets/css/vendors/slick/slick.css">
            <link rel="stylesheet" type="text/css" href="/assets/css/vendors/slick/slick-theme.css">
            <link rel="stylesheet" type="text/css" href="/assets/css/bulk-style.css">
            <link id="color-link" rel="stylesheet" type="text/css" href="/assets/css/style.css?v=1.2">
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
            <section class="coming-soon-section pt-0">
                <div class="bg-black"></div>
                <div class="container-fluid-lg w-100">
                    <div class="row">
                        <div class="col-12 mx-auto">
                            <div class="coming-box">
                                <div>
                                    <div>
                                        <img src="{{ image_url(config('images.default.logo'), 'images') }}" class="img-fluid">
                                    </div>
                                    <div class="coming-title">
                                        <h2>{{ additional_setting('coming_soon_title') }}</h2>
                                    </div>
                                    @if (additional_setting('coming_soon_text'))
                                        <p class="coming-text">{{ additional_setting('coming_soon_text') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <script src="/assets/js/jquery-3.6.0.min.js"></script>
            <script src="/assets/js/jquery-ui.min.js"></script>
            <script src="/assets/js/bootstrap/bootstrap.bundle.min.js"></script>
            <script src="/assets/js/bootstrap/popper.min.js"></script>
            <script src="/assets/js/bootstrap/bootstrap-notify.min.js"></script>
            <script src="/assets/js/lazysizes.min.js"></script>
            <script src="/assets/js/script.js?v=1.1"></script>
        </body>
    </html>
@endif
