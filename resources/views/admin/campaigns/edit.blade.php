@extends('admin.layouts.app')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => route('admin.campaigns.index'), 'label' => 'Kampanyalar'],
            ['url' => 'javascript:;', 'label' => 'Düzenle']
        ]">
        <li class="nav-item">
            <button type="submit" class="btn btn-success dash-btn mr-2" form="campaign-form" data-ajax-submit>
                <i class="las la-save"></i> Kaydet
            </button>
            <a href="{{ route('admin.campaigns.index') }}" class="btn btn-info dash-btn">
                <i class="las la-list"></i> Listeye Dön
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing switch-outer-container">
            <div class="col-12 col-xl-9 layout-spacing">
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>Düzenle</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="form-group">
                            <x-backend.input id="name" label="Kampanya Adı" type="text" :value="$campaign->name" :required="true" autofocus form="campaign-form"/>
                        </div>
                        <div class="form-group">
                            <label for="general_description">Genel Açıklaması</label>
                            <textarea name="general_description" id="general_description" class="form-control" rows="3" form="campaign-form" placeholder="Ürün detay sayfasında görünecek kampanya açıklaması (opsiyonel)">{{ old('general_description', $campaign->general_description) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="campaign-type">Kampanya Tipi</label>
                                    <select name="type" id="campaign-type" class="form-control" form="campaign-form">
                                        <option value="" hidden>SEÇ</option>
                                        <option value="product" {{ $campaign->type === 'product' ? 'selected' : '' }}>Ürün Bazlı</option>
                                        <option value="brand" {{ $campaign->type === 'brand' ? 'selected' : '' }}>Marka Bazlı</option>
                                        <option value="category" {{ $campaign->type === 'category' ? 'selected' : '' }}>Kategori Bazlı</option>
                                        <option value="cart" {{ $campaign->type === 'cart' ? 'selected' : '' }}>Sepet Bazlı</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group {{ $campaign->type !== 'product' ? 'd-none' : '' }}" id="subtype-wrapper">
                                    <label for="campaign-sub-type">Ürün Bazlı Kampanya Tanımı</label>
                                    <select name="sub_type" id="campaign-sub-type" class="form-control" form="campaign-form">
                                        <option value="" hidden>SEÇ</option>
                                        <option value="tiered_price" {{ $campaign->sub_type === 'tiered_price' ? 'selected' : '' }}>Ürün İndirim Kampanyası</option>
                                        <option value="free_product" {{ $campaign->sub_type === 'free_product' ? 'selected' : '' }}>Hediye Ürün Kampanyası</option>
                                        <option value="free_shipping" {{ $campaign->sub_type === 'free_shipping' ? 'selected' : '' }}>Bedelsiz Nakliye Kampanyası</option>
                                        {{-- <option value="bonus_product" {{ $campaign->sub_type === 'bonus_product' ? 'selected' : '' }}>Adet Bazlı Bonus Ürün Kampanyası</option> --}}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="campaign-partial-area" class="mt-4"></div>

                        <div class="row">
                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="use_date_filter">Tarih Filtresini Kullan</label>
                                    <span class="switch">
                                        <label>
                                            <input type="checkbox" name="use_date_filter" id="use_date_filter" data-js="campaign-use-date-filter" {{ $campaign->use_date_filter ? 'checked' : '' }} form="campaign-form">
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 col-xl-5">
                                <div id="date-fields" data-js="campaign-date-fields" style="display: {{ $campaign->use_date_filter ? 'block' : 'none' }}">
                                    <div class="form-group">
                                        <label for="start_date">Kampanya Tarihi (İlk ve Son)</label>
                                        <div class="input-group">
                                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $campaign->start_date }}" form="campaign-form">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">/</span>
                                            </div>
                                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $campaign->end_date }}" form="campaign-form">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                                        <input type="checkbox" name="status" {{ $campaign->status ? 'checked' : '' }} form="campaign-form">
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" id="status-label-text">{{ $campaign->status ? 'Aktif' : 'Pasif' }}</label>
                        </div>
                    </div>
                </div>
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>Otomatik Uygula</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" name="auto_apply" form="campaign-form" {{ $campaign->auto_apply ? 'checked' : '' }}>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" id="status-label-text">{{ $campaign->auto_apply ? 'Aktif' : 'Pasif' }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form action="{{ route('admin.campaigns.update', [$campaign->id]) }}" method="POST" id="campaign-form" data-ajax-form>
        @csrf
        @method('PUT')
    </form>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const typeSelect = document.getElementById('campaign-type');
            const subTypeSelect = document.getElementById('campaign-sub-type');
            const partialArea = document.getElementById('campaign-partial-area');

            function loadPartial(subType) {
                partialArea.innerHTML = '<p>Yükleniyor...</p>';

                fetch(`/aka/campaigns/partials/${subType}?campaign_id={{ $campaign->id }}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            partialArea.innerHTML = data.html;
                        } else {
                            partialArea.innerHTML = `<p class="text-danger">${data.message}</p>`;
                        }
                    })
                    .catch(() => {
                        partialArea.innerHTML = '<p class="text-danger">Bir hata oluştu.</p>';
                    });
            }

            if (subTypeSelect.value) {
                loadPartial(subTypeSelect.value);
            }

            subTypeSelect.addEventListener('change', function () {
                loadPartial(this.value);
            });

            typeSelect.addEventListener('change', function () {
                const wrapper = document.getElementById('subtype-wrapper');
                if (this.value === 'product') {
                    wrapper.classList.remove('d-none');
                } else {
                    wrapper.classList.add('d-none');
                }
            });
        });
    </script>
@endsection
