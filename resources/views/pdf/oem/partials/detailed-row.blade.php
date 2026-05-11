<tr>
    <td class="row-img">
        @if ($product->base64_image)
            <img src="{{ $product->base64_image }}" alt="Görsel">
        @else
            Görsel Yok
        @endif
    </td>
    <td>{{ $product->code }}</td>
    <td style="text-align: left;">
        <ul>
            @foreach ($product->attributeValues->groupBy('attribute_id') as $values)
                <li>
                    {{ $values->first()->attribute->name }}:
                    <span>{{ $values->pluck('name')->implode(', ') }}</span>
                </li>
            @endforeach
        </ul>
    </td>
    <td>{{ $product->box_quantity <= 0 ? 1 : $product->box_quantity }}</td>
    <td>{{ number_format($product->price_1, 2) . ' ' . $product->price_1_currency }}</td>
</tr>
