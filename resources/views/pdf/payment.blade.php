<!doctype html>
<html lang="tr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>ONLİNE ÖDEME DEKONTU</title>
        <style type="text/css">
            * { font-family: "DejaVu Sans"; font-size: 14px; }
            *, ::after, ::before { box-sizing: border-box; }
            td { vertical-align: top; }
            .fs-15 { font-size: 15px; margin-bottom: 10px; }
            .fs-30 { font-size: 30px; margin-bottom: 10px; }
            .box { border: 1px solid #c3c3c3; padding: 10px; border-radius: 5px; min-height: 90px; }
            .header-title {
                font-size: 22px;
                font-weight: bold;
                text-align: center;
                padding-top: 10px;
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
                <td style="width: 33.33%;">
                    <img src="{{ $logo }}" style="height: 100px;">
                </td>
                <td style="width: 33.33%;" class="header-title">
                    ONLİNE ÖDEME DEKONTU
                </td>
                <td style="width: 33.33%; text-align: right;">
                    <div><b>İşlem No:</b> <span style="font-size:14px;color:#c62828;font-weight:bold;">{{ $model->eta_payment_number }}</span></div>
                    <div><b>Tarih:</b> {{ $model->formatted_completed_at }}</div>
                </td>
                </tr>
            </tbody>
        </table>
        <hr style="margin-bottom: 10px; border: 0; border-top: 1px solid #999;">
        <table style="width: 100%; margin-bottom: 10px;">
            <tbody>
                <tr>
                <td style="width: 50%;">
                    <div style="font-size: 15px; font-weight: bold; margin-bottom: 5px;">ÖDEME VE KART BİLGİSİ</div>
                    <div style="font-size: 13px;">{{ $model->oid }}</div>
                    <div style="font-size: 13px;">{{ "{$model->card_name} - {$model->formatted_phone_number}" }}</div>
                    <div style="font-size: 13px;">{{ $model->card_number }}</div>
                </td>
                <td style="width: 50%;">
                    <div style="font-size: 15px; font-weight: bold; margin-bottom: 5px;">CARİ HESAP BİLGİSİ</div>
                    <div style="font-size: 13px;">{{ str_limit($model->user->name, '50', '..') }}</div>
                    <div style="font-size: 13px;">{{ $model->user->code }}</div>
                    <div><span style="font-size: 12px;">{{ str_limit($model->user->address_1, '130', '..') }}</span></div>
                </td>
                </tr>
            </tbody>
        </table>
        <table style="width: 100%; margin-bottom: 20px;">
            <tbody>
                <tr>
                    <td style="width: 100%;" colspan="3">
                        <div style="font-size: 15px; font-weight: bold; margin-bottom: 5px;">İŞLEM BİLGİLERİ</div>
                    </td>
                </tr>
                <tr>
                    <td style="width: 25%; padding-right: 1em;">
                        <div class="box">
                            <div class="fs-15">ÖDEME TÜRÜ</div>
                            <div class="fs-30">{{ $formattedInstallment }}</div>
                        </div>
                    </td>
                    <td style="width: 25%; padding-right: 1em;">
                        <div class="box">
                            <div class="fs-15">ÖDENEN TUTAR</div>
                            <div class="fs-30">{{ number_format($amountPaid, 2) }} TL</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="box">
                            <div class="fs-15">AYLIK ÖDEME TUTARI</div>
                            <div class="fs-30">{{ number_format($monthlyPaymentAmount, 2) }} TL</div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div style="margin-bottom: 10px; font-size: 12px;">Ödemeniz, {{ general_info('company_official_name', config('app.name')) }} aracılığıyla "<b>{{ str_upper($spokenPrice) }}</b>" olarak tahsil edilmiştir.</div>
        <hr style="border: 0; border-top: 1px solid #999;">
        <div style="margin-bottom: 10px; font-size: 11px;">Cari hesaba mahsuben, 3D Secure (şifreli doğrulama) sistemi üzerinden sanal POS aracılığıyla kredi kartı tahsilatı gerçekleştirilmiştir.</div>
        <div style="margin-bottom: 10px; font-size: 11px;">Bu makbuz, kart hamilinin işlemi kendi isteğiyle ve güvenli ödeme altyapısı üzerinden onayladığını gösterir.</div>
        <div style="margin-bottom: 10px; font-size: 11px;">Kart hamili, bu işlemle ilgili olarak gerek banka, gerekse {{ general_info('company_official_name', config('app.name')) }} nezdinde herhangi bir itiraz, iade veya dava hakkı bulunmadığını kabul ve taahhüt eder.</div>
        <div style="margin-bottom: 10px; font-size: 11px;">Makbuzda yer alan bilgiler ile kurum kayıtlarının uyuşmaması halinde, {{ general_info('company_official_name', config('app.name')) }} kayıtları esas alınır.</div>
        <div style="margin-bottom: 30px; font-size: 11px;">Tüm işlemler, güvenli çevrimiçi ödeme altyapısı üzerinden gerçekleştirilmiştir.</div>
        <table style="width: 100%;">
            <tbody>
                <tr>
                    <td style="width: 50%;">
                        <div style="margin-bottom: 10px;"><b style="font-size: 13px;">Sanal Pos Banka Adı</b></div>
                        <div style="margin-bottom: 0px; font-size: 11px;">{{ $model->bankIntegration->full_name }}</div>
                    </td>
                    <td style="width: 50%;">
                        <div style="margin-bottom: 10px;"><b style="font-size: 13px;">Ödediğim Tutar Karşılığındaki Malı ve/veya Hizmeti Teslim Aldım.</b></div>
                        <div style="margin-bottom: 0px; font-size: 11px;">Bu ödeme {{ str_limit($model->user->name, '80', '..') }} nezdindeki cari hesabınıza mahsup edilecektir.</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </body>
</html>
