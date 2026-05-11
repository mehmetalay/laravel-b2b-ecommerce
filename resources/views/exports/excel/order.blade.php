
<table>
    <thead>
        <tr>
            <th colspan="14">{{ trans('translations.exports.excel.order.siparis_detayi') }}</th>
        </tr>
        <tr>
            <th colspan="2">{{ trans('translations.exports.excel.order.siparis_no') }}:</th>
            <th colspan="12">#{{ $order->id }}</th>
        </tr>
        <tr>
            <th colspan="2">{{ trans('translations.exports.excel.order.siparis_tarihi') }}:</th>
            <th colspan="12">{{ $order->formatted_created_at }}</th>
        </tr>
        <tr>
            <th colspan="2">{{ trans('translations.exports.excel.order.plasiyer') }}:</th>
            <th colspan="12">{{ $order->salesman_name }}</th>
        </tr>
        <tr>
            <th colspan="2">{{ trans('translations.exports.excel.order.bayi') }}:</th>
            <th colspan="12">{{ $order->dealer_name }}</th>
        </tr>
        <tr>
            <th colspan="2">{{ trans('translations.exports.excel.order.urunler') }}:</th>
            <th colspan="12">{{ $order->formatted_total_product_price }}</th>
        </tr>
        <tr>
            <th colspan="2">{{ trans('translations.exports.excel.order.sepet_indirimi_1') }} (%{{ $order->cart_discount_rate_1 ?? 0 }}):</th>
            <th colspan="12">-{{ $order->formatted_cart_discount_1 }}</th>
        </tr>
        <tr>
            <th colspan="2">{{ trans('translations.exports.excel.order.toplam_urun') }}:</th>
            <th colspan="12">{{ $order->total_quantity . ' ' . trans('translations.exports.excel.order.adet') }}</th>
        </tr>
        <tr>
            <th colspan="2">{{ trans('translations.exports.excel.order.toplam_tutar') }}:</th>
            <th colspan="12">{{ $order->total_amount }}</th>
        </tr>
        <tr>
            <th colspan="2">{{ trans('translations.exports.excel.order.genel_aciklama') }}:</th>
            <th colspan="12">{{ $order->explanation }}</th>
        </tr>
        <tr>
            <th>{{ trans('translations.exports.excel.order.resim') }}</th>
            <th>{{ trans('translations.exports.excel.order.urun_adi') }}</th>
            <th>{{ trans('translations.exports.excel.order.urun_kodu') }}</th>
            <th>{{ trans('translations.exports.excel.order.barkod') }}</th>
            <th>{{ trans('translations.exports.excel.order.fiyat') }}</th>
            <th>{{ trans('translations.exports.excel.order.adet') }}</th>
            <th>{{ trans('translations.exports.excel.order.indirim') }}</th>
            <th>{{ trans('translations.exports.excel.order.toplam_fiyat') }}</th>
            <th>{{ trans('translations.exports.excel.order.doviz') }}</th>
            <th>{{ trans('translations.exports.excel.order.aciklama') }}</th>
            <th>{{ trans('translations.exports.excel.order.kucuk_resim_linki') }}</th>
            <th>{{ trans('translations.exports.excel.order.buyuk_resim_linki') }}</th>
            <th>{{ trans('translations.exports.excel.order.aciklama') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->orderProducts()->with('product')->get()->sortBy('product.code') as $orderProduct)
            <tr>
                <td><img src="{{ asset($orderProduct->product->image_small_url_1_raw) }}" width="50"></td>
                <td>{{ $orderProduct->product_name }}</td>
                <td>{{ $orderProduct->product_code }}</td>
                <td>{{ $orderProduct->product_barcode }}</td>
                <td>{{ $orderProduct->formatted_price }}</td>
                <td>{{ $orderProduct->quantity }}</td>
                <td>{{ $orderProduct->discount }}</td>
                <td>{{ $orderProduct->formatted_total_price }}</td>
                <td>{{ $order->currency }}</td>
                <td>{{ $orderProduct->explanation }}</td>
                <td><a href="{{ asset($orderProduct->product->image_small_url_1_raw) }}"><u>{{ trans('translations.exports.excel.order.goruntule') }}</u></a></td>
                <td><a href="{{ asset($orderProduct->product->image_large_url_1_raw) }}"><u>{{ trans('translations.exports.excel.order.goruntule') }}</u></a></td>
                <td>{{ $orderProduct->explanation }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
