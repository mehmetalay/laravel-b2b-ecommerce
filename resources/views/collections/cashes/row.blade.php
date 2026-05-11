<tr id="parent-{{ $model->id }}">
    <td>{{ $model->user->name }}</td>
    <td class="text-center">{{ $model->sequence_number }}</td>
    <td class="text-center">{{ number_format($model->amount, 2) . ' ' . $model->currency_type }}</td>
    <td class="text-center">{{ format_date_time($model->collection_date) }}</td>
    <td><a href="javascript:;" data-js="add-edit" data-url="{{ route('collections.cashes.edit', [$model->id]) }}" data-title="{{ trans('translations.collections.cashes.duzenle') }}"><i class="fa-solid fa-edit"></i></a></td>
</tr>
