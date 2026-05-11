@extends('admin.layouts.app')

@php
    $placeholders = [
        'musteri_fatura_unvani' => 'Müşteri Fatura Ünvanı',
        'musteri_fatura_adresi' => 'Müşteri Fatura Adresi',
        'telefon' => 'Telefon',
        'faks' => 'Faks',
        'ticaret_sicil_no' => 'Ticaret Sicil No',
        'vergi_dairesi' => 'Vergi Dairesi',
        'vergi_no' => 'Vergi No',
        'firma_yetkilisi' => 'Firma Yetkilisi',
        'mobil_telefon_numarasi' => 'Mobil Telefon Numarası',
        'e_posta_adresi' => 'E-Posta Adresi',
        'satin_alma_yetkilisi' => 'Satınalma Yetkilisi',
        'satin_alma_mobil_telefon_numarasi' => 'Satın Alma Mobil Telefon Numarası',
        'satin_alma_e_posta_adresi' => 'Satın Alma E-Posta Adresi',
        'odeme_yetkilisi' => 'Ödeme Yetkilisi',
        'odeme_yetkilisi_mobil_telefon_numarasi' => 'Ödeme Yetkilisi Mobil Telefon Numarası',
        'odeme_yetkilisi_e_posta_adresi' => 'Ödeme Yetkilisi E-Posta Adresi',
        'muhasebe_yetkilisi' => 'Muhasebe Yetkilisi',
        'muhasebe_yetkilisi_mobil_telefon_numarasi' => 'Muhasebe Yetkilisi Mobil Telefon Numarası',
        'muhasebe_yetkilisi_e_posta_adresi' => 'Muhasebe Yetkilisi E-Posta Adresi',
        'aylik_odeme_gunleri' => 'Aylık Ödeme Günleri',
        'imza_tarihi' => 'İmza Tarihi',
        'banka_hesaplari_tablosu' => 'Banka Hesapları Tablosu',
        'email_listesi' => 'E-Posta Listesi',
        'gsm_listesi' => 'GSM Listesi',
        'sevk_depo_tablosu' => 'Sevk Depo Tablosu',
    ];
@endphp

@section('title', 'Yeni')

@section('content')
    <x-backend.breadcrumb :items="[
        ['url' => 'javascript:;', 'label' => 'Sözleşme Yönetimi'],
        ['url' => route('admin.contracts.templates.index'), 'label' => 'Sözleşme Şablonları'],
        ['url' => 'javascript:;', 'label' => 'Yeni'],
    ]">
        <li class="nav-item">
            <button type="submit" class="btn btn-success dash-btn mr-2" form="template-form" data-ajax-submit>
                <i class="las la-save"></i> Kaydet
            </button>
            <a href="{{ route('admin.contracts.templates.index') }}" class="btn btn-info dash-btn">
                <i class="las la-list"></i> Listeye Dön
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing switch-outer-container">
            <div class="col-12 col-xl-9 layout-spacing">
                <form action="{{ route('admin.contracts.templates.store') }}" method="POST" id="template-form"
                    data-ajax-form>
                    @csrf
                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>Yeni Sözleşme</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="form-group">
                                <label class="col-form-label" for="title">Başlık <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label" for="dealer_type">Sözleşme Türü <span
                                        class="text-danger">*</span></label>
                                <select name="dealer_type" id="dealer_type" class="selectpicker w-100"
                                    data-live-search="true" title="Seç">
                                    <option value="dealer">Bayi</option>
                                    <option value="subdealer">Alt Bayi</option>
                                    <option value="all">Tümü</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label" for="content">Sözleşme Metni (HTML destekli) <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" id="content" name="content" data-editor="tinymce-6.7.0"></textarea>
                            </div>
                            <div class="mt-3">
                                <div class="alert alert-secondary">
                                    <strong>Kullanılabilir değişkenler:</strong>
                                    <div class="mt-2 d-flex flex-wrap" style="gap: .5rem;">
                                        @foreach ($placeholders as $key => $label)
                                            <button type="button" class="btn btn-outline-secondary placeholder-btn"
                                                data-placeholder="[{{ $key }}]">
                                                {{ $label }} <code>[{{ $key }}]</code>
                                            </button>
                                        @endforeach
                                    </div>
                                    <div class="mt-2">
                                        <small>Butonlara tıklayarak placeholder’ı editöre ekleyebilir veya köşeli parantezli
                                            haliyle kopyalayıp yapıştırabilirsiniz.</small>
                                    </div>
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
                                        <input type="checkbox" name="is_active" checked form="template-form">
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" id="status-label-text">Aktif</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.placeholder-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var ph = this.getAttribute('data-placeholder');

                    if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
                        tinymce.activeEditor.execCommand('mceInsertContent', false, ph);
                        tinymce.activeEditor.focus();
                    } else {
                        var textarea = document.getElementById('editor');
                        if (textarea) {
                            textarea.value += ph;
                            textarea.focus();
                        }
                    }
                });
            });
        });
    </script>
@endsection
