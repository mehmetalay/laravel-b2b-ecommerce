<div class="row">
    @foreach ($attributes as $attribute)
        <div class="col-sm-6 col-md-4">
            <div class="form-group">
                <label>{{ $attribute->name }}</label>
                <select class="form-control attribute-value-select"
                    name="attribute_values_{{ $productId }}_{{ $attribute->id }}[]"
                    multiple>
                    @foreach ($attribute->attributeValues as $value)
                        <option value="{{ $value->id }}"
                            {{ in_array((int) $value->id, $selectedAttributeValueIds, true) ? 'selected' : '' }}>
                            {{ $value->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    @endforeach
</div>
