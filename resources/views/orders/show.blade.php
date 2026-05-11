<div class="row g-sm-4 g-3">
    <div class="col-xxl-9 col-lg-8">
        <div class="cart-table order-table order-table-2">
            <div class="table-responsive">
                <table class="table mb-0">
                    <tbody>
                        @foreach ($order->orderProducts()->with('product')->get()->sortBy('product.code') as $item)
                            <tr>
                                <td class="product-detail">
                                    <div class="product border-0">
                                        <a href="{{ route('product.detail', [$item->product_slug]) }}" target="_blank" class="product-image">
                                            <img src="{{ $item->product->image_small_url_1 }}" class="img-fluid blur-up lazyload" alt="">
                                        </a>
                                        <div class="product-detail">
                                            <ul>
                                                <li class="name">
                                                    <a href="{{ route('product.detail', [$item->product_slug]) }}" target="_blank">{{ str_limit($item->product_name, 50) }} <i class="las la-external-link-alt font-20"></i></a>
                                                </li>
                                                <li class="text-content">{{ $item->product_code }}</li>
                                                @if ($item->campaign_note)
                                                    <li>
                                                        <small class="text-success">
                                                            <i class="fa fa-tag"></i>
                                                            {!! $item->campaign_note !!}
                                                        </small>
                                                    </li>
                                                @endif

                                                @if ($item->is_campaign_gift)
                                                    <li>
                                                        <small class="text-primary">
                                                            🎁 Kampanya Hediyesi
                                                        </small>
                                                    </li>
                                                @endif

                                            </ul>
                                        </div>
                                    </div>
                                </td>
                                <td class="price">
                                    <h4 class="table-title text-content">{{ trans('translations.siparis.fiyat') }}</h4>
                                    <h6 class="theme-color">{{ $item->formatted_price }}</h6>
                                </td>
                                <td class="quantity">
                                    <h4 class="table-title text-content">{{ trans('translations.siparis.adet') }}</h4>
                                    <h4 class="text-title">{{ $item->quantity }}</h4>
                                </td>
                                <td class="price">
                                    <h4 class="table-title text-content">{{ trans('translations.siparis.indirim') }}</h4>
                                    <h6 class="theme-color">
                                        @if ($item->discount > 0)
                                            <div class="text-success">
                                                Satır indirim: -{{ number_format($item->discount, 2) }} ₺
                                            </div>
                                        @endif

                                        @if ($item->campaign_discount > 0)
                                            <div class="text-success">
                                                Kampanya: -{{ number_format($item->campaign_discount, 2) }} ₺
                                            </div>
                                        @endif
                                    </h6>
                                </td>
                                <td class="subtotal">
                                    <h4 class="table-title text-content">{{ trans('translations.siparis.toplam') }}</h4>
                                    <h5>{{ $item->formatted_total_price }}</h5>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-lg-4">
        <div class="row g-4">
            <div class="col-lg-12 col-sm-6">
                <div class="summery-box">
                    <div class="summery-header">
                        <h3>{{ trans('translations.siparis.siparis_ozeti') }}</h3>
                        @if (auth('web')->check() && auth('web')->user()->role === 'salesman')
                            <a href="{{ route('excel.export.order', [$order->id]) }}" class="ms-auto"><span class="badge alert-success">{{ trans('translations.siparis.excele_aktar') }}</span></a>
                        @endif
                        <a href="{{ route('orders.download.all-images', [$order->id]) }}" class="ms-auto"><span class="badge alert-success">{{ trans('translations.siparis.tum_resimleri_indir') }}</span></a>
                    </div>
                    <ul class="summery-contain">
                        <li>
                            <h4>{{ trans('translations.siparis.urunler') }}</h4>
                            <h4 class="price">{{ $order->formatted_total_product_price }}</h4>
                        </li>
                        
                        <li>
                            <h4>{{ trans('translations.siparis.toplam_urun') }}</h4>
                            <h4 class="price theme-color">{{ $order->total_quantity }} {{ trans('translations.siparis.adet') }}</h4>
                        </li>

                        @if ($order->cart_discount_rate_1)
                            <li>
                                <h4>{{ trans('translations.siparis.sepet_indirimi_1') }} (%{{ $order->cart_discount_rate_1 }})</h4>
                                <h4 class="price text-danger">-{{ $order->formatted_cart_discount_1 }}</h4>
                            </li>
                        @endif
                        
                        @if ($order->campaign_discount_total > 0)
                            <li>
                                <h4>Kampanya İndirimi</h4>
                                <h4 class="price text-success">
                                    -{{ number_format($order->campaign_discount_total, 2) }} ₺
                                </h4>
                            </li>
                        @endif
                    </ul>
                    <ul class="summery-total">
                        <li class="list-total">
                            <h4>{{ trans('translations.siparis.toplam_tutar') }}</h4>
                            <h4 class="price">{{ $order->total_amount }}</h4>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-12 col-sm-6">
                <div class="summery-box">
                    <div class="summery-header">
                        <h3>{{ trans('translations.siparis.siparis_bilgisi') }}</h3>
                    </div>
                    <ul class="summery-contain">
                        <li>
                            <h4>{{ trans('translations.siparis.siparis_no') }}:</h4>
                            <h4 class="price">#{{ $order->id }}</h4>
                        </li>
                        <li>
                            <h4>{{ trans('translations.siparis.siparis_durumu') }}:</h4>
                            @if ($order->creator_type === 'subdealer' && $order->status === 'pending')
                                <h4 class="price"><span class="badge alert-secondary">{{ trans('translations.siparis.onay_bekleniyor') }}</span></h4>
                            @else
                                <h4 class="price"><span class="badge {{ $order->orderStatus->front_color_name }}">{{ $order->orderStatus->name }}</span></h4>
                            @endif
                        </li>
                        <li>
                            <h4>{{ trans('translations.siparis.siparis_tarihi') }}:</h4>
                            <h4 class="price">{{ $order->formatted_created_at }}</h4>
                        </li>
                        <li>
                            <h4>{{ trans('translations.siparis.plasiyer') }}:</h4>
                            <h4 class="price">{{ $order->salesman_name }}</h4>
                        </li>
                        <li>
                            <h4>{{ trans('translations.siparis.bayi') }}:</h4>
                            <h4 class="price">{{ str_limit($order->dealer_name, 20) }}</h4>
                        </li>
                        <li>
                            <h4>{{ trans('translations.siparis.odeme_plani') }}:</h4>
                            <h4 class="price">{{ $order->payment_plan_name }}</h4>
                        </li>
                        <li>
                            <h4>{{ trans('translations.siparis.odeme_turu') }}:</h4>
                            <h4 class="price">{{ $order->payment_type_name }}</h4>
                        </li>
                        <li>
                            <h4>{{ trans('translations.siparis.kampanya_durumu') }}:</h4>
                            <h4 class="price">{{ $order->campaign_status }}</h4>
                        </li>
                        <li>
                            <h4>{{ trans('translations.siparis.doviz_kuru_usd') }}:</h4>
                            <h4 class="price">{{ $order->formatted_usd_exchange_rate }}</h4>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-12 col-sm-6">
                <div class="summery-box">
                    <div class="summery-header">
                        <h3>Teslimat Bilgileri</h3>
                    </div>
                    <ul class="summery-contain">
                        <li>
                            <h4>Teslimat Şekli:</h4>
                            <h4 class="price">{{ $order->delivery_summary['delivery_type'] }}</h4>
                        </li>

                        @foreach ($order->delivery_summary['items'] as $item)
                            <li class="mb-1">
                                <h4>{{ $item['label'] }}:</h4>
                                <h4 class="price">{!! $item['value'] !!}</h4>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
