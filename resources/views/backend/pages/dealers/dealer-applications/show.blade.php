@extends('backend.layouts.app')

@section('title', 'Başvuru Detayı')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => route('admin.dealer-application.index'), 'label' => 'Bayi Başvuruları'],
            ['url' => route('admin.dealer-application.show', $dealer_application->id), 'label' => 'Başvuru Detayı']
        ]">
        <li class="nav-item">
            <a href="{{ route('admin.dealer-application.index') }}" class="btn btn-info dash-btn">
                <i class="las la-list"></i> Listeye Dön
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing switch-outer-container">
            <div class="col-12 layout-spacing">
                <div class="widget-content">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card-box">
                                <h5 class="card-title">Firma Bilgileri</h5>
                                <p class="card-text">Firma Adı: <span class="text-muted">{{ $dealer_application->company_name }}</span></p>
                                <p class="card-text">Vergi Dairesi: <span class="text-muted">{{ $dealer_application->tax_office }}</span></p>
                                <p class="card-text">Vergi Numarası: <span class="text-muted">{{ $dealer_application->tax_number }}</span></p>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card-box">
                                <h5 class="card-title">Adres Bilgileri</h5>
                                <p class="card-text">Adres: <span class="text-muted">{{ $dealer_application->address }}</span></p>
                                <p class="card-text">İlçe: <span class="text-muted">{{ $dealer_application->district }}</span></p>
                                <p class="card-text">Sehir: <span class="text-muted">{{ $dealer_application->city }}</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card-box">
                                <h5 class="card-title">İletişim Bilgileri</h5>
                                <p class="card-text">Telefon Numarası: <span class="text-muted">{{ $dealer_application->phone_number ?? '-' }}</span></p>
                                <p class="card-text">Cep Telefonu Numarası: <span class="text-muted">{{ $dealer_application->mobile_phone_number ?? '-' }}</span></p>
                                <p class="card-text">Faks Numarası: <span class="text-muted">{{ $dealer_application->fax_number ?? '-' }}</span></p>
                                <p class="card-text">E-Posta Adresi: <span class="text-muted">{{ $dealer_application->email_address ?? '-' }}</span></p>
                                <p class="card-text">Web Adresi: <span class="text-muted">{{ $dealer_application->web_address ?? '-' }}</span></p>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card-box">
                                <h5 class="card-title">Kişi Bilgileri</h5>
                                <p class="card-text">Yetkili Ad Soyad: <span class="text-muted">{{ $dealer_application->authorized_name_surname }}</span></p>
                                <p class="card-text">T.C. Kimlik Numarası: <span class="text-muted">{{ $dealer_application->identity_number ?? '-' }}</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-box">
                        <h5 class="card-title">Evraklar</h5>
                        @forelse ($dealer_application->documents as $key => $document)
                            <p class="card-text">Dosya {{ ($key + 1) }}: <span class="text-muted"><a href="{{ route('admin.dealer-application.download.document', ['path' => $document->path]) }}"><u>İndir</u></a></span></p>
                        @empty
                            <p class="card-text text-muted">Evrak yüklemedi.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
