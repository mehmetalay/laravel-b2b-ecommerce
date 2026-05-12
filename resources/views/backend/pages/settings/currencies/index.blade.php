@extends('backend.layouts.app')

@section('title', 'Döviz Ayarları')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Ayarlar'],
            ['url' => route('admin.settings.currencies.index'), 'label' => 'Döviz Ayarları'],
        ]">
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-lg-12">
                <div class="statbox widget box box-shadow">
                    <div class="widget-header">
                        <h4>Döviz Ayarları</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-left">ID</th>
                                        <th>Döviz Kuru</th>
                                        <th class="text-center">Sembol</th>
                                        <th class="text-center">Alış Fiyatı</th>
                                        <th class="text-center">Satış Fiyatı</th>
                                        <th class="text-center">Manuel Kur</th>
                                        <th class="text-center">Alış Fiyatı (Manuel)</th>
                                        <th class="text-center">Satış Fiyatı (Manuel)</th>
                                        <th class="text-center">Durumu</th>
                                        <th class="text-center">Son Güncelleme</th>
                                        <th class="no-content" style="width: 150px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        <tr id="parent-{{ $item->id }}">
                                            <td class="text-left">{{ $item->id }}</td>
                                            <td>{{ $item->code }}</td>
                                            <td class="text-center">{{ $item->symbol }}</td>
                                            <td class="text-center">{{ number_format($item->buy, 2) }}</td>
                                            <td class="text-center">{{ number_format($item->sell, 2) }}</td>
                                            <td class="text-center">
                                                <x-backend.status-badge type="active_passive" :value="$item->manual_override" />
                                            </td>
                                            <td class="text-center">{{ $item->manual_override == 1 ? number_format($item->manual_buy, 2) : '-' }}</td>
                                            <td class="text-center">{{ $item->manual_override == 1 ? number_format($item->manual_sell, 2) : '-' }}</td>
                                            <td class="text-center">
                                                <x-backend.status-badge type="active_passive" :value="$item->status" />
                                            </td>
                                            <td class="text-center">{{ format_date_time($item->updated_at) }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.settings.currencies.edit', [$item->id]) }}" title="Düzenle" class="btn btn-info font-15 p-2"><i class="las la-edit"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="11">Döviz yok.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
