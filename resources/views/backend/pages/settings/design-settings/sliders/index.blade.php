@extends('backend.layouts.app')

@section('title', 'Slider Yönetimi')

@section('content')
    @php
        $sliderVueProps = [
            'currentType' => $type,
            'typeOptions' => [
                ['value' => 'slider', 'label' => 'Slider'],
                ['value' => 'payment_slider', 'label' => 'Ödeme Slider'],
                ['value' => 'category_slider', 'label' => 'Kategori Slider'],
                ['value' => 'campaign_slider', 'label' => 'Kampanya Slider'],
            ],
            'createUrl' => route('admin.settings.design-settings.sliders.create'),
            'indexUrl' => route('admin.settings.design-settings.sliders.index'),
            'sortUrl' => route('admin.settings.design-settings.sliders.sort'),
            'endpoint' => url('/admin/api/sliders'),
        ];
    @endphp

    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Ayarlar'],
            ['url' => 'javascript:;', 'label' => 'Tasarım Ayarları'],
            ['url' => route('admin.settings.design-settings.sliders.index'), 'label' => 'Slider Yönetimi'],
        ]">
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div data-vue="sliders-index" data-props='@json($sliderVueProps, JSON_UNESCAPED_UNICODE)'></div>
            </div>
        </div>
    </div>
@endsection
