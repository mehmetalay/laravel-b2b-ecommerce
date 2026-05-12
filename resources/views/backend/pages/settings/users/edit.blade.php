@extends('backend.layouts.app')

@section('title', 'Düzenle')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Ayarlar'],
            ['url' => route('admin.settings.users.index'), 'label' => 'Kullanıcılar'],
            ['url' => route('admin.settings.users.edit', [$user->id]), 'label' => 'Düzenle'],
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
                <form action="{{ route('admin.settings.users.update', [$user->id]) }}" method="POST" id="user-form" data-ajax-form>
                    @csrf
                    @method('PATCH')
                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>Düzenle: "{{ $user->name . ' ' . $user->surname }}"</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="name" label="Adı" type="text" :value="$user->name" :required="true" autofocus/>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="surname" label="Soyadı" type="text" :value="$user->surname" :required="true"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="email" label="E-posta Adresi" type="text" :value="$user->email" :required="true"/>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="username" label="Kullanıcı Adı" type="text" :value="$user->username" :required="true" onkeyup="if (/[^|a-z0-9]+/g.test(this.value)) this.value = this.value.replace(/[^|a-z0-9]+/g,'')"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="password" label="Şifre Değiştir" type="text"/>
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
                                @foreach ($permissions as $permission)
                                    <label class="col-3 col-form-label" for="permission-{{ $permission->id }}">{{ $permission->name }}</label>
                                    <div class="col-3">
                                        <span class="switch">
                                            <label>
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission-{{ $permission->id }}" {{ $user->permissions->contains($permission->id) ? 'checked' : '' }}>
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
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" name="status" form="user-form" {{ $user->status == 1 ? 'checked' : '' }}>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" id="status-label-text">{{ $user->status == 1 ? 'Aktif' : 'Pasif' }}</label>
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
                                        <input type="checkbox" name="block_entry" form="user-form" {{ $user->block_entry == 1 ? 'checked' : '' }}>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" id="status-label-text">{{ $user->block_entry == 1 ? 'Aktif' : 'Pasif' }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
