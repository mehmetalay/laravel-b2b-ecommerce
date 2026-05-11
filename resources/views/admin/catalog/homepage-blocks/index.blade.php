@extends('admin.layouts.app')

@section('title', 'Anasayfa Blokları')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Stok'],
            ['url' => route('admin.catalog.homepage-blocks.index'), 'label' => 'Anasayfa Blokları'],
        ]">
        <li class="nav-item">
            <a href="javascript:;" class="btn btn-info dash-btn" data-js="add-edit" data-type="add" data-title="Yeni" data-url="{{ route('admin.catalog.homepage-blocks.create') }}">
                <i class="las la-plus"></i> Yeni
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div class="statbox widget box box-shadow">
                    <div class="card-box">
                        <div class="row">
                            <div class="col-sm-12 col-md-3">
                                <form class="form-group" method="GET" accept-charset="utf-8" id="filter-form">
                                    <x-label for="name" text="Ara" />
                                    <div class="input-group">
                                        <x-backend.input id="name" type="text" :value="request()->get('name')" placeholder="Başlık ara.." />
                                        <div class="input-group-append">
                                            <button class="btn btn-soft-info" type="submit">Ara</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-sm-12 col-md-2 align-self-center">
                                @if (request()->query())
                                    <x-backend.filter-clear-button :route="route('admin.catalog.homepage-blocks.index')"/>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="statbox widget box box-shadow">
                    <div class="widget-header">
                        <h4>Anasayfa Blokları</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Başlık</th>
                                        <th class="text-center">Sıra</th>
                                        <th class="text-center">Ürünler</th>
                                        <th class="text-center">Durum</th>
                                        <th class="text-center no-content" style="width: 150px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="table-body">
                                    @forelse ($items as $item)
                                        <tr id="parent-{{ $item->id }}">
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->title_tr }}</td>
                                            <td class="text-center">{{ $item->sort_order }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.catalog.homepage-blocks.products', $item) }}">
                                                    <span class="badge outline-badge-info">{{ $item->products->count() }} ürün seçildi <i class="las la-level-down-alt"></i></span>
                                                </a>
                                            </td>
                                            <td class="text-center"><x-backend.status-badge type="active_passive" :value="$item->is_active" /></td>
                                            <td>
                                                <a href="javascript:;" title="Düzenle" data-js="add-edit" data-type="edit" data-title="Düzenle" data-url="{{ route('admin.catalog.homepage-blocks.edit', ['homepage_block' => $item->id]) }}" class="btn btn-info font-15 p-2"><i class="las la-edit"></i></a>
                                                <a href="javascript:;" class="btn btn-danger font-15 p-2" data-selector="row-delete" data-url="{{ route('admin.catalog.homepage-blocks.destroy', ['homepage_block' => $item->id]) }}" title="Sil"><i class="las la-trash"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="13">Veri yok.</td>
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

@section('js')
    <script>
        //
    </script>
@endsection
