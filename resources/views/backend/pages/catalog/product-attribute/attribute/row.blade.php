<tr id="parent-{{ $model->id }}">
    <td class="text-left">{{ $model->id }}</td>
    <td>{{ $model->name }}</td>
    <td>{{ $model->name_en }}</td>
    <td>
        <a href="{{ route('admin.catalog.product-attributes.attributes.attribute-values.index', ['attribute' => $model->id]) }}">
            <span class="badge outline-badge-info">Özellik Değerleri <i class="las la-level-down-alt"></i></span>
        </a>
    </td>
    <td class="text-center">
        <x-backend.status-badge type="active_passive" :value="$model->status" />
    </td>
    <td class="text-center">
        <a href="javascript:;" title="Düzenle" class="btn btn-info font-15 p-2" data-js="add-edit" data-type="edit" data-title="Düzenle" data-url="{{ route('admin.catalog.product-attributes.attribute-groups.attributes.edit', ['attributeGroup' => $attributeGroup->id, 'attribute' => $model->id]) }}"><i class="las la-edit"></i></a>
        {{-- <a href="javascript:;" class="btn btn-danger font-15 p-2" data-selector="row-delete" data-url="{{ route('admin.catalog.product-attributes.attribute-groups.attributes.destroy', ['attributeGroup' => $attributeGroup->id, 'attribute' => $model->id]) }}" title="Sil"><i class="las la-trash"></i></a> --}}
    </td>
</tr>
