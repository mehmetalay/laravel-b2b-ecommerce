<div class="rule-item border p-3 rounded mb-3" data-partial="tiered_price">
    <h6>Ürün İndirim Kampanyası</h6>

    <label>Ürün(ler)</label>
    <div class="selected-products mb-2" id="tiered-price-products">
        <button type="button" class="btn btn-outline-primary open-product-popup" data-action="open-product-popup" data-target="tiered-price-products" data-multiple="true">
            Ürün Ekle
        </button>

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
                                <div>
                                    <small class="text-muted">{{ $product->code }}</small>
                                </div>
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

    <table class="table tiered-table mt-2">
        <thead>
            <tr>
                <th>Min Adet</th>
                <th>İndirim Değeri</th>
                <th>İndirim Tipi</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            @if ($ruleData && isset($ruleData->extra['tiers']))
                @foreach ($ruleData->extra['tiers'] as $index => $tier)
                    <tr class="tiered-row">
                        <td>
                            <input type="number" name="rules[0][extra][tiers][{{ $index }}][min_quantity]" class="form-control" required value="{{ $tier['min_quantity'] }}" form="campaign-form">
                        </td>
                        <td>
                            <input type="number" step="0.01" name="rules[0][extra][tiers][{{ $index }}][action_value]" class="form-control" required value="{{ $tier['action_value'] }}" form="campaign-form">
                        </td>
                        <td>
                            <select name="rules[0][extra][tiers][{{ $index }}][price_type]" class="form-control" form="campaign-form">
                                <option value="percent" {{ $tier['price_type'] == 'percent' ? 'selected' : '' }}>Yüzde</option>
                                <option value="fixed" {{ $tier['price_type'] == 'fixed' ? 'selected' : '' }}>Fiyat İndirimi</option>
                                <option value="net" {{ ($tier['price_type'] ?? '') == 'net' ? 'selected' : '' }}>Net Fiyat</option>
                            </select>
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-danger remove-tier" data-action="remove-tier">-</button>
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <button type="button" class="btn btn-outline-primary add-tier" data-action="add-tier">+ Satır Ekle</button>

    <input type="hidden" name="rules[0][rule_type]" value="tiered_price" form="campaign-form">
</div>
