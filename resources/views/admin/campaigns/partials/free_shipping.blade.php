<div class="rule-item border p-3 rounded mb-3" data-partial="free_shipping">
    <h6>Bedelsiz Nakliye Kampanyası</h6>

    <label>Ürün(ler)</label>
    <div class="selected-products mb-2" id="free-shipping-products">
        <button type="button" class="btn btn-outline-primary open-product-popup" data-action="open-product-popup" data-target="free-shipping-products" data-multiple="true">
            Ürün Ekle
        </button>

        <table class="table table-bordered mt-2">
            <tbody>
                @if ($ruleData)
                    @php
                        $selectedProducts = $ruleData->campaign->products ?? collect();
                    @endphp

                    @foreach($selectedProducts as $product)
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
                @endif
            </tbody>
        </table>
    </div>

    <div class="row mt-3">
        <div class="col-md-3">
            <label>Minimum Adet</label>
            <input type="number" name="rules[0][extra][min_quantity]" class="form-control" value="{{ $ruleData->extra['min_quantity'] ?? '' }}" form="campaign-form">
            <small class="text-muted">Ürün adedi (opsiyonel)</small>
        </div>

        <div class="col-md-3">
            <label>Minimum Tutar</label>
            <input type="number" step="0.01" name="rules[0][extra][min_amount]" class="form-control" value="{{ $ruleData->extra['min_amount'] ?? '' }}" form="campaign-form">
            <small class="text-muted">Ürün toplami / sepet miktarı (opsiyonel)</small>
        </div>
    </div>

    <input type="hidden" name="rules[0][rule_type]" value="free_shipping" form="campaign-form">
</div>
