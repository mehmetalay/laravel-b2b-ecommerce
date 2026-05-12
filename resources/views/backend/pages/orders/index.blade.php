@extends('backend.layouts.app')

@section('title', 'Siparişler')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Siparişler']
        ]">
        <li class="nav-item"></li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div data-vue="orders-index"></div>
            </div>
        </div>
    </div>
@endsection
