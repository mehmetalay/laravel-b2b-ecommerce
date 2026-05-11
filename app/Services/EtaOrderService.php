<?php

namespace App\Services;

class EtaOrderService
{
    public function __construct(
        private EtaService $etaService,
        private OrderService $orderService
    ) {}

    public function sendOrder($orderId)
    {
        $lockService = null;
        $lockAcquired = false;

        try {
            $lockService = new BatchLockService('EtaOrderService::sendOrder:' . $orderId, 60);

            $lockAcquired = $lockService->acquire();
            if (! $lockAcquired) {
                return response('İşlem hala devam ediyor.', 409);
            }

            if (! $this->orderService->markAsProcessing($orderId)) {
                return;
            }

            logSession("OrderID: {$orderId} | ETA order send started", '', 'info', 'eta_order_logs');

            $order = $this->orderService->getFirst($orderId);

            $vatRate = 20;
            $totalProductPriceWithoutDiscount = $order->total_product_price;
            $totalDiscountAmount = $order->total_discount_amount;
            $subtotalAfterLineDiscount = $order->subtotal_after_line_discount;
            $totalVatAmount = $order->total_vat_amount;
            $totalPriceExclVat = $order->total_price_excl_vat;
            $grandDiscountTotal = $order->grand_discount_total;
            $totalCartDiscount = $order->cart_discount_1;

            $totals = [];

            $totals[] = [
                "SPTTAR" => $order->eta_created_at,
                "SPTREFNO" => 1006,
                "SPTTIPI" => 3,
                "SPTGCFLAG" => 2,
                "SPTKAYONC" => 1,
                "SPTAKFLAG" => 1,
                "SPTKAYNAK" => 17,
                "SPTONCELIK" => 0,
                "SPTIADEFLAG" => 0,
                "SPTKDVDAHILFLAG" => 0,
                "SPTCARKOD" => $order->eta_account_code,
                "SPTEVRAKNO1" => $order->eta_sp_number,
                "SPTEVRAKNO2" => $order->eta_order_number,
                "SPTEVRAKNO3" => $order->eta_document_number,
                "SPTKONU" => 1,
                "SPTBASLIK" => "Kal.İnd.1 (%)",
                "SPTTUTAR" => $grandDiscountTotal,
                "SPTMATRAH" => $grandDiscountTotal,
                "SPTORAN" => 0,
                "SPTKDVORAN" => 0,
                "SPTFORMUL" => "",
                "SPTDOVKOD" => $order->eta_currency_code,
                "SPTDOVTUR" => $order->eta_currency_type,
                "SPTDOVTUTAR" => 0,
                "SPTVADETAR" => $order->eta_empty_date,
                "SPTTAMSAYI1" => 0,
                "SPTTAMSAYI2" => 0,
                "SPTTAMSAYI3" => 0,
                "SPTKOSUL" => "",
                "SPTACIKLAMA1" => "",
                "SPTSAYI1" => 0,
                "SPTVADESAYI" => 0,
                "SPTKARTKOD" => "",
                "SPTEFATFLAG" => 0,
            ];

            $totalBrutBeforeVat = $totalProductPriceWithoutDiscount - $totalDiscountAmount;

            // KDV
            $totals[] = [
                "SPTTAR" => $order->eta_created_at,
                "SPTREFNO" => 1006,
                "SPTTIPI" => 3,
                "SPTGCFLAG" => 2,
                "SPTKAYONC" => 1,
                "SPTAKFLAG" => 1,
                "SPTKAYNAK" => 17,
                "SPTONCELIK" => 0,
                "SPTIADEFLAG" => 0,
                "SPTKDVDAHILFLAG" => 0,
                "SPTCARKOD" => $order->eta_account_code,
                "SPTEVRAKNO1" => $order->eta_sp_number,
                "SPTEVRAKNO2" => $order->eta_order_number,
                "SPTEVRAKNO3" => $order->eta_document_number,
                "SPTKONU" => 101,
                "SPTBASLIK" => "KDV(%20)",
                "SPTTUTAR" => $totalVatAmount,
                "SPTMATRAH" => $subtotalAfterLineDiscount,
                "SPTORAN" => 0,
                "SPTKDVORAN" => 20,
                "SPTFORMUL" => "",
                "SPTDOVKOD" => $order->eta_currency_code,
                "SPTDOVTUR" => $order->eta_currency_type,
                "SPTDOVTUTAR" => "0",
                "SPTVADETAR" => $order->eta_empty_date,
                "SPTTAMSAYI1" => 0,
                "SPTTAMSAYI2" => 0,
                "SPTTAMSAYI3" => 0,
                "SPTKOSUL" => "",
                "SPTACIKLAMA1" => "",
                "SPTSAYI1" => 0,
                "SPTVADESAYI" => 0,
                "SPTKARTKOD" => "",
                "SPTEFATFLAG" => 0,
            ];

            if ($order->cart_discount_1 > 0) {
                $totals[] = [
                    "SPTTAR" => $order->eta_created_at,
                    "SPTREFNO" => 1006,
                    "SPTTIPI" => 3,
                    "SPTGCFLAG" => 2,
                    "SPTKAYONC" => 1,
                    "SPTAKFLAG" => 1,
                    "SPTKAYNAK" => 17,
                    "SPTONCELIK" => 0,
                    "SPTIADEFLAG" => 0,
                    "SPTKDVDAHILFLAG" => 0,
                    "SPTCARKOD" => $order->eta_account_code,
                    "SPTEVRAKNO1" => $order->eta_sp_number,
                    "SPTEVRAKNO2" => $order->eta_order_number,
                    "SPTEVRAKNO3" => $order->eta_document_number,
                    "SPTKONU" => 31,
                    "SPTBASLIK" => "Genel İndirim 1",
                    "SPTTUTAR" => $order->cart_discount_1,
                    "SPTMATRAH" => $totalBrutBeforeVat,
                    "SPTORAN" => $order->cart_discount_rate_1,
                    "SPTKDVORAN" => 0,
                    "SPTFORMUL" => "FISF45-FISF41",
                    "SPTDOVKOD" => $order->eta_currency_code,
                    "SPTDOVTUR" => $order->eta_currency_type,
                    "SPTDOVTUTAR" => 0,
                    "SPTVADETAR" => $order->eta_empty_date,
                    "SPTTAMSAYI1" => 0,
                    "SPTTAMSAYI2" => 0,
                    "SPTTAMSAYI3" => 0,
                    "SPTKOSUL" => "FISFGI012>0",
                    "SPTACIKLAMA1" => "",
                    "SPTSAYI1" => 0,
                    "SPTVADESAYI" => 0,
                    "SPTKARTKOD" => "",
                    "SPTEFATFLAG" => 0,
                ];
            }

            $movements = $order->orderProducts->map(function ($item, $index) use ($order, $totalProductPriceWithoutDiscount, $totalCartDiscount) {
                $product = $item->product;

                $price = $item->price;
                $quantity = $item->quantity;
                $vatRate = $product->vat_rate;

                // 🔹 İndirim oranları
                $productDiscountRate = $item->discount; // ürün indirimi %
                $campaignDiscountRate = $item->campaign_discount_percent; // kampanya %

                // 🔹 Brüt satır
                $lineGross = $price * $quantity;

                // 🔹 Ürün + kampanya indirim tutarları
                $productDiscountAmount = $lineGross * ($productDiscountRate / 100);
                $campaignDiscountAmount = $lineGross * ($campaignDiscountRate / 100);

                // 🔹 Sepet indirimi satır payı
                $lineCartDiscount1 = 0;
                if ($totalCartDiscount > 0 && $totalProductPriceWithoutDiscount > 0) {
                    $lineCartDiscount1 = ($lineGross / $totalProductPriceWithoutDiscount) * $totalCartDiscount;
                }

                // 🔹 Toplam indirim
                $totalLineDiscount = $productDiscountAmount + $campaignDiscountAmount + $lineCartDiscount1;

                // 🔹 Nihai net (KDV matrahı)
                $lineNet = $lineGross - $totalLineDiscount;

                // 🔹 KDV
                $vatAmount = $lineNet * ($vatRate / 100);

                // 🔹 KDV dahil
                $lineTotal = $lineNet + $vatAmount;

                $lineDescription = $item->eta_description_3;

                return [
                    "SIPHARTAR" => $order->eta_created_at,
                    "SIPHARREFNO" => 1006,
                    "SIPHARTIPI" => 3,
                    "SIPHARGCFLAG" => 2,
                    "SIPHARAKFLAG" => 1,
                    "SIPHARKAYONC" => 1,
                    "SIPHARKAYNAK" => 17,
                    "SIPHARCARKOD" => $order->eta_account_code,
                    "SIPHAREVRAKNO" => "",
                    "SIPHARSIRANO" => $index + 1,
                    "SIPHARKODTIP" => 1,
                    "SIPHARSTKKOD" => $product->code,// ürün kodu
                    "SIPHARSTKCINS" => $product->name,// ürün adı
                    "SIPHARSTKBRM" => $product->unit_name_1 ?? '',// birim adı
                    "SIPHARDEPOKOD" => "",
                    "SIPHARBARKOD" => $product->barcode ?? '',// ürün barkodu
                    "SIPHAROZDESKOD" => "",
                    "SIPHAROZKOD" => "",
                    "SIPHARBENZERKOD" => "",
                    "SIPHARMIKTAR" => $quantity,// adet
                    "SIPHARMIKTAR2" => 0,
                    "SIPHARMIKTAR3" => 0,
                    "SIPHARMIKTAR4" => 0,
                    "SIPHARMIKTAR5" => 0,
                    "SIPHARFIYTIP" => "",
                    "SIPHARDOVFIYAT" => 0,

                    "SIPHARFIYAT" => $price, // ürün fiyatı
                    "SIPHARTUTAR" => $lineGross, // indirimsiz tutar
                    "SIPHARKDVYUZ" => $vatRate, // kdv oranı

                    "SIPHARISKYUZ1" => $productDiscountRate, // satır iskontosu
                    "SIPHARISKYUZ2" => $campaignDiscountRate, // kampanya oranı

                    "SIPHARISKYUZ3" => 0,
                    "SIPHARISKYUZ4" => 0,
                    "SIPHARISKYUZ5" => 0,

                    "SIPHARISKYTUT1" => $productDiscountAmount, // satır indirim tutarı
                    "SIPHARISKYTUT2" => $campaignDiscountAmount, // kampanya indirimi

                    "SIPHARISKYTUT3" => 0,
                    "SIPHARISKYTUT4" => 0,
                    "SIPHARISKYTUT5" => 0,
                    "SIPHARISKGTUT1" => 0,
                    "SIPHARISKGTUT2" => 0,
                    "SIPHARISKGTUT3" => 0,
                    "SIPHARISKGTUT4" => 0,
                    "SIPHARISKGTUT5" => 0,

                    "SIPHARDIGERIND" => $lineCartDiscount1, // sepet indirim tutarı
                    "SIPHARTOPLAMIND" => $totalLineDiscount,

                    "SIPHARKDVMATRAH" => $lineNet, // kdv hariç ara toplam
                    "SIPHARKDVTUTAR" => $vatAmount,  // ara toplam kdv tutarı
                    "SIPHARTOPLAMTUT" => $lineTotal, // kdv dahil ara toplam

                    "SIPHARVADETAR" => $order->eta_empty_date,
                    "SIPHARACIKLAMA" => $lineDescription ?? '',
                    "SIPHARACIKLAMA1" => "",
                    "SIPHARACIKLAMA2" => "",
                    "SIPHARACIKLAMA3" => $lineDescription ?? "",
                    "SIPHARPARTINO" => "",
                    "SIPHARMASMER" => "",
                    "SIPHARSERINO1" => "",
                    "SIPHARSERINO2" => "",
                    "SIPHARRB1" => 0,
                    "SIPHARRB2" => 0,
                    "SIPHARRB3" => 0,
                    "SIPHARRB4" => 0,
                    "SIPHARRB5" => 0,
                    "SIPHARSATKOD" => $order->salesman_code,
                    "SIPHARODEMEKOD" => "",
                    "SIPHARTOPLAMMAS" => 0,
                    "SIPHARNETTUTAR" => $lineGross - $productDiscountAmount - $campaignDiscountAmount - $lineCartDiscount1, // ara toplam
                    "SIPHARNETFIYAT" => $price * (1 - $productDiscountRate / 100) * (1 - $campaignDiscountRate / 100) * (1 - $order->cart_discount_rate_1 / 100), // net fiyat
                    "SIPHARTERMINTAR" => $order->eta_empty_date,
                    "SIPHARREZERVFLAG" => 1,
                    "SIPHARKARSILAFLAG" => 1,
                    "SIPHARTAKIPNO" => $order->eta_st_number,
                    "SIPHARTESFLAG" => 0,
                    "SIPHARTESSONTAR" => $order->eta_empty_date,
                    "SIPHARTESMIKTAR" => 0,
                    "SIPHARTESMIKTAR2" => 0,
                    "SIPHARTESMIKTAR3" => 0,
                    "SIPHARTESMIKTAR4" => 0,
                    "SIPHARTESMIKTAR5" => 0,
                    "SIPHARKALMIKTAR" => $quantity,//adet
                    "SIPHARKALMIKTAR2" => 0,
                    "SIPHARKALMIKTAR3" => 0,
                    "SIPHARKALMIKTAR4" => 0,
                    "SIPHARKALMIKTAR5" => 0,
                    "SIPHAROTVMATRAH" => $lineNet, // ara toplam
                    "SIPHAROTVORAN" => 0,
                    "SIPHAROTVFIYAT" => 0,
                    "SIPHARTOPOTV" => 0,
                    "SIPHAROTVTUTAR" => $lineNet, // ara toplam
                    "SIPHAREBTEN" => 0,
                    "SIPHAREBTBOY" => 0,
                    "SIPHAREBTYUK" => 0,
                    "SIPHAREBTHCM" => 0,
                    "SIPHAREBTAGR" => 0,
                    "SIPHAREKCHAR1" => "",
                    "SIPHAREKCHAR2" => "",
                    "SIPHAREKINT1" => 0,
                    "SIPHAREKINT2" => 0,
                    "SIPHAREKDATE1" => $order->eta_empty_date,
                    "SIPHAREKDATE2" => $order->eta_empty_date,
                    "SIPHAREKTUT1" => 0,
                    "SIPHAREKTUT2" => 0,
                    "SIPHAREKMIK1" => 0,
                    "SIPHAREKMIK2" => 0,
                    "SIPHAREKDOVTUT1" => 0,
                    "SIPHAREKDOVTUT2" => 0,
                    "SIPHAREKORAN1" => 0,
                    "SIPHAREKORAN2" => 0,
                    "SIPHARDOVKUR" => $order->currency !== 'TL' ? $order->eta_exchange_rate : 0.00,
                    "SIPHAREFATFLAG" => 0,
                    "SIPHAROTVVERKOD" => "",
                    "SIPHAREKVERGI" => "",
                    "SIPHARDOVKOD" => $order->eta_currency_code,
                    "SIPHARDOVTUR" => $order->eta_currency_type,
                    "SIPHARDOVTUTAR" => 0,
                    "SIPHARDISTIP" => 0,
                    "SIPHARDISKOD" => "",
                ];
            })->toArray();

            $payload = [
                'RefNo' => [
                    "HARREFMODUL" => 17,
                    "HARREFKONU" => 1,
                    "HARREFDEGER" => 1006
                ],
                'Fis' => [
                    "SIPFISTAR" => $order->eta_created_at,
                    "SIPFISREFNO" => 1006,
                    "SIPFISTIPI" => 3,
                    "SIPFISGCFLAG" => 2,
                    "SIPFISAKFLAG" => 1,
                    "SIPFISKAYONC" => 1,
                    "SIPFISKAYNAK" => 17,
                    "SIPFISIRSREFNO" => 0,
                    "SIPFISIRSTAR" => $order->eta_empty_date,
                    "SIPFISIRSFLAG" => 0,
                    "SIPFISFATREFNO" => 0,
                    "SIPFISFATTAR" => $order->eta_empty_date,
                    "SIPFISFATFLAG" => 0,
                    "SIPFISREZERVFLAG" => 1,
                    "SIPFISKARSILAFLAG" => 1,
                    "SIPFISKESINFLAG" => 1,
                    "SIPFISBASFLAG" => 0,
                    "SIPFISKDVDAHILFLAG" => 0,
                    "SIPFISTERMINTAR" => $order->eta_empty_date,
                    "SIPFISTERMINSAAT" => "",
                    "SIPFISGECERTAR" => $order->eta_empty_date,
                    "SIPFISGECERSAAT" => $order->eta_created_time,
                    "SIPFISKESINTAR" => $order->eta_empty_date,
                    "SIPFISKESINSAAT" => "",
                    "SIPFISTESLIMTAR" => $order->eta_empty_date,
                    "SIPFISTESLIMFLAG" => 0,
                    "SIPFISVADETAR" => $order->eta_empty_date,
                    "SIPFISANADEPO" => "",
                    "SIPFISPARTIKOD" => "PRT001",
                    "SIPFISODEMEKOD" => $order->eta_payment_type,
                    "SIPFISSATKOD" => $order->salesman_code,
                    "SIPFISMASMER" => "MASMER",
                    "SIPFISCARKOD" => $order->eta_account_code,
                    "SIPFISCARUNVAN" => $order->eta_account_name,
                    "SIPFISEVRAKNO1" => "",
                    "SIPFISEVRAKNO2" => $order->eta_order_number,
                    "SIPFISEVRAKNO3" => $order->eta_document_number,
                    "SIPFISOZKOD1" => $order->eta_payment_type,
                    "SIPFISOZKOD2" => "B2B",
                    "SIPFISOZKOD3" => "OZK003",
                    "SIPFISACIKLAMA1" => $order->eta_description1,
                    "SIPFISACIKLAMA2" => $order->eta_description2,
                    "SIPFISACIKLAMA3" => $order->eta_description3,
                    "SIPFISHAZKOD" => "ADM001",
                    "SIPFISHAZTAR" => $order->eta_empty_date,
                    "SIPFISHAZNOT" => "Sistem oluşturdu",
                    "SIPFISKONTKOD" => "KNT001",
                    "SIPFISKONTTAR" => $order->eta_empty_date,
                    "SIPFISKONTNOT" => "Kontrol edildi",
                    "SIPFISONAYKOD" => "ONAY001",
                    "SIPFISONAYTAR" => $order->eta_empty_date,
                    "SIPFISONAYNOT" => "Onaylandı",
                    "SIPFISKDVORANI" => $vatRate,
                    "SIPFISKDVVADETAR" => $order->eta_empty_date,
                    "SIPFISMALTOP" => $totalProductPriceWithoutDiscount,
                    "SIPFISKALINDYTOP" => $totalDiscountAmount,
                    "SIPFISKALINDTTOP" => 0,
                    "SIPFISSATINDTOP" => 0,
                    "SIPFISGENINDTOP" => $order->cart_discount_1,
                    "SIPFISSATMASTOP" => 0,
                    "SIPFISGENMASTOP" => 0,
                    "SIPFISBRUTTOPLAM" => $subtotalAfterLineDiscount,
                    "SIPFISKDVMATRAHI" => $subtotalAfterLineDiscount,
                    "SIPFISKDVTUTARI" => $totalVatAmount,
                    "SIPFISARATOPLAM" => $totalPriceExclVat,
                    "SIPFISKDVALTIINDTOP" => 0,
                    "SIPFISKDVALTIEKTOP" => 0,
                    "SIPFISGENTOPLAM" => $totalPriceExclVat,
                    "SIPFISDOVTAR" => $order->eta_empty_date,
                    "SIPFISDOVKOD" => $order->eta_currency_code,
                    "SIPFISDOVTUR" => $order->eta_currency_type,
                    "SIPFISDOVKUR" => $order->currency !== 'TL' ? 1 : 0,
                    "SIPFISGENDOVTOP" => 0,
                    "SIPFISSEVNO" => 1,
                    "SIPHARDOVKUR" => $order->currency !== 'TL' ? $order->eta_exchange_rate : 0.00,
                    "SIPFISISYKOD" => "",
                    "SIPFISOTVFLAG" => 0,
                    "SIPFISOTVKDVBLOKAJ" => 0,
                    "SIPFISTOPOTV" => 0,
                    "SIPFISTOPOTUT" => $subtotalAfterLineDiscount,
                    "SIPFISADRESNO" => 1,
                    "SIPFISADRES1" => $order->eta_adress1 ?? '',
                    "SIPFISADRES2" => $order->eta_adress2 ?? '',
                    "SIPFISADRES3" => $order->eta_adress3 ?? '',
                    "SIPFISPOSTAKOD" => $order->eta_postal_code,
                    "SIPFISULKE" => "TÜRKİYE",
                    "SIPFISIL" => $order->eta_province ?? '',
                    "SIPFISILCE" => $order->eta_district ?? '',
                    "SIPFISVERDAIRE" => $order->eta_tax_office ?? '',
                    "SIPFISVERHESNO" => $order->eta_tax_number ?? '',
                    "SIPFISKAPALIFLAG" => 0,
                    "SIPFISTCKIMLIKNO" => $order->eta_identity_number ?? '',
                    "SIPFISTRNTAR" => $order->eta_empty_date,
                    "SIPFISTRNREFNO" => 0,
                    "SIPFISTRNTIPI" => 0,
                    "SIPFISEFATFLAG" => 0,
                    "SIPFISEKVERGIINDTOP" => 0,
                    "SIPFISEKVERGIILVTOP" => 0,
                    "SIPFISEKVERGITOP" => 0,
                    "SIPFISDISTIP" => 0,
                    "SIPFISDISKOD" => "",
                ],
                'Hareketler' => $movements,
                'Toplamlar' => $totals,
            ];

            logSession("OrderID: {$orderId} | ETA order params", [
                'payload' => $payload,
            ], 'info', 'eta_order_logs');

            $response = $this->etaService->importOrder($payload);

            if (
                is_array($response) &&
                data_get($response, 'evrakNo')
            ) {
                $this->orderService->markAsSent($orderId, $response['evrakNo']);

                logSession("OrderID: {$orderId} | Eta API response", ['response' => $response], 'info', 'eta_order_logs');
            } else {
                logSession("OrderID: {$orderId} | Eta API response", ['response' => $response], 'error', 'eta_order_logs');
                $this->orderService->markAsFailed($orderId, 'Eta API response is not valid');
            }

        } catch (\Throwable $e) {
            logSession('Eta API exception', ['error' => $e->getMessage()], 'error', 'eta_order_logs');
            $this->orderService->markAsFailed($orderId, $e->getMessage());
            return;
        } finally {
            if ($lockService && $lockAcquired) {
                $lockService->release();
            }
        }
    }
}

?>
