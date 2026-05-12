<div class="header-top">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-xxl-4 col-lg-4 col-md-6 col-12">
                <div class="currency-info d-flex flex-wrap align-items-center gap-3">
                    <div class="currency-item">
                        <i class="fa-solid fa-dollar-sign text-white me-1"></i>
                        <span class="text-white fw-semibold">USD:</span>
                        <span class="text-white fw-bold">{{ number_format($USDExchangeRate, 2) }}</span>
                    </div>
                    <div class="currency-item">
                        <i class="fa-solid fa-euro-sign text-white me-1"></i>
                        <span class="text-white fw-semibold">EUR:</span>
                        <span class="text-white fw-bold">{{ number_format($EURExchangeRate, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-lg-4 col-md-6 col-12 text-center my-2 my-md-0">
                <span class="text-white">
                    {!! $currentAccountService->getUserSummary() !!}
                </span>
            </div>
            <div class="col-xxl-4 col-lg-4 col-md-12 col-12 d-flex justify-content-center align-items-center flex-wrap gap-3 mt-2 mt-lg-0">
                <ul class="about-list right-nav-about m-0">
                    <li class="right-nav-list">
                        <div class="salesman-info">

                            @if (auth('web')->check())

                                @if (auth('web')->user()->role === 'dealer')
                                    @if (auth('web')->user()->salesmann)
                                        <span class="text-white">
                                            <i class="fa-solid fa-user-tie me-1"></i>
                                            <strong>
                                                {{ auth('web')->user()->salesman_name }}
                                            </strong>
                                            @if (auth('web')->user()->salesman_phone)
                                                <div>
                                                    <a href="tel:{{ auth('web')->user()->salesman_phone }}" class="text-white">
                                                        <small>
                                                            <i class="fa-solid fa-phone me-1"></i>
                                                            {{ format_phone_number(auth('web')->user()->salesman_phone) }}
                                                        </small>
                                                    </a>
                                                </div>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-white">
                                            <i class="fa-solid fa-user-tie me-1"></i>
                                            Plasiyer atanmamış
                                        </span>
                                    @endif

                                @else

                                    <span class="text-white">
                                        <i class="fa-solid fa-user-tie me-1"></i>
                                        <strong>
                                            {{ auth('web')->user()->name }}
                                        </strong>
                                        @if (auth('web')->user()->phone)
                                            <div>
                                                <a href="tel:{{ auth('web')->user()->phone }}" class="text-white">
                                                    <small>
                                                        <i class="fa-solid fa-phone me-1"></i>
                                                        {{ format_phone_number(auth('web')->user()->phone) }}
                                                    </small>
                                                </a>
                                            </div>
                                        @endif
                                    </span>

                                @endif
                            @elseif (auth('subdealer')->check())

                                <span class="text-white">
                                    <i class="fa-solid fa-user-tie me-1"></i>
                                    <strong>
                                        {{ auth('subdealer')->user()->dealer->name }}
                                    </strong>
                                    @if (auth('subdealer')->user()->dealer->phone)
                                        <div>
                                            <a href="tel:{{ auth('subdealer')->user()->dealer->phone }}" class="text-white">
                                                <i class="fa-solid fa-phone me-1"></i>
                                                {{ format_phone_number(auth('subdealer')->user()->dealer->phone) }}
                                            </a>
                                        </div>
                                    @endif
                                </span>

                            @endif
                        </div>
                    </li>
                    <li class="right-nav-list">
                        <div class="dropdown theme-form-select">
                            <button class="btn dropdown-toggle" type="button" id="select-language" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="{{ image_url(config('images.default.country.' . app()->getLocale()), 'country') }}" class="img-fluid blur-up lazyload" alt="language-{{ app()->getLocale() }}">
                                <span>{{ app()->getLocale() == 'tr' ? 'Türkçe' : 'English' }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="select-language">
                                @if (app()->getLocale() == 'en')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('language', ['tr']) }}">
                                            <img src="{{ image_url(config('images.default.country.tr'), 'country') }}" class="img-fluid blur-up lazyload" alt="language-tr">
                                            <span>Türkçe</span>
                                        </a>
                                    </li>
                                @endif
                                @if (app()->getLocale() == 'tr')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('language', ['en']) }}">
                                            <img src="{{ image_url(config('images.default.country.en'), 'country') }}" class="img-fluid blur-up lazyload" alt="language-en">
                                            <span>English</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                    <li class="right-nav-list">
                        <div class="dropdown theme-form-select">
                            <button class="btn dropdown-toggle" type="button" id="select-payment-type" data-bs-toggle="dropdown" aria-expanded="false">
                                <span id="current-payment-type">
                                    {{ session('cart_payment_type') ? ucfirst(session('cart_payment_type_text')) : 'Ödeme Türü Seç' }}
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end sm-dropdown-menu" aria-labelledby="select-payment-type">
                                @if ($currentAccountService->currentAccount())
                                    @php
                                        $methods = $currentAccountService->currentAccount() ? explode(',', $currentAccountService->currentAccount()->allowed_payment_methods) : [];
                                    @endphp
                                    @if (in_array('cash', $methods))
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);" data-payment="cash">Nakit</a>
                                        </li>
                                    @endif
                                    @if (in_array('credit', $methods))
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);" data-payment="credit">Kredi Kartı</a>
                                        </li>
                                    @endif
                                    @if (in_array('term', $methods))
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);" data-payment="term">Vadeli</a>
                                        </li>
                                    @endif
                                @else
                                    <li>
                                        <span class="dropdown-item">Önce bayi seçiniz</span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
