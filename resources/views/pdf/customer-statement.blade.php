<!doctype html>
<html lang="tr">
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color:#111; }

            /* Header */
            .header { width:100%; margin-bottom: 12px; }
            .header td { vertical-align: middle; }
            .logo { width: 160px; }
            .logo img { height: 65px; }
            .title { text-align: right; }
            .title .h1 { font-size: 14px; font-weight: bold; margin: 0; }
            .title .muted { font-size: 8px; color:#666; margin-top: 2px; }

            /* Info bar */
            .info { width:100%; margin-top: 8px; border:1px solid #e5e5e5; border-radius: 6px; }
            .info td { font-size: 8px; }
            .info .label { color:#666; }

            /* Table */
            table.grid { width:100%; border-collapse: collapse; margin-top: 10px; }
            table.grid th, table.grid td { border:1px solid #ddd; padding:6px; }
            table.grid th { background:#f5f5f5; font-weight: 700; font-size: 8px; }
            .text-center { text-align:center; }
            .text-right { text-align:right; }

            /* Totals */
            table.totals { width: 100%; margin-top: 10px; border-collapse: collapse; }
            table.totals td { padding: 4px 0; }
            .totals .label { text-align: right; color:#666; }
            .totals .value { text-align: right; font-weight: 700; width: 160px; }
        </style>
    </head>
    <body>

        @php
            $logo = pdf_image_base64('assets/images/logo.png');
            $currency = $totals['currency'] ?? ($items[0]['DOVKOD'] ?? 'TRY');
        @endphp

        {{-- HEADER --}}
        <table class="header">
            <tr>
                <td class="logo">
                    @if($logo)
                        <img src="{{ $logo }}" alt="Logo">
                    @endif
                </td>
                <td class="title">
                    <div class="h1">Müşteri Ekstresi</div>
                    <div class="muted">{{ now()->format('d.m.Y H:i') }}</div>
                </td>
            </tr>
        </table>

        {{-- INFO --}}
        <table class="info">
            <tr>
                <td class="label">Bayi</td>
                <td class="value">{{ $dealer->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Cari Kod</td>
                <td class="value">{{ $dealer->code ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Döviz</td>
                <td class="value">{{ $currency }}</td>
            </tr>
        </table>

        {{-- LIST --}}
        <table class="grid">
            <thead>
                <tr>
                    <th style="width:75px;">Tarih</th>
                    <th style="width:80px;">İşlem Tipi</th>
                    <th>Açıklama</th>
                    <th style="width:75px;">Vade</th>
                    <th style="width:85px;" class="text-center">Borç</th>
                    <th style="width:85px;" class="text-center">Alacak</th>
                    <th style="width:85px;" class="text-center">Bakiye</th>
                </tr>
            </thead>
            <tbody>
                @php $carryoverBalance = 0; @endphp

                @forelse ($items as $item)
                    @php
                        $debt = (float)($item['BORC'] ?? 0);
                        $receivable = (float)($item['ALACAK'] ?? 0);
                        $carryoverBalance += $debt;
                        $carryoverBalance -= $receivable;
                    @endphp
                    <tr>
                        <td>{{ date('d.m.Y', strtotime($item['FISTARIHI'])) }}</td>
                        <td>{{ $item['ISLEMTIPI'] }}</td>
                        <td>{{ $item['ACIKLAMA'] ?? '-' }}</td>
                        <td>{{ date('d.m.Y', strtotime($item['VADE'])) }}</td>
                        <td class="text-right">{{ number_format($debt, 2) . ' ' . ($item['DOVKOD'] ?? $currency) }}</td>
                        <td class="text-right">{{ number_format($receivable, 2) . ' ' . ($item['DOVKOD'] ?? $currency) }}</td>
                        <td class="text-right"><strong>{{ number_format($carryoverBalance, 2) . ' ' . ($item['DOVKOD'] ?? $currency) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Hareket bulunamadı.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- TOTALS --}}
        @if(!empty($items))
            <table class="totals">
                <tr>
                    <td class="label"><strong>Toplam Borç:</strong></td>
                    <td class="value">{{ number_format($totals['debit'] ?? 0, 2) . ' ' . $currency }}</td>
                </tr>
                <tr>
                    <td class="label"><strong>Toplam Alacak:</strong></td>
                    <td class="value">{{ number_format($totals['credit'] ?? 0, 2) . ' ' . $currency }}</td>
                </tr>
                <tr>
                    <td class="label"><strong>Bakiye:</strong></td>
                    <td class="value">{{ number_format($totals['balance'] ?? 0, 2) . ' ' . $currency }}</td>
                </tr>
            </table>
        @endif

    </body>
</html>
