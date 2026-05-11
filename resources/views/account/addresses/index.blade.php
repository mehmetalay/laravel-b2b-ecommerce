@extends('layouts.app')

@section('content')
    <section class="section-b-space">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="title">
                        <h2>Adreslerim</h2>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-4 col-xl-3 mb-3">
                            <a href="javascript:;" data-action="add-address" data-title="Yeni Adres Ekle" class="btn btn-animation btn-sm w-50">
                                + Yeni Adres Ekle
                            </a>
                        </div>
                    </div>

                    <div class="row g-3" id="address-card-list" data-js="address-card-list">
                        @foreach($addresses as $address)
                            <div class="col-md-4" data-js="address-card" data-address-id="{{ $address->id }}">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $address->title }}</h5>

                                        <p class="card-text small text-muted">
                                            {{ $address->company_name }}<br>
                                            {{ $address->address }}<br>
                                            {{ $address->city->name }}
                                            / {{ $address->district->name }}
                                            / {{ $address->neighborhood->name }}
                                        </p>

                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-secondary text-white"
                                                    data-action="edit-address"
                                                    data-id="{{ $address->id }}">
                                                Düzenle
                                            </button>

                                            <button class="btn btn-sm btn-danger text-white"
                                                    data-action="delete-address"
                                                    data-id="{{ $address->id }}">
                                                Sil
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
@endpush

