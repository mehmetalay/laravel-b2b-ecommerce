@extends('layouts.app')

@section('content')
    <section class="section-b-space">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="title">
                        <h2>{{ trans('translations.collections.promissories.senet_tahsilatlari') }}</h2>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-4 col-xl-3 mb-3">
                            <a href="{{ route('collections.promissories.create') }}" class="btn btn-sm theme-bg-color text-white w-50">{{ trans('translations.collections.promissories.yeni') }}</a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ trans('translations.collections.promissories.cari') }}</th>
                                    <th class="text-center">{{ trans('translations.collections.promissories.sira_no') }}</th>
                                    <th class="text-center">{{ trans('translations.collections.promissories.vade_adedi') }}</th>
                                    <th class="text-center">{{ trans('translations.collections.promissories.tarih') }}</th>
                                    <th class="text-center">{{ trans('translations.collections.promissories.olusturulma_tarihi') }}</th>
                                    <th class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                @forelse ($promissories as $item)
                                    <tr id="parent-{{ $item->id }}">
                                        <td>{{ $item->user->name }}</td>
                                        <td class="text-center">{{ $item->sequence_number }}</td>
                                        <td class="text-center">{{ $item->maturity_number }}</td>
                                        <td class="text-center">{{ format_date_time($item->collection_date) }}</td>
                                        <td class="text-center">{{ format_date_time($item->created_at) }}</td>
                                        <td>
                                            <a href="{{ route('collections.promissories.edit', [$item->id]) }}"><i class="fa-solid fa-edit"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">{{ trans('translations.collections.promissories.veri_yok') }}.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $promissories->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
