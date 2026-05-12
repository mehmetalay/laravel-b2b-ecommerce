@extends('backend.layouts.app')

@section('title', 'Yeni')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Ayarlar'],
            ['url' => route('admin.settings.users.index'), 'label' => 'Kullanıcılar'],
            ['url' => route('admin.settings.users.create'), 'label' => 'Yeni'],
        ]">
        <li class="nav-item">
            <button type="submit" class="btn btn-success dash-btn mr-2" form="user-form" data-ajax-submit>
                <i class="las la-save"></i> Kaydet
            </button>
            <a href="{{ route('admin.settings.users.index') }}" class="btn btn-info dash-btn">
                <i class="las la-list"></i> Listeye Dön
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing switch-outer-container">
            <div class="col-12 col-xl-9 layout-spacing">
                <form action="{{ route('admin.settings.users.store') }}" method="POST" id="user-form" data-ajax-form>
                    @csrf
                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>Kullanıcı Ekle</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="name" label="Adı" type="text" :required="true" autofocus/>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="surname" label="Soyadı" type="text" :required="true"/>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="email" label="E-posta Adresi" type="text" :required="true"/>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="username" label="Kullanıcı Adı" type="text" :required="true" onkeyup="if (/[^|a-z0-9]+/g.test(this.value)) this.value = this.value.replace(/[^|a-z0-9]+/g,'')"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="password" label="Şifre" type="text" :required="true"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>İzinler</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="form-group row">
                                @foreach($permissions as $permission)
                                    <label class="col-3 col-form-label" for="permission-{{ $permission->id }}">{{ $permission->name }}</label>
                                    <div class="col-3">
                                        <span class="switch">
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission-{{ $permission->id }}">
                                                <span></span>
                                            </label>
                                        </span>
                                    </div>
                                @endforeach
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
                        <div class="w-100">
                            <div class="form-group row">
                                <div class="col-3">
                                    <span class="switch align-items-start">
                                        <label>
                                            <input type="checkbox" name="status" checked form="user-form">
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
    </div>
@endsection
