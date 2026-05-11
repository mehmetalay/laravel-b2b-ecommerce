<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>OEM Fiyat Listesi</title>
        <style>
            @page { margin: 0; }
            body { margin: 0; padding: 0; }
            .cover-page {
                width: 100%;
                height: 100%;
            }
            .cover-page img {
                width: 100%;
                height: auto;
                display: block;
                object-fit: cover;
            }
        </style>
    </head>
    <body>
        <div class="cover-page">
            <img src="{{ $oemCoverImg }}" alt="Kapak Görseli">
        </div>
    </body>
</html>
