
<table>
    <thead>
        <tr>
            <th colspan="10">{{ trans('translations.exports.excel.cart.sepet_listesi') }}</th>
        </tr>
        <tr>
            <th colspan="10">{{ trans('translations.exports.excel.cart.plasiyer') }}: {{ auth('web')->user()->code . ' ' . auth('web')->user()->name }}</th>
        </tr>
        <tr>
            <th colspan="10">{{ trans('translations.exports.excel.cart.bayi') }}: {{ $currentAccountService->currentAccount()->code . ' ' . $currentAccountService->currentAccount()->name }}</th>
        </tr>
        <tr>
            <th colspan="10">{{ trans('translations.exports.excel.cart.tarih') }}: {{ $today_date }}</th>
        </tr>
        @if ($currentAccountService->groupCurrencyStatus()['has_tl'])
            <tr>
                <th colspan="10">{{ trans('translations.exports.excel.cart.urunler_tl') }}: {{ number_format($cartService->totalProductPrices('TL'), additional_setting('decimal', 2)) . ' ₺' }}</th>
            </tr>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_usd'])
            <tr>
                <th colspan="10">{{ trans('translations.exports.excel.cart.urunler_usd') }}: {{ number_format($cartService->totalProductPrices('USD'), additional_setting('decimal', 2)) . ' $' }}</th>
            </tr>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_eur'])
            <tr>
                <th colspan="10">{{ trans('translations.exports.excel.cart.urunler_eur') }}: {{ number_format($cartService->totalProductPrices('EUR'), additional_setting('decimal', 2)) . ' €' }}</th>
            </tr>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_gbp'])
            <tr>
                <th colspan="10">{{ trans('translations.exports.excel.cart.urunler_gbp') }}: {{ number_format($cartService->totalProductPrices('GBP'), additional_setting('decimal', 2)) . ' £' }}</th>
            </tr>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_tl'])
            <tr>
                <th colspan="10">{{ trans('translations.exports.excel.cart.toplam_urun_adedi_tl') }}: {{ $cartService->totalQuantity()['tl'] }}</th>
            </tr>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_usd'])
            <tr>
                <th colspan="10">{{ trans('translations.exports.excel.cart.toplam_urun_adedi_usd') }}: {{ $cartService->totalQuantity()['usd'] }}</th>
            </tr>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_eur'])
            <tr>
                <th colspan="10">{{ trans('translations.exports.excel.cart.toplam_urun_adedi_eur') }}: {{ $cartService->totalQuantity()['eur'] }}</th>
            </tr>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_gbp'])
            <tr>
                <th colspan="10">{{ trans('translations.exports.excel.cart.toplam_urun_adedi_gbp') }}: {{ $cartService->totalQuantity()['gbp'] }}</th>
            </tr>
        @endif
        <tr>
            <th colspan="10">{{ trans('translations.exports.excel.cart.toplam_urun_adedi') }}: {{ $cartService->totalQuantity()['total'] }}</th>
        </tr>
        @if ($currentAccountService->groupCurrencyStatus()['has_tl'])
            <tr>
                <th colspan="10">{{ trans('translations.exports.excel.cart.1_indirim_tutari_tl') }}: - {{ number_format($cartService->cartDiscount1('TL'), additional_setting('decimal', 2)) . ' ₺' . ' (%' . (session()->get('cart_discount_rate_tl_1') ?? 0) . ')' }}</th>
            </tr>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_usd'])
            <tr>
                <th colspan="10">{{ trans('translations.exports.excel.cart.1_indirim_tutari_usd') }}: - {{ number_format($cartService->cartDiscount1('USD'), additional_setting('decimal', 2)) . ' $' . ' (%' . (session()->get('cart_discount_rate_usd_1') ?? 0) . ')' }}</th>
            </tr>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_eur'])
            <tr>
                <th colspan="10">{{ trans('translations.exports.excel.cart.1_indirim_tutari_eur') }}: - {{ number_format($cartService->cartDiscount1('EUR'), additional_setting('decimal', 2)) . ' €' . ' (%' . (session()->get('cart_discount_rate_eur_1') ?? 0) . ')' }}</th>
            </tr>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_gbp'])
            <tr>
                <th colspan="10">{{ trans('translations.exports.excel.cart.1_indirim_tutari_gbp') }}: - {{ number_format($cartService->cartDiscount1('GBP'), additional_setting('decimal', 2)) . ' £' . ' (%' . (session()->get('cart_discount_rate_gbp_1') ?? 0) . ')' }}</th>
            </tr>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_tl'])
            <tr>
                <th colspan="10">{{ trans('translations.exports.excel.cart.toplam_tl') }}: {{ number_format($cartService->grandTotalWithVat()['tl']['grand_total'], additional_setting('decimal', 2)) . ' ₺' }}</th>
            </tr>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_usd'])
            <tr>
                <th colspan="10">{{ trans('translations.exports.excel.cart.toplam_usd') }}: {{ number_format($cartService->grandTotalWithVat()['usd']['grand_total'], additional_setting('decimal', 2)) . ' $' }}</th>
            </tr>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_eur'])
            <tr>
                <th colspan="10">{{ trans('translations.exports.excel.cart.toplam_eur') }}: {{ number_format($cartService->grandTotalWithVat()['eur']['grand_total'], additional_setting('decimal', 2)) . ' €' }}</th>
            </tr>
        @endif
        @if ($currentAccountService->groupCurrencyStatus()['has_gbp'])
            <tr>
                <th colspan="10">{{ trans('translations.exports.excel.cart.toplam_gbp') }}: {{ number_format($cartService->grandTotalWithVat()['gbp']['grand_total'], additional_setting('decimal', 2)) . ' £' }}</th>
            </tr>
        @endif
        <tr>
            <th>{{ trans('translations.exports.excel.cart.resim') }}</th>
            <th>{{ trans('translations.exports.excel.cart.urun_adi') }}</th>
            <th>{{ trans('translations.exports.excel.cart.urun_kodu') }}</th>
            <th>{{ trans('translations.exports.excel.cart.barkod') }}</th>
            <th>{{ trans('translations.exports.excel.cart.fiyat') }}</th>
            <th>{{ trans('translations.exports.excel.cart.adet') }}</th>
            <th>{{ trans('translations.exports.excel.cart.toplam_fiyat') }}</th>
            <th>{{ trans('translations.exports.excel.cart.doviz') }}</th>
            <th>{{ trans('translations.exports.excel.cart.kucuk_resim_linki') }}</th>
            <th>{{ trans('translations.exports.excel.cart.buyuk_resim_linki') }}</th>
            <th>{{ trans('translations.exports.excel.cart.aciklama') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($carts as $cart)
            <tr>
                <td><img src="{{ asset($cart->product->image_small_url_1_raw) }}" width="50"></td>
                <td>{{ $cart->product->product_name }}</td>
                <td>{{ $cart->product->code }}</td>
                <td>{{ $cart->product->barcode }}</td>
                <td>{{ $cart->productPrice() }}</td>
                <td>{{ $cart->quantity }}</td>
                <td>{{ $cart->productPrice($cart->quantity, $cart->discount) }}</td>
                <td>{{ $cart->currency }}</td>
                <td><a href="{{ asset($cart->product->image_small_url_1_raw) }}"><u>{{ trans('translations.exports.excel.cart.goruntule') }}</u></a></td>
                <td><a href="{{ asset($cart->product->image_large_url_1_raw) }}"><u>{{ trans('translations.exports.excel.cart.goruntule') }}</u></a></td>
                <td>{{ $cart->explanation }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
