@extends('admin.layouts.app')

@section('title', 'Kampanyalar')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Kampanyalar']
        ]">
        <li class="nav-item">
            <a href="{{ route('admin.campaigns.create') }}" class="btn btn-info dash-btn">
                <i class="las la-plus"></i> Yeni
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div class="statbox widget box box-shadow">
                    <div class="widget-header">
                        <h4>Kampanyalar</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Kampanya Adı</th>
                                        <th class="text-center">Tarih Filtresi</th>
                                        <th class="text-center">Durum</th>
                                        <th class="text-center">Oluşturulma Tarihi</th>
                                        <th class="no-content" style="width: 150px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        <tr id="parent-{{ $item->id }}">
                                            <td class="text-left">{{ $item->name }}</td>
                                            <td class="text-center">
                                                <x-backend.status-badge type="active_passive" :value="$item->use_date_filter" />
                                                @if ($item->use_date_filter == 1)
                                                    <div><small>{{ format_date_time($item->start_date, true)->isoFormat('D MMMM YYYY') . ' - ' . format_date_time($item->end_date, true)->isoFormat('D MMMM YYYY') }}</small></div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <x-backend.status-badge type="active_passive" :value="$item->status" />
                                            </td>
                                            <td class="text-center">{{ format_date_time($item->created_at) }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.campaigns.edit', [$item->id]) }}" title="Detay" class="btn btn-info font-15 p-2"><i class="las la-eye"></i></a>
                                                <a href="javascript:;" class="btn btn-danger font-15 p-2" data-selector="row-delete" data-url="{{ route('admin.campaigns.destroy', $item->id) }}" title="Sil"><i class="las la-trash"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="5">Veri yok.</td>
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
