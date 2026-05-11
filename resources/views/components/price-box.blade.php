@if ($viewType === 'grid')
    <div class="price-box">
        <div class="price-row">
            <div class="label blue">Liste Fiyatı</div>
            <div class="value blue">{!! $product->product_list_price_show !!}</div>
        </div>
        @allowedPayment('cash', $product)
            <div class="price-row">
                <div class="label green">Nakit/Havale {!! $product->product_discount_rate_cash_short !!}</div>
                <div class="value green">{!! $product->product_cash_price_show !!}</div>
            </div>
        @endallowedPayment
        @allowedPayment('credit', $product)
            <div class="price-row">
                <div class="label yellow">Kredi Kartı {!! $product->product_discount_rate_credit_short !!}</div>
                <div class="value yellow">{!! $product->product_credit_price_show !!}</div>
            </div>
        @endallowedPayment
        @allowedPayment('term', $product)
            <div class="price-row">
                <div class="label red">Vadeli {!! $product->product_discount_rate_term_short !!}</div>
                <div class="value red">{!! $product->product_term_price_show !!}</div>
            </div>
        @endallowedPayment
    </div>
@elseif ($viewType === 'list')
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
@elseif ($viewType === 'detail')
    <div class="price-box">
        @if ($product->product_price_list)
            <div class="price-row">
                <div class="label blue">Liste Fiyatı</div>
                <div class="value blue">{!! $product->product_price_list_formatted !!}</div>
            </div>
        @endif
        @if ($product->product_price_list < $product->product_price_special)
            <div class="price-row">
                <div class="label teal">Özel Fiyatınız <span>(-%{{ discount_percentage($product->product_price_list, $product->product_price_special) }})</span></div>
                <div class="value teal">{!! $product->product_price_special_formatted !!}</div>
            </div>
        @endif
        @if ($product->product_price_cash)
            @allowedPayment('cash', $product)
                <div class="price-row">
                    <div class="label green">Nakit/Havale Fiyatı {!! $product->product_discount_rate_cash_formatted !!}</div>
                    <div class="value green">{!! $product->product_price_cash_formatted !!}</div>
                </div>
            @endallowedPayment
        @endif
        @if ($product->product_price_credit)
            @allowedPayment('credit', $product)
                <div class="price-row">
                    <div class="label yellow">Kredi Kart Fiyatı {!! $product->product_discount_rate_credit_formatted !!}</div>
                    <div class="value yellow">{!! $product->product_price_credit_formatted !!}</div>
                </div>
            @endallowedPayment
        @endif
        @if ($product->product_price_term)
            @allowedPayment('term', $product)
                <div class="price-row">
                    <div class="label red">Vadeli Fiyatı {!! $product->product_discount_rate_term_formatted !!}</div>
                    <div class="value red">{!! $product->product_price_term_formatted !!}</div>
                </div>
            @endallowedPayment
        @endif
    </div>
@endif

