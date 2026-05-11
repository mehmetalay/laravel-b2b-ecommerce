<div class="price-box">
    <div class="price-row">
        <div class="label blue">Liste Fiyatı</div>
        <div class="value blue">{!! $product->product_list_price_show !!}</div>
    </div>
    <div class="price-row">
        @allowedPayment('cash', $product)
            <div class="label green">Nakit/Havale {!! $product->product_discount_rate_cash_short !!}</div>
            <div class="value green">{!! $product->product_cash_price_show !!}</div>
        @endallowedPayment
    </div>
</div>
<div class="price-box">
    <div class="price-row">
        @allowedPayment('credit', $product)
            <div class="label yellow">Kredi Kartı {!! $product->product_discount_rate_credit_short !!}</div>
            <div class="value yellow">{!! $product->product_credit_price_show !!}</div>
        @endallowedPayment
    </div>
    <div class="price-row">
        @allowedPayment('term', $product)
            <div class="label red">Vadeli {!! $product->product_discount_rate_term_short !!}</div>
            <div class="value red">{!! $product->product_term_price_show !!}</div>
        @endallowedPayment
    </div>
</div>

