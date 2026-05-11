{{-- bayi seçimi modalı --}}
<div class="modal current-account-modal fade theme-modal" data-component="current-account" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('translations.modal.bayiler') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="current-accounts-list">
                    <div class="search-input">
                        <input type="search" class="form-control" data-target="current-account-search" placeholder="{{ trans('translations.modal.bayi_ara') }}">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <div class="disabled-box">
                        <h6>{{ trans('translations.modal.bir_bayi_secin') }}</h6>
                    </div>
                    <ul class="current-accounts-select custom-height" data-target="current-account-list"></ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentTypeModal" data-js="payment-type-modal" tabindex="-1" aria-labelledby="paymentTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="paymentTypeModalLabel">Ödeme Tipi Seçiniz</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="text-muted mb-4">Lütfen sepete eklemeden önce bir ödeme tipi seçiniz:</p>

                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    @if ($currentAccountService->currentAccount())
                        @php
                            $methods = $currentAccountService->currentAccount() ? explode(',', $currentAccountService->currentAccount()->allowed_payment_methods) : [];
                        @endphp
                        @if (in_array('cash', $methods))
                            <button data-js="select-payment-type" data-payment-type="cash" class="btn btn-success text-white btn-lg px-4 py-2 shadow-sm">Nakit</button>
                        @endif
                        @if (in_array('credit', $methods))
                            <button data-js="select-payment-type" data-payment-type="credit" class="btn btn-warning text-white btn-lg px-4 py-2 shadow-sm">Kredi Kartı</button>
                        @endif
                        @if (in_array('term', $methods))
                            <button data-js="select-payment-type" data-payment-type="term" class="btn btn-danger text-white btn-lg px-4 py-2 shadow-sm">Vadeli</button>
                        @endif
                    @else
                        Önce bayi seçiniz
                    @endif
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button class="btn btn-animation dark-button btn-sm" data-bs-dismiss="modal"><i class="flaticon-cancel-12"></i> {{ trans('translations.modal.iptal') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- sepet modalları --}}
@if (Route::is('cart.*'))
    <div class="modal fade theme-modal" data-modal="submit-order" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('translations.modal.siparis_gonder') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form
                        data-form="submit-order"
                        data-order-store-url="{{ route('orders.store') }}"
                        data-order-preview-url="{{ route('orders.preview') }}"
                        data-is-order-confirmation="{{ additional_setting('is_order_confirmation') ? 1 : 0 }}"
                    >
                        @csrf
                        <div class="row g-2">

                            @if (auth('web')->check() && auth('web')->user()->role === 'salesman')

                                @if (additional_setting('payment_plan_selection', false))
                                    <div class="col-12">
                                        <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                                            <select class="form-select theme-form-select" name="payment_plan_id" id="payment_plan_id">
                                                <option value="" selected hidden>{{ trans('translations.modal.sec') }}</option>
                                                @foreach ($paymentPlans as $paymentPlan)
                                                    <option value="{{ $paymentPlan->row_id }}" {{ isset($currentAccountService->currentAccount()->payment_reference) && $currentAccountService->currentAccount()->payment_reference == $paymentPlan->row_id ? 'selected' : '' }}>{{ $paymentPlan->definition }}</option>
                                                @endforeach
                                            </select>
                                            <label for="payment_plan_id">{{ trans('translations.modal.odeme_plani') . (additional_setting('payment_plan_required', true) ? ' *' : '') }}</label>
                                        </div>
                                    </div>
                                @endif

                                @if (additional_setting('payment_type_selection', false))
                                    <div class="col-12">
                                        <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                                            <select class="form-select theme-form-select" name="payment_type_id" id="payment_type_id">
                                                <option value="" selected hidden>{{ trans('translations.modal.sec') }}</option>
                                                @foreach ($paymentTypes as $paymentType)
                                                    <option value="{{ $paymentType->row_id }}" {{ isset($currentAccountService->currentAccount()->payment_type) && $currentAccountService->currentAccount()->payment_type == $paymentType->row_id ? 'selected' : '' }}>{{ $paymentType->name }}</option>
                                                @endforeach
                                            </select>
                                            <label for="payment_type_id">{{ trans('translations.modal.odeme_turu') . (additional_setting('payment_type_required', true) ? ' *' : '') }}</label>
                                        </div>
                                    </div>
                                @endif

                            @endif

                            <div class="col-12">
                                <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                                    <select class="form-select theme-form-select" name="delivery_type" id="delivery_type">
                                        <option value="" selected hidden>{{ trans('translations.modal.sec') }}</option>
                                        <option value="Kargo">Kargo</option>
                                        <option value="Ambar">Ambar</option>
                                        <option value="Depo Teslim">Depo Teslim</option>
                                        <option value="Transit Sevk">Transit Sevk</option>
                                    </select>
                                    <label for="delivery_type">{{ trans('translations.modal.teslimat_sekli') . (additional_setting('delivery_type_required', true) ? ' *' : '') }}</label>
                                </div>
                            </div>

                            <div class="col-12 d-none" data-delivery-field="cargo">
                                <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                                    <select class="form-select theme-form-select" name="cargo_company_id" id="cargo_company_id" disabled>
                                        <option value="" selected hidden>{{ trans('translations.modal.sec') }}</option>
                                        @foreach ($cargoCompanies as $cargoCompany)
                                            <option value="{{ $cargoCompany->id }}">{{ $cargoCompany->name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="cargo_company_id">Kargo Firması *</label>
                                </div>
                            </div>

                            <div class="col-12 d-none" data-delivery-field="ambar">
                                <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                                    <input type="text" class="form-control" name="warehouse_name" id="warehouse_name" placeholder="Ambar adı" disabled>
                                    <label for="warehouse_name">Ambar Adı *</label>
                                </div>
                            </div>

                            <div class="col-12 d-none" data-delivery-field="depo">
                                <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                                    <input type="text" class="form-control" name="pickup_person" id="pickup_person" placeholder="Teslim alacak kişi" disabled>
                                    <label for="pickup_person">Teslim Alacak Kişi *</label>
                                </div>
                            </div>

                            <div class="col-12 d-none" data-delivery-field="transit">
                                <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                                    <input type="text" class="form-control" name="transit_note" id="transit_note" placeholder="Şube / yönlendirme notu" disabled>
                                    <label for="transit_note">Transit Sevk Bilgisi *</label>
                                </div>
                            </div>

                            <div id="shipping-address-wrapper" data-js="shipping-address-wrapper" class="d-none">
                                <div class="form-floating mb-2 theme-form-floating">
                                    <select class="form-select theme-form-select" name="shipping_address_id" id="shipping_address_id" data-js="shipping-address-id">
                                        <option value="" hidden disabled selected>Seç</option>
                                    </select>
                                    <label>Sevk Adresi *</label>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="btn-group d-none" id="address-action-buttons" data-js="address-action-buttons">
                                        <button class="btn btn-sm btn-secondary text-white" data-action="edit-address">
                                            Düzenle
                                        </button>
                                        <button class="btn btn-sm btn-danger text-white" data-action="delete-address">
                                            Sil
                                        </button>
                                    </div>

                                    <a href="javascript:;" class="btn btn-animation btn-sm" data-action="add-address">
                                        + Yeni Adres Ekle
                                    </a>
                                </div>

                                <div class="border rounded p-2 mb-2 mt-2 small text-muted d-none" id="address-preview" data-js="address-preview"></div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                                    <textarea class="form-control" name="explanation" id="explanation" rows="4"></textarea>
                                    <label for="explanation">{{ trans('translations.modal.aciklama') }}</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check ps-0 mb-4 remember-box">
                                    <input class="checkbox_animated check-box" type="checkbox" name="send_email" id="send_email" checked>
                                    <label class="form-check-label" for="send_email">{{ trans('translations.modal.e_posta_gonder') }}</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check ps-0 mb-4 remember-box">
                                    <input class="checkbox_animated check-box" type="checkbox" name="send_sms" id="send_sms" checked>
                                    <label class="form-check-label" for="send_sms">{{ trans('translations.modal.sms_gonder') }}</label>
                                </div>
                            </div>

                        </div>
                        <input type="hidden" name="order_preview_token">
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-animation dark-button btn-sm" data-bs-dismiss="modal"><i class="flaticon-cancel-12"></i> {{ trans('translations.modal.iptal') }}</button>
                    <a href="javascript:;" class="btn btn-animation btn-sm" data-js="submit-order" data-action="submit-order" data-form-target="submit-order" data-modal-close="submit-order">{{ trans('translations.modal.gonder') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade theme-modal" data-modal="order-confirmation" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sipariş Onayı</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Stok Kodu</th>
                                    <th>Ürün Adı</th>
                                    <th class="text-center">Miktar</th>
                                    <th class="text-center">Fiyat</th>
                                    <th class="text-center">İndirim</th>
                                    <th class="text-center">KDV</th>
                                    <th class="text-center">Net Fiyat</th>
                                    <th class="text-center">Tutar</th>
                                </tr>
                            </thead>
                            <tbody data-target="order-confirmation-tbody"></tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-animation dark-button btn-sm" data-bs-dismiss="modal">
                        <i class="flaticon-cancel-12"></i> {{ trans('translations.modal.iptal') }}
                    </button>

                    <a href="javascript:;" class="btn btn-animation btn-sm" data-action="confirm-submit-order" data-form-target="submit-order" data-modal-close="order-confirmation">
                        Onayla ve Gönder
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade theme-modal cart-export-modal" tabindex="-1" aria-labelledby="cart-export-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cart-export-modal-label">{{ trans('translations.modal.sepeti_kaydet') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                                <input type="text" class="form-control" id="cart_name" name="cart_name">
                                <label>{{ trans('translations.modal.sepet_adi') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-animation dark-button btn-sm" data-bs-dismiss="modal"><i class="flaticon-cancel-12"></i> {{ trans('translations.modal.iptal') }}</button>
                    <a href="javascript:;" class="btn btn-animation btn-sm" data-js="backup-the-cart">{{ trans('translations.modal.kaydet') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade theme-modal import-backed-up-cart" tabindex="-1" aria-labelledby="import-backed-up-cart-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="import-backed-up-cart-label">{{ trans('translations.modal.kaydedilen_sepeti_yukle') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="col-12">
                        <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                            <select class="form-select theme-form-select" name="backed_up_cart_id" id="backed_up_cart_id">
                                <option value="" selected hidden>{{ trans('translations.modal.sec') }}</option>
                                @foreach ($backedUpCarts as $backedUpCart)
                                    <option value="{{ $backedUpCart->id }}">{{ $backedUpCart->name . ' - ' . format_date_time($backedUpCart->created_at) }}</option>
                                @endforeach
                            </select>
                            <label for="backed_up_cart_id">{{ trans('translations.modal.sepet_adi') }}</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-animation dark-button btn-sm" data-bs-dismiss="modal"><i class="flaticon-cancel-12"></i> {{ trans('translations.modal.iptal') }}</button>
                    <a href="javascript:;" class="btn btn-animation btn-sm" data-js="import-cart">{{ trans('translations.modal.yukle') }}</a>
                </div>
            </div>
        </div>
    </div>

    @if (auth('web')->check() && auth('web')->user()->role === 'salesman')
        <div class="modal fade theme-modal" data-selector="edit-price-modal" tabindex="-1" aria-labelledby="edit-price-modal-label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="edit-price-modal-label">{{ trans('translations.modal.fiyati_ayarla') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form data-selector="edit-price-form">
                            <input type="hidden" data-selector="row-id">

                            <div class="mb-3">
                            <label class="form-label">{{ trans('translations.modal.liste_fiyati') }}</label>
                            <input type="text" class="form-control" data-selector="list-price" readonly>
                            </div>

                            <div class="mb-3">
                            <label class="form-label">{{ trans('translations.modal.indirim') }}</label>
                            <input type="number" step="0.01" class="form-control" data-selector="discount-rate" {{ auth('web')->user()->can_edit_discount ? '' : 'readonly' }}>
                            </div>

                            <div class="mb-3">
                            <label class="form-label">{{ trans('translations.modal.net_fiyat') }}</label>
                            <input type="number" step="0.01" class="form-control" data-selector="net-price" {{ auth('web')->user()->can_edit_price ? '' : 'readonly' }}>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-animation dark-button btn-sm" data-bs-dismiss="modal"><i class="flaticon-cancel-12"></i> {{ trans('translations.modal.iptal') }}</button>
                        <a href="javascript:;" class="btn btn-animation btn-sm" data-selector="save-price-btn">{{ trans('translations.modal.kaydet') }}</a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div data-js="cart-campaign-modal">
        @include('carts.partials.campaign-modal')
    </div>
@endif

{{-- adres modalı --}}
@if (Route::is('cart.index') || Route::is('addresses.index'))
    <div class="modal fade theme-modal" data-modal="address-form" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="address-modal-title">Yeni Adres</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <form data-form="address-form">
                        @csrf
                        <input type="hidden" name="id" id="address_id">

                        <div class="row g-2">
                            <div class="col-6">
                                <input class="form-control" name="title" placeholder="Adres Başlığı *">
                            </div>
                            <div class="col-6">
                                <input class="form-control" name="company_name" placeholder="Firma Ünvanı / Ad Soyad *">
                            </div>

                            <div class="col-6">
                                <input class="form-control" name="tax_office" placeholder="Vergi Dairesi">
                            </div>
                            <div class="col-6">
                                <input class="form-control" name="tax_number" placeholder="Vergi No">
                            </div>

                            <div class="col-4">
                                <select class="form-select" name="city_id" id="city_id" data-js="city-id"></select>
                            </div>
                            <div class="col-4">
                                <select class="form-select" name="district_id" id="district_id" data-js="district-id"></select>
                            </div>
                            <div class="col-4">
                                <select class="form-select" name="neighborhood_id" id="neighborhood_id" data-js="neighborhood-id"></select>
                            </div>

                            <div class="col-12">
                                <textarea class="form-control" name="address" placeholder="Açık adres *"></textarea>
                            </div>

                            <div class="col-6">
                                <input class="form-control" name="phone" placeholder="Telefon" data-mask-phone>
                            </div>

                            <div class="col-6">
                                <label>
                                    <input type="checkbox" name="is_default"> Varsayılan adres
                                </label>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <a href="javascript:;" class="btn btn-animation dark-button btn-sm" data-bs-dismiss="modal">
                        İptal
                    </a>
                    <a href="javascript:;" class="btn btn-animation btn-sm" data-action="save-address" data-form-target="address-form">
                        Kaydet
                    </a>
                </div>

            </div>
        </div>
    </div>
@endif

{{-- hesabim --}}
@if (Route::is('account.dashboard'))
    <div class="modal fade theme-modal" id="editProfile" tabindex="-1" aria-labelledby="exampleModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel2">Düzenle & şifre Değiştir</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-xxl-12">
                            <form>
                                <div class="form-floating theme-form-floating">
                                    <input type="text" class="form-control" name="name" id="name" value="{{ auth('web')->check() ? auth('web')->user()->name : (auth('subdealer')->check() ? auth('subdealer')->user()->name : '') }}" disabled>
                                    <label for="name">Adı Soyadı/Ünvan</label>
                                </div>
                            </form>
                        </div>

                        <div class="col-xxl-6">
                            <form>
                                <div class="form-floating theme-form-floating">
                                    <input type="email" class="form-control" name="email" id="email" value="{{ auth('web')->check() ? auth('web')->user()->email : (auth('subdealer')->check() ? auth('subdealer')->user()->email : '') }}" disabled>
                                    <label for="email">E-Posta Adresi</label>
                                </div>
                            </form>
                        </div>

                        <div class="col-xxl-6">
                            <form>
                                <div class="form-floating theme-form-floating">
                                    <input class="form-control" type="text" name="phone" id="phone" value="{{ auth('web')->check() ? auth('web')->user()->phone : (auth('subdealer')->check() ? auth('subdealer')->user()->phone : '') }}" disabled>
                                    <label for="phone">Telefon Numarası</label>
                                </div>
                            </form>
                        </div>

                        <div class="col-xxl-6">
                            <form>
                                <div class="form-floating theme-form-floating">
                                    <input type="text" class="form-control" id="password" name="password" data-password-input>
                                    <label for="password">Yeni Şifre</label>
                                    <div class="invalid-feedback" data-password-error></div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-animation dark-button btn-sm" data-bs-dismiss="modal">Kapat</button>
                    <button type="button" class="btn btn-animation btn-sm" data-action="update-password">Kaydet</button>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ödeme --}}
@if (Route::is('payments.page') || Route::is('payments.payment-link'))
    <div class="modal fade" id="payment-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content" style="height: 800px;">
                <div class="modal-header justify-content-center">
                    <h4 class="modal-title text-danger">{{ trans('translations.modal.islem_tamamlanana_kadar_lutfen_bekleyiniz') }}</h4>
                </div>
                <div class="modal-body" data-js="payment-modal-body"></div>
            </div>
        </div>
    </div>
    <div class="modal fade theme-modal installment-table" tabindex="-1" aria-labelledby="installment-table-label" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="installment-table-label">{{ trans('translations.modal.taksit_tablosu') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="row">
                            @foreach($bankIntegrations as $bankIntegration)
                                <div class="col-6 col-md-4 col-lg-4 mb-4">
                                    <div class="card">
                                        <div class="card-header text-center fw-bold">
                                            <img src="{{ $bankIntegration->final_logo_path }}" alt="{{ $bankIntegration->name }}" width="120">
                                        </div>
                                        <div class="card-body p-2" style="{{ $bankIntegration->final_color }}">
                                            <table class="table table-sm text-center">
                                                <thead>
                                                    <tr>
                                                        <th>{{ trans('translations.modal.taksit') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($bankIntegration->installments as $installment)
                                                        <tr>
                                                            <td>{{ $installment->installment }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- sipariş --}}
@if (Route::is('orders.index'))
    <div class="modal fade theme-modal order-show" tabindex="-1" aria-labelledby="order-show-label" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="order-show-label">{{ trans('translations.modal.siparis_detayi') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button class="btn btn-animation btn-sm" data-bs-dismiss="modal"><i class="flaticon-cancel-12"></i> {{ trans('translations.modal.kapat') }}</button>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ürün filtreleme --}}
<style>
    .modal-body.modal-scroll {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }
</style>
@if (Route::is('product.all') || Route::is('product.list') || Route::is('product.search') || Route::is('product.block'))
    <div class="modal fade theme-modal product-filter" tabindex="-1" aria-labelledby="product-filter-label" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="product-filter-label">{{ trans('translations.modal.urun_filtrele') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body modal-scroll">
                    <form class="row g-sm-4 g-3" id="product-filter" method="GET" action="{{ route('product.filter') }}">
                        @if (isset($type) && $type === 'all')
                            <div class="col-xl-4 col-md-6">
                                <div class="category-title">
                                    <h3>{{ trans('translations.modal.kategoriler') }}</h3>
                                </div>
                                <div class="mb-3">
                                    <input type="text" class="form-control form-control-sm" placeholder="Kategori ara..." data-js="filter-search">
                                </div>
                                <ul class="category-list custom-padding custom-height">
                                    @foreach ($categories as $cat)
                                        @php
                                            $catCheck = '';
                                            if (isset($_GET['kategoriler'])) {
                                                $catCheck = in_array($cat->slug, explode(',', $_GET['kategoriler'])) ? 'checked' : '';
                                            }
                                        @endphp
                                        <li>
                                            <div class="form-check ps-0 m-0 category-list-box">
                                                <input class="checkbox_animated" type="checkbox" id="{{ 'category-' . $cat->id }}" name="categories[]" value="{{ $cat->slug }}" {{ $catCheck }}>
                                                <label class="form-check-label" for="{{ 'category-' . $cat->id }}">
                                                    <span class="name">{{ $cat->name }}</span>
                                                </label>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="col-xl-4 col-md-6">
                            <div class="category-title">
                                <h3>{{ trans('translations.modal.markalar') }}</h3>
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control form-control-sm" placeholder="Marka ara..." data-js="filter-search">
                            </div>
                            <ul class="category-list custom-padding custom-height">
                                @foreach ($brands as $brand)
                                    @php
                                        $brandCheck = '';
                                        if (isset($_GET['markalar'])) {
                                            $brandCheck = in_array($brand->name, explode(',', $_GET['markalar'])) ? 'checked' : '';
                                        }
                                    @endphp
                                    <li>
                                        <div class="form-check ps-0 m-0 category-list-box">
                                            <input class="checkbox_animated" type="checkbox" id="{{ 'brand-' . $brand->id }}" name="brands[]" value="{{ $brand->name }}" {{ $brandCheck }}>
                                            <label class="form-check-label" for="{{ 'brand-' . $brand->id }}">
                                                <span class="name">{{ $brand->name }}</span>
                                            </label>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        @foreach ($usedAttributes ?? [] as $attribute)
                            <div class="col-xl-4 col-md-6">
                                <div class="category-title">
                                    <h3>{{ app()->getLocale() == 'tr' ? $attribute->name : $attribute->name_en }}</h3>
                                </div>
                                <div class="mb-3">
                                    <input type="text" class="form-control form-control-sm" placeholder="{{ (app()->getLocale() == 'tr' ? $attribute->name : $attribute->name_en) . ' ara...' }}" data-js="filter-search">
                                </div>
                                <ul class="category-list custom-padding custom-height">
                                    @foreach ($attribute->attributeValues as $value)
                                        @php
                                            $featureCheck = '';
                                            if (isset($_GET['ozellikler'])) {
                                                foreach (explode(';', $_GET['ozellikler']) as $feature) {
                                                    foreach (explode(',', explode(':', $feature)[1]) as $featureValue) {
                                                        $featureValues[] = $featureValue;
                                                    }
                                                }

                                                $featureCheck = in_array($value->slug, $featureValues) ? 'checked' : '';
                                            }
                                        @endphp
                                        <li>
                                            <div class="form-check ps-0 m-0 category-list-box">
                                                <input class="checkbox_animated" type="checkbox" id="{{ $value->slug }}" name="features[]" value="{{ $value->slug }}" {{ $featureCheck }}>
                                                <label class="form-check-label" for="{{ $value->slug }}">
                                                    <span class="name">{{ app()->getLocale() == 'tr' ? $value->name : $value->name_en }}</span>
                                                </label>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach

                        @if (false)
                            <div class="col-xl-4 col-md-6">
                                <div class="category-title">
                                    <h3>{{ trans('translations.modal.stok') }}</h3>
                                </div>
                                <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="minStock" id="minStock" placeholder="Min" value="{{ request()->get('minStok') }}">
                                        <span class="input-group-text">-</span>
                                        <input type="number" class="form-control" name="maxStock" id="maxStock" placeholder="Max" value="{{ request()->get('maxStok') }}">
                                        <button class="btn theme-bg-color text-white" type="submit"><i data-feather="search"></i></button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <input type="hidden" name="page_type" value="{{ $type ?? '' }}">
                        <input type="hidden" name="slug" value="{{ $slug ?? '' }}">

                        @if (isset($_GET['sirala']))
                            <input type="hidden" name="sorting" value="{{ $_GET['sirala'] }}">
                        @endif
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light btn-sm" data-bs-dismiss="modal"><i class="flaticon-cancel-12"></i> {{ trans('translations.modal.iptal') }}</button>
                    <button type="submit" class="btn btn-animation btn-sm" form="product-filter">{{ trans('translations.modal.filtrele') }}</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('input', function(e) {
            if (e.target.matches('[data-js="filter-search"]')) {
                const searchTerm = e.target.value.toLocaleLowerCase('tr-TR');
                const parentCol = e.target.closest('.col-xl-4');
                if (!parentCol) return;

                const listItems = parentCol.querySelectorAll('ul.category-list li');

                listItems.forEach(function(li) {
                    const nameSpan = li.querySelector('.name');
                    if (!nameSpan) return;

                    const text = nameSpan.textContent.toLocaleLowerCase('tr-TR');
                    if (text.includes(searchTerm)) {
                        li.classList.remove('d-none');
                    } else {
                        li.classList.add('d-none');
                    }
                });
            }
        });
    </script>
@endif

{{-- ürün detay - kampanya --}}
@if (Route::is('product.detail'))
    @if (isset($campaigns) && $campaigns->count())
        <div class="modal fade theme-modal" id="productCampaignModal" tabindex="-1" aria-labelledby="productCampaignLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productCampaignLabel">
                            {{ $product->name }} – Kampanyalar
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        @foreach($campaigns as $campaign)
                            @php $rule = $campaign->rules->first(); @endphp

                            <div class="campaign-box border p-3 rounded mb-3">
                                <h5 class="mb-2">{{ $campaign->name }}</h5>

                                {{-- TIERED PRICE --}}
                                @if($campaign->sub_type === 'tiered_price')
                                    <p>Bu ürün için kademeli indirim geçerlidir:</p>

                                    @foreach($rule->extra['tiers'] ?? [] as $tier)
                                        <div>
                                            {{ $tier['min_quantity'] }}+ adet →
                                            @if($tier['price_type'] === 'percent')
                                                %{{ $tier['action_value'] }} indirim
                                            @elseif($tier['price_type'] === 'fixed')
                                                {{ $tier['action_value'] }} ₺ indirim
                                            @elseif($tier['price_type'] === 'net')
                                                Net fiyat: {{ $tier['action_value'] }} ₺
                                            @endif
                                        </div>
                                    @endforeach
                                @endif

                                {{-- FREE PRODUCT --}}
                                @if($campaign->sub_type === 'free_product')
                                    <p>
                                        Bu üründen en az
                                        <strong>{{ $rule->extra['min_quantity'] ?? 1 }}</strong> adet alana,
                                        aşağıdaki ürünlerden
                                        <strong>{{ $rule->extra['gift_quantity'] }}</strong> adet hediye edilir:
                                    </p>

                                    <ul class="ps-3">
                                        @foreach(($rule->extra['gifts'] ?? []) as $gid)
                                            @php $gift = \App\Models\Product::find($gid); @endphp
                                            @if($gift)
                                                <li>{{ $gift->name }} ({{ $gift->code }})</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @endif


                                {{-- FREE SHIPPING --}}
                                @if($campaign->sub_type === 'free_shipping')

                                    @php
                                        $minQty = $rule->extra['min_quantity'] ?? null;
                                        $minAmount = $rule->extra['min_amount'] ?? null;
                                    @endphp

                                    @if($minQty)
                                        <p>
                                            Bu üründen en az
                                            <strong>{{ $minQty }}</strong> adet alındığında kargo bedelsizdir.
                                        </p>
                                    @endif

                                    @if($minAmount)
                                        <p>
                                            Bu üründen toplam
                                            <strong>{{ number_format($minAmount, 2) }} ₺</strong> ve üzeri alışverişte kargo bedelsizdir.
                                        </p>
                                    @endif

                                    @if(!$minQty && !$minAmount)
                                        <p>Bu üründe bedelsiz kargo kampanyası geçerlidir.</p>
                                    @endif
                                @endif


                                {{-- BONUS PRODUCT --}}
                                @if($campaign->sub_type === 'bonus_product')
                                    <p>
                                        Bu üründen
                                        <strong>{{ $rule->extra['min_quantity'] }}</strong> adet aldığınızda,
                                        <strong>{{ $rule->extra['bonus_quantity'] }}</strong> adet ürün bedelsiz olarak eklenir.
                                    </p>
                                @endif

                            </div>

                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    @endif
@endif

{{-- sözleşme --}}
@if (Route::is('contract.*') && additional_setting('use_contract_approval', false))
    <div class="modal fade theme-modal" id="smsCode" tabindex="-1" aria-labelledby="smsCode-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="smsCode-label">{{ trans('translations.modal.sms_onay_kodu') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="smsCodeForm">
                        @csrf
                        <div class="form-group">
                            <label for="sms-code">{{ trans('translations.modal.sms_ile_gonderilen_4_haneli_kodu_girin') }}</label>
                            <input type="text" class="form-control" id="sms-code" name="sms_code" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-animation btn-sm" data-bs-dismiss="modal"><i class="flaticon-cancel-12">{{ trans('translations.modal.kapat') }}</button>
                    <button type="submit" class="btn btn-animation btn-sm smsCode-button" data-js="verify-sms-code" data-url="{{ route('contract.approve', ['actor_type' => $actor_type, 'actor_id' => $actor_id, 'template' => $template->id]) }}">{{ trans('translations.modal.onayla') }}</button>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- tahsilatlar (nakit, çek, senet) --}}
@if (Route::is('collections.cashes.index') || Route::is('dealers.sub-dealers.index'))
    <div class="modal fade theme-modal" id="add-edit-modal" tabindex="-1" aria-labelledby="add-edit-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-edit-modal-label"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body" id="add-edit-modal-body"></div>
                <div class="modal-footer">
                    <button class="btn btn-animation dark-button btn-sm" data-bs-dismiss="modal"><i class="flaticon-cancel-12"></i> {{ trans('translations.modal.kapat') }}</button>
                    <button type="submit" class="btn btn-animation btn-sm" id="add-edit-modal-button" form="add-edit-modal-form"></i> {{ trans('translations.modal.kaydet') }}</button>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- çek --}}
@if (Route::is('collections.cheques.create'))
    <div class="modal fade theme-modal" id="cheque-modal" tabindex="-1" aria-labelledby="cheque-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cheque-modal-label"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body" id="cheque-modal-body">
                    <form id="cheque-modal-form">
                        @csrf
                        <div class="form-floating theme-form-floating mb-lg-3 mb-2">
                            <input type="text" class="form-control" id="serial_number" name="serial_number">
                            <label for="serial_number">{{ trans('translations.modal.seri_no') }}&nbsp;<span class="text-danger">*</span></label>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6 mb-lg-3 mb-2">
                                <div class="form-floating theme-form-floating date-box">
                                    <input type="date" class="form-control" id="maturity_date" name="maturity_date" value="{{ date('Y-m-d') }}">
                                    <label for="maturity_date">{{ trans('translations.modal.vade_tarihi') }}&nbsp;<span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-lg-3 mb-2">
                                <div class="form-floating theme-form-floating">
                                    <select class="form-select theme-form-select" name="clio_type" id="clio_type">
                                        <option value="Kendisi" selected>{{ trans('translations.modal.kendisi') }}</option>
                                        <option value="Müşteri">{{ trans('translations.modal.musteri') }}</option>
                                    </select>
                                    <label for="clio_type">{{ trans('translations.modal.clio_tipi') }}&nbsp;<span class="text-danger">*</span></label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating theme-form-floating mb-lg-3 mb-2">
                            <input type="text" class="form-control" id="debtor" name="debtor" value="{{ $currentAccountService->currentAccount()->name }}" disabled>
                            <label for="debtor">{{ trans('translations.modal.borclu') }}&nbsp;<span class="text-danger">*</span></label>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6 mb-lg-3 mb-2">
                                <div class="form-floating theme-form-floating mb-lg-3 mb-2">
                                    <input type="text" class="form-control" id="amount" name="amount" data-format="price">
                                    <label for="amount">{{ trans('translations.modal.tutar') }}&nbsp;<span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-lg-3 mb-2">
                                <div class="form-floating theme-form-floating mb-lg-3 mb-2">
                                    <select class="form-select theme-form-select" name="currency_type" id="currency_type">
                                        <option value="" selected hidden>{{ trans('translations.modal.sec') }}</option>
                                        <option value="TL">TL</option>
                                        <option value="USD">USD</option>
                                        {{-- <option value="EUR">EUR</option> --}}
                                    </select>
                                    <label for="currency_type">{{ trans('translations.modal.doviz_turu') }}&nbsp;<span class="text-danger">*</span></label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating theme-form-floating mb-lg-3 mb-2">
                            <input type="text" class="form-control" id="bank_name" name="bank_name">
                            <label for="bank_name">{{ trans('translations.modal.banka_adi') }}</label>
                        </div>
                        <div class="form-floating theme-form-floating mb-lg-3 mb-2">
                            <input type="number" class="form-control" id="branch_code" name="branch_code">
                            <label for="branch_code">{{ trans('translations.modal.sube_kodu') }}</label>
                        </div>
                        <div class="form-floating theme-form-floating mb-lg-3 mb-2">
                            <input type="text" class="form-control" id="iban" name="iban">
                            <label for="iban">{{ trans('translations.modal.iban') }}</label>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="title">
                                    <h4>{{ trans('translations.modal.cek_cogaltma') }}</h4>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-lg-6 mb-2">
                                <div class="form-floating theme-form-floating">
                                    <input type="text" class="form-control" id="piece" name="piece" min="1" value="1">
                                    <label for="piece">{{ trans('translations.modal.adet') }}&nbsp;<span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-lg-6 mb-2">
                                <div class="form-floating theme-form-floating">
                                    <select class="form-control" name="maturity_day" id="maturity_day">
                                        @for ($i = 1; $i <= 31; $i++)
                                            <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <label for="maturity_day">{{ trans('translations.modal.vade_gunu') }}&nbsp;<span class="text-danger">*</span></label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-animation dark-button btn-sm" data-bs-dismiss="modal"><i class="flaticon-cancel-12"></i> {{ trans('translations.modal.kapat') }}</button>
                    <button type="submit" class="btn btn-animation btn-sm" id="cheque-modal-button" form="cheque-modal-form"></i> {{ trans('translations.modal.kaydet') }}</button>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- senet --}}
@if (Route::is('collections.promissories.create'))
    <div class="modal fade theme-modal" id="promissory-modal" tabindex="-1" aria-labelledby="promissory-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="promissory-modal-label"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body" id="promissory-modal-body">
                    <form id="promissory-modal-form">
                        @csrf
                        <div class="form-floating theme-form-floating mb-lg-3 mb-2">
                            <input type="text" class="form-control" id="serial_number" name="serial_number">
                            <label for="serial_number">{{ trans('translations.modal.seri_no') }}&nbsp;<span class="text-danger">*</span></label>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6 mb-lg-3 mb-2">
                                <div class="form-floating theme-form-floating date-box">
                                    <input type="date" class="form-control" id="maturity_date" name="maturity_date" value="{{ date('Y-m-d') }}">
                                    <label for="maturity_date">{{ trans('translations.modal.vade_tarihi') }}&nbsp;<span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-lg-3 mb-2">
                                <div class="form-floating theme-form-floating">
                                    <select class="form-select theme-form-select" name="clio_type" id="clio_type">
                                        <option value="Kendisi" selected>{{ trans('translations.modal.kendisi') }}</option>
                                        <option value="Müşteri">{{ trans('translations.modal.musteri') }}</option>
                                    </select>
                                    <label for="clio_type">{{ trans('translations.modal.clio_tipi') }}&nbsp;<span class="text-danger">*</span></label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating theme-form-floating mb-lg-3 mb-2">
                            <input type="text" class="form-control" id="debtor" name="debtor" value="{{ $currentAccountService->currentAccount()->name }}" disabled>
                            <label for="debtor">{{ trans('translations.modal.borclu') }}&nbsp;<span class="text-danger">*</span></label>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6 mb-lg-3 mb-2">
                                <div class="form-floating theme-form-floating mb-lg-3 mb-2">
                                    <input type="text" class="form-control" id="amount" name="amount" data-format="price">
                                    <label for="amount">{{ trans('translations.modal.tutar') }}&nbsp;<span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-lg-3 mb-2">
                                <div class="form-floating theme-form-floating mb-lg-3 mb-2">
                                    <select class="form-select theme-form-select" name="currency_type" id="currency_type">
                                        <option value="" selected hidden>{{ trans('translations.modal.sec') }}</option>
                                        <option value="TL">TL</option>
                                        <option value="USD">USD</option>
                                        {{-- <option value="EUR">EUR</option> --}}
                                    </select>
                                    <label for="currency_type">{{ trans('translations.modal.doviz_turu') }}&nbsp;<span class="text-danger">*</span></label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="title">
                                    <h4>{{ trans('translations.modal.senet_cogaltma') }}</h4>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-lg-6 mb-2">
                                <div class="form-floating theme-form-floating">
                                    <input type="text" class="form-control" id="piece" name="piece" min="1" value="1">
                                    <label for="piece">{{ trans('translations.modal.adet') }}&nbsp;<span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-lg-6 mb-2">
                                <div class="form-floating theme-form-floating">
                                    <select class="form-control" name="maturity_day" id="maturity_day">
                                        @for ($i = 1; $i <= 31; $i++)
                                            <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <label for="maturity_day">{{ trans('translations.modal.vade_gunu') }}&nbsp;<span class="text-danger">*</span></label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-animation dark-button btn-sm" data-bs-dismiss="modal"><i class="flaticon-cancel-12"></i> {{ trans('translations.modal.kapat') }}</button>
                    <button type="submit" class="btn btn-animation btn-sm" id="promissory-modal-button" form="promissory-modal-form"></i> {{ trans('translations.modal.kaydet') }}</button>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- auth --}}
@if (Route::is('login.form'))
    <div class="modal fade theme-modal" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModal-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModal-label">{{ trans('translations.modal.sifre_degistirme') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="changePasswordModalForm">
                        @csrf
                        <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                            <input type="password" name="new_password" id="newPassword" class="form-control">
                            <label for="newPassword">{{ trans('translations.modal.yeni_sifre') }}</label>
                        </div>
                        <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                            <input type="password" name="confirm_password" id="confirmPassword" class="form-control">
                            <label for="confirmPassword">{{ trans('translations.modal.yeni_sifre_tekrar') }}</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-animation btn-sm" data-bs-dismiss="modal"><i class="flaticon-cancel-12">{{ trans('translations.modal.kapat') }}</button>
                    <button type="submit" class="btn btn-animation btn-sm" form="changePasswordModalForm">{{ trans('translations.modal.degistir') }}</button>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- anasayfa - anket --}}
@if(Route::is('index'))
    <div class="modal fade theme-modal" id="surveyModal" tabindex="-1" aria-labelledby="survey-notice-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="survey-notice-modal-label">Anketimize Katılın!</h5>
                </div>
                <div class="modal-body">
                    <p>2 dakika ayırarak ankete katılabilirsiniz. Fikirleriniz bizim için çok değerli!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="closeSurveyBtn">
                        <i class="flaticon-cancel-12"></i> Daha Sonra
                    </button>
                    <a href="javascript:;" id="goSurveyBtn" class="btn btn-animation btn-sm">Katıl</a>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- mail gönder --}}
<div class="modal fade" data-modal="send-b2b-mail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" data-modal-title>Mail Gönder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form data-mail-form>
                    <div class="mb-3">
                        <label class="form-label">E-posta Adresi</label>
                        <input type="email" class="form-control" name="recipient_email" data-recipient-email>
                    </div>

                    <input type="hidden" name="mail_type" data-mail-type>
                    <input type="hidden" name="ref_id" data-ref-id>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-danger btn-sm text-white" data-bs-dismiss="modal">Kapat</button>
                <a href="javascript:;" class="btn btn-animation btn-sm" data-send-mail>Gönder</a>
            </div>
        </div>
    </div>
</div>
