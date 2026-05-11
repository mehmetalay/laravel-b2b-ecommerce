<div class="cart-table">
    @php
        $cartItems = $cartService->carts();

        // Sepette UYGULANMIŞ kampanyalar
        $appliedCampaignIds = $cartItems
            ->where('is_campaign_gift', 0)
            ->pluck('campaign_id')
            ->filter()
            ->unique();

        $appliedCampaigns = \App\Models\Campaign::with(['rules','products'])
            ->whereIn('id', $appliedCampaignIds)
            ->get();

        $incompleteGiftCampaigns = $appliedCampaigns->filter(fn ($campaign) =>
            $cartService->hasIncompleteGiftSelection($campaign)
        );
    @endphp
    @if ($incompleteGiftCampaigns->isNotEmpty())
        <div class="alert alert-warning mb-4">
            <strong>Dikkat!</strong>
            <ul class="list-group mb-0 mt-2">
                @foreach ($incompleteGiftCampaigns as $campaign)
                    <li class="list-group-item d-flex justify-content-between align-items-center mb-2">
                        "{{ $campaign->name }}" kampanyası için hediye ürün seçimi tamamlanmamış. Lütfen hediye ürün seçiminizi yapınız.
                        <a href="javascript:;" class="btn btn-animation btn-sm" data-selector="select-gifts" data-campaign-id="{{ $campaign->id }}">
                            Hediye Seç
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="table-responsive">
        <table class="table">
            <tbody>
                <tr>
                    <td colspan="{{ count($cartService->carts()) ? 6 : 7 }}" class="cart-header">
                        <h3>{{ trans('translations.cart.sepet') }}</h3>

                        @if ($cartService->carts()->count())
                            <div class="mobile-delete-all-cart">
                                <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#cartCampaignModal">
                                    <i class="fa-solid fa-gift"></i> Kampanyaları İncele
                                </a>
                            </div>
                        @endif
                        @if (count($cartService->carts()))
                            <div class="mobile-delete-all-cart">
                                <a href="javascript:;" data-js="delete-all-cart"><i class="fa-sharp fa-solid fa-trash"></i> {{ trans('translations.cart.sepeti_bosalt') }}</a>
                            </div>
                        @endif
                    </td>
                    @if ($cartService->carts()->count())
                        <td class="cart-header-delete-all-cart">
                            <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#cartCampaignModal">
                                <i class="fa-solid fa-gift"></i> Kampanyaları İncele
                            </a>
                        </td>
                    @endif
                    @if (count($cartService->carts()))
                        <td class="cart-header-delete-all-cart">
                            <a href="javascript:;" data-js="delete-all-cart"><i class="fa-sharp fa-solid fa-trash"></i> {{ trans('translations.cart.sepeti_bosalt') }}</a>
                        </td>
                    @endif
                </tr>
                @forelse ($cartService->carts() as $cart)

                    @php
                        $qty = (int) $cart->quantity;
                        $purchaseMin = (int) additional_setting('purchase_limit_minimum', 1);
                        $boxQuantity = (int) ($cart->product->box_quantity ?? 0);
                        $minQuantity = ($boxQuantity > 1) ? max($boxQuantity, $purchaseMin) : $purchaseMin;
                    @endphp

                    <tr class="product-box-contain" {{ $cart->is_campaign_gift ? "style=background-color:#e6ffdc;" : '' }}>
                        <td class="product-detail">
                            <div class="product border-0">
                                <a href="{{ route('product.detail', [$cart->product->slug]) }}" class="product-image">
                                    <img src="{{ $cart->product->image_small_url_1 }}" class="img-fluid blur-up lazyload" alt="">
                                </a>
                                <div class="product-detail">
                                    <ul>
                                        <li class="name">
                                            <a href="{{ route('product.detail', [$cart->product->slug]) }}">{{ Str::limit($cart->product->product_name, 20, '...') }}</a>
                                        </li>
                                        <li class="code">{{ $cart->product->code }}</li>
                                        <li class="price-mobile">
                                            {{ trans('translations.cart.urun_fiyati') }}: {!! $cart->productPriceShow() !!}
                                        </li>
                                        <li class="price-mobile">
                                            {{ trans('translations.cart.net_fiyat') }}: {!! $cart->productPriceShow(0, $cart->effective_discount, true) !!}
                                        </li>
                                        <li class="quantity-mobile">
                                            {{ trans('translations.cart.adet') }}
                                            @if ($cart->is_campaign_gift)
                                                : {{ $cart->quantity }}
                                            @else
                                                <div class="quantity-price w-50">
                                                    <div class="cart_qty">
                                                        <div class="input-group" data-selector="quantity-container" data-box-quantity="{{ $cart->product->box_quantity ?? 1 }}" data-box-exact="{{ $cart->product->box_quantity_must_be_exact ? 'true' : 'false' }}" data-url="/sepet/update/quantity/{{ $cart->id }}">
                                                            <button type="button"
                                                                class="btn{{ $qty == $minQuantity ? '' : ' qty-left-minus' }}"
                                                                data-id="{{ $cart->id }}"
                                                                data-url="{{ route('cart.delete.product', $cart->id) }}"
                                                                {{ $qty == $minQuantity ? 'data-js=delete-product-cart' : 'data-selector=qty-minus' }}
                                                            >
                                                                @if ($qty == $minQuantity)
                                                                    <i class="fa fa-trash ms-0" aria-hidden="true"></i>
                                                                @else
                                                                    <i class="fa fa-minus ms-0" aria-hidden="true"></i>
                                                                @endif
                                                            </button>

                                                            <input class="form-control input-number qty-input" type="text" name="quantity" value="{{ $cart->quantity }}" data-url="/sepet/update/quantity/{{ $cart->id }}" data-selector="qty-value">

                                                            <button type="button" class="btn qty-right-plus" data-selector="qty-plus">
                                                                <i class="fa fa-plus ms-0" aria-hidden="true"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </li>
                                        @if (auth('web')->check() && (auth('web')->user()->role === 'salesman' || auth('web')->user()->role === 'dealer'))
                                            <li class="discount-mobile">
                                                {{ trans('translations.cart.indirim_orani') }}: %{{ number_format($cart->effective_discount, 2) }}
                                                @if (auth('web')->user()->role === 'salesman' && (auth('web')->user()->can_edit_discount || auth('web')->user()->can_edit_price) && $cart->is_campaign_gift == false)
                                                    <a href="javascript:;" class="edit-price" data-bs-toggle="modal" data-bs-target='[data-selector="edit-price-modal"]' data-id="{{ $cart->id }}" data-list-price="{{ $cart->productPrice() }}" data-discount="{{ $cart->effective_discount }}">{{ trans('translations.cart.fiyat_duzenle') }}</a>
                                                @endif
                                            </li>
                                        @endif
                                        <li class="subtotal-mobile">
                                            {{ trans('translations.cart.ara_toplam') }}: {!! $cart->productPriceShow($cart->quantity, $cart->effective_discount, true) !!}
                                        </li>
                                        <li class="remove-mobile">
                                            <a href="javascript:;" class="remove close_button" data-js="delete-product-cart" data-url="{{ route('cart.delete.product', $cart->id) }}" data-id="{{ $cart->id }}" title="Sepetten Ürün Sil">{{ trans('translations.cart.sil') }}</a>
                                        </li>
                                        @if (additional_setting('cart_item_note_visibility', false))
                                            <li>
                                                <h4 class="table-title text-content mt-2 mb-1">{{ trans('translations.cart.aciklama') }}</h4>
                                                <div class="input-group">
                                                    <textarea class="form-control explanation" name="explanation" rows="1" data-selector="cart-update-explanation" data-url="/sepet/update/explanation/{{ $cart->id }}" data-old-value="{{ $cart->explanation }}">{{ $cart->explanation }}</textarea>
                                                </div>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </td>
                        <td class="price">
                            <h4 class="table-title text-content">{{ trans('translations.cart.urun_fiyati') }}</h4>
                            <h5>
                                {!! $cart->productPriceShow() !!}
                            </h5>
                        </td>
                        <td class="price">
                            <h4 class="table-title text-content">İndirim</h4>
                            @if (auth('web')->check() && (auth('web')->user()->role === 'salesman' || auth('web')->user()->role === 'dealer'))
                                <h5>%{{ number_format($cart->effective_discount, 2) }}</h5>
                                @if (auth('web')->user()->role === 'salesman' && (auth('web')->user()->can_edit_discount || auth('web')->user()->can_edit_price) && $cart->is_campaign_gift == false)
                                    <a href="javascript:;" class="edit-price mb-2" data-bs-toggle="modal" data-bs-target='[data-selector="edit-price-modal"]' data-id="{{ $cart->id }}" data-list-price="{{ $cart->productPrice() }}" data-discount="{{ $cart->effective_discount }}">{{ trans('translations.cart.fiyat_duzenle') }}</a>
                                @endif
                            @endif
                            @if ($cart->line_discount > 0)
                                <h5>
                                    <small class="text-content">Satır indirimi: -{{ number_format($cart->line_discount, 2) }} {{ $cart->currency }}</small>
                                </h5>
                            @endif
                            @if ($cart->campaign_discount > 0)
                                <h5>
                                    <small class="text-content">Kampanya (%{{ number_format($cart->campaign_discount_percent, 2) }}): -{{ number_format($cart->campaign_discount, 2) }} {{ $cart->currency }}</small>
                                </h5>
                            @endif
                            @if ($cart->line_discount > 0 || $cart->campaign_discount > 0)
                                <h5>
                                    <small class="text-content">Toplam: -{{ number_format($cart->line_discount + $cart->campaign_discount, 2) }} {{ $cart->currency }}</small>
                                </h5>
                            @endif
                        </td>
                        <td class="price">
                            <h4 class="table-title text-content">{{ trans('translations.cart.net_fiyat') }}</h4>
                            <h5>
                                {!! $cart->productPriceShow(0, $cart->effective_discount, true) !!}
                            </h5>
                        </td>
                        <td class="price">
                            <h4 class="table-title text-content">{{ trans('translations.cart.kdv') }}</h4>
                            <h5>%{{ $cart->product->vat_rate }}</h5>
                        </td>
                        <td class="quantity">
                            <h4 class="table-title text-content">{{ trans('translations.cart.adet') }}</h4>
                            @if ($cart->is_campaign_gift)
                                <h5>{{ $cart->quantity }}</h5>
                            @else

                                <div class="quantity-price">
                                    <div class="cart_qty">
                                        <div class="input-group" data-selector="quantity-container" data-box-quantity="{{ $cart->product->box_quantity ?? 1 }}" data-box-exact="{{ $cart->product->box_quantity_must_be_exact ? 'true' : 'false' }}" data-url="/sepet/update/quantity/{{ $cart->id }}">
                                            <button type="button"
                                                class="btn{{ $qty == $minQuantity ? '' : ' qty-left-minus' }}"
                                                data-id="{{ $cart->id }}"
                                                data-url="{{ route('cart.delete.product', $cart->id) }}"
                                                {{ $qty == $minQuantity ? 'data-js=delete-product-cart' : 'data-selector=qty-minus' }}
                                            >
                                                @if ($qty == $minQuantity)
                                                    <i class="fa fa-trash ms-0" aria-hidden="true"></i>
                                                @else
                                                    <i class="fa fa-minus ms-0" aria-hidden="true"></i>
                                                @endif
                                            </button>

                                            <input class="form-control input-number qty-input" type="text" name="quantity" value="{{ $cart->quantity }}" data-url="/sepet/update/quantity/{{ $cart->id }}" data-selector="qty-value">
                                            <button type="button" class="btn qty-right-plus" data-selector="qty-plus">
                                                <i class="fa fa-plus ms-0" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td class="price">
                            <h4 class="table-title text-content">{{ trans('translations.cart.ara_toplam') }}</h4>
                            <h5>{!! $cart->productPriceShow($cart->quantity, $cart->effective_discount, true) !!}</h5>
                        </td>
                        <td class="save-remove">
                            <h4 class="table-title text-content">{{ trans('translations.cart.islem') }}</h4>
                            @if (!$cart->is_campaign_gift)
                                @if(
                                    $cart->campaign_rule_type === 'free_product' &&
                                    app(\App\Services\CampaignService::class)->freeProductAllowsSameProduct(\App\Models\Campaign::find($cart->campaign_id))
                                )
                                    <a href="javascript:;" class="save notifi-wishlist" data-selector="add-same-product-gift" data-cart-id="{{ $cart->id }}" data-campaign-id="{{ $cart->campaign_id }}">
                                        Aynı Üründen Hediye Ekle
                                    </a>
                                @endif
                            @endif
                            <a href="javascript:;" class="remove close_button" data-js="delete-product-cart" data-url="{{ route('cart.delete.product', $cart->id) }}" data-id="{{ $cart->id }}" title="{{ trans('translations.cart.sepetten_urun_sil') }}">{{ trans('translations.cart.sil') }}</a>
                        </td>
                    </tr>
                    @if ($cart->campaign_note)
                        <tr>
                            <td colspan="8" class="p-2">
                                <div class="m-0">
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="fa-solid fa-tag me-1"></i>
                                        {{ $cart->campaign_note }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="5">{{ trans('translations.cart.sepet_bos') }}.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
