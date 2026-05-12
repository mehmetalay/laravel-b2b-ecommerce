@extends('frontend.layouts.app')

@section('content')
    <section class="section-b-space">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="title">
                        <h2>Bayiler</h2>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-4 col-xl-3 mb-3">
                            <a href="javascript:;" data-js="add-edit" data-url="{{ route('dealers.sub-dealers.create') }}" data-title="Yeni" class="btn btn-sm theme-bg-color text-white w-50">Yeni</a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Adı</th>
                                    <th>E-posta Adresi</th>
                                    <th>Kullanıcı Adı</th>
                                    <th>Telefon</th>
                                    <th class="text-center">Durum</th>
                                    <th class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                @forelse ($items as $item)
                                    <tr id="row-{{ $item->id }}">
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->email }}</td>
                                        <td>{{ $item->username }}</td>
                                        <td>{{ $item->formatted_phone }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $item->status ? 'alert-success' : 'alert-danger' }}">{{ $item->status ? 'Aktif' : 'Pasif' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="javascript:;" data-js="add-edit" data-url="{{ route('dealers.sub-dealers.edit', [$item->id]) }}" data-title="Düzenle"><i class="fa-solid fa-edit"></i></a>
                                            <a href="javascript:;" class="text-danger" data-selector="row-delete" data-url="{{ route('dealers.sub-dealers.destroy', [$item->id]) }}" title="Sil"><i class="fa-solid fa-trash"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">Veri yok.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $items->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script>
        $('body').on('click', '[data-js=add-edit]', function(e) {
            e.preventDefault();
            var $this = $(this);
            var title = $this.data('title');
            var url = $this.data('url');
            $.get(url, function(data) {
                $('#add-edit-modal').modal('show');
                $('#add-edit-modal-label').html(title);
                $('#add-edit-modal-body').html(data);
                applyPhoneMask();
            });
        });
        $('body').on('submit', '#add-edit-modal-form', function(e) {
            e.preventDefault();

            var el = $('#add-edit-modal-button');
            var htmlButton = el.html();
            el.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>&nbsp;İşleminiz yapılıyor, lütfen bekleyin...');

            const formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                data: formData,
                type: 'POST',
                contentType: false,
                processData: false,
                dataType: 'JSON',
                success: function(response) {
                    if (response.success) {
                        if (response.type == 'add') {
                            $('#table-body tr:contains("Veri yok.")').remove();
                            $('#table-body').prepend(response.row);
                        } else {
                            $('#row-' + response.id).replaceWith(response.row);
                        }
                        notify('success', response.message);
                        $('#add-edit-modal').modal('hide');
                        el.html(htmlButton).prop('disabled', false);
                    } else {
                        el.html(htmlButton).prop('disabled', false);
                        var message = response.warning ? response.warning : response.error;
                        notify((response.warning ? 'warning' : 'error'), message);
                    }
                },
                error: function(xhr, status, error) {
                    notify('error', 'İstek sırasında bir hata oluştu. Lütfen site yöneticisi ile iletişime geçin.');
                    el.html(htmlButton).prop('disabled', false);
                }
            });
        });
    </script>
@endsection
