@extends('backend.layouts.app')

@section('title', 'Genel Bilgiler')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Ayarlar'],
            ['url' => route('admin.settings.general-infos.index'), 'label' => 'Genel Bilgiler'],
        ]">
        <li class="nav-item">
            <button type="submit" class="btn btn-success dash-btn" form="info-form" data-ajax-submit>
                <i class="las la-save"></i> Kaydet
            </button>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <form action="{{ route('admin.settings.general-infos.update', [$general_info->id]) }}" method="POST" id="info-form" data-ajax-form>
            @csrf
            @method('PATCH')
            <div class="row layout-top-spacing switch-outer-container">
                <div class="col-12 layout-spacing">
                    <div class="statbox widget box box-shadow">
                        <div class="widget-header">
                            <h4>Genel Bilgiler</h4>
                        </div>
                        <div class="widget-content widget-content-area pills-vertical-line">
                            <div class="form-group">
                                <x-backend.input id="company_name" label="Firma Adı" type="text" :value="$general_info->company_name" autofocus/>
                            </div>
                            <div class="form-group">
                                <x-backend.input id="company_official_name" label="Firma Resmi Adı" type="text" :value="$general_info->company_official_name"/>
                            </div>
                            <div class="form-group">
                                <x-backend.input id="company_website" label="Firma İnternet Sitesi" type="text" :value="$general_info->company_website"/>
                            </div>
                            <div class="form-group">
                                <x-backend.input id="authorized_person" label="Yetkili Kişi" type="text" :value="$general_info->authorized_person"/>
                            </div>
                            <div class="form-group">
                                <x-backend.input id="company_phone_number" label="Firma Telefon Numarası" type="text" :value="$general_info->company_phone_number"/>
                            </div>
                            <div class="form-group">
                                <x-backend.input id="company_phone_number_2" label="Firma Telefon Numarası 2" type="text" :value="$general_info->company_phone_number_2"/>
                            </div>
                            <div class="form-group">
                                <x-backend.input id="company_mobile_number" label="Firma Cep Numarası" type="text" :value="$general_info->company_mobile_number"/>
                            </div>
                            <div class="form-group">
                                <x-backend.input id="fax_number" label="Faks Numarası" type="text" :value="$general_info->fax_number"/>
                            </div>
                            <div class="form-group">
                                <x-backend.input id="email_address" label="E-posta Adresi" type="text" :value="$general_info->email_address"/>
                            </div>
                            <div class="form-group">
                                <x-backend.input id="email_address_2" label="E-posta Adresi 2" type="text" :value="$general_info->email_address_2"/>
                            </div>
                            <div class="form-group">
                                <x-backend.input id="company_full_address" label="Firma Açık Adresi" type="text" :value="$general_info->company_full_address"/>
                            </div>
                            <div class="form-group">
                                <x-backend.input id="google_maps_link" label="Google Maps Link" type="text" :value="$general_info->google_maps_link"/>
                            </div>
                            <div class="form-group">
                                <x-backend.textarea name="google_maps_embed" label="Google Maps Embed Code" rows="2" :value="$general_info->google_maps_embed" />
                            </div>
                            <div class="form-group">
                                <x-backend.input id="seo_meta_title" label="SEO Meta Title" type="text" :value="$general_info->seo_meta_title"/>
                            </div>
                            <div class="form-group">
                                <x-backend.input id="seo_meta_description" label="SEO Meta Description" type="text" :value="$general_info->seo_meta_description"/>
                            </div>
                            <div class="form-group">
                                <x-backend.input id="seo_meta_keywords" label="SEO Meta Keywords" type="text" :value="$general_info->seo_meta_keywords"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
