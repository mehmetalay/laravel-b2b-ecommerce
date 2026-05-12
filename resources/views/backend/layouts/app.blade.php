<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ image_url('favicon-32x32.png', 'favicon') }}">
    <link href="/admin/assets/css/loader.css" rel="stylesheet" type="text/css" />
    <script src="/admin/assets/js/loader.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700&amp;display=swap"
        rel="stylesheet">
    <link href="/admin/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/admin/assets/css/main.css?v={{ filemtime('admin/assets/css/main.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="/admin/assets/css/structure.css?v={{ filemtime('admin/assets/css/structure.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="/admin/plugins/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" type="text/css" />
    <link href="/admin/plugins/highlight/styles/monokai-sublime.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet"
        href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link href="/admin/plugins/flatpickr/flatpickr.css" rel="stylesheet" type="text/css">
    <link href="/admin/plugins/flatpickr/custom-flatpickr.css" rel="stylesheet" type="text/css">
    <link href="/admin/assets/css/elements/tooltip.css" rel="stylesheet" type="text/css" />
    <link href="/admin/assets/css/tables/tables.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="/admin/plugins/tagify/tagify.css">
    <link rel="stylesheet" type="text/css" href="/admin/assets/css/forms/custom-tagify.css">
    <link href="/admin/assets/css/forms/form-widgets.css" rel="stylesheet" type="text/css">
    <link href="/admin/assets/css/forms/switch-theme.css?v=1.2" rel="stylesheet" type="text/css">
    <link href="/admin/assets/css/forms/radio-theme.css" rel="stylesheet" type="text/css">
    <link href="/admin/plugins/select2/select2.min.css?v=1.1" rel="stylesheet" type="text/css">
    <link href="/admin/assets/css/dropzone.min.css" rel="stylesheet" />
    <link href="/admin/assets/css/cropper.min.css" rel="stylesheet" />
    <link href="/admin/assets/css/ui-elements/ribbons.css" rel="stylesheet" type="text/css" />
    <link href="/admin/assets/css/basic-ui/tabs.css" rel="stylesheet" type="text/css" />
    <link href="/admin/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="/admin/assets/css/ui-elements/loading-spinners.css">
    <x-asey.css />
    <style>
        .tagify {
            font-size: 14px;
            padding: 0.40rem 0.85rem;
            border: 1px solid #bfc9d4;
        }

        @media (min-width: 1200px) and (max-width: 1350px) {
            .menubar-wrapper ul.menu-categories li.menu>.dropdown-toggle div:first-child i {
                display: none;
            }
        }

        @media (min-width: 1200px) and (max-width: 1550px) {
            .menubar-wrapper ul.menu-categories li.menu>.dropdown-toggle {
                font-size: 12px;
                padding: 0px 12px 0px 12px;
            }

            .menubar-wrapper ul.menu-categories li.menu>.dropdown-toggle div:first-child i {
                margin-right: 6px;
                font-size: 18px;
            }
        }
    </style>
    @if (Route::is('admin.report.*'))
        <style>
            .table {
                position: relative;
            }

            .table>thead>tr>th {
                position: sticky;
                top: 50px;
            }

            .table-responsive {
                overflow-x: visible;
            }
        </style>
    @endif
    @yield('css')
</head>

<body>
    <div id="load_screen">
        <div class="boxes">
            <div class="box">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="box">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="box">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="box">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
        <p class="xato-loader-heading">Panel</p>
    </div>
    <div class="header-container fixed-top">
        <header class="header navbar navbar-expand-sm">
            <ul class="navbar-item theme-brand flex-row text-center">
                <li class="nav-item theme-logo">
                    <a href="{{ route('admin.index') }}">
                        <img src="{{ image_url(config('images.default.logo'), 'images') }}" class="navbar-logo"
                            alt="logo">
                    </a>
                </li>
            </ul>
            <ul class="navbar-item flex-row ml-sm-auto">
                <li class="nav-item dropdown fullscreen-dropdown d-none d-lg-flex">
                    <a class="nav-link full-screen-mode" href="javascript:;">
                        <i class="las la-compress" id="fullScreenIcon"></i>
                    </a>
                </li>
                <li class="nav-item dropdown message-dropdown">
                    <a href="javascript:;" class="nav-link dropdown-toggle" id="messageDropdown" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <i class="las la-envelope"></i>
                    </a>
                    <div class="dropdown-menu position-absolute" aria-labelledby="messageDropdown">
                        <div class="nav-drop is-notification-dropdown">
                            <div class="inner">
                                <div class="nav-drop-header">
                                    <span class="text-black font-12 strong">0 yeni mesaj</span>
                                </div>
                                <div class="nav-drop-body account-items pb-0">
                                    <hr class="account-divider">
                                    <div class="text-center">
                                        <a class="text-primary strong font-13" href="javascript:;">Tümünü Görüntüle</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown user-profile-dropdown">
                    <a href="javascript:;" class="nav-link dropdown-toggle user" id="userProfileDropdown"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        <img src="/admin/assets/img/user.png" alt="avatar">
                    </a>
                    <div class="dropdown-menu position-absolute" aria-labelledby="userProfileDropdown">
                        <div class="nav-drop is-account-dropdown">
                            <div class="inner">
                                <div class="nav-drop-body account-items pb-0">
                                    <a id="profile-link" class="account-item"
                                        href="{{ route('admin.settings.users.edit', [auth()->user()->id]) }}">
                                        <div class="media align-center">
                                            <div class="media-left">
                                                <div class="image">
                                                    <img class="rounded-circle avatar-xs"
                                                        src="/admin/assets/img/user.png" alt="">
                                                </div>
                                            </div>
                                            <div class="media-content ml-3">
                                                <h6 class="font-13 mb-0 strong">
                                                    {{ auth()->user()->name . ' ' . auth()->user()->surname }}</h6>
                                                <small>{{ auth()->user()->username }}</small>
                                            </div>
                                            <div class="media-right">
                                                <i data-feather="check"></i>
                                            </div>
                                        </div>
                                    </a>
                                    <a class="account-item"
                                        href="{{ route('admin.settings.users.edit', [auth()->user()->id]) }}">
                                        <div class="media align-center">
                                            <div class="icon-wrap">
                                                <i class="las la-user font-20"></i>
                                            </div>
                                            <div class="media-content ml-3">
                                                <h6 class="font-13 mb-0 strong">Kullanıcı Profili</h6>
                                            </div>
                                        </div>
                                    </a>
                                    <a class="account-item" href="{{ route('index') }}" target="_blank">
                                        <div class="media align-center">
                                            <div class="icon-wrap">
                                                <i class="las la-external-link-square-alt font-20"></i>
                                            </div>
                                            <div class="media-content ml-3">
                                                <h6 class="font-13 mb-0 strong">Siteyi Görüntüle</h6>
                                            </div>
                                        </div>
                                    </a>
                                    <hr class="account-divider">
                                    <a class="account-item" href="{{ route('admin.logout') }}">
                                        <div class="media align-center">
                                            <div class="icon-wrap">
                                                <i class="las la-sign-out-alt font-20"></i>
                                            </div>
                                            <div class="media-content ml-3">
                                                <h6 class="font-13 mb-0 strong">Güvenli Çıkış</h6>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </header>
    </div>
    <div class="main-container" id="container">
        <div class="overlay"></div>
        <div class="search-overlay"></div>
        <div class="rightbar-overlay"></div>
        <div class="menubar-wrapper menubar-theme">
            <nav id="sidebar">
                <ul class="list-unstyled menu-categories" id="accordionExample">
                    <li class="menu main-single-menu {{ Route::is('admin.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.index') }}" class="dropdown-toggle collapsed" aria-expanded="false">
                            <div>
                                <i class="las la-home"></i>
                                <span>Anasayfa</span>
                            </div>
                        </a>
                    </li>
                    <li
                        class="menu main-single-menu {{ Route::is('admin.catalog.*', 'admin.catalog.*') ? 'active' : '' }}">
                        <a href="#catalog" class="dropdown-toggle collapsed" data-toggle="collapse"
                            aria-expanded="false">
                            <div>
                                <i class="las la-tags"></i>
                                <span>Stok</span>
                            </div>
                            <div>
                                <i class="las la-angle-right sidemenu-right-icon"></i>
                            </div>
                        </a>
                        <ul class="collapse submenu list-unstyled" id="catalog" data-parent="#accordionExample">
                            <li {{ Route::is('admin.catalog.products.index') ? 'class=active' : '' }}>
                                <a href="{{ route('admin.catalog.products.index') }}">Ürünler</a>
                            </li>
                            <li {{ Route::is('admin.catalog.categories.*') ? 'class=active' : '' }}>
                                <a href="{{ route('admin.catalog.categories.index') }}">Kategoriler</a>
                            </li>
                            <li {{ Route::is('admin.catalog.brands.*') ? 'class=active' : '' }}>
                                <a href="{{ route('admin.catalog.brands.index') }}">Markalar</a>
                            </li>
                            <li {{ Route::is('admin.catalog.product-attributes.attribute-groups.*') ? 'class=active' : '' }}>
                                <a href="{{ route('admin.catalog.product-attributes.attribute-groups.index') }}">Ürün
                                    Özellikleri</a>
                            </li>
                            <li {{ Route::is('admin.catalog.homepage-blocks.*') ? 'class=active' : '' }}>
                                <a href="{{ route('admin.catalog.homepage-blocks.index') }}">Anasayfa Blokları</a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu main-single-menu {{ Route::is('admin.campaigns.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.campaigns.index') }}" class="dropdown-toggle collapsed"
                            aria-expanded="false">
                            <div>
                                <i class="las la-book"></i>
                                <span>Kampanyalar</span>
                            </div>
                        </a>
                    </li>
                    <li class="menu main-single-menu {{ Route::is('admin.orders.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.orders.index') }}" class="dropdown-toggle collapsed"
                            aria-expanded="false">
                            <div>
                                <i class="las la-shopping-basket"></i>
                                <span>Siparişler</span>
                            </div>
                        </a>
                    </li>
                    <li class="menu main-single-menu {{ Route::is('admin.current-accounts.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.current-accounts.index') }}" class="dropdown-toggle collapsed"
                            aria-expanded="false">
                            <div>
                                <i class="las la-store"></i>
                                <span>Cariler</span>
                            </div>
                        </a>
                    </li>
                    <li class="menu main-single-menu {{ Route::is('admin.contracts.*') ? 'active' : '' }}">
                        <a href="#contracts" class="dropdown-toggle collapsed" data-toggle="collapse"
                            aria-expanded="false">
                            <div>
                                <i class="las la-file-contract"></i>
                                <span>Sözleşme Yönetimi</span>
                            </div>
                            <div>
                                <i class="las la-angle-right sidemenu-right-icon"></i>
                            </div>
                        </a>
                        <ul class="collapse submenu list-unstyled" id="contracts" data-parent="#accordionExample">
                            <li {{ Route::is('admin.contracts.templates.*') ? 'class=active' : '' }}>
                                <a href="{{ route('admin.contracts.templates.index') }}">Sözleşme Şablonları</a>
                            </li>
                            <li {{ Route::is('admin.contracts.signatures.*') ? 'class=active' : '' }}>
                                <a href="{{ route('admin.contracts.signatures.index') }}">Sözleşme Kayıtları</a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu main-single-menu {{ Route::is('admin.salesmans.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.salesmans.index') }}" class="dropdown-toggle collapsed"
                            aria-expanded="false">
                            <div>
                                <i class="las la-id-card"></i>
                                <span>Plasiyerler</span>
                            </div>
                        </a>
                    </li>
                    <li class="menu main-single-menu {{ Route::is('admin.surveys.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.surveys.index') }}" class="dropdown-toggle collapsed"
                            aria-expanded="false">
                            <div>
                                <i class="las la-poll"></i>
                                <span>Anketler</span>
                            </div>
                        </a>
                    </li>
                    <li class="menu main-single-menu {{ Route::is('admin.report.*') ? 'active' : '' }}">
                        <a href="#reports" class="dropdown-toggle collapsed" data-toggle="collapse"
                            aria-expanded="false">
                            <div>
                                <i class="las la-chart-pie"></i>
                                <span>Raporlar</span>
                            </div>
                            <div>
                                <i class="las la-angle-right sidemenu-right-icon"></i>
                            </div>
                        </a>
                        <ul class="collapse submenu list-unstyled" id="reports" data-parent="#accordionExample">
                            <li {{ Route::is('admin.report.no-image-products.*') ? 'class=active' : '' }}>
                                <a href="{{ route('admin.report.no-image-products') }}">Resim Olmayan Ürünler</a>
                            </li>
                            <li {{ Route::is('admin.report.products-without-quantity.*') ? 'class=active' : '' }}>
                                <a href="{{ route('admin.report.products-without-quantity') }}">Adedi Olmayan
                                    Ürünler</a>
                            </li>
                            <li {{ Route::is('admin.report.inactive-products.*') ? 'class=active' : '' }}>
                                <a href="{{ route('admin.report.inactive-products') }}">Pasif Ürünler</a>
                            </li>
                            <li {{ Route::is('admin.report.non-splittable-packages.*') ? 'class=active' : '' }}>
                                <a href="{{ route('admin.report.non-splittable-packages') }}">Paket Bölünemez Ürünler</a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu main-single-menu {{ Route::is('admin.dealer-application.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.dealer-application.index') }}" class="dropdown-toggle collapsed"
                            aria-expanded="false">
                            <div>
                                <i class="las la-user-plus"></i>
                                <span>Bayi Başvuruları</span>
                            </div>
                        </a>
                    </li>
                    <li class="menu main-single-menu {{ Route::is('admin.payments.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.payments.index') }}" class="dropdown-toggle collapsed"
                            aria-expanded="false">
                            <div>
                                <i class="las la-credit-card"></i>
                                <span>Ödeme Raporları</span>
                            </div>
                        </a>
                    </li>
                    @if (false)
                        <li class="menu main-single-menu {{ Route::is('admin.payment-links.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.payment-links.index') }}" class="dropdown-toggle collapsed"
                                aria-expanded="false">
                                <div>
                                    <i class="las la-link"></i>
                                    <span>Ödeme Linkleri</span>
                                </div>
                            </a>
                        </li>
                    @endif
                    <li class="menu main-single-menu {{ Route::is('admin.settings.*') ? 'active' : '' }}">
                        <a href="#settings" class="dropdown-toggle collapsed" data-toggle="collapse"
                            aria-expanded="false">
                            <div>
                                <i class="las la-cog"></i>
                                <span>Ayarlar</span>
                            </div>
                            <div>
                                <i class="las la-angle-right sidemenu-right-icon"></i>
                            </div>
                        </a>
                        <ul class="collapse submenu list-unstyled" id="settings" data-parent="#accordionExample">
                            <li {{ Route::is('admin.settings.additional-settings.*') ? 'class=active' : '' }}>
                                <a href="{{ route('admin.settings.additional-settings.index') }}">Ek Ayarlar</a>
                            </li>
                            <li {{ Route::is('admin.settings.general-infos.*') ? 'class=active' : '' }}>
                                <a href="{{ route('admin.settings.general-infos.index') }}">Genel Bilgiler</a>
                            </li>
                            <li {{ Route::is('admin.settings.users.*') ? 'class=active' : '' }}>
                                <a href="{{ route('admin.settings.users.index') }}">Kullanıcılar</a>
                            </li>
                            <li {{ Route::is('admin.settings.pos-managements.index') ? 'class=active' : '' }}>
                                <a href="{{ route('admin.settings.pos-managements.index') }}">POS Yönetimi</a>
                            </li>
                            <li {{ Route::is('admin.settings.currencies.*') ? 'class=active' : '' }}>
                                <a href="{{ route('admin.settings.currencies.index') }}">Döviz Ayarları</a>
                            </li>
                            <li>
                                <a href="#definitions" data-toggle="collapse" aria-expanded="false"
                                    class="dropdown-toggle collapsed">
                                    Tanımlar <i class="las la-angle-right sidemenu-right-icon"></i>
                                </a>
                                <ul class="collapse list-unstyled sub-submenu" id="definitions" data-parent="#settings">
                                    <li>
                                        <a href="{{ route('admin.settings.definitions.payment-plans.index') }}">Ödeme
                                            Planları</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.settings.definitions.payment-types.index') }}">Ödeme
                                            Türleri</a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="#design-settings" data-toggle="collapse" aria-expanded="false"
                                    class="dropdown-toggle collapsed">
                                    Tasarım Ayarları <i class="las la-angle-right sidemenu-right-icon"></i>
                                </a>
                                <ul class="collapse list-unstyled sub-submenu" id="design-settings"
                                    data-parent="#settings">
                                    <li>
                                        <a href="{{ route('admin.settings.design-settings.theme-settings.index') }}">Tema
                                            Ayarları</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.settings.design-settings.sliders.index') }}">Slider
                                            Yönetimi</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
        <div id="content" class="main-content">
            @yield('content')
            <div class="responsive-msg-component">
                <p>
                    <a class="close-msg-component"><i class="las la-times"></i></a> Duyarlı işlevleri görüntülemek için
                    lütfen sayfayı yeniden yükleyin
                </p>
            </div>
            <div class="footer-wrapper">
                <div class="footer-section f-section-1">
                    <p>{{ date('Y') }} © <a target="_blank" href="#">Developed by Mehmet Alay</a> - Tüm hakları
                        saklıdır.</p>
                </div>
                <div class="footer-section f-section-2">
                    <p>Version 1.0.0</p>
                </div>
            </div>
            <div class="scroll-top-arrow" style="display: none;">
                <i class="las la-angle-up"></i>
            </div>
        </div>
    </div>
    @include('backend.layouts.modal')
    <script src="/admin/assets/js/libs/jquery-3.1.1.min.js"></script>
    <script src="/admin/assets/js/jquery-ui.min.js"></script>
    <script src="/admin/bootstrap/js/popper.min.js"></script>
    <script src="/admin/bootstrap/js/bootstrap.min.js"></script>
    <script src="/admin/plugins/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script>
        $(document).ready(function () {
            if (window.App && typeof window.App.init === 'function') {
                window.App.init();
            }
        });
    </script>
    <script src="/admin/assets/js/custom.js"></script>
    <script src="/admin/plugins/flatpickr/flatpickr.js"></script>
    <script src="/admin/plugins/tagify/tagify.min.js"></script>
    <script src="/admin/assets/js/forms/forms-validation.js"></script>
    <script src="/admin/plugins/select2/select2.min.js?v=1.1"></script>
    <script src="/admin/assets/js/forms/custom-select2.js"></script>
    <script src="/admin/assets/js/dropzone.min.js"></script>
    <script src="/admin/assets/js/cropper.min.js"></script>
    <script src="/admin/assets/js/jquery.inputmask.min.js"></script>
    <script src="/admin/plugins/bootstrap-select/bootstrap-select.min.js"></script>
    <x-asey.js />
    @php
        $cssFiles = glob(public_path('build/assets/*.css')) ?: [];
    @endphp

    @foreach ($cssFiles as $cssFile)
        <link rel="stylesheet" href="{{ asset('build/assets/' . basename($cssFile)) }}">
    @endforeach

    @if (file_exists(public_path('build/app.js')))
        <script type="module" src="{{ asset('build/app.js') }}"></script>
    @endif
    <script>
        const pushAdminFlashNotify = (type, message) => {
            if (!message) {
                return;
            }

            notify(type, message);
        };

        pushAdminFlashNotify('success', @json(session('success')));
        pushAdminFlashNotify('error', @json(session('error')));
        pushAdminFlashNotify('warning', @json(session('warning')));

        function applyAdminPhoneNormalization(selector) {
            $(selector).inputmask("(999) 999-9999");
            $(selector).keyup(function() {
                if ($(this).val().substring(1, 3) == '90') {
                    $(this).val('');
                } else if ($(this).val().substring(1, 2) == '0') {
                    $(this).val('');
                }
            });
        }

        applyAdminPhoneNormalization('#phone');
    </script>
    @if (Route::is('admin.payment-links.create') || Route::is('admin.payment-links.edit'))
        <script src="{{ mix('js/backend/modules/payments/index.js') }}"></script>
    @endif
    <script>
        $('body').on('click', '[data-js=add-edit]', function (e) {
            e.preventDefault();

            const $this = $(this);
            const $title = $this.data('title');
            const $type = $this.data('type');
            const $url = $this.data('url');

            $.get($url, function (data) {
                const $modal = $('[add-edit-modal]');
                $modal.modal('show');

                $('[add-edit-modal-title]').html($title);
                $('[add-edit-modal-body]').html(data);
                $('[data-submit-type="save_new"]').show();

                if ($type == 'edit' || $type == 'duplicate') {
                    $('[data-submit-type="save_new"]').hide();
                }

                if ($type == 'duplicate') {
                    const $duplicateAction = $this.data('duplicate-action');
                    $('#add-edit-modal-form').attr('action', $duplicateAction);
                }

                $modal.on('shown.bs.modal', function () {
                    $modal.find('[autofocus]').trigger('focus');
                });
            });
        });

        let clickedButtonType = 'save';

        $(document).on('click', '[modal-submit-button]', function () {
            clickedButtonType = $(this).data('submit-type');
        });

        $('body').on('submit', '#add-edit-modal-form', function (e) {
            e.preventDefault();

            const $form = $(this);
            const $submitButtons = $('[modal-submit-button]');
            const $buttonSave = $('[data-submit-type="save"]');
            const $buttonSaveNew = $('[data-submit-type="save_new"]');

            const originalSaveHTML = $buttonSave.html();
            const originalSaveNewHTML = $buttonSaveNew.html();

            const loadingText = `<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>`;

            if (clickedButtonType === 'save') {
                $buttonSave.html(loadingText);
            } else {
                $buttonSaveNew.html(loadingText);
            }

            $submitButtons.prop('disabled', true);

            $.post($form.attr('action'), $form.serialize(), function (data) {
                $buttonSave.html(originalSaveHTML);
                $buttonSaveNew.html(originalSaveNewHTML);
                $submitButtons.prop('disabled', false);

                const isSuccess = data?.status === 'success' || data?.success === true;

                if (isSuccess) {
                    const $tableBody = $('#table-body');

                    if (data.type === 'add') {
                        $tableBody.find('.no-data').remove();
                        $tableBody.prepend(data.row);
                    } else {
                        $('#parent-' + data.id).replaceWith(data.row);
                    }

                    notify('success', data.message);

                    if (clickedButtonType === 'save') {
                        $('[add-edit-modal]').modal('hide');
                    } else {
                        $form[0].reset();
                        $form.find('select').val(null).trigger('change');
                    }

                    const $datatableRoot = $('[data-server-datatable-component]').first();
                    const datatableComponent = $datatableRoot.data('server-datatable-component');
                    const refreshEventName = window.SERVER_DATATABLE_REFRESH_EVENT;

                    if (datatableComponent && refreshEventName) {
                        window.dispatchEvent(new CustomEvent(refreshEventName, {
                            detail: {
                                component: datatableComponent,
                                reason: 'modal-submit',
                                source: 'layout-modal',
                            }
                        }));
                    }

                } else {
                    const message = data?.message || 'İşlem başarısız.';
                    const level = data?.status === 'warning' ? 'warning' : 'error';
                    notify(level, message);
                }

            }).fail(function () {
                $buttonSave.html(originalSaveHTML);
                $buttonSaveNew.html(originalSaveNewHTML);
                $submitButtons.prop('disabled', false);
                notify('error', 'İstek sırasında bir hata oluştu. Lütfen site yöneticisi ile iletişime geçin.');
            });
        });
    </script>
    
    @if (
        Route::is('admin.campaigns.create')
        || Route::is('admin.campaigns.edit')
        || Route::is('admin.catalog.homepage-blocks.products')
    )
        <script src="{{ mix('js/backend/modules/campaigns/index.js') }}"></script>
    @endif

    @if (Route::is('admin.settings.additional-settings.index'))
        <script src="{{ mix('js/backend/modules/settings/additional-settings-tagify.js') }}"></script>
    @endif

    @if (Route::is('admin.catalog.homepage-blocks.products'))
        <script src="{{ mix('js/backend/modules/homepage-blocks/index.js') }}"></script>
    @endif

    @if (Route::is('admin.contracts.templates.create') || Route::is('admin.contracts.templates.edit'))
        <script src="https://cdn.jsdelivr.net/npm/tinymce@6.7.0/tinymce.min.js"></script>
        <script>
            tinymce.init({
                selector: '[data-editor="tinymce-6.7.0"]',
                height: 500,
                plugins: `
                        advlist autolink lists link charmap preview anchor
                        searchreplace visualblocks code fullscreen
                        insertdatetime media table help wordcount
                    `,
                toolbar: `
                        undo redo |
                        blocks fontsize |
                        bold italic underline strikethrough |
                        forecolor backcolor |
                        alignleft aligncenter alignright alignjustify |
                        bullist numlist outdent indent |
                        table |
                        removeformat |
                        link | code | fullscreen
                    `,
                menubar: false,
                branding: false,
                forced_root_block: 'p',
                convert_urls: false,
                extended_valid_elements: 'table[border|cellpadding|cellspacing|style],tr,td[style|colspan|rowspan],th[style|colspan|rowspan],thead,tbody,tfoot',
                fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt 48pt",
                setup: function(editor) {
                    editor.on('blur', function() {
                        editor.save();
                        $(editor.targetElm).trigger('change');
                    });
                }
            });
        </script>
    @endif

    @stack('scripts')
    @yield('js')
</body>

</html>

