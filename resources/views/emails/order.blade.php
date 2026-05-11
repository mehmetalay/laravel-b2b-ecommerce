<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Sipariş</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style type="text/css">
    body {
      width: 100% !important;
      height: 100% !important;
      padding: 0 !important;
      margin: 0 !important;
      background-color: #f4f6f9;
      font-family: 'Source Sans Pro', Helvetica, Arial, sans-serif;
    }
    table {
      border-collapse: collapse !important;
    }
    .wrapper {
      width: 100%;
      max-width: 600px;
      margin: 0 auto;
    }
    .card {
      background-color: #ffffff;
      border-radius: 6px;
      border: 1px solid #e3e6eb;
      margin-bottom: 20px;
      padding: 24px;
    }
    h2, h3 {
      margin: 0 0 12px 0;
      color: #1a3e72;
      font-weight: 600;
    }
    p {
      margin: 0 0 8px 0;
      color: #555555;
      font-size: 13px;
      line-height: 20px;
    }
    .table-header {
      background-color: #e8f0fc;
      font-weight: bold;
      color: #1a3e72;
    }
    .total-row {
      border-top: 2px solid #1a3e72;
      font-weight: bold;
      color: #1a3e72;
    }
    .discount {
      color: #e70000;
      font-size: 12px;
    }
  </style>
</head>
<body>
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" bgcolor="#f4f6f9" style="padding: 24px;">
                <table class="wrapper" border="0" cellpadding="0" cellspacing="0" width="100%">

                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding-bottom: 20px;">
                            <a href="{{ route('index') }}" target="_blank">
                                <img src="{{ image_url(config('images.default.logo'), 'images') }}" alt="Logo" width="180" style="display: block;">
                            </a>
                        </td>
                    </tr>

                    <!-- Sipariş Başlığı -->
                    <tr>
                        <td>
                            <div class="card">
                                <h2>{{ trans('translations.emails.order.siparis_no') }} #{{ $order->id }}</h2>
                                <p>
                                    {{ isset($order->plasiyer)
                                        ? trans('translations.emails.order.username_adina_plasiyername_adli_plasiyer_tarafindan_siparis_olusturulmustur_asagida_siparis_detaylari_bulunmaktadir',
                                            ['user_name' => $order->user->name, 'plasiyer_name' => $order->plasiyer->name])
                                        : trans('translations.emails.order.username_adli_bayi_tarafindan_siparis_olusturulmustur_asagida_siparis_detaylari_bulunmaktadir',
                                            ['user_name' => $order->user->name])
                                    }}
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Sipariş Ürünleri -->
                    <tr>
                        <td>
                            <div class="card">
                                <h3>{{ trans('translations.emails.order.siparis_detaylari') }}</h3>
                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr class="table-header">
                                        <td align="left" style="padding:8px;">{{ trans('translations.emails.order.urun') }}</td>
                                        <td align="right" style="padding:8px;">{{ trans('translations.emails.order.tutar') }}</td>
                                    </tr>
                                    @foreach ($order->orderProducts()->with('product')->get()->sortBy('product.code') as $key => $item)
                                        <tr style="border-bottom:1px solid #e9ecef;">
                                            <td style="padding:8px;">
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td width="70" valign="top">
                                                            <img src="{{ str_replace(' ', '%20', asset($item->product->image_small_url_1_raw)) }}" width="70" style="display:block; border:1px solid #ddd; border-radius:4px;">
                                                        </td>
                                                        <td valign="top" style="padding-left:12px;">
                                                            <p><strong>{{ $item->product_name_code }}</strong></p>
                                                            <p style="font-size:12px;">{{ trans('translations.emails.order.adet') }}: {{ $item->quantity }}</p>
                                                            @if ($item->discount)
                                                                <p style="font-size:12px;">{{ trans('translations.emails.order.indirim') }}: {{ $item->discount }}</p>
                                                            @endif
                                                            @if ($item->explanation)
                                                                <p style="font-size:12px;">{{ trans('translations.emails.order.aciklama') }}: {{ $item->explanation }}</p>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td align="right" style="padding:8px; white-space:nowrap;">{{ $item->formatted_total_price }}</td>
                                        </tr>
                                    @endforeach
                                </table>

                                <!-- Sepet Özeti -->
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top:16px;">
                                    <tr>
                                        <td style="padding:6px;">{{ trans('translations.emails.order.urunler') }}</td>
                                        <td align="right" style="padding:6px;">{{ $order->formatted_total_product_price }}</td>
                                    </tr>
                                    
                                    <tr>
                                        <td style="padding:6px;">{{ trans('translations.emails.order.toplam_urun') }}</td>
                                        <td align="right" style="padding:6px;">{{ $order->total_quantity . ' ' . trans('translations.emails.order.adet') }}</td>
                                    </tr>

                                    @if ($order->cart_discount_rate_1)
                                        <tr>
                                            <td style="padding:6px;">{{ trans('translations.emails.order.sepet_indirimi_1') }} (%{{ $order->cart_discount_rate_1 }})</td>
                                            <td align="right" style="padding:6px;">-{{ $order->formatted_cart_discount_1 }}</td>
                                        </tr>
                                    @endif
                                    
                                    <tr class="total-row">
                                        <td style="padding:8px;">{{ trans('translations.emails.order.toplam_tutar') }}</td>
                                        <td align="right" style="padding:8px;">{{ $order->total_amount }}</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    <!-- Sipariş Bilgileri -->
                    <tr>
                        <td>
                            <div class="card">
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="width:50%; vertical-align:top; padding-right:15px;">
                                            <h3>{{ trans('translations.emails.order.siparis_bilgisi') }}</h3>
                                            <p>{{ trans('translations.emails.order.siparis_no') }}: <strong>{{ $order->id }}</strong></p>
                                            <p>{{ trans('translations.emails.order.siparis_tarihi') }}: <strong>{{ $order->formatted_created_at }}</strong></p>
                                            <p>{{ trans('translations.emails.order.plasiyer') }}: <strong>{{ $order->salesman_name }}</strong></p>
                                            <p>{{ trans('translations.emails.order.bayi') }}: <strong>{{ $order->dealer_name }}</strong></p>
                                        </td>
                                        <td style="width:50%; vertical-align:top; padding-left:15px;">
                                            <h3>{{ trans('translations.emails.order.odeme_bilgisi') }}</h3>
                                            <p>{{ trans('translations.emails.order.odeme_plani') }}: <strong>{{ $order->payment_plan_name }}</strong></p>
                                            <p>{{ trans('translations.emails.order.odeme_turu') }}: <strong>{{ $order->payment_type_name }}</strong></p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    <!-- Açıklama -->
                    @if ($order->explanation)
                        <tr>
                            <td>
                                <div class="card">
                                    <h3>{{ trans('translations.emails.order.genel_aciklama') }}</h3>
                                    <p>{{ $order->explanation }}</p>
                                </div>
                            </td>
                        </tr>
                    @endif

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding:20px; color:#999999; font-size:12px;">
                            © {{ date('Y') }} {{ general_info('company_official_name') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
