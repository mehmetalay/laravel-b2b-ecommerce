<?php

namespace App\Application\Order;

use Illuminate\Http\JsonResponse;
use App\Services\CartService;
use App\Application\Order\DTO\OrderCreationContext;

class OrderValidator
{
    public function __construct(private CartService $cartService) {}

    public function validate(OrderCreationContext $context): ?JsonResponse
    {
        if (
            additional_setting('is_order_confirmation') &&
            session('order_preview_token') !== request('order_preview_token')
        ) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Lütfen siparişi tekrar onaylayınız.',
            ]);
        }

        if (auth('web')->check() && auth('web')->user()->role === 'salesman' && $context->currentAccount == null) {
            return response()->json([
                'status' => 'warning',
                'message' => trans('translations.cart_controller.lutfen_bayi_seciniz'),
            ]);
        }

        if ($context->currentAccount && $context->currentAccount->is_order_closed) {
            return response()->json([
                'status' => 'warning',
                'message' => auth('subdealer')->check()
                    ? 'Hesabınızla ilgili sipariş verme işlemi kapatılmıştır.'
                    : ($context->currentAccount->closure_reason ?: 'Hesabınızla ilgili sipariş verme işlemi kapatılmıştır.'),
            ]);
        }

        if (auth('subdealer')->check() && !auth('subdealer')->user()->can_place_order) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Sipariş verme yetkiniz bulunmamaktadır.',
            ]);
        }

        if (count($context->carts) === 0) {
            return response()->json([
                'status' => 'warning',
                'message' => trans('translations.order_controller.lutfen_siparis_vermek_icin_sepetinize_en_az_bir_urun_ekleyiniz'),
            ]);
        }

        $validationResult = $this->cartService->validateCartAvailability();
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
                'message' => trans('translations.order_controller.lutfen_odeme_plani_seciniz'),
            ]);
        }

        if (
            auth('web')->check() && auth('web')->user()->role === 'salesman' &&
            additional_setting('payment_type_selection') && additional_setting('payment_type_required') &&
            blank(request('payment_type_id'))
        ) {
            return response()->json([
                'status' => 'warning',
                'message' => trans('translations.order_controller.lutfen_odeme_turu_seciniz'),
            ]);
        }

        if (
            additional_setting('delivery_type_selection') &&
            additional_setting('delivery_type_required') &&
            blank(request('delivery_type'))
        ) {
            return response()->json([
                'status' => 'warning',
                'message' => trans('translations.order_controller.lutfen_teslimat_secenegini_seciniz'),
            ]);
        }

        switch ($context->deliveryType) {
            case 'Kargo':
                if (blank(request('cargo_company_id'))) {
                    return response()->json([
                        'status' => 'warning',
                        'message' => 'Lütfen kargo firmasını seçiniz.',
                    ]);
                }

                if (blank(request('shipping_address_id'))) {
                    return response()->json([
                        'status' => 'warning',
                        'message' => 'Lütfen sevk adresini seçiniz.',
                    ]);
                }
                break;

            case 'Ambar':
                if (blank(request('warehouse_name'))) {
                    return response()->json([
                        'status' => 'warning',
                        'message' => 'Lütfen ambar adını giriniz.',
                    ]);
                }
                break;

            case 'Depo Teslim':
                if (blank(request('pickup_person'))) {
                    return response()->json([
                        'status' => 'warning',
                        'message' => 'Lütfen teslim alacak kişiyi giriniz.',
                    ]);
                }
                break;

            case 'Transit Sevk':
                break;
        }

        return null;
    }
}
