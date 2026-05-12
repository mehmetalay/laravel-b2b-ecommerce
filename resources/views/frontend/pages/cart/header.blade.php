@if (count($cartService->carts()))
    {{-- ARA TOPLAM & SATIR İNDİRİMİ TOPLAMI --}}
    @if (auth('web')->check() || (auth('subdealer')->check() && auth('subdealer')->user()->can_view_prices))
        @if ($currentAccountService->groupCurrencyStatus()['has_tl'])
            <div class="price-box">
                <h5>{{ trans('translations.cart.ara_toplam') }} :</h5>
                <h4 class="theme-color fw-bold">{{ number_format($cartService->totalProductPrices('TL'), additional_setting('decimal', 2)) . ' ₺' }}</h4>
            </div>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_usd'])
            <div class="price-box">
                <h5>{{ trans('translations.cart.ara_toplam') }} :</h5>
                <h4 class="theme-color fw-bold">{{ number_format($cartService->totalProductPrices('USD'), additional_setting('decimal', 2)) . ' $' }}</h4>
            </div>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_eur'])
            <div class="price-box">
                <h5>{{ trans('translations.cart.ara_toplam') }} :</h5>
                <h4 class="theme-color fw-bold">{{ number_format($cartService->totalProductPrices('EUR'), additional_setting('decimal', 2)) . ' €' }}</h4>
            </div>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_gbp'])
            <div class="price-box">
                <h5>{{ trans('translations.cart.ara_toplam') }} :</h5>
                <h4 class="theme-color fw-bold">{{ number_format($cartService->totalProductPrices('GBP'), additional_setting('decimal', 2)) . ' £' }}</h4>
            </div>
        @endif
    @endif

    {{-- KDV TOPLAMI --}}
    @if (auth('web')->check() || (auth('subdealer')->check() && auth('subdealer')->user()->can_view_prices))
        @if ($currentAccountService->groupCurrencyStatus()['has_tl'])
            <div class="price-box">
                <h5>{{ trans('translations.cart.kdv_toplami') }} :</h5>
                <h4 class="theme-color fw-bold">{{ number_format($cartService->totalVat('TL'), additional_setting('decimal', 2)) . ' ₺' }}</h4>
            </div>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_usd'])
            <div class="price-box">
                <h5>{{ trans('translations.cart.kdv_toplami') }} :</h5>
                <h4 class="theme-color fw-bold">{{ number_format($cartService->totalVat('USD'), additional_setting('decimal', 2)) . ' $' }}</h4>
            </div>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_eur'])
            <div class="price-box">
                <h5>{{ trans('translations.cart.kdv_toplami') }} :</h5>
                <h4 class="theme-color fw-bold">{{ number_format($cartService->totalVat('EUR'), additional_setting('decimal', 2)) . ' €' }}</h4>
            </div>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_gbp'])
            <div class="price-box">
                <h5>{{ trans('translations.cart.kdv_toplami') }} :</h5>
                <h4 class="theme-color fw-bold">{{ number_format($cartService->totalVat('GBP'), additional_setting('decimal', 2)) . ' £' }}</h4>
            </div>
        @endif
    @endif

    {{-- TOPLAM ÜRÜN ADEDİ --}}
    <div class="price-box">
        <h5>{{ trans('translations.cart.toplam_urun_adedi') }} :</h5>
        <h4 class="theme-color fw-bold">{{ $cartService->totalQuantity()['total'] . ' ' . trans('translations.cart.adet') }}</h4>
    </div>

    {{-- ÇEŞİT ÜRÜN SAYISI --}}
    <div class="price-box">
        <h5>{{ trans('translations.cart.cesit_urun_sayisi') }} :</h5>
        <h4 class="theme-color fw-bold">{{ $cartService->productCount()['count'] . ' ' . trans('translations.cart.cesit') }}</h4>
    </div>

    {{-- GENEL TOPLAM --}}
    @if (auth('web')->check() || (auth('subdealer')->check() && auth('subdealer')->user()->can_view_prices))
        @if ($currentAccountService->groupCurrencyStatus()['has_tl'])
            <div class="price-box">
                <h5>{{ trans('translations.cart.toplam_tl') }} :</h5>
                <h4 class="theme-color fw-bold">{{ number_format($cartService->grandTotalWithVat()['tl']['grand_total'], additional_setting('decimal', 2)) . ' ₺' }}</h4>
            </div>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_usd'])
            <div class="price-box">
                <h5>{{ trans('translations.cart.toplam_usd') }} :</h5>
                <h4 class="theme-color fw-bold">{{ number_format($cartService->grandTotalWithVat()['usd']['grand_total'], additional_setting('decimal', 2)) . ' $' }}</h4>
            </div>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_eur'])
            <div class="price-box">
                <h5>{{ trans('translations.cart.toplam_eur') }} :</h5>
                <h4 class="theme-color fw-bold">{{ number_format($cartService->grandTotalWithVat()['eur']['grand_total'], additional_setting('decimal', 2)) . ' €' }}</h4>
            </div>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_gbp'])
            <div class="price-box">
                <h5>{{ trans('translations.cart.toplam_gbp') }} :</h5>
                <h4 class="theme-color fw-bold">{{ number_format($cartService->grandTotalWithVat()['gbp']['grand_total'], additional_setting('decimal', 2)) . ' £' }}</h4>
            </div>
        @endif
    @endif

    {{-- SEPETE GİT BUTONU --}}
    <div class="button-group">
        <a href="{{ route('cart.index') }}" class="btn btn-sm cart-button btn-block">{{ trans('translations.cart.sepete_git') }}</a>
    </div>
@else
    <div class="text-center">
        <h4>{{ trans('translations.cart.sepet_bos') }}.</h4>
    </div>
@endif
