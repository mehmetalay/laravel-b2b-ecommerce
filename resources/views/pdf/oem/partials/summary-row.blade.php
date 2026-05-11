<tr>
    <td class="row-img">
        @if ($product->base64_image)
            <img src="{{ $product->base64_image }}" alt="Görsel">
        @else
            Görsel Yok
        @endif
    </td>
    <td>{{ $product->code }}</td>

    @if ($category && $category->id == 2)
        {{-- OEM (kategori_id = 2) için özel alanlar --}}
        <td>{{ $product->attributeValues->firstWhere('attribute_id', 17)->name ?? '-' }}</td>
        <td style="font-size: 14px; white-space: nowrap;">
            @php
                $modelYears = $product->attributeValues
                    ->where('attribute_id', 5)
                    ->pluck('name')
                    ->filter()
                    ->map(fn($year) => (int) $year)
                    ->sort()
                    ->values();

                $modelYearRange = $modelYears->isNotEmpty()
                    ? $modelYears->first() . ' - ' . $modelYears->last()
                    : '-';
            @endphp
            {{ $modelYearRange }}
        </td>
        <td>
            @php
                $filteredAttributes = $product->attributeValues
                    ->groupBy('attribute_id')
                    ->reject(fn($values) => in_array($values->first()->attribute_id, [17, 15, 12, 11, 16, 5, 14, 4, 13]))
                    ->map(fn($values) => $values->first()->attribute->name . ': ' . $values->pluck('name')->implode(', '));
            @endphp
            {{ $filteredAttributes->implode(' + ') }}
        </td>
    @else
        {{-- OEM dışındaki kategoriler için: sadece ürün özellikleri --}}
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
    @endif

    <td>{{ $product->box_quantity <= 0 ? 1 : $product->box_quantity }}</td>
    <td>{{ number_format($product->price_1, 2) . ' ' . $product->price_1_currency }}</td>
</tr>
