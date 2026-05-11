<div class="rule-item border p-3 rounded mb-3" data-partial="bonus_product">
    <h6>Adet Bazlı Bonus Ürün Kampanyası</h6>

    <label>Ürün</label>
    <div class="selected-products mb-2" id="bonus-product-selected">
        <button type="button" class="btn btn-outline-primary open-product-popup" data-action="open-product-popup" data-target="bonus-product-selected" data-multiple="true">Ürün Seç</button>

        <table class="table table-bordered mt-2">
            <tbody>
                @if ($ruleData)
                    @php
                        $selectedProducts = $ruleData->campaign->products ?? collect();
                    @endphp

                    @foreach ($selectedProducts as $product)
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

    <div class="row mt-2">
        <div class="col-md-3">
            <label>Minimum Alım Adedi</label>
            <input type="number" name="rules[0][extra][min_quantity]" class="form-control" required value="{{ $ruleData->extra['min_quantity'] ?? '' }}" form="campaign-form">
        </div>

        <div class="col-md-3">
            <label>Bedelsiz Adet</label>
            <input type="number" name="rules[0][extra][bonus_quantity]" class="form-control" required value="{{ $ruleData->extra['bonus_quantity'] ?? '' }}" form="campaign-form">
        </div>
    </div>

    <input type="hidden" name="rules[0][rule_type]" value="bonus_product" form="campaign-form">
</div>
