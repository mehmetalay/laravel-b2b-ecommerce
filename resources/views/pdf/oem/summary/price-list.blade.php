<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>OEM Fiyat Listesi</title>
        <style>
            @page { margin: 0; }
            body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; margin: 0; padding: 0; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #525252; padding: 2px; text-align: center; vertical-align: middle; }
            th { background-color: #888; color: #000000; font-weight: 500; }
            th { font-weight: 400; }
            .category-title { background-color: #e10000; color: #000000; border: 1px solid #525252; padding: 4px 10px; text-align: center; font-weight: 700; font-size: 16px; }
            .row-img img { max-width: 75px; max-height: 75px; }
        </style>
    </head>
    <body>
        @if ($category && $category->id == 2)
            @foreach ($products as $brand => $items)
                <div class="category-title">{{ $brand }}</div>
                <table>
                    <thead>
                        <tr>
                            <th>GÖRSEL</th>
                            <th>ÜRÜN KODU</th>
                            <th>ARABA MODELİ</th>
                            <th>MODEL YILI</th>
                            <th>ÜRÜN ÖZELLİKLERİ</th>
                            <th>KOLİ İÇİ ADETİ</th>
                            <th>FİYAT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $product)
                            @include('pdf.oem.partials.summary-row', ['product' => $product, 'category' => $category])
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @else
            <table>
                <thead>
                    <tr>
                        <th>GÖRSEL</th>
                        <th>ÜRÜN KODU</th>
                        <th>ÜRÜN ÖZELLİKLERİ</th>
                        <th>KOLİ İÇİ ADETİ</th>
                        <th>FİYAT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        @include('pdf.oem.partials.summary-row', ['product' => $product, 'category' => $category])
                    @endforeach
                </tbody>
            </table>
        @endif
    </body>
</html>
