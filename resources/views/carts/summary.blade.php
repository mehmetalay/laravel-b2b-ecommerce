<div class="summery-box p-sticky">
    <div class="summery-header">
        <h3>{{ trans('translations.cart.siparis_ozeti') }}</h3>
    </div>
    <div class="summery-contain">
        <ul>
            @if (auth('web')->check() || (auth('subdealer')->check() && auth('subdealer')->user()->can_view_prices))
                {{-- ARA TOPLAM & SATIR İNDİRİMİ TOPLAMI --}}
                @if ($currentAccountService->groupCurrencyStatus()['has_tl'])
                    <li>
                        <h4>{{ trans('translations.cart.urun_toplami') }}</h4>
                        <h4 class="price">{{ number_format($cartService->totalProductPriceBeforeDiscount('TL'), additional_setting('decimal', 2)) . ' ₺' }}</h4>
                    </li>
                @endif
                @if ($currentAccountService->groupCurrencyStatus()['has_usd'])
                    <li>
                        <h4>{{ trans('translations.cart.urun_toplami') }}</h4>
                        <h4 class="price">{{ number_format($cartService->totalProductPriceBeforeDiscount('USD'), additional_setting('decimal', 2)) . ' $' }}</h4>
                    </li>
                @endif
                @if ($currentAccountService->groupCurrencyStatus()['has_eur'])
                    <li>
                        <h4>{{ trans('translations.cart.urun_toplami') }}</h4>
                        <h4 class="price">{{ number_format($cartService->totalProductPriceBeforeDiscount('EUR'), additional_setting('decimal', 2)) . ' €' }}</h4>
                    </li>
                @endif
                @if ($currentAccountService->groupCurrencyStatus()['has_gbp'])
                    <li>
                        <h4>{{ trans('translations.cart.urun_toplami') }}</h4>
                        <h4 class="price">{{ number_format($cartService->totalProductPriceBeforeDiscount('GBP'), additional_setting('decimal', 2)) . ' £' }}</h4>
                    </li>
                @endif

                {{-- SATIR İNDİRİMİ --}}
                @if ($currentAccountService->groupCurrencyStatus()['has_tl'] && $cartService->totalLineDiscount('TL') > 0)
                    <li class="text-success">
                        <h4>{{ trans('translations.cart.urun_indirimi') }}</h4>
                        <h4 class="price text-success">-{{ number_format($cartService->totalLineDiscount('TL'), additional_setting('decimal', 2)) . ' ₺' }}</h4>
                    </li>
                @endif
                @if ($currentAccountService->groupCurrencyStatus()['has_usd'] && $cartService->totalLineDiscount('USD') > 0)
                    <li class="text-success">
                        <h4>{{ trans('translations.cart.urun_indirimi') }}</h4>
                        <h4 class="price text-success">-{{ number_format($cartService->totalLineDiscount('USD'), additional_setting('decimal', 2)) . ' $' }}</h4>
                    </li>
                @endif
                @if ($currentAccountService->groupCurrencyStatus()['has_eur'] && $cartService->totalLineDiscount('EUR') > 0)
                    <li class="text-success">
                        <h4>{{ trans('translations.cart.urun_indirimi') }}</h4>
                        <h4 class="price text-success">-{{ number_format($cartService->totalLineDiscount('EUR'), additional_setting('decimal', 2)) . ' €' }}</h4>
                    </li>
                @endif
                @if ($currentAccountService->groupCurrencyStatus()['has_gbp'] && $cartService->totalLineDiscount('GBP') > 0)
                    <li class="text-success">
                        <h4>{{ trans('translations.cart.urun_indirimi') }}</h4>
                        <h4 class="price text-success">-{{ number_format($cartService->totalLineDiscount('GBP'), additional_setting('decimal', 2)) . ' £' }}</h4>
                    </li>
                @endif

                {{-- KAMPANYA İNDİRİMİ TOPLAMI --}}
                @if ($currentAccountService->groupCurrencyStatus()['has_tl'] && $cartService->applyCampaignToCartTotals('TL') > 0)
                    <li class="text-success">
                        <h4>{{ trans('translations.cart.urun_indirimi') }}</h4>
                        <h4 class="price text-success">-{{ number_format($cartService->applyCampaignToCartTotals('TL'), additional_setting('decimal', 2)) . ' ₺' }}</h4>
                    </li>
                @endif
                @if ($currentAccountService->groupCurrencyStatus()['has_usd'] && $cartService->applyCampaignToCartTotals('USD') > 0)
                    <li class="text-success">
                        <h4>{{ trans('translations.cart.urun_indirimi') }}</h4>
                        <h4 class="price text-success">-{{ number_format($cartService->applyCampaignToCartTotals('USD'), additional_setting('decimal', 2)) . ' $' }}</h4>
                    </li>
                @endif
                @if ($currentAccountService->groupCurrencyStatus()['has_eur'] && $cartService->applyCampaignToCartTotals('EUR') > 0)
                    <li class="text-success">
                        <h4>{{ trans('translations.cart.urun_indirimi') }}</h4>
                        <h4 class="price text-success">-{{ number_format($cartService->applyCampaignToCartTotals('EUR'), additional_setting('decimal', 2)) . ' €' }}</h4>
                    </li>
                @endif
                @if ($currentAccountService->groupCurrencyStatus()['has_gbp'] && $cartService->applyCampaignToCartTotals('GBP') > 0)
                    <li class="text-success">
                        <h4>{{ trans('translations.cart.urun_indirimi') }}</h4>
                        <h4 class="price text-success">-{{ number_format($cartService->applyCampaignToCartTotals('GBP'), additional_setting('decimal', 2)) . ' £' }}</h4>
                    </li>
                @endif
            @endif

            {{-- ara toplam koy --}}

            @php
                $cart_discount_tl_1 = $cartService->cartDiscount1('TL');
                $cart_discount_usd_1 = $cartService->cartDiscount1('USD');
                $cart_discount_eur_1 = $cartService->cartDiscount1('EUR');
                $cart_discount_gbp_1 = $cartService->cartDiscount1('GBP');
            @endphp

            {{-- SEPET İNDİRİMİ 1 --}}
            @if ($cart_discount_tl_1 || $cart_discount_usd_1 || $cart_discount_eur_1 || $cart_discount_gbp_1)
                @if ($cart_discount_tl_1 && $currentAccountService->groupCurrencyStatus()['has_tl'])
                    <li>
                        <h4>{{ trans('translations.cart.1_indirim_tutari_tl') }}</h4>
                        <h4 class="price text-success">- {{ number_format($cart_discount_tl_1, additional_setting('decimal', 2)) . ' ₺' . ' (%' . session()->get('cart_discount_rate_tl_1') . ')' }}</h4>
                    </li>
                @endif
                @if ($cart_discount_usd_1 && $currentAccountService->groupCurrencyStatus()['has_usd'])
                    <li>
                        <h4>{{ trans('translations.cart.1_indirim_tutari_usd') }}</h4>
                        <h4 class="price text-success">- {{ number_format($cart_discount_usd_1, additional_setting('decimal', 2)) . ' $' . ' (%' . session()->get('cart_discount_rate_usd_1') . ')' }}</h4>
                    </li>
                @endif
                @if ($cart_discount_eur_1 && $currentAccountService->groupCurrencyStatus()['has_eur'])
                    <li>
                        <h4>{{ trans('translations.cart.1_indirim_tutari_eur') }}</h4>
                        <h4 class="price text-success">- {{ number_format($cart_discount_eur_1, additional_setting('decimal', 2)) . ' €' . ' (%' . session()->get('cart_discount_rate_eur_1') . ')' }}</h4>
                    </li>
                @endif
                @if ($cart_discount_gbp_1 && $currentAccountService->groupCurrencyStatus()['has_gbp'])
                    <li>
                        <h4>{{ trans('translations.cart.1_indirim_tutari_gbp') }}</h4>
                        <h4 class="price text-success">- {{ number_format($cart_discount_gbp_1, additional_setting('decimal', 2)) . ' £' . ' (%' . session()->get('cart_discount_rate_gbp_1') . ')' }}</h4>
                    </li>
                @endif
            @endif

            {{-- KDV TOPLAMI --}}
            @if (auth('web')->check() || (auth('subdealer')->check() && auth('subdealer')->user()->can_view_prices))
                @if ($currentAccountService->groupCurrencyStatus()['has_tl'])
                    <li>
                        <h4>{{ trans('translations.cart.kdv') }}</h4>
                        <h4 class="price">{{ number_format($cartService->totalVat('TL'), additional_setting('decimal', 2)) . ' ₺' }}</h4>
                    </li>
                @endif
                @if ($currentAccountService->groupCurrencyStatus()['has_usd'])
                    <li>
                        <h4>{{ trans('translations.cart.kdv') }}</h4>
                        <h4 class="price">{{ number_format($cartService->totalVat('USD'), additional_setting('decimal', 2)) . ' $' }}</h4>
                    </li>
                @endif
                @if ($currentAccountService->groupCurrencyStatus()['has_eur'])
                    <li>
                        <h4>{{ trans('translations.cart.kdv') }}</h4>
                        <h4 class="price">{{ number_format($cartService->totalVat('EUR'), additional_setting('decimal', 2)) . ' €' }}</h4>
                    </li>
                @endif
                @if ($currentAccountService->groupCurrencyStatus()['has_gbp'])
                    <li>
                        <h4>{{ trans('translations.cart.kdv') }}</h4>
                        <h4 class="price">{{ number_format($cartService->totalVat('GBP'), additional_setting('decimal', 2)) . ' £' }}</h4>
                    </li>
                @endif
            @endif

            {{-- TOPLAM ÜRÜN ADEDİ --}}
            <li class="align-items-start">
                <h4>{{ trans('translations.cart.urun_adedi') }}</h4>
                <h4 class="price text-end">{{ $cartService->totalQuantity()['total'] . ' ' . trans('translations.cart.adet') }}</h4>
            </li>

            {{-- ÇEŞİT ÜRÜN SAYISI --}}
            <li class="align-items-start">
                <h4>{{ trans('translations.cart.urun_cesidi') }}</h4>
                <h4 class="price text-end">{{ $cartService->productCount()['count'] . ' ' . trans('translations.cart.cesit') }}</h4>
            </li>

            {{-- ÖDEME TİPİ --}}
            <li class="align-items-start">
                <h4>{{ trans('translations.cart.odeme_yontemi') }}</h4>
                <h4 class="price {{ session()->get('cart_payment_type_color') }} text-end">{{ session()->get('cart_payment_type_text', 'Seçilmedi') }}</h4>
            </li>

            {{-- NAKLİYE BEDELSİZ --}}
            @if ($cartService->hasFreeShipping())
                <li class="align-items-start">
                    <h4>{{ trans('translations.cart.nakliye') }}</h4>
                    <h4 class="price text-success text-end"><strong>{{ trans('translations.cart.bedelsiz') }}</strong></h4>
                </li>
            @endif
        </ul>
    </div>

    {{-- GENEL TOPLAM --}}
    @if (auth('web')->check() || (auth('subdealer')->check() && auth('subdealer')->user()->can_view_prices))
        <ul class="summery-total">
            @if ($currentAccountService->groupCurrencyStatus()['has_tl'])
                <li class="list-total border-top-0">
                    <h4>{{ trans('translations.cart.genel_toplam_kdv_dahil') }}</h4>
                    <h4 class="price theme-color">{{ number_format($cartService->grandTotalWithVat()['tl']['grand_total'], additional_setting('decimal', 2)) . ' ₺' }}</h4>
                </li>
            @endif
            @if ($currentAccountService->groupCurrencyStatus()['has_usd'])
                <li class="list-total border-top-0">
                    <h4>{{ trans('translations.cart.genel_toplam_kdv_dahil') }}</h4>
                    <h4 class="price theme-color">{{ number_format($cartService->grandTotalWithVat()['usd']['grand_total'], additional_setting('decimal', 2)) . ' $' }}</h4>
                </li>
            @endif
            @if ($currentAccountService->groupCurrencyStatus()['has_eur'])
                <li class="list-total border-top-0">
                    <h4>{{ trans('translations.cart.genel_toplam_kdv_dahil') }}</h4>
                    <h4 class="price theme-color">{{ number_format($cartService->grandTotalWithVat()['eur']['grand_total'], additional_setting('decimal', 2)) . ' €' }}</h4>
                </li>
            @endif
            @if ($currentAccountService->groupCurrencyStatus()['has_gbp'])
                <li class="list-total border-top-0">
                    <h4>{{ trans('translations.cart.genel_toplam_kdv_dahil') }}</h4>
                    <h4 class="price theme-color">{{ number_format($cartService->grandTotalWithVat()['gbp']['grand_total'], additional_setting('decimal', 2)) . ' £' }}</h4>
                </li>
            @endif
        </ul>
    @endif

    {{-- BUTONLAR --}}
    <div class="button-group cart-button">
        <ul>
            <li>
                <a href="javascript:;" class="btn btn-animation proceed-btn fw-bold" data-bs-toggle="modal" data-bs-target="[data-modal='submit-order']">{{ trans('translations.cart.siparisi_onayla') }}</a>
            </li>
            @if (false && auth('web')->check() && auth('web')->user()->role === 'salesman')
                <li>
                    @if (count($cartService->carts()))
                        <a href="javascript:;" class="btn btn-light shopping-button text-dark" data-bs-toggle="modal" data-bs-target=".cart-export-modal"><i class="fa-solid fa-arrow-up-from-bracket"></i> {{ trans('translations.cart.sepeti_kaydet') }}</a>
                    @else
                        <a href="javascript:;" class="btn btn-light shopping-button text-dark" data-bs-toggle="modal" data-bs-target=".import-backed-up-cart"><i class="fa-solid fa-cart-arrow-down"></i> {{ trans('translations.cart.kaydedilen_sepeti_yukle') }}</a>
                    @endif
                </li>
                <li>
                    <a href="javascript:;" class="btn btn-light shopping-button text-dark" data-download-url="{{ route('excel.export.cart') }}" data-file-name="sepet-listesi" data-download-file><i class="fa-solid fa-file-excel"></i> {{ trans('translations.cart.excele_aktar') }}</a>
                </li>
            @endif
            <li>
                <a href="javascript:;" class="btn btn-light shopping-button text-dark" data-download-url="{{ route('cart.download.all-images') }}" data-file-name="sepet-urun-resimleri" data-download-file><i class="fa-solid fa-file-arrow-down"></i> {{ trans('translations.cart.tum_resimleri_indir') }}</a>
            </li>
            @if (session('last_visited_product'))
                <li>
                    <a href="{{ session('last_visited_product') }}" class="btn btn-light shopping-button text-dark"><i class="fa-solid fa-arrow-left"></i> {{ trans('translations.product.alisverise_devam_et') }}</a>
                </li>
            @endif
        </ul>
    </div>

    {{-- SEPET İNDİRİMİ UYGULAMA --}}
    @if (auth('web')->check() && auth('web')->user()->role === 'salesman')
        @if ($currentAccountService->groupCurrencyStatus()['has_tl'])
            <div class="summery-contain">
                <div class="coupon-cart">
                    <h6 class="text-content mb-2">{{ trans('translations.cart.sepet_indirimi_tl') }}</h6>
                    <div class="mb-3 coupon-box input-group">
                        <input type="text" class="form-control" name="discount" id="discount" placeholder="{{ trans('translations.cart.indirim_orani') }}">
                        <button class="btn-apply" data-js="general-discount-apply" data-currency="tl">{{ trans('translations.cart.uygula') }}</button>
                    </div>
                    <a href="javascript:;" data-js="cancel-all-discounts" data-currency="tl">{{ trans('translations.cart.sepet_indirimi_iptal_et') }}</a>
                </div>
            </div>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_usd'])
            <div class="summery-contain">
                <div class="coupon-cart">
                    <h6 class="text-content mb-2">{{ trans('translations.cart.sepet_indirimi_usd') }}</h6>
                    <div class="mb-3 coupon-box input-group">
                        <input type="text" class="form-control" name="discount" id="discount" placeholder="{{ trans('translations.cart.indirim_orani') }}">
                        <button class="btn-apply" data-js="general-discount-apply" data-currency="usd">{{ trans('translations.cart.uygula') }}</button>
                    </div>
                    <a href="javascript:;" data-js="cancel-all-discounts" data-currency="usd">{{ trans('translations.cart.sepet_indirimi_iptal_et') }}</a>
                </div>
            </div>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_eur'])
            <div class="summery-contain">
                <div class="coupon-cart">
                    <h6 class="text-content mb-2">{{ trans('translations.cart.sepet_indirimi_eur') }}</h6>
                    <div class="mb-3 coupon-box input-group">
                        <input type="text" class="form-control" name="discount" id="discount" placeholder="{{ trans('translations.cart.indirim_orani') }}">
                        <button class="btn-apply" data-js="general-discount-apply" data-currency="eur">{{ trans('translations.cart.uygula') }}</button>
                    </div>
                    <a href="javascript:;" data-js="cancel-all-discounts" data-currency="eur">{{ trans('translations.cart.sepet_indirimi_iptal_et') }}</a>
                </div>
            </div>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_gbp'])
            <div class="summery-contain">
                <div class="coupon-cart">
                    <h6 class="text-content mb-2">{{ trans('translations.cart.sepet_indirimi_gbp') }}</h6>
                    <div class="mb-3 coupon-box input-group">
                        <input type="text" class="form-control" name="discount" id="discount" placeholder="{{ trans('translations.cart.indirim_orani') }}">
                        <button class="btn-apply" data-js="general-discount-apply" data-currency="gbp">{{ trans('translations.cart.uygula') }}</button>
                    </div>
                    <a href="javascript:;" data-js="cancel-all-discounts" data-currency="gbp">{{ trans('translations.cart.sepet_indirimi_iptal_et') }}</a>
                </div>
            </div>
        @endif
    @endif
</div>
