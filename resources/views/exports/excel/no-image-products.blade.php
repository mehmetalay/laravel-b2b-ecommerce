<table>
    <thead>
        <tr>
            <th colspan="3">Resim Olmayan Ürünler</th>
        </tr>
        <tr>
            <th>ID</th>
            <th>Ürün Adı</th>
            <th>Ürün Kodu</th>
            <th>Durum</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->code }}</td>
                <td>{{ $item->status == 1 ? 'Aktif' : 'Pasif' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
