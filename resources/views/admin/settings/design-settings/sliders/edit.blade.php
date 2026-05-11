@extends('admin.layouts.app')

@section('title', 'Düzenle')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Ayarlar'],
            ['url' => 'javascript:;', 'label' => 'Tasarım Ayarları'],
            ['url' => route('admin.settings.design-settings.sliders.index'), 'label' => 'Slider Yönetimi'],
            ['url' => route('admin.settings.design-settings.sliders.edit', $slider->id), 'label' => 'Düzenle'],
        ]">
        <li class="nav-item">
            <button type="submit" class="btn btn-success dash-btn mr-2" form="slider-form" data-ajax-submit>
                <i class="las la-save"></i> Kaydet
            </button>
            <a href="{{ route('admin.settings.design-settings.sliders.index') }}" class="btn btn-info dash-btn">
                <i class="las la-list"></i> Listeye Dön
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row justify-content-center layout-top-spacing switch-outer-container">
            <div class="col-12 col-xl-6 layout-spacing">
                <form action="{{ route('admin.settings.design-settings.sliders.update', [$slider->id]) }}" method="POST" id="slider-form" data-ajax-form>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="type" id="sliderType" value="{{ $slider->type }}">
                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>Düzenle</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="form-group">
                                <label for="type" class="col-form-label">Slider Türü <span class="text-danger">*</span></label>
                                <select id="type" class="form-control" disabled>
                                    <option {{ $slider->type === 'slider' ? 'selected' : '' }}>
                                        Anasayfa Slider
                                    </option>
                                    <option {{ $slider->type === 'payment_slider' ? 'selected' : '' }}>
                                        Ödeme Slider
                                    </option>
                                    <option {{ $slider->type === 'category_slider' ? 'selected' : '' }}>
                                        Kategori Slider
                                    </option>
                                    <option {{ $slider->type === 'campaign_slider' ? 'selected' : '' }}>
                                        Kampanya Slider
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <x-backend.input id="link" label="Link" type="text" :value="$slider->link"/>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="sort_order" label="Sıra" type="text" :value="$slider->sort_order"/>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label" for="target_blank">Yeni sekmede aç</label>
                                        <span class="switch">
                                            <label>
                                                <input type="checkbox" name="target_blank" id="target_blank" {{ $slider->target_blank ? 'checked' : '' }}>
                                                <span></span>
                                            </label>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>Masaüstü Resim (Önerilen Ebat: <strong id="desktopResolution">-</strong>)</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="image_desktop_tr" class="col-form-label">TR <small class="text-danger">(Zorunlu)</small></label>
                                        <div class="image-upload">
                                            <div class="image-edit">
                                                <input type="file" id="image_desktop_tr_upload" data-preview="#image_desktop_tr_preview" name="image_desktop_tr" accept=".png, .jpg, .jpeg" form="slider-form">
                                                <label for="image_desktop_tr_upload">
                                                    <i class="las la-pen"></i>
                                                </label>
                                            </div>
                                            <div class="image-preview">
                                                <div id="image_desktop_tr_preview" style="background-image: url({{ image_url($slider->image_desktop_tr, "{$type}.desktop") }}); background-size: contain;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="image_desktop_tr" class="col-form-label">EN <small class="text-muted">(İsteğe Bağlı)</small></label>
                                        <div class="image-upload">
                                            <div class="image-edit">
                                                <input type="file" id="image_desktop_en_upload" data-preview="#image_desktop_en_preview" name="image_desktop_en" accept=".png, .jpg, .jpeg" form="slider-form">
                                                <label for="image_desktop_en_upload">
                                                    <i class="las la-pen"></i>
                                                </label>
                                            </div>
                                            <div class="image-preview">
                                                <div id="image_desktop_en_preview" style="background-image: url({{ image_url($slider->image_desktop_en, "{$type}.desktop") }}); background-size: contain;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>Tablet Resim  (Önerilen Ebat: <strong id="tabletResolution">-</strong>)</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="image_desktop_tr" class="col-form-label">TR <small class="text-muted">(İsteğe Bağlı)</small></label>
                                        <div class="image-upload">
                                            <div class="image-edit">
                                                <input type="file" id="image_tablet_tr_upload" data-preview="#image_tablet_tr_preview" name="image_tablet_tr" accept=".png, .jpg, .jpeg" form="slider-form">
                                                <label for="image_tablet_tr_upload">
                                                    <i class="las la-pen"></i>
                                                </label>
                                            </div>
                                            <div class="image-preview">
                                                <div id="image_tablet_tr_preview" style="background-image: url({{ image_url($slider->image_tablet_tr, "{$type}.tablet") }}); background-size: contain;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="image_desktop_tr" class="col-form-label">EN <small class="text-muted">(İsteğe Bağlı)</small></label>
                                        <div class="image-upload">
                                            <div class="image-edit">
                                                <input type="file" id="image_tablet_en_upload" data-preview="#image_tablet_en_preview" name="image_tablet_en" accept=".png, .jpg, .jpeg" form="slider-form">
                                                <label for="image_tablet_en_upload">
                                                    <i class="las la-pen"></i>
                                                </label>
                                            </div>
                                            <div class="image-preview">
                                                <div id="image_tablet_en_preview" style="background-image: url({{ image_url($slider->image_tablet_en, "{$type}.tablet") }}); background-size: contain;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>Mobil Resim  (Önerilen Ebat: <strong id="mobileResolution">-</strong>)</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="image_desktop_tr" class="col-form-label">TR <small class="text-muted">(İsteğe Bağlı)</small></label>
                                        <div class="image-upload">
                                            <div class="image-edit">
                                                <input type="file" id="image_mobile_tr_upload" data-preview="#image_mobile_tr_preview" name="image_mobile_tr" accept=".png, .jpg, .jpeg" form="slider-form">
                                                <label for="image_mobile_tr_upload">
                                                    <i class="las la-pen"></i>
                                                </label>
                                            </div>
                                            <div class="image-preview">
                                                <div id="image_mobile_tr_preview" style="background-image: url({{ image_url($slider->image_mobile_tr, "{$type}.mobile") }}); background-size: contain;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="image_desktop_tr" class="col-form-label">EN <small class="text-muted">(İsteğe Bağlı)</small></label>
                                        <div class="image-upload">
                                            <div class="image-edit">
                                                <input type="file" id="image_mobile_en_upload" data-preview="#image_mobile_en_preview" name="image_mobile_en" accept=".png, .jpg, .jpeg" form="slider-form">
                                                <label for="image_mobile_en_upload">
                                                    <i class="las la-pen"></i>
                                                </label>
                                            </div>
                                            <div class="image-preview">
                                                <div id="image_mobile_en_preview" style="background-image: url({{ image_url($slider->image_mobile_en, "{$type}.mobile") }}); background-size: contain;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-12 col-xl-3 layout-spacing">
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>Durumu</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="w-100">
                            <div class="form-group row">
                                <div class="col-3">
                                    <span class="switch align-items-start">
                                        <label>
                                            <input type="checkbox" name="status" form="slider-form" {{ $slider->status ? 'checked' : '' }}>
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                                <label class="col-9 col-form-label" id="status-label-text"> {{ $slider->status ? 'Aktif' : 'Pasif' }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        const sliderSizes = @json($sliderSizes);

        function updateResolutions(type) {
            const sizes = sliderSizes[type] || {};

            document.getElementById('desktopResolution').innerText =
                sizes.desktop ?? '-';

            document.getElementById('tabletResolution').innerText =
                sizes.tablet ?? '-';

            document.getElementById('mobileResolution').innerText =
                sizes.mobile ?? '-';
        }

        const sliderType = document.getElementById('sliderType');
        sliderType.addEventListener('change', () => updateResolutions(sliderType.value));

        updateResolutions(sliderType.value);
    </script>
@endsection
