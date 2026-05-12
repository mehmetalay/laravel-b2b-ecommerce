@extends('backend.layouts.app')

@section('title', 'Sözleşme Şablonları')

@section('content')
    <x-backend.breadcrumb :items="[
        ['url' => 'javascript:;', 'label' => 'Sözleşme Yönetimi'],
        ['url' => route('admin.contracts.templates.index'), 'label' => 'Sözleşme Şablonları'],
    ]">
        <li class="nav-item">
            <a href="{{ route('admin.contracts.templates.create') }}" class="btn btn-info dash-btn">
                <i class="las la-plus"></i> Yeni
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div class="statbox widget box box-shadow">
                    <div class="widget-header">
                        <h4>Sözleşme Şablonları</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-left">ID</th>
                                        <th class="text-left">Başlık</th>
                                        <th class="text-center">Versiyon</th>
                                        <th class="text-center">Tür</th>
                                        <th class="text-center">Durumu</th>
                                        <th class="text-center">Oluşturulma Tarihi</th>
                                        <th class="no-content" style="width: 150px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($templates as $item)
                                        <tr id="parent-{{ $item->id }}">
                                            <td>{{ $item->id }}</td>
                                            <td class="text-left">{{ $item->title }}</td>
                                            <td class="text-center">{{ 'V' . $item->version }}</td>
                                            <td class="text-center">{{ strtoupper($item->dealer_type_label) }}</td>
                                            <td class="text-center"><span
                                                    class="badge {{ $item->is_active == 1 ? 'badge-success' : 'badge-danger' }}">{{ $item->is_active == 1 ? 'Aktif' : 'Pasif' }}</span>
                                            </td>
                                            <td class="text-center">{{ format_date_time($item->created_at) }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.contracts.templates.edit', [$item->id]) }}"
                                                    title="Düzenle" class="btn btn-info font-15 p-2"><i
                                                        class="las la-edit"></i></a>
                                                <a href="javascript:;" class="btn btn-danger font-15 p-2"
                                                    data-selector="row-delete"
                                                    data-url="{{ route('admin.contracts.templates.destroy', [$item->id]) }}"
                                                    title="Sil">
                                                    <i class="las la-trash"></i>
                                                </a>
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
                        {{ $templates->appends(request()->except('page'))->links('pagination::admin-bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
