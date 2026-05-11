<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>Sipariş</title>
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
                            <td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 24px;">
                                <p style="margin: 0;">{{ isset($order->plasiyer) ? trans('translations.emails.order.username_adina_plasiyername_adli_plasiyer_tarafindan_siparis_olusturulmustur_asagida_siparis_detaylari_bulunmaktadir', ['user_name' => $order->user->name, 'plasiyer_name' => $order->plasiyer->name]) : trans('translations.emails.order.username_adli_bayi_tarafindan_siparis_olusturulmustur_asagida_siparis_detaylari_bulunmaktadir', ['user_name' => $order->user->name]) }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td align="left" bgcolor="#ffffff" style="font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 24px;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td align="left" bgcolor="#e9ecef" width="75%" style="padding: 12px;font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 24px;"><strong>{{ trans('translations.emails.order.siparis_no') }}</strong></td>
                                        <td align="left" bgcolor="#e9ecef" width="25%" style="padding: 12px;font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 24px;"><strong>{{ '#' . $order->id }}</strong></td>
                                    </tr>
                                    <!-- product item -->
                                    @foreach ($order->orderProducts()->with('product')->get()->sortBy('product.code') as $key => $item)
                                        <tr style="{{ $key != 0 ? 'border-top: 1px solid #dfdfdf;' : '' }}">
                                            <td align="left" width="75%" style="padding: 6px 12px;font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 24px;">
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                    <tbody>
                                                        <tr style="display: flex;align-items: center;">
                                                            <td align="left" bgcolor="#ffffff" valign="top">
                                                                <img src="{{ str_replace(' ', '%20', asset($item->product->image_small_url_1_raw)) }}" style="display: block;" width="70">
                                                            </td>
                                                            <td align="left" bgcolor="#ffffff" valign="top" style="padding-left: 12px;">
                                                                <p style="margin: 0;">{{ $item->product_name_code }}</p>
                                                                <p style="margin: 0;font-size:12px;">{{ trans('translations.emails.order.adet') . ': ' . $item->quantity }}</p>
                                                                <p style="margin: 0;font-size:12px;">{{ trans('translations.emails.order.indirim') . ': ' . $item->discount }}</p>
                                                                @if ($item->explanation)
                                                                    <p style="margin: 0;font-size:12px;">{{ trans('translations.emails.order.aciklama') . ': ' . $item->explanation }}</p>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                            <td align="left" width="25%" style="padding: 6px 12px;font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 24px;">{{ $item->formatted_total_price }}</td>
                                        </tr>
                                    @endforeach
                                    <!-- end product item -->
                                </table>
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 14px 0px 14px 0px;">
                                    <!-- Ürünler toplamı -->
                                    <tr>
                                        <td align="left" width="65%" style="padding: 6px 12px;font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 24px;">{{ trans('translations.emails.order.urunler') }}</td>
                                        <td align="left" width="35%" style="padding: 6px 12px;font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 24px;">{{ $order->formatted_total_product_price }}</td>
                                    </tr>
                                    
                                    <!-- Toplam Ürün -->
                                    <tr>
                                        <td align="left" width="65%" style="padding: 6px 12px;font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 24px;">{{ trans('translations.emails.order.toplam_urun') }}</td>
                                        <td align="left" width="35%" style="padding: 6px 12px;font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 24px;">{{ $order->total_quantity . ' ' . trans('translations.emails.order.adet') }}</td>
                                    </tr>

                                    <!-- Sepet İndirimi 1 -->
                                    @if ($order->cart_discount_rate_1)
                                        <tr>
                                            <td align="left" width="65%" style="padding: 6px 12px;font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 24px;">{{ trans('translations.emails.order.sepet_indirimi_1') . ' (%' . $order->cart_discount_rate_1 . ')' }}</td>
                                            <td align="left" width="35%" style="padding: 6px 12px;font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 24px;">-{{ $order->formatted_cart_discount_1 }}</td>
                                        </tr>
                                    @endif

                                    <!-- Toplam Tutar -->
                                    <tr>
                                        <td align="left" width="65%" style="padding: 12px; font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 24px; border-top: 2px dashed #e9ecef; border-bottom: 2px dashed #e9ecef;"><strong>{{ trans('translations.emails.order.toplam_tutar') }}</strong></td>
                                        <td align="left" width="35%" style="padding: 12px; font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 24px; border-top: 2px dashed #e9ecef; border-bottom: 2px dashed #e9ecef;"><strong>{{ $order->total_amount }}</strong></td>
                                    </tr>
                                </table>
                                @if ($order->explanation)
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 14px 0px 14px 0px;">
                                        <!-- açıklama -->
                                        <tr>
                                            <td style="padding: 6px 12px;font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-weight: bold; font-size:14px; line-height: 24px;">{{ trans('translations.emails.order.genel_aciklama') }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 6px 12px;font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 24px;">{{ $order->explanation }}</td>
                                        </tr>
                                    </table>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td align="center" bgcolor="#e9ecef">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                        <tr>
                            <td align="left" bgcolor="#ffffff" style="padding: 6px 10px 6px 10px; font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 24px;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="width: 50%; vertical-align: top; padding: 0 15px 0 0;">
                                            <table style="width: 100%; border-collapse: collapse;">
                                                <tr>
                                                    <td style="padding: 0 0 10px 0; font-weight: bold; font-size:14px;">{{ trans('translations.emails.order.siparis_bilgisi') }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0 0 10px 0;">{{ trans('translations.emails.order.siparis_no') }}: <strong>{{ $order->id }}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0 0 10px 0;">{{ trans('translations.emails.order.siparis_tarihi') }}: <strong>{{ $order->formatted_created_at }}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0 0 10px 0;">{{ trans('translations.emails.order.plasiyer') }}: <strong>{{ $order->salesman_name }}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0;">{{ trans('translations.emails.order.bayi') }}: <strong>{{ $order->dealer_name }}</strong></td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td style="width: 50%; vertical-align: top; padding: 0 0 0 15px;">
                                            <table style="width: 100%; border-collapse: collapse;">
                                                <tr>
                                                    <td style="padding: 0 0 10px 0; font-weight: bold; font-size:14px;">{{ trans('translations.emails.order.odeme_bilgisi') }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0 0 10px 0;">{{ trans('translations.emails.order.odeme_plani') }}: <strong>{{ $order->payment_plan_name }}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0;">{{ trans('translations.emails.order.odeme_turu') }}: <strong>{{ $order->payment_type_name }}</strong></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
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
