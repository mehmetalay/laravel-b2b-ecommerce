<table>
    <thead>
        <tr>
            <th colspan="4">Resim Olmayan Ürünler</th>
        </tr>
        <tr>
            <th>Resim</th>
            <th>Ürün Adı</th>
            <th>Ürün Kodu</th>
            <th>Stok</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td><img src="{{ asset($item->image_small_url_1_raw) }}" alt="" width="50"></td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->code }}</td>
                <td>{{ $item->stock }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
