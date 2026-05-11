<tr id="row-{{ $model->id }}">
    <td>{{ $model->name }}</td>
    <td>{{ $model->email }}</td>
    <td>{{ $model->username }}</td>
    <td>{{ $model->phone }}</td>
    <td class="text-center">
        <span class="badge {{ $model->status ? 'alert-success' : 'alert-danger' }}">{{ $model->status ? 'Aktif' : 'Pasif' }}</span>
    </td>
    <td class="text-center">
        <a href="javascript:;" data-js="add-edit" data-url="{{ route('dealers.sub-dealers.edit', [$model->id]) }}" data-title="Düzenle"><i class="fa-solid fa-edit"></i></a>
        <a href="javascript:;" class="text-danger" data-selector="row-delete" data-url="{{ route('dealers.sub-dealers.destroy', [$model->id]) }}" title="Sil"><i class="fa-solid fa-trash"></i></a>
    </td>
</tr>