@extends('admin.layouts.app')

@section('title', 'Sözleşme İmza Kayıtları')

@section('content')
    <x-backend.breadcrumb :items="[
        ['url' => 'javascript:;', 'label' => 'Sözleşme Yönetimi'],
        ['url' => 'javascript:;', 'label' => 'Sözleşme İmza Kayıtları'],
    ]">
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-content widget-content-area br-6">
                        {{-- filtre --}}
                        <form action="{{ route('admin.contracts.signatures.index') }}" method="GET" id="search-form"
                            class="row">
                            <div class="col-sm-12 col-md-2">
                                <div class="form-group">
                                    <label class="col-form-label" for="name">Bayi & Alt Bayi Adı</label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        value="{{ request()->get('name') }}">
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-2">
                                <div class="form-group">
                                    <label class="col-form-label" for="actor_type">Kullanıcı Tipi</label>
                                    <select name="actor_type" id="actor_type" class="form-control"
                                        value="{{ request()->get('actor_type') }}">
                                        <option value="">Tümü</option>
                                        <option value="dealer"
                                            {{ request()->get('actor_type') == 'dealer' ? 'selected' : '' }}>Bayiler
                                        </option>
                                        <option value="subdealer"
                                            {{ request()->get('actor_type') == 'subdealer' ? 'selected' : '' }}>Alt Bayiler
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-2">
                                <div class="form-group">
                                    <label class="col-form-label" for="status">Durum</label>
                                    <select name="status" id="status" class="form-control"
                                        value="{{ request()->get('status') }}">
                                        <option value="">Tümü</option>
                                        <option value="verified"
                                            {{ request()->get('status') == 'verified' ? 'selected' : '' }}>Onaylandı
                                        </option>
                                        <option value="pending"
                                            {{ request()->get('status') == 'pending' ? 'selected' : '' }}>Bekliyor</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label" for="date_from">Tarih (İlk ve Son)</label>
                                    <div class="input-group">
                                        <input class="form-control" type="date" name="date_from" form="search-form"
                                            value="{{ request()->get('date_from', $firstDate) }}">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">/</span>
                                        </div>
                                        <input class="form-control" type="date" name="date_to" form="search-form"
                                            value="{{ request()->get('date_to', $lastDate) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-3">
                                <label class="col-form-label d-none d-md-block">&nbsp;</label>
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-primary mr-2">
                                        Ara
                                    </button>
                                    @if (request()->query())
                                        <a href="{{ route('admin.contracts.signatures.index') }}" class="btn btn-danger">
                                            Filtreleri Temizle
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="statbox widget box box-shadow">
                    <div class="widget-header">
                        <h4>Sözleşme İmza Kayıtları</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-left">Kullanıcı</th>
                                        <th class="text-center">Tip</th>
                                        <th class="text-center">Sözleşme</th>
                                        <th class="text-center">Durum</th>
                                        <th class="text-center">İmza Tarihi</th>
                                        <th class="text-center">IP Adresi</th>
                                        <th class="no-content" style="width: 150px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        <tr>
                                            <td>
                                                {{ $item->actor_name }}
                                                @if ($item->actor_type === 'subdealer')
                                                    <br>
                                                    <small class="text-muted">Bayi Adı: {{ $item->subdealer->dealer->name ?? '-' }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <x-backend.status-badge type="user_type" :value="$item->actor_type" />
                                            </td>
                                            <td class="text-center">
                                                {{ $item->template->title . ' (V' . $item->template->version . ')' }}</td>
                                            <td class="text-center"><span
                                                    class="badge {{ $item->status == 'verified' ? 'badge-success' : 'badge-secondary' }}">{{ $item->status == 'verified' ? 'Onaylandı' : 'Bekliyor' }}</span>
                                            </td>
                                            <td class="text-center">
                                                {{ $item->signed_at ? format_date_time($item->signed_at) : '-' }}</td>
                                            <td class="text-center">{{ $item->ip_address ?? '-' }}</td>
                                            <td class="text-center">
                                                @if ($item->pdf_path)
                                                    <a href="{{ asset($item->pdf_path) }}" target="_blank"
                                                        class="btn btn-danger font-15 p-1" title="PDF Görüntüle">
                                                        <i class="las la-file-pdf"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="6">Veri yok.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $items->appends(request()->except('page'))->links('pagination::admin-bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
