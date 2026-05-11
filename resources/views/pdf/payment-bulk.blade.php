<!doctype html>
<html lang="tr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
            .page-break {
                page-break-after: always;
            }
        </style>
    </head>
    <body>

        @foreach($receipts as $receipt)

            @php
                extract($receipt);
            @endphp

            @include('pdf.payment')

            @if(!$loop->last)
                <div class="page-break"></div>
            @endif

        @endforeach

    </body>
</html>