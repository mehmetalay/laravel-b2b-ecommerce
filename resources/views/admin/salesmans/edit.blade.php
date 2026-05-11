@extends('admin.layouts.app')

@section('title', 'Düzenle')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Plasiyerler'],
            ['url' => route('admin.salesmans.edit', $salesman->id), 'label' => 'Düzenle'],
        ]">
        <li class="nav-item">
            <button type="submit" class="btn btn-success dash-btn mr-2" form="salesman-form" data-ajax-submit>
                <i class="las la-save"></i> Kaydet
            </button>
            <a href="{{ route('admin.salesmans.index') }}" class="btn btn-info dash-btn">
                <i class="las la-list"></i> Listeye Dön
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing switch-outer-container">
            <div class="col-12 col-xl-9 layout-spacing">
                <form action="{{ route('admin.salesmans.update', [$salesman->id]) }}" method="POST" id="salesman-form" data-ajax-form>
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
                                        <x-backend.input id="name" label="Plasiyer Adı Soyadı" type="text" :value="$salesman->name" :required="true" autofocus/>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="email" label="E-posta Adresi" type="text" :value="$salesman->email" :required="true"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="phone" label="Telefon Numarası" type="text" :value="$salesman->phone" :required="true"/>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="code" label="Kodu" type="text" :value="$salesman->code" :required="true"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="password" label="Şifre Değiştir" type="text"/>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="access_type">Erişim Tipi <span class="text-danger">*</span></label>
                                        <select name="access_type" id="access_type" class="selectpicker w-100" data-live-search="true" title="Seç">
                                            <option value="all_customers" {{ $salesman->access_type === 'all_customers' ? 'selected' : '' }}>Tüm Bayiler</option>
                                            <option value="specific_code" {{ $salesman->access_type === 'specific_code' ? 'selected' : '' }}>Plasiyer Koduna Göre</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="hide_category_ids">İzin Verilmeyen Kategori Adları</label>
                                        <input name="hide_category_ids" id="hide_category_ids" class="form-control tagify--outside" placeholder="Kategori adı yaz...">
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
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
                                        <input type="checkbox" name="status" form="salesman-form" {{ $salesman->status ? 'checked' : '' }}>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" id="status-label-text"> {{ $salesman->status ? 'Aktif' : 'Pasif' }}</label>
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
                                        <input type="checkbox" name="block_entry" form="salesman-form" {{ $salesman->block_entry == 1 ? 'checked' : '' }}>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" id="status-label-text">{{ $salesman->block_entry == 1 ? 'Aktif' : 'Pasif' }}</label>
                        </div>
                    </div>
                </div>
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>Ayarlar</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" name="report_access" form="salesman-form" {{ $salesman->report_access ? 'checked' : '' }}>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" id="status-label-text">Raporlara Erişim</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" name="show_all_installments" form="salesman-form" {{ $salesman->show_all_installments ? 'checked' : '' }}>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" id="status-label-text">Tüm Taksitleri Göster</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" name="can_edit_price" form="salesman-form" {{ $salesman->can_edit_price ? 'checked' : '' }}>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" id="status-label-text">Fiyat Düzenlenebilir</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" name="can_edit_discount" form="salesman-form" {{ $salesman->can_edit_discount ? 'checked' : '' }}>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" id="status-label-text">İndirim Düzenlenebilir</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" name="hide_all_stock_quantities" form="salesman-form" {{ $salesman->hide_all_stock_quantities ? 'checked' : '' }}>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" id="status-label-text">Tüm Stokları Gizle</label>
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
            @if (isset($salesman) && $salesman->hide_category_ids != null)
                tagify.addTags([
                    @foreach ($categoryIds as $id)
                        @if(isset($categories[$id]))
                            {value: "{{ $categories[$id]->name }}", id:"{{ $id }}"},
                        @endif
                    @endforeach
                ]);
            @endif
        });
    </script>
@endsection
