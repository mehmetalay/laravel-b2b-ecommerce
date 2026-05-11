<?php

namespace App\Application\Order\Actions;

use App\Services\CartService;
use App\Services\CurrentAccountService;

class PreviewOrderAction
{
    public function handle(CartService $cartService, CurrentAccountService $currentAccountService)
    {
        try {
            $currentAccount = $currentAccountService->currentAccount();

            if (auth('web')->check() && auth('web')->user()->role === 'salesman' && $currentAccount == null) {
                return response()->json([
                    'status' => 'warning',
                    'message' => trans('translations.cart_controller.lutfen_bayi_seciniz')]
                );
            }

            if ($currentAccount && $currentAccount->is_order_closed) {
                return response()->json([
                    'status' => 'warning',
                    'message' => auth('subdealer')->check()
                        ? 'Hesabınızla ilgili sipariş verme işlemi kapatılmıştır.'
                        : ($currentAccount->closure_reason ?: 'Hesabınızla ilgili sipariş verme işlemi kapatılmıştır.')
                ]);
            }

            if (auth('subdealer')->check() && !auth('subdealer')->user()->can_place_order) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Sipariş verme yetkiniz bulunmamaktadır.'
                ]);
            }

            $carts = $cartService->carts();
            if (count($carts) === 0) {
                return response()->json([
                    'status' => 'warning',
                    'message' => trans('translations.order_controller.lutfen_siparis_vermek_icin_sepetinize_en_az_bir_urun_ekleyiniz')
                ]);
            }

            $validationResult = $cartService->validateCartAvailability();
            if ($validationResult['modified']) {
                return response()->json([
                    'status' => 'warning',
                    'message' => trans('translations.order_controller.yeni_guncellemeler_nedeniyle_sepetiniz_guncellendi_lutfen_sayfayi_yenileyerek_kontrol_ediniz'),
                    'reload' => true,
                    'warnings' => $validationResult['warnings'],
                ]);
            }

            if (
                auth('web')->check() && auth('web')->user()->role === 'salesman' &&
                additional_setting('payment_plan_selection') && additional_setting('payment_plan_required') &&
                blank(request('payment_plan_id'))
            ) {
                return response()->json([
                    'status' => 'warning',
                    'message' => trans('translations.order_controller.lutfen_odeme_plani_seciniz')
                ]);
            }

            if (
                auth('web')->check() && auth('web')->user()->role === 'salesman' &&
                additional_setting('payment_type_selection') && additional_setting('payment_type_required') &&
                blank(request('payment_type_id'))
            ) {
                return response()->json([
                    'status' => 'warning',
                    'message' => trans('translations.order_controller.lutfen_odeme_turu_seciniz')
                ]);
            }

            if (
                additional_setting('delivery_type_selection') &&
                additional_setting('delivery_type_required') &&
                blank(request('delivery_type'))
            ) {
                return response()->json([
                    'status' => 'warning',
                    'message' => trans('translations.order_controller.lutfen_teslimat_secenegini_seciniz')
                ]);
            }

            $deliveryType = request('delivery_type');

            switch ($deliveryType) {

                case 'Kargo':
                    if (blank(request('cargo_company_id'))) {
                        return response()->json([
                            'status' => 'warning',
                            'message' => 'Lütfen kargo firmasını seçiniz.'
                        ]);
                    }

                    if (blank(request('shipping_address_id'))) {
                        return response()->json([
                            'status' => 'warning',
                            'message' => 'Lütfen sevk adresini seçiniz.'
                        ]);
                    }
                    break;

                case 'Ambar':
                    if (blank(request('warehouse_name'))) {
                        return response()->json([
                            'status' => 'warning',
                            'message' => 'Lütfen ambar adını giriniz.'
                        ]);
                    }
                    break;

                case 'Depo Teslim':
                    if (blank(request('pickup_person'))) {
                        return response()->json([
                            'status' => 'warning',
                            'message' => 'Lütfen teslim alacak kişiyi giriniz.'
                        ]);
                    }
                    break;

                case 'Transit Sevk':
                    // Bilerek HİÇBİR şey zorunlu değil
                    break;
            }

            $rows = [];
            foreach ($carts as $cart) {
                $product = $cart->product;

                $qty = (int) $cart->quantity;

                $price = $cart->productPrice();
                $discount1 = $cart->effective_discount;
                $discount2 = $cart->campaign_discount_percent;
                $discount = $cart->effective_discount + $cart->campaign_discount_percent;

                $vatRate = $product->vat_rate;

                $netPrice = $price * (1 - ($discount1 / 100)) * (1 - ($discount2 / 100));
                $total = $netPrice * $qty;

                $decimal = additional_setting('decimal');

                $rows[] = [
                    'stock_code' => (string) $product->code,
                    'product_name' => (string) $product->product_name,
                    'quantity' => $qty,
                    'price' => number_format($price, $decimal),
                    'discount' => number_format($discount, $decimal),
                    'vat' => number_format($vatRate, $decimal),
                    'net_price' => number_format($netPrice, $decimal),
                    'total' => number_format($total, $decimal)
                ];
            }

            $token = uniqid();

            session(['order_preview_token' => $token]);

            return response()->json([
                'status' => 'success',
                'rows' => $rows,
                'token' => $token,
            ]);
        } catch (\Throwable $e) {
            logException($e, 'OrderController::preview', true);
            return response()->json([
                'status' => 'error',
                'message' => trans('translations.order_controller.istek_sirasinda_bir_hata_olustu_lutfen_site_yoneticisiyle_iletisime_gecin')
            ]);
        }
    }
}
