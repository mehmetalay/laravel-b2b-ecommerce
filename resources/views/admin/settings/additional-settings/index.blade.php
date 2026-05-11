@extends('admin.layouts.app')

@section('title', 'Ek Ayarlar')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Ayarlar'],
            ['url' => route('admin.settings.additional-settings.index'), 'label' => 'Ek Ayarlar'],
        ]">
        <li class="nav-item">
            <button type="submit" class="btn btn-success dash-btn" form="setting-form" data-ajax-submit>
                <i class="las la-save"></i> Kaydet
            </button>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <form action="{{ route('admin.settings.additional-settings.update', [$additionalSetting->id]) }}" method="POST" id="setting-form" data-ajax-form>
            @csrf
            @method('PATCH')
            <div class="row layout-top-spacing switch-outer-container">
                <div class="col-12 layout-spacing">
                    <div class="statbox widget box box-shadow">
                        <div class="widget-header">
                            <h4>Ek Ayarlar</h4>
                        </div>
                        <div class="widget-content widget-content-area pills-vertical-line">
                            <div class="row">
                                <div class="col-12 col-xl-3">
                                    <div class="nav flex-column nav-pills w-75" id="v-line-pills-tab" role="tablist" aria-orientation="vertical">
                                        <a class="nav-link active mb-3" id="payment-and-price-additional-setting-tab" data-toggle="pill" href="#payment-and-price-additional-setting" role="tab" aria-controls="payment-and-price-additional-setting" aria-selected="true">Ödeme ve Fiyat Ek Ayarları</a>
                                        <a class="nav-link mb-3" id="add-to-cart-additional-setting-tab" data-toggle="pill" href="#add-to-cart-additional-setting" role="tab" aria-controls="add-to-cart-additional-setting" aria-selected="true">Sepete Ekleme Ek Ayarları</a>
                                        <a class="nav-link mb-3" id="showcase-and-view-additional-setting-tab" data-toggle="pill" href="#showcase-and-view-additional-setting" role="tab" aria-controls="showcase-and-view-additional-setting" aria-selected="false">Vitrin ve Görünüm Ek Ayarları</a>
                                        <a class="nav-link mb-3" id="site-additional-setting-tab" data-toggle="pill" href="#site-additional-setting" role="tab" aria-controls="site-additional-setting" aria-selected="false">Site Ek Ayarları</a>
                                        <a class="nav-link mb-3" id="general-administration-tab" data-toggle="pill" href="#general-administration" role="tab" aria-controls="general-administration" aria-selected="false">Genel Yönetim</a>
                                    </div>
                                </div>
                                <div class="col-12 col-xl-9">
                                    <div class="tab-content" id="v-line-pills-tabContent">
                                        <div class="tab-pane fade show active" id="payment-and-price-additional-setting" role="tabpanel" aria-labelledby="payment-and-price-additional-setting-tab">
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="decimal">Fiyat Ondalık Hane Sayısı</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <input type="number" class="form-control" name="decimal" value="{{ $additionalSetting->decimal }}">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12">Ödeme Planı</label>
                                                <div class="col-lg-2 col-sm-12">
                                                    <span class="switch">
                                                        <label>
                                                            <input type="checkbox" name="payment_plan_selection" id="payment_plan_selection" {{ $additionalSetting->payment_plan_selection ? 'checked' : '' }}>
                                                            <span></span> Göster
                                                        </label>
                                                    </span>
                                                </div>
                                                <div class="col-lg-2 col-sm-12">
                                                    <span class="switch">
                                                        <label>
                                                            <input type="checkbox" name="payment_plan_required" id="payment_plan_required" {{ $additionalSetting->payment_plan_required ? 'checked' : '' }}>
                                                            <span></span> Zorunlu
                                                        </label>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12">Ödeme Türü</label>
                                                <div class="col-lg-2 col-sm-12">
                                                    <span class="switch">
                                                        <label>
                                                            <input type="checkbox" name="payment_type_selection" id="payment_type_selection" {{ $additionalSetting->payment_type_selection ? 'checked' : '' }}>
                                                            <span></span> Göster
                                                        </label>
                                                    </span>
                                                </div>
                                                <div class="col-lg-2 col-sm-12">
                                                    <span class="switch">
                                                        <label>
                                                            <input type="checkbox" name="payment_type_required" id="payment_type_required" {{ $additionalSetting->payment_type_required ? 'checked' : '' }}>
                                                            <span></span> Zorunlu
                                                        </label>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12">Teslimat Seçeneği</label>
                                                <div class="col-lg-2 col-sm-12">
                                                    <span class="switch">
                                                        <label>
                                                            <input type="checkbox" name="delivery_type_selection" id="delivery_type_selection" {{ $additionalSetting->delivery_type_selection ? 'checked' : '' }}>
                                                            <span></span> Göster
                                                        </label>
                                                    </span>
                                                </div>
                                                <div class="col-lg-2 col-sm-12">
                                                    <span class="switch">
                                                        <label>
                                                            <input type="checkbox" name="delivery_type_required" id="delivery_type_required" {{ $additionalSetting->delivery_type_required ? 'checked' : '' }}>
                                                            <span></span> Zorunlu
                                                        </label>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="is_order_confirmation">Sipariş Onay Formu</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <span class="switch align-items-start">
                                                        <label>
                                                            <input type="checkbox" name="is_order_confirmation" id="is_order_confirmation" {{ $additionalSetting->is_order_confirmation ? 'checked' : '' }}>
                                                            <span></span>
                                                        </label>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="add-to-cart-additional-setting" role="tabpanel" aria-labelledby="add-to-cart-additional-setting-tab">
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="purchase_limit_minimum">Minimum Sepete Ekleme Miktarı Limiti</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <input type="number" class="form-control" name="purchase_limit_minimum" value="{{ $additionalSetting->purchase_limit_minimum }}">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="purchase_limit_maximum">Maksimum Sepete Ekleme Miktarı Limiti</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <input type="number" class="form-control" name="purchase_limit_maximum" value="{{ $additionalSetting->purchase_limit_maximum }}">
                                                    <small class="text-muted">Boş bıraktığınız durumda çalışmayacaktır.</small>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="cart_item_note_visibility">Sepet Satır Açıklaması Gösterimi</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <span class="switch align-items-start">
                                                        <label>
                                                            <input type="checkbox" name="cart_item_note_visibility" id="cart_item_note_visibility" {{ $additionalSetting->cart_item_note_visibility ? 'checked' : '' }}>
                                                            <span></span>
                                                        </label>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="allow_over_order">Stok Aşımına İzin Ver</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <span class="switch align-items-start">
                                                        <label>
                                                            <input type="checkbox" name="allow_over_order" id="allow_over_order" {{ $additionalSetting->allow_over_order ? 'checked' : '' }}>
                                                            <span></span>
                                                        </label>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="showcase-and-view-additional-setting" role="tabpanel" aria-labelledby="showcase-and-view-additional-setting-tab">
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="display_of_out_of_stock_products">Stokta Olmayan Ürünlerin Gösterimi</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <span class="switch align-items-start">
                                                        <label>
                                                            <input type="checkbox" name="display_of_out_of_stock_products" id="display_of_out_of_stock_products" {{ $additionalSetting->display_of_out_of_stock_products ? 'checked' : '' }}>
                                                            <span></span>
                                                        </label>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="show_stock">
                                                    Stok Gösterimi
                                                    <br>
                                                    <small class="text-muted">Stok Gösterimi seçeneği pasif durumda ise, plasiyer dahil tüm kullanıcılara gizlenecektir.</small>
                                                </label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <span class="switch align-items-start">
                                                        <label>
                                                            <input type="checkbox" name="show_stock" id="show_stock" {{ $additionalSetting->show_stock ? 'checked' : '' }}>
                                                            <span></span>
                                                        </label>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="is_critical_stock_enabled">Kritik stok uyarıları</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <span class="switch align-items-start">
                                                        <label>
                                                            <input type="checkbox" name="is_critical_stock_enabled" id="is_critical_stock_enabled" {{ $additionalSetting->is_critical_stock_enabled ? 'checked' : '' }} data-toggle-scope data-toggle-target="critical-stock-threshold">
                                                            <span></span>
                                                        </label>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="form-group row" {{ $additionalSetting->is_critical_stock_enabled ? '' : 'style=display:none' }} data-toggle-panel="critical-stock-threshold">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="critical_stock_threshold">Kritik Stok Eşiği</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <input type="number" class="form-control" name="critical_stock_threshold" id="critical_stock_threshold" value="{{ $additionalSetting->critical_stock_threshold }}">
                                                    <small class="text-muted">Boş bıraktığınız durumda 1 kritik stok çalışacaktır.</small>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="maximum_stock_number_display_user">Maksimum Stok Sayısı Gösterimi (Bayi)</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <input type="number" class="form-control" name="maximum_stock_number_display_user" id="maximum_stock_number_display_user" value="{{ $additionalSetting->maximum_stock_number_display_user }}">
                                                    <small class="text-muted">Boş bıraktığınız durumda çalışmayacaktır.</small>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="maximum_stock_number_display_plasiyer">Maksimum Stok Sayısı Gösterimi (Plasiyer)</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <input type="number" class="form-control" name="maximum_stock_number_display_plasiyer" id="maximum_stock_number_display_plasiyer" value="{{ $additionalSetting->maximum_stock_number_display_plasiyer }}">
                                                    <small class="text-muted">Boş bıraktığınız durumda çalışmayacaktır.</small>
                                                </div>
                                            </div>
                                            {{-- <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="min_stock_quantity">Minimum Stok Adedi Üzeri Ürünleri Listeleme</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <input type="number" class="form-control" name="min_stock_quantity" id="min_stock_quantity" value="{{ $additionalSetting->min_stock_quantity }}">
                                                    <small class="text-muted">"Stokta Olmayan Ürünlerin Gösterimi" seçeneği aktif durumda bu filtreleme çalışmayacaktır.</small>
                                                </div>
                                            </div> --}}
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="max_stock_quantity">Maksimum Stok Adedi Altı Ürünleri Listeleme</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <input type="number" class="form-control" name="max_stock_quantity" id="max_stock_quantity" value="{{ $additionalSetting->max_stock_quantity }}">
                                                    <small class="text-muted">Boş bıraktığınız durumda maksimum stok adedi filtrelenmeyecektir.</small>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="product_record_per_page">Her Sayfadaki Ürün Adedi</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <select class="form-control" name="product_record_per_page" id="product_record_per_page">
                                                        <option value="24" {{ 24 == $additionalSetting->product_record_per_page ? 'selected' : '' }}>24</option>
                                                        <option value="48" {{ 48 == $additionalSetting->product_record_per_page ? 'selected' : '' }}>48</option>
                                                        <option value="96" {{ 96 == $additionalSetting->product_record_per_page ? 'selected' : '' }}>96</option>
                                                        <option value="144" {{ 144 == $additionalSetting->product_record_per_page ? 'selected' : '' }}>144</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="default_product_view_type">Varsayılan Ürün Görünümü</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <select class="form-control" name="default_product_view_type" id="default_product_view_type">
                                                        <option value="grid" {{ 'grid' == $additionalSetting->default_product_view_type ? 'selected' : '' }}>Izgara Görünümü</option>
                                                        <option value="list" {{ 'list' == $additionalSetting->default_product_view_type ? 'selected' : '' }}>Liste Görünümü</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="site-additional-setting" role="tabpanel" aria-labelledby="site-additional-setting-tab">
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="admin_password">Yönetim Giriş Şifresi</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <x-backend.input id="admin_password" type="text" :value="$additionalSetting->admin_password" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="order_emails">Sipariş Mail Adresleri</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <input class="form-control tagify" name="order_emails" id="order_emails" value="{{ $additionalSetting->order_emails }}">
                                                    <small class="text-danger">Kelime yazdıktan sonra <strong>Enter</strong> tuşuna basınız.</small>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="payment_emails">Ödeme Mail Adresleri</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <input class="form-control tagify" name="payment_emails" id="payment_emails" value="{{ $additionalSetting->payment_emails }}">
                                                    <small class="text-danger">Kelime yazdıktan sonra <strong>Enter</strong> tuşuna basınız.</small>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="dealer_application_mails">Bayi Başvuru Mail Adresleri</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <input class="form-control tagify" name="dealer_application_mails" id="dealer_application_mails" value="{{ $additionalSetting->dealer_application_mails }}">
                                                    <small class="text-danger">Kelime yazdıktan sonra <strong>Enter</strong> tuşuna basınız.</small>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="use_contract_approval">
                                                    Sözleşme Onayını Kullan
                                                </label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <span class="switch align-items-start">
                                                        <label>
                                                            <input type="checkbox" name="use_contract_approval" id="use_contract_approval" {{ $additionalSetting->use_contract_approval ? 'checked' : '' }}>
                                                            <span></span>
                                                        </label>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="default_company_id">Varsayılan Sanal POS Firması</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <select class="form-control" name="default_company_id" id="default_company_id">
                                                        @foreach ($companies as $company)
                                                            <option value="{{ $company->id }}" {{ $company->id == $additionalSetting->default_company_id ? 'selected' : '' }}>{{ $company->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="general-administration" role="tabpanel" aria-labelledby="general-administration-tab">
                                            <div class="form-group row">
                                                <label class="col-form-label col-lg-3 col-sm-12" for="site_status">Site Durumu</label>
                                                <div class="col-lg-3 col-sm-12">
                                                    <span class="switch align-items-start">
                                                        <label>
                                                            <input type="checkbox" name="site_status" id="site_status" {{ $additionalSetting->site_status ? 'checked' : '' }} data-toggle-scope data-toggle-target="coming-soon-title">
                                                            <span></span>
                                                        </label>
                                                    </span>
                                                </div>
                                            </div>
                                            <div id="site-status" {{ $additionalSetting->site_status ? 'style=display:none' : '' }} data-toggle-panel="coming-soon-title" data-toggle-mode="when-checked">
                                                <div class="form-group row">
                                                    <label class="col-form-label col-lg-3 col-sm-12" for="coming_soon_title">Başlık</label>
                                                    <div class="col-lg-3 col-sm-12">
                                                        <x-backend.input id="coming_soon_title" type="text" :value="$additionalSetting->coming_soon_title" />
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-form-label col-lg-3 col-sm-12" for="coming_soon_text">Metin</label>
                                                    <div class="col-lg-3 col-sm-12">
                                                        <x-backend.textarea name="coming_soon_text" rows="4" :value="$additionalSetting->coming_soon_text" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('js')
    <script>
        function toggleApply($cb){
            var target = $cb.data('toggle-target');
            var checked = $cb.is(':checked');

            $('[data-toggle-panel="'+target+'"]').each(function(){
                var mode = $(this).data('toggle-mode') || 'when-unchecked';
                var show = (mode === 'when-checked') ? !checked : checked;

                $(this)[show ? 'fadeIn' : 'fadeOut']('slow');
            });
        }

        $(document).on('change', '[data-toggle-scope][data-toggle-target]', function () {
            toggleApply($(this));
        });

        $('[data-toggle-scope][data-toggle-target]').each(function () {
            toggleApply($(this));
        });
    </script>
@endsection
