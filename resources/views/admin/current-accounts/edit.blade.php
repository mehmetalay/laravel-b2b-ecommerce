@extends('admin.layouts.app')

@section('title', 'Düzenle')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => route('admin.current-accounts.index'), 'label' => 'Cari Hesaplar'],
            ['url' => route('admin.current-accounts.edit', $current_account->id), 'label' => 'Düzenle'],
        ]">
        <li class="nav-item">
            <button type="submit" class="btn btn-success dash-btn mr-2" form="dealer-form" data-ajax-submit>
                <i class="las la-save"></i> Kaydet
            </button>
            <a href="{{ route('admin.current-accounts.index') }}" class="btn btn-info dash-btn">
                <i class="las la-list"></i> Listeye Dön
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing switch-outer-container">
            <div class="col-12 col-xl-9 layout-spacing">
                <form action="{{ route('admin.current-accounts.update', [$current_account->id]) }}" method="POST" id="dealer-form" data-ajax-form>
                    @csrf
                    @method('PATCH')
                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>Düzenle</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="name" label="Adı Soyadı" type="text" :value="$current_account->name" :required="true" autofocus/>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="email" label="E-posta Adresi" type="text" :value="$current_account->email" :required="true"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="code" label="Kodu" type="text" :value="$current_account->code" :required="true"/>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="company_id">Kullanılacak Sanal POS Firması</label>
                                        <select class="form-control" name="company_id" id="company_id">
                                            <option value="" selected hidden>Seç</option>
                                            @foreach ($companies as $company)
                                                <option value="{{ $company->id }}" {{ $current_account->company_id === $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <div class="statbox widget box box-shadow mb-4">
                                <div class="widget-header">
                                    <h4>Ürün Fiyat Artış ve Azalış</h4>
                                </div>
                                <div class="widget-content widget-content-area">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-group">
                                                <label for="increase_and_decrease_type">Türü <span class="text-danger">*</span></label>
                                                <select class="form-control" name="increase_and_decrease_type" id="increase_and_decrease_type">
                                                    <option value="1" {{ $current_account->increase_and_decrease_type == 1 ? 'selected' : '' }}>Artış</option>
                                                    <option value="2" {{ $current_account->increase_and_decrease_type == 2 ? 'selected' : '' }}>Azalış</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-group">
                                                <label for="increase_and_decrease_rate">Oran <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                    <input class="form-control" type="number" name="increase_and_decrease_rate" id="increase_and_decrease_rate" value="{{ $current_account->increase_and_decrease_rate }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="statbox widget box box-shadow mb-4">
                                <div class="widget-header">
                                    <h4>İzin Verilmeyen Kategori Adları</h4>
                                </div>
                                <div class="widget-content widget-content-area">
                                    <div class="form-group">
                                        <label for="hide_category_ids">Kategori</label>
                                        <input name="hide_category_ids" id="hide_category_ids" class="form-control tagify--outside" placeholder="Kategori adı yaz...">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="statbox widget box box-shadow mb-4">
                                <div class="widget-header">
                                    <h4>Gizlenecek Ürün İsimleri</h4>
                                </div>
                                <div class="widget-content widget-content-area">
                                    <div class="form-group">
                                        <label for="hidden_product_prefixes">Ürün Adları</label>
                                        <input name="hidden_product_prefixes" id="hidden_product_prefixes" class="form-control tagify--outside" placeholder="Ürün adını yaz...">
                                        <small class="text-danger">Kelime yazdıktan sonra <strong>Enter</strong> tuşuna basınız. Bu alana yazacağınız ürün isimleri ile başlayan ürünler, bayi tarafından görüntülenmeyecektir.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="statbox widget box box-shadow mb-4">
                                <div class="widget-header">
                                    <h4>Şifre</h4>
                                </div>
                                <div class="widget-content widget-content-area">
                                    <div class="form-group">
                                        <x-backend.input id="password" label="Şifre Değiştir" type="text"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="password_must_change">Şifre Değiştirme Zorunluluğu</label>
                                        <span class="switch align-items-start">
                                            <label>
                                                <input type="checkbox" name="password_must_change" form="dealer-form" {{ $current_account->password_must_change == 1 ? 'checked' : '' }}>
                                                <span></span>
                                            </label>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>Ayarlar</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="form-group row">
                                <label class="col-3 col-form-label" for="is_order_closed">
                                    Siparişe Kapalı
                                </label>
                                <div class="col-3">
                                    <span class="switch">
                                        <label>
                                            <input type="checkbox"
                                            name="is_order_closed"
                                            id="is_order_closed"
                                            {{ $current_account->is_order_closed ? 'checked' : '' }}>
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-3 col-form-label" for="hide_all_prices">
                                    Tüm Fiyatları Gizle
                                </label>
                                <div class="col-3">
                                    <span class="switch">
                                        <label>
                                            <input type="checkbox"
                                            name="hide_all_prices"
                                            id="hide_all_prices"
                                            {{ $current_account->hide_all_prices ? 'checked' : '' }}>
                                            <span></span>
                                        </label>
                                    </span>
                                </div>

                                <label class="col-3 col-form-label" for="hide_all_stock_quantities">
                                    Tüm Stokları Gizle
                                </label>
                                <div class="col-3">
                                    <span class="switch">
                                        <label>
                                            <input type="checkbox"
                                            name="hide_all_stock_quantities"
                                            id="hide_all_stock_quantities"
                                            {{ $current_account->hide_all_stock_quantities ? 'checked' : '' }}>
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-3 col-form-label" for="group_by_product_code">
                                    Ürün Koduna Göre Gruplandır
                                </label>
                                <div class="col-3">
                                    <span class="switch">
                                        <label>
                                            <input type="checkbox"
                                            name="group_by_product_code"
                                            id="group_by_product_code"
                                            {{ $current_account->group_by_product_code ? 'checked' : '' }}>
                                            <span></span>
                                        </label>
                                    </span>
                                </div>

                                <label class="col-3 col-form-label" for="report_access">
                                    Raporlara Erişim
                                </label>
                                <div class="col-3">
                                    <span class="switch">
                                        <label>
                                            <input type="checkbox"
                                            name="report_access"
                                            id="report_access"
                                            {{ $current_account->report_access ? 'checked' : '' }}>
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-3 col-form-label" for="receipt_enabled">
                                    Tahsilat Dekontu Kesilsin
                                </label>
                                <div class="col-3">
                                    <span class="switch">
                                        <label>
                                            <input type="checkbox"
                                            name="receipt_enabled"
                                            id="receipt_enabled"
                                            {{ $current_account->receipt_enabled ? 'checked' : '' }}>
                                            <span></span>
                                        </label>
                                    </span>
                                </div>

                                <label class="col-3 col-form-label">
                                    Taksitli Ödeme Yetkisi
                                </label>
                                <div class="col-3">
                                    <span class="switch">
                                        <label>
                                            <input type="checkbox"
                                                   name="is_installment_allowed"
                                                   id="is_installment_allowed"
                                                   data-toggle
                                                   data-target="installment-info"
                                                   {{ $current_account->is_installment_allowed ? 'checked' : '' }}>
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                            </div>

                            <div data-toggle-id="installment-info"
                                {{ !$current_account->is_installment_allowed ? 'style=display:none' : '' }}>

                                <div class="form-group row">
                                    <label class="col-3 col-form-label"
                                        for="show_all_installments">
                                        Tüm Taksitleri Göster
                                        <small class="form-text text-muted">
                                            İşaretlenirse, yönetici panelinde tanımlanan taksit durumları dikkate alınmaz.
                                        </small>
                                    </label>
                                    <div class="col-3">
                                        <span class="switch">
                                            <label>
                                                <input type="checkbox"
                                                name="show_all_installments"
                                                id="show_all_installments"
                                                {{ $current_account->show_all_installments ? 'checked' : '' }}>
                                                <span></span>
                                            </label>
                                        </span>
                                    </div>

                                    <label class="col-3 col-form-label"
                                        for="max_installment">
                                        Maksimum Taksit
                                        <small class="form-text text-muted">
                                            Kullanıcıların ödeme sırasında seçebileceği en yüksek taksit sayısı.
                                        </small>
                                    </label>
                                    <div class="col-3">
                                        <select name="max_installment"
                                                class="form-control form-control-sm">
                                            @foreach(range(2,12) as $i)
                                                <option value="{{ $i }}"
                                                    {{ $current_account->max_installment == $i ? 'selected' : '' }}>
                                                    {{ $i.' Taksit' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-3 col-form-label" for="can_collect_payments">
                                    Tahsilata İzin Ver
                                </label>
                                <div class="col-3">
                                    <span class="switch">
                                        <label>
                                            <input type="checkbox"
                                                name="can_collect_payments"
                                                id="can_collect_payments"
                                                {{ $current_account->can_collect_payments ? 'checked' : '' }}>
                                            <span></span>
                                        </label>
                                    </span>
                                </div>

                                <label class="col-3 col-form-label" for="can_collect_payments">
                                    Minimum Stok
                                </label>
                                <div class="col-3">
                                    <input type="number"
                                        class="form-control form-control-sm"
                                        name="min_stock_quantity"
                                        id="min_stock_quantity"
                                        value="{{ $current_account->min_stock_quantity }}">
                                    <small class="text-danger">Girilen değerin üzerindeki stoklara sahip ürünler listelenir.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-12 col-xl-3 layout-spacing">
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>Durumu</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" name="status" form="dealer-form" {{ $current_account->status ? 'checked' : '' }}>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" id="status-label-text"> {{ $current_account->status ? 'Aktif' : 'Pasif' }}</label>
                        </div>
                    </div>
                </div>
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>Giriş Engeli</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" name="block_entry" form="dealer-form" {{ $current_account->block_entry == 1 ? 'checked' : '' }}>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" id="status-label-text">{{ $current_account->block_entry == 1 ? 'Aktif' : 'Pasif' }}</label>
                        </div>
                    </div>
                </div>
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>Ödeme Yöntemleri</h4>
                    </div>
                    @php
                        $selectedMethods = $current_account->allowed_payment_methods ? explode(',', $current_account->allowed_payment_methods) : [];
                    @endphp
                    <div class="widget-content widget-content-area">
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" id="cash" name="allowed_payment_methods[]" value="cash" {{ in_array('cash', $selectedMethods) ? 'checked' : '' }} form="dealer-form">
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" for="cash">Nakit</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" id="credit" name="allowed_payment_methods[]" value="credit" {{ in_array('credit', $selectedMethods) ? 'checked' : '' }} form="dealer-form">
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" for="credit">Kredi Kartı</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" id="term" name="allowed_payment_methods[]" value="term" {{ in_array('term', $selectedMethods) ? 'checked' : '' }} form="dealer-form">
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" for="term">Vadeli</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        var categories = @json($categories);
        $(function () {
            // category_input
            var category_input = document.querySelector('input[name="hide_category_ids"]'),
            tagify = new Tagify(category_input, {
                enforceWhitelist : true,
                delimiters : null,
                whitelist: categories.map(category => ({ value: category.name, id: category.id })),
                dropdown: {
                    position: "input",
                    enabled : 0
                }
            });
            @if (isset($current_account) && $current_account->hide_category_ids != null)
                tagify.addTags([
                    @foreach ($categoryIds as $id)
                        @if(isset($categories[$id]))
                            {value: "{{ $categories[$id]->name }}", id:"{{ $id }}"},
                        @endif
                    @endforeach
                ]);
            @endif
            // hidden_product_prefixes
            var hidden_product_prefixes = document.querySelector('input[name="hidden_product_prefixes"]'),
            hidden_product_prefixes_tagify = new Tagify(hidden_product_prefixes, {});
            @if (isset($current_account) && $current_account->hidden_product_prefixes != NULL)
                hidden_product_prefixes_tagify.addTags([
                    @foreach (explode(',', $current_account->hidden_product_prefixes) as $prefix)
                        {value: "{{ $prefix }}" },
                    @endforeach
                ]);
            @endif
        });
        $('input[name="is_order_closed"]').change(function() {
            if ($(this).is(':checked')) {
                $('#is-order-closed-div').show();
            } else {
                $('#is-order-closed-div').hide();
            }
        });
    </script>
@endsection
