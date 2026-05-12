<tr id="parent-{{ $model->id }}">
    <td>{{ $model->id }}</td>
    <td>{{ $model->title_tr }}</td>
    <td class="text-center">{{ $model->sort_order }}</td>
    <td class="text-center">
        <a href="{{ route('admin.catalog.homepage-blocks.products', $model) }}">
            <span class="badge outline-badge-info">{{ $model->products->count() }} ürün seçildi <i class="las la-level-down-alt"></i></span>
        </a>
    </td>
    <td class="text-center"><x-backend.status-badge type="active_passive" :value="$model->is_active" /></td>
    <td>
        <a href="javascript:;" title="Düzenle" data-js="add-edit" data-type="edit" data-title="Düzenle" data-url="{{ route('admin.catalog.homepage-blocks.edit', ['homepage_block' => $model->id]) }}" class="btn btn-info font-15 p-2"><i class="las la-edit"></i></a>
        <a href="javascript:;" class="btn btn-danger font-15 p-2" data-selector="row-delete" data-url="{{ route('admin.catalog.homepage-blocks.destroy', ['homepage_block' => $model->id]) }}" title="Sil"><i class="las la-trash"></i></a>
    </td>
</tr>