<div class="rule-item border p-3 rounded mb-3" data-partial="free_product">
    <h6>Hediye Ürün Kampanyası</h6>

    <label>Tetikleyici Ürün(ler)</label>
    <div class="selected-products mb-2" id="trigger-products">
        <button type="button" class="btn btn-outline-primary open-product-popup" data-action="open-product-popup" data-target="trigger-products" data-multiple="true">
            Ürün Ekle
        </button>

        <table class="table table-bordered mt-2">
            <tbody>
                @php
                    $triggerProducts = $ruleData?->campaign?->products ?? collect();
                @endphp

                @foreach ($triggerProducts as $product)
                    <tr data-id="{{ $product->id }}">
                        <td>
                            {{ $product->name }}
                            <div><small class="text-muted">{{ $product->code }}</small></div>
                            <input type="hidden" name="products[]" value="{{ $product->id }}" form="campaign-form">
                        </td>

                        <td class="text-center">
                            <a href="javascript:;" class="btn btn-danger btn-sm delete-row" data-action="delete-row">
                                <i class="las la-trash"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>


    <div class="row mt-2">
        <div class="col-md-3">
            <label>Minimum Alım Adedi</label>
            <input type="number" name="rules[0][extra][min_quantity]" class="form-control" required value="{{ $ruleData->extra['min_quantity'] ?? 1 }}" form="campaign-form">
            <small class="text-muted">Bu üründen en az kaç adet alınmalı?</small>
        </div>
    </div>

    <label class="mt-3">Hediye Ürün(ler)</label>
    <div class="selected-products mb-2" id="gift-products">
        <button type="button" class="btn btn-outline-success open-product-popup" data-action="open-product-popup" data-target="gift-products" data-multiple="true">
            Hediye Ürün Ekle
        </button>

        <table class="table table-bordered mt-2">
            <tbody>
                @foreach (($ruleData->extra['gifts'] ?? []) as $giftId)
                    @php $gift = \App\Models\Product::find($giftId); @endphp
                    @if ($gift)
                        <tr data-id="{{ $gift->id }}">
                            <td>
                                {{ $gift->name }}
                                <div><small class="text-muted">{{ $gift->code }}</small></div>
                                <input type="hidden" name="rules[0][extra][gifts][]" value="{{ $gift->id }}" form="campaign-form">
                            </td>
                            <td class="text-center">
                                <a href="javascript:;" class="btn btn-danger btn-sm delete-row" data-action="delete-row">
                                    <i class="las la-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row mt-2">
        <div class="col-md-3">
            <label>Hediye Adet</label>
            <input type="number" name="rules[0][extra][gift_quantity]" class="form-control" required value="{{ $ruleData->extra['gift_quantity'] ?? 1 }}" form="campaign-form">
            <small class="text-muted">Her tetikleme için kaç adet hediye verilecek?</small>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="form-group row align-items-center">
                <label class="col-3 col-form-label">
                    Aynı Ürün Hediye
                    <small class="text-muted d-block">
                        Hediye ürün olarak tetikleyici ürün eklenir
                    </small>
                </label>
                <div class="col-3">
                    <span class="switch">
                        <label>
                            <input
                                type="checkbox"
                                class="form-check-input"
                                name="rules[0][extra][same_product_gift]"
                                value="1"
                                {{ !empty($ruleData->extra['same_product_gift']) ? 'checked' : '' }}
                                form="campaign-form"
                            >
                            <span></span>
                        </label>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="form-group row align-items-center">
                <label class="col-3 col-form-label">
                    Katlamalı
                    <small class="text-muted d-block">
                        Adet arttıkça hediye adedi katlanır
                    </small>
                </label>
                <div class="col-3">
                    <span class="switch">
                        <label>
                            <input
                                type="checkbox"
                                class="form-check-input"
                                name="rules[0][extra][is_stackable]"
                                value="1"
                                {{ !empty($ruleData->extra['is_stackable']) ? 'checked' : '' }}
                                form="campaign-form"
                            >
                            <span></span>
                        </label>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="rules[0][rule_type]" value="free_product" form="campaign-form">
</div>
