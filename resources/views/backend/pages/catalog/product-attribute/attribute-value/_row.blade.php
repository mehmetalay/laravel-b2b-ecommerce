<tr id="parent-{{ $model->id }}">
    <td class="text-left">{{ $model->id }}</td>
    <td>{{ $model->name }}</td>
    <td>{{ $model->name_en }}</td>
    <td class="text-center">
        <x-backend.status-badge type="active_passive" :value="$model->status" />
    </td>
    <td class="text-center">
        <a href="javascript:;" title="Düzenle" class="btn btn-info font-15 p-2" data-js="add-edit" data-type="edit" data-title="Düzenle" data-url="{{ route('admin.catalog.product-attributes.attributes.attribute-values.edit', ['attribute' => $attribute->id, 'attribute_value' => $model->id]) }}"><i class="las la-edit"></i></a>
        <a href="javascript:;" class="btn btn-danger font-15 p-2" data-selector="row-delete" data-url="{{ route('admin.catalog.product-attributes.attributes.attribute-values.destroy', ['attribute' => $attribute->id, 'attribute_value' => $model->id]) }}" title="Sil"><i class="las la-trash"></i></a>
    </td>
</tr>
