@extends('backend.layouts.app')

@section('title', 'Kullanıcılar')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Ayarlar'],
            ['url' => route('admin.settings.users.index'), 'label' => 'Kullanıcılar'],
        ]">
        <li class="nav-item">
            <a href="{{ route('admin.settings.users.create') }}" class="btn btn-info dash-btn">
                <i class="las la-plus"></i> Yeni
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-lg-12">
                <div class="statbox widget box box-shadow">
                    <div class="card-box">
                        <div class="row">
                            <div class="col-sm-12 col-md-4 filtered-list-search align-self-center">
                                <form class="form-group" method="GET" accept-charset="utf-8">
                                    <div class="input-group">
                                        <x-backend.input id="name" type="text" :value="request()->get('name')" placeholder="Adı, soyadı, e-posta adresi, kullanıcı adı.." />
                                        <div class="input-group-append">
                                            <button class="btn btn-soft-info" type="submit" id="filter-form">Ara</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-sm-12 col-md-4 align-self-center">
                                @if (!empty($_SERVER['QUERY_STRING']))
                                    <a href="javascript:;" class="btn btn-danger mr-1" onclick="removeFiltersFromURL()">Filtreleri Temizle</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="statbox widget box box-shadow">
                    <div class="widget-header">
                        <h4>Kullanıcılar</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-left">ID</th>
                                        <th>Adı Soyadı</th>
                                        <th>E-posta Adresi</th>
                                        <th class="text-center">Kullanıcı Adı</th>
                                        <th class="text-center">Durumu</th>
                                        <th class="text-center">Giriş Engeli</th>
                                        <th class="text-center">Son Giriş Tarihi</th>
                                        <th class="no-content" style="width: 150px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        <tr id="parent-{{ $item->id }}">
                                            <td class="text-left">{{ $item->id }}</td>
                                            <td>{{ $item->name . ' ' . $item->surname }}</td>
                                            <td>{{ $item->email }}</td>
                                            <td class="text-center">{{ $item->username }}</td>
                                            <td class="text-center">
                                                <x-backend.status-badge type="active_passive" :value="$item->status" />
                                            </td>
                                            <td class="text-center">
                                                <x-backend.status-badge type="active_passive" :value="$item->block_entry" />
                                            </td>
                                            <td class="text-center">{{ $item->last_login_date != null ? format_date_time($item->last_login_date) : '-' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.settings.users.edit', [$item->id]) }}" title="Düzenle" class="btn btn-info font-15 p-2"><i class="las la-edit"></i></a>
                                                <a href="javascript:;" class="btn btn-danger font-15 p-2" data-selector="row-delete" data-url="{{ route('admin.settings.users.destroy', [$item->id]) }}" title="Sil"><i class="las la-trash"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="11">Kullanıcı yok.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <x-backend.pagination :paginator="$items" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
