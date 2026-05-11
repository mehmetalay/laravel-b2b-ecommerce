@extends('admin.layouts.app')

@section('title', 'Cari Hesaplar')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Cari Hesaplar'],
        ]">
        <li class="nav-item">
            <a href="javascript:;" class="btn btn-info dash-btn" data-action="erp-import" data-url="/aka/current-accounts/import">
                <i class="las la-download"></i> İçeri Aktar
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-content widget-content-area br-6">
                        <div class="row">
                            <div class="col-sm-12 col-md-4 filtered-list-search align-self-center">
                                <form class="form-group" method="GET" accept-charset="utf-8">
                                    <div class="input-group">
                                        <x-backend.input id="name" type="text" :value="request()->get('name')" placeholder="Cari adı, kodu, e-posta adresi, il, ilçe.." />
                                        <div class="input-group-append">
                                            <button class="btn btn-soft-info" type="submit" id="filter-form">Ara</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-sm-12 col-md-4 align-self-center">
                                @if (request()->query())
                                    <x-backend.filter-clear-button :route="route('admin.current-accounts.index')"/>
                                @endif
                            </div>
                            <div class="col-sm-12 col-md-4 text-sm-right text-center align-self-center"></div>
                        </div>
                    </div>
                </div>
                <div class="statbox widget box box-shadow">
                    <div class="widget-header">
                        <h4>Cari Hesaplar</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-left">Cari</th>
                                        <th class="text-center">E-posta Adresi</th>
                                        <th class="text-center">İl/İlçe</th>
                                        <th class="text-center">Firma Adı</th>
                                        <th class="text-center">Durumu</th>
                                        <th class="text-center">Giriş Engeli</th>
                                        <th class="text-center">Son Giriş Tarihi</th>
                                        <th class="no-content" style="width: 150px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        <tr id="parent-{{ $item->id }}">
                                            <td>
                                                {{ $item->name }}
                                                <div>
                                                    <small class="text-muted">{{ $item->code }}</small>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $item->email != null ? $item->email : '-' }}</td>
                                            <td class="text-center">{{ $item->province . ($item->province && $item->district ? ' /' : '') . ($item->district ? ' ' . $item->district : '') }}</td>
                                            <td class="text-center">{{ $item->company ? $item->company->name : 'Seçilmedi' }}</td>
                                            <td class="text-center">
                                                <x-backend.status-badge type="active_passive" :value="$item->status" />
                                            </td>
                                            <td class="text-center">
                                                <x-backend.status-badge type="active_passive" :value="$item->block_entry" />
                                            </td>
                                            <td class="text-center">{{ $item->last_login_date ? format_date_time($item->last_login_date) : '-' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.current-accounts.edit', [$item->id]) }}" title="Düzenle" class="btn btn-info font-15 p-2"><i class="las la-edit"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="11">Veri yok.</td>
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

@push('scripts')
    <script src="{{ mix('js/admin/modules/import/erp-import.js') }}"></script>
@endpush
