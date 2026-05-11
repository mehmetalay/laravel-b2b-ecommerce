<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>Ödeme Linki</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style type="text/css">
            @media screen {
                @font-face {
                    font-family: 'Source Sans Pro';
                    font-style: normal;
                    font-weight: 400;
                    src: local('Source Sans Pro Regular'), local('SourceSansPro-Regular'), url(https://fonts.gstatic.com/s/sourcesanspro/v10/ODelI1aHBYDBqgeIAH2zlBM0YzuT7MdOe03otPbuUS0.woff) format('woff');
                }
                @font-face {
                    font-family: 'Source Sans Pro';
                    font-style: normal;
                    font-weight: 700;
                    src: local('Source Sans Pro Bold'), local('SourceSansPro-Bold'), url(https://fonts.gstatic.com/s/sourcesanspro/v10/toadOcfmlt9b38dHJxOBGFkQc6VGVFSmCnC_l7QZG60.woff) format('woff');
                }
            }
            body,
            table,
            td,
            a {
                -ms-text-size-adjust: 100%; /* 1 */
                -webkit-text-size-adjust: 100%; /* 2 */
            }
            table,
            td {
                mso-table-rspace: 0pt;
                mso-table-lspace: 0pt;
            }
            img {
                -ms-interpolation-mode: bicubic;
            }
            a[x-apple-data-detectors] {
                font-family: inherit !important;
                font-size: inherit !important;
                font-weight: inherit !important;
                line-height: inherit !important;
                color: inherit !important;
                text-decoration: none !important;
            }
            div[style*="margin: 16px 0;"] {
                margin: 0 !important;
            }
            body {
                width: 100% !important;
                height: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            table {
                border-collapse: collapse !important;
            }
            a {
                color: #1a82e2;
            }
            img {
                height: auto;
                line-height: 100%;
                text-decoration: none;
                border: 0;
                outline: none;
            }
        </style>
    </head>
    <body style="background-color: #e9ecef;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td align="center" bgcolor="#e9ecef">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                        <tr>
                            <td align="center" valign="top" style="padding: 36px 24px;">
                                <a href="{{ route('index') }}" target="_blank" style="display: inline-block;">
                                    <img src="{{ image_url(config('images.default.logo'), 'images') }}" alt="Logo" border="0" width="250" style="display: block; width: 250px; max-width: 250px; min-width: 250px;">
                                </a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td align="center" bgcolor="#e9ecef">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                        <tr>
                            <td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
                                <p style="margin: 0;font-weight: 700;">Sayın {{ $payment_link->user->name }},</p>
                                <p style="margin: 0;">{{ config('app.name') }} sistemimiz üzerinden yapmanız gereken ödemeniz için bir Ödeme Linki oluşturulmuştur. Aşağıdaki bağlantıyı kullanarak ödemenizi güvenli bir şekilde tamamlayabilirsiniz:</p>
                            </td>
                        </tr>
                        <tr>
                            <td align="left" bgcolor="#ffffff" style="padding: 24px;font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif;font-size: 16px;line-height: 38px;">
                                <a href="{{ route('payments.payment-link', [$payment_link->token]) }}" style="color: #d21a15;font-weight: 700;">{{ route('payments.payment-link', [$payment_link->token]) }}</a>
                            </td>
                        </tr>
                        <tr>
                            <td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
                                <p style="margin: 0;font-weight: 700;">{{ trans('translations.emails.payment_link.odeme_bilgileri') }}</p>
                                <p style="margin: 0;">{{ trans('translations.emails.payment_link.odeme_tutari') }} {{ number_format($payment_link->amount, 2) . ' TL' }}</p>
                                <p style="margin: 0;">{{ trans('translations.emails.payment_link.tutar_degistirilemez') }} {{ $payment_link->amount_locked ? trans('translations.emails.payment_link.evet') : trans('translations.emails.payment_link.hayir') }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 24px;">
                                <p style="margin: 0;">{{ trans('translations.emails.payment_link.linke_tiklayarak_odemenizi_en_kisa_surede_tamamlamanizi_rica_ederiz_herhangi_bir_sorunuz_veya_yardima_ihtiyaciniz_olursa_bizimle_iletisime_gecmekten_cekinmeyin') }}</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td align="center" bgcolor="#e9ecef">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                        <tr>
                            <td align="center" bgcolor="#e9ecef" style="padding: 24px; font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 20px; color: #666;">
                                <p style="margin: 0;">{{ general_info('company_official_name') }}</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
