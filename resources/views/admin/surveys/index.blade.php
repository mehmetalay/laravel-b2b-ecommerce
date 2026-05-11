@extends('admin.layouts.app')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => route('admin.surveys.index'), 'label' => 'Anketler'],
        ]">
        <li class="nav-item">
            <a href="{{ route('admin.surveys.create') }}" class="btn btn-info dash-btn">
                <i class="las la-plus"></i> Yeni Anket
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-lg-12">
                <div class="statbox widget box box-shadow">
                    <div class="widget-header">
                        <h4>Anketler</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-left">#</th>
                                        <th>Başlık</th>
                                        <th class="text-center">Kullanım Tarihi</th>
                                        <th class="text-center">Durum</th>
                                        <th class="text-center">Toplam Katılımcı</th>
                                        <th></th>
                                        <th class="no-content" style="width: 150px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        <tr id="parent-{{ $item->id }}">
                                            <td class="text-left">{{ $item->id }}</td>
                                            <td>{{ $item->title }}</td>
                                            <td class="text-center">
                                                <x-backend.status-badge type="active_passive" :value="$item->use_dates" />
                                                @if ($item->use_dates == 1)
                                                    <div><small>{{ format_date_time($item->start_at, true)->isoFormat('D MMMM YYYY') . ' - ' . format_date_time($item->end_at, true)->isoFormat('D MMMM YYYY') }}</small></div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <x-backend.status-badge type="active_passive" :value="$item->is_active" />
                                            </td>
                                            <td class="text-center">{{ $item->answers()->distinct('dealer_id')->count() }}</td>
                                            <td>
                                                <a href="{{ route('admin.surveys.results', $item->id) }}" class="btn btn-sm btn-primary p-2">Sonuçları Görüntüle</a>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.surveys.edit', [$item->id]) }}" class="btn btn-info p-2" title="Düzenle"><i class="las la-edit"></i></a>
                                                <a href="javascript:;" class="btn btn-danger p-2" data-selector="row-delete" data-url="{{ route('admin.surveys.destroy', $item->id) }}" title="Sil"><i class="las la-trash"></i></a>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection