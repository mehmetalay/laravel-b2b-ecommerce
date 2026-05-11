@extends('layouts.app')

@section('content')
    <section class="cart-section section-small-space" data-js="cart-index-page">
        <div
            data-js="cart-index-config"
            data-is-order-confirmation="{{ additional_setting('is_order_confirmation') ? 1 : 0 }}"
            data-order-store-url="{{ route('orders.store') }}"
            data-order-preview-url="{{ route('orders.preview') }}"
            data-delete-all-url="{{ route('cart.delete.all') }}"
            data-general-discount-url="{{ route('cart.discount.general') }}"
            data-cancel-all-discounts-url="{{ route('cart.discount.all-cancel') }}"
            data-backup-cart-url="{{ route('cart.import') }}"
            data-import-cart-url="{{ route('cart.export') }}"
            data-apply-campaign-url="/sepet/campaign/apply"
            data-remove-all-campaigns-url="/sepet/campaign/remove"
            data-remove-single-campaign-url="/sepet/campaign/remove-single"
            data-free-product-modal-url="/sepet/campaign/free-product/modal"
            data-select-gifts-url="/sepet/campaign/free-product/select-gifts"
            data-add-same-product-gift-url="/sepet/campaign/free-product/add-same-product"
            data-update-price-url-template="/sepet/update/price/{id}"
            data-msg-confirm-title="{{ trans('translations.cart.emin_misiniz') }}"
            data-msg-delete-all-confirm="{{ trans('translations.cart.sepeti_bosaltmak_istediginizden_emin_misiniz') }}"
            data-msg-delete-product-confirm="{{ trans('translations.cart.urunu_sepetten_silmek_istediginizden_emin_misiniz') }}"
            data-msg-cancel-all-discounts-confirm="{{ trans('translations.cart.sepet_indirimini_iptal_etmek_istediginizden_emin_misiniz') }}"
            data-msg-confirm-delete-all="{{ trans('translations.cart.evet_bosalt') }}"
            data-msg-confirm-delete-product="{{ trans('translations.cart.evet_sil') }}"
            data-msg-confirm-cancel-discounts="{{ trans('translations.cart.evet_iptal_et') }}"
            data-msg-confirm-cancel="{{ trans('translations.cart.hayir') }}"
            data-msg-request-error="{{ trans('translations.cart.istek_sirasinda_bir_hata_olustu_lutfen_site_yoneticisiyle_iletisime_gecin') }}"
            data-msg-save-failed="{{ trans('translations.cart.islem_basarisiz') }}"
            data-msg-product-removed="Ürün sepetten silindi"
            data-msg-campaign-apply-failed="Kampanya uygulanamadı."
            data-msg-gift-select-save-failed="Hediye seçimi kaydedilemedi."
            data-msg-gift-count-required-template="Toplam {limit} adet hediye seçmelisiniz."
            hidden
        ></div>
        <div class="container-fluid">
            <div class="row g-sm-3 g-1">
                <div class="col-xxl-9" data-js="cart-list">
                    <div class="text-center">
                        <div class="spinner-border text-danger" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3" data-js="cart-summary">
                    <div class="text-center">
                        <div class="spinner-border text-danger" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script src="{{ mix('js/frontend/modules/order/delivery.js') }}"></script>
    <script src="{{ mix('js/frontend/modules/order/order.js') }}"></script>
    <script src="{{ mix('js/frontend/modules/cart/index.js') }}"></script>
@endsection
