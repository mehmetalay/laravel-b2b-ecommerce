<tr id="parent-{{ $model->id }}">
    <td class="text-left">{{ $model->id }}</td>
    <td>{{ $model->name }}</td>
    <td>
        <a href="{{ route('admin.catalog.product-attributes.attribute-groups.attributes.index', ['attributeGroup' => $model->id]) }}">
            <span class="badge outline-badge-info">Özellikler <i class="las la-level-down-alt"></i></span>
        </a>
    </td>
    <td class="text-center">
        <x-backend.status-badge type="active_passive" :value="$model->status" />
    </td>
    <td class="text-center">
        <a href="javascript:;" title="Kopyala" class="btn btn-secondary font-15 p-2" data-js="add-edit" data-type="duplicate" data-duplicate-action="{{ route('admin.catalog.product-attributes.attribute-groups.duplicate', $model->id) }}" data-title="Kopyala" data-url="{{ route('admin.catalog.product-attributes.attribute-groups.create') }}"><i class="las la-copy"></i></a>
        <a href="javascript:;" title="Düzenle" class="btn btn-info font-15 p-2" data-js="add-edit" data-type="edit" data-title="Düzenle" data-url="{{ route('admin.catalog.product-attributes.attribute-groups.edit', [$model->id]) }}"><i class="las la-edit"></i></a>
        {{-- <a href="javascript:;" class="btn btn-danger font-15 p-2" data-selector="row-delete" data-url="{{ route('admin.catalog.product-attributes.attribute-groups.destroy', [$model->id]) }}" title="Sil"><i class="las la-trash"></i></a> --}}
    </td>
</tr>
