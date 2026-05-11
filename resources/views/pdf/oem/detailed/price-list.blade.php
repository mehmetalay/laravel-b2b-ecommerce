<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>OEM Fiyat Listesi</title>
        <style>
            @page { margin: 0; }
            body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; margin: 0; padding: 0; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #525252; padding: 6px; text-align: center; vertical-align: middle; }
            th { background-color: #888; color: #fff; font-size: 18px; font-weight: 500; }
            th { font-size: 16px; font-weight: 400; }
            .category-title { background-color: #e10000; color: #000000; padding: 6px 10px; font-size: 30px; margin-top: 25px; text-align: center; font-weight: 700; }
            .row-img img { max-width: 200px; max-height: 200px; }
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
                            <th style="text-align: left;">ÜRÜN ÖZELLİKLERİ</th>
                            <th>KOLİ İÇİ ADETİ</th>
                            <th>FİYAT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $product)
                            @include('pdf.oem.partials.detailed-row', ['product' => $product])
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
                        <th style="text-align: left;">ÜRÜN ÖZELLİKLERİ</th>
                        <th>KOLİ İÇİ ADETİ</th>
                        <th>FİYAT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        @include('pdf.oem.partials.detailed-row', ['product' => $product])
                    @endforeach
                </tbody>
            </table>
        @endif
    </body>
</html>
