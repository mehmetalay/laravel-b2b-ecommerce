<!doctype html>
<html lang="tr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>ÖDEME LİNKİ MAKBUZU</title>
        <style type="text/css">
            * {
                font-family: "DejaVu Sans Mono", monospace;
                font-size: 14px;
            }
            *, ::after, ::before {
                box-sizing: border-box;
            }
            .td-25 {
                width: 25%;
            }
            .td-33 {
                width: 33.33%;
            }
            .td-50 {
                width: 50%;
            }
            .td-75 {
                width: 75%;
            }
            .td-100 {
                width: 100%;
            }
        </style>
    </head>
    <body>
        @php
            $logo = pdf_image_base64('assets/images/logo.png');
        @endphp
        <table style="width: 100%">
            <tbody>
                <tr>
                <td class="td-50">
                    <img src="{{ $logo }}" style="height: 75px;">
                </td>
                <td class="td-50" style="text-align: right;">
                    <h2 style="margin: 0px;">ÖDEME LİNKİ MAKBUZU</h2>
                    <div><strong>No.</strong> <span style="font-size: 2.5rem;font-weight: 300;line-height: 1.2;color:#dc3545;">{{ $model->id }}</span></div>
                    <div><strong>İşlem Tarihi:</strong> {{ $model->formatted_payment_date }}</div>
                </td>
                </tr>
            </tbody>
        </table>
        <hr style="margin-bottom: 10px;">
        <table style="width: 100%; margin-bottom: 10px;">
            <tbody>
                <tr>
                <td class="td-50">
                    <h6 style="margin-bottom: 5px; margin-top: 0px;">ÖDEME VE KART BİLGİSİ</h6>
                    <div>{{ $model->oid }}</div>
                    <div>{{ "{$model->card_name} - {$model->formatted_phone_number}" }}</div>
                    <div>{{ $model->card_number }}</div>
                </td>
                <td class="td-50">
                    <h6 style="margin-bottom: 5px; margin-top: 0px;">CARİ HESAP BİLGİSİ</h6>
                    <div>{{ $model->user->name }}</div>
                    <div><span style="font-size: 12px;">{{ $model->user->address }}</span></div>
                </td>
                </tr>
            </tbody>
        </table>
        <table style="width: 100%; margin-bottom: 10px;">
            <tbody>
                <tr>
                    <td class="td-25">
                        <div style="border: 1px solid #c3c3c3; padding: 10px; border-radius: 5px;">
                            <div style="font-size: 14px; margin-bottom: 20px;">GİRİLEN TUTAR</div>
                            <div style="font-size: 30px;margin-bottom: 10px;">{{ $model->formatted_entered_amount }} TL</div>
                        </div>
                    </td>
                    <td class="td-25">
                        <div style="border: 1px solid #c3c3c3; padding: 10px; border-radius: 5px;">
                            <div style="font-size: 14px; margin-bottom: 20px;">TAKSİT SAYISI</div>
                            <div style="font-size: 30px;margin-bottom: 10px;">{{ $model->formatted_installment }}</div>
                        </div>
                    </td>
                    <td class="td-25">
                        <div style="border: 1px solid #c3c3c3; padding: 10px; border-radius: 5px;">
                        <div style="font-size: 14px; margin-bottom: 20px;">ÖDENEN TUTAR</div>
                        <div style="font-size: 30px;margin-bottom: 10px;">{{ number_format($amountPaid, 2) }} TL</div>
                        </div>
                    </td>
                    <td class="td-25">
                        <div style="border: 1px solid #c3c3c3; padding: 10px; border-radius: 5px;">
                        <div style="font-size: 14px; margin-bottom: 20px;">ÖDENEN TUTAR (USD)</div>
                        <div style="font-size: 30px;margin-bottom: 10px;">{{ number_format($amountPaidUSD, 2) }} USD</div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div style="margin-bottom: 10px; font-size: 11px;">C/H’ye mahsuben kredi kartı tahsilatı yapılmıştır.</div>
        <div style="margin-bottom: 10px; font-size: 11px;">Yalnız; {{ $spokenPrice }}.</div>
        <div style="margin-bottom: 10px; font-size: 11px;">Bu makbuz üzerindeki bilgiler ile kurum kayıtlarının uyuşmaması halinde {{ general_info('company_official_name') }} kayıtları esas alınacaktır.</div>
        <div style="margin-bottom: 10px; font-size: 11px;">İşbu makbuz, kart hamili ile adına tahsilat yapılan kişi adına ödeme alan arasında düzenlenmiştir. Bu belgenin imzalanması ile kart sahibinin yukarıda bilgileri bulunan ödeme işlemine muavafakat ettiği kabul olunur. Kart hamilinin işbu makbuz ile yapılan kredi kartı nedeni ile gerek banka, gerekse ödeme alan gerçek/tüzel kişiye karşı hiçbir itiraz ve dava hakkı bulunmadığını gayrikabili rücu olarak kabul ve taahhüt eder.</div>
        <div style="margin-bottom: 0px; font-size: 12px;"><strong>Sanal Pos Banka Adı:</strong> {{ $model->bankIntegration->full_name }}</div>
        <div style="margin-bottom: 10px; font-size: 12px;"><b>USD Kur:</b> {{ number_format($USDExchangeRate, 6) }}</div>
    </body>
</html>
