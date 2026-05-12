@extends('backend.layouts.app')

@section('title', 'Tema Ayarları')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Ayarlar'],
            ['url' => 'javascript:;', 'label' => 'Tasarım Ayarları'],
            ['url' => route('admin.settings.design-settings.theme-settings.index'), 'label' => 'Tema Ayarları'],
        ]">
        <li class="nav-item">
            <button type="submit" class="btn btn-success dash-btn" form="theme-form" data-ajax-submit>
                <i class="las la-save"></i> Kaydet
            </button>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <form action="{{ route('admin.settings.design-settings.theme-settings.update', [1]) }}" method="POST" id="theme-form" data-ajax-form>
            @csrf
            @method('PATCH')
            <div class="row layout-top-spacing switch-outer-container">
                <div class="col-12 layout-spacing">
                    <div class="statbox widget box box-shadow">
                        <div class="widget-header">
                            <h4>Tema Ayarları</h4>
                        </div>
                        <div class="widget-content widget-content-area pills-vertical-line">
                            <div class="row">
                                <div class="col-12 col-xl-3">
                                    <div class="nav flex-column nav-pills w-75" id="v-line-pills-tab" role="tablist" aria-orientation="vertical">
                                        <a class="nav-link active mb-3" id="general-setting-tab" data-toggle="pill" href="#general-setting" role="tab" aria-controls="general-setting" aria-selected="true">Genel Ayarlar</a>
                                        <a class="nav-link mb-3" id="footer-tab" data-toggle="pill" href="#footer" role="tab" aria-controls="footer" aria-selected="false">Footer</a>
                                        <a class="nav-link mb-3" id="social-medias-tab" data-toggle="pill" href="#social-medias" role="tab" aria-controls="social-medias" aria-selected="false">Sosyal Ağlar</a>
                                    </div>
                                </div>
                                <div class="col-12 col-xl-9">
                                    <div class="tab-content" id="v-line-pills-tabContent">
                                        <div class="tab-pane fade show active" id="general-setting" role="tabpanel" aria-labelledby="general-setting-tab">
                                            <div class="form-group row">
                                                <div class="col-xl-12 col-md-12 col-sm-12 col-12 mb-4">
                                                    <h5>Genel Ayarlar</h5>
                                                </div>
                                                <div class="text-left col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                                    <x-label for="logo_picture" text="Logo" />
                                                    <p><small>Önerilen Ebat: {{ config('images.sizes.logo.recommended_resolution') }}</small></p>
                                                </div>
                                                <div class="col-lg-6 col-md-9 col-sm-12">
                                                    <div class="image-upload">
                                                        <div class="image-edit">
                                                            <input type="file" id="logo_picture_upload" data-preview="#logo_picture_preview" name="logo_picture" accept=".png, .jpg, .jpeg">
                                                            <label for="logo_picture_upload">
                                                                <i class="las la-pen"></i>
                                                            </label>
                                                        </div>
                                                        <div class="image-preview">
                                                            <div id="logo_picture_preview" style="background-image: url({{ image_url(config('images.default.logo'), 'images') }}); background-size: contain;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="form-group row">
                                                <div class="text-left col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                                    <x-label for="favicon_picture" text="Favicon" />
                                                    <p><small>Önerilen Ebat: {{ config('images.sizes.favicon.recommended_resolution') }}</small></p>
                                                </div>
                                                <div class="col-lg-6 col-md-9 col-sm-12">
                                                    <div class="image-upload">
                                                        <div class="image-edit">
                                                            <input type="file" id="favicon_picture_upload" data-preview="#favicon_picture_preview" name="favicon_picture" accept=".png, .jpg, .jpeg">
                                                            <label for="favicon_picture_upload">
                                                                <i class="las la-pen"></i>
                                                            </label>
                                                        </div>
                                                        <div class="image-preview">
                                                            <div id="favicon_picture_preview" style="background-image: url({{ image_url(config('images.default.favicon.android_512'), 'favicon') }}); background-size: contain;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="form-group row">
                                                <div class="text-left col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                                    <x-label for="nopic_image" text="Varsayılan Katalog Görseli" />
                                                    <p>
                                                        <small>Resmi yüklenmemiş ürünler için kullanabileceğiniz varsayılan görsel.</small><br>
                                                        <small>Önerilen Ebat: {{ config('images.sizes.no_image.recommended_resolution') }}</small>
                                                    </p>
                                                </div>
                                                <div class="col-lg-6 col-md-9 col-sm-12">
                                                    <div class="image-upload">
                                                        <div class="image-edit">
                                                            <input type="file" id="nopic_image_upload" data-preview="#nopic_image_preview" name="nopic_image" accept=".png, .jpg, .jpeg" />
                                                            <label for="nopic_image_upload">
                                                                <i class="las la-pen"></i>
                                                            </label>
                                                        </div>
                                                        <div class="image-preview">
                                                            <div id="nopic_image_preview" style="background-image: url({{ image_url(config('images.default.no_image'), 'product.small') }}); background-size: contain;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="footer" role="tabpanel" aria-labelledby="footer-tab">
                                            <div class="form-group row">
                                                <div class="text-left col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                                    <x-label for="footer_logo_picture" text="Footer Logo Resmi" />
                                                    <p><small>Önerilen Ebat: {{ config('images.sizes.footer_logo.recommended_resolution') }}</small></p>
                                                </div>
                                                <div class="col-lg-6 col-md-9 col-sm-12">
                                                    <div class="image-upload">
                                                        <div class="image-edit">
                                                            <input type="file" id="footer_logo_picture_upload" data-preview="#footer_logo_picture_preview" name="footer_logo_picture" accept=".png, .jpg, .jpeg" />
                                                            <label for="footer_logo_picture_upload">
                                                                <i class="las la-pen"></i>
                                                            </label>
                                                        </div>
                                                        <div class="image-preview">
                                                            <div id="footer_logo_picture_preview" style="background-image: url({{ image_url(config('images.default.footer_logo'), 'images') }}); background-size: contain;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="text-left col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                                    <x-label for="footer_about_us_text" text="Footer Hakkında Metini" />
                                                </div>
                                                <div class="col-lg-6 col-md-9 col-sm-12">
                                                    <x-backend.textarea name="footer_about_us_text" rows="4" :value="$themeSetting->footer_about_us_text" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="text-left col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                                    <x-label for="footer_about_us_text_en" text="Footer Hakkında Metini (EN)" />
                                                </div>
                                                <div class="col-lg-6 col-md-9 col-sm-12">
                                                    <x-backend.textarea name="footer_about_us_text_en" rows="4" :value="$themeSetting->footer_about_us_text_en" />
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="form-group row">
                                                <div class="text-left col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                                    <x-label for="footer_ssl_logo_picture" text="Footer SSL Resmi" />
                                                    <p><small>Önerilen Ebat: {{ config('images.sizes.footer_ssl_image.recommended_resolution') }}</small></p>
                                                </div>
                                                <div class="col-lg-6 col-md-9 col-sm-12">
                                                    <div class="image-upload">
                                                        <div class="image-edit">
                                                            <input type="file" id="footer_ssl_logo_picture_upload" data-preview="#footer_ssl_logo_picture_preview" name="footer_ssl_logo_picture" accept=".png, .jpg, .jpeg" />
                                                            <label for="footer_ssl_logo_picture_upload">
                                                                <i class="las la-pen"></i>
                                                            </label>
                                                        </div>
                                                        <div class="image-preview">
                                                            <div id="footer_ssl_logo_picture_preview" style="background-image: url({{ image_url(config('images.default.footer_ssl_image'), 'images') }}); background-size: contain;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="form-group row">
                                                <div class="text-left col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                                    <x-label for="copyright" text="Copyright Yazısı" class="col-form-label" />
                                                </div>
                                                <div class="col-lg-6 col-md-9 col-sm-12">
                                                    <x-backend.input id="copyright" type="text" :value="$themeSetting->copyright" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="text-left col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                                    <x-label for="copyright_en" text="Copyright Yazısı (EN)" class="col-form-label" />
                                                </div>
                                                <div class="col-lg-6 col-md-9 col-sm-12">
                                                    <x-backend.input id="copyright_en" type="text" :value="$themeSetting->copyright_en" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="social-medias" role="tabpanel" aria-labelledby="social-medias-tab">
                                            <div class="form-group row">
                                                <div class="text-left col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                                    <x-label for="facebook" text="Facebook Adresi" class="col-form-label" />
                                                </div>
                                                <div class="col-lg-6 col-md-9 col-sm-12">
                                                    <x-backend.input id="facebook" type="text" :value="$themeSetting->facebook" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="text-left col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                                    <x-label for="instagram" text="Instagram Adresi" class="col-form-label" />
                                                </div>
                                                <div class="col-lg-6 col-md-9 col-sm-12">
                                                    <x-backend.input id="instagram" type="text" :value="$themeSetting->instagram" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="text-left col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                                    <x-label for="twitter" text="Twitter Adresi" class="col-form-label" />
                                                </div>
                                                <div class="col-lg-6 col-md-9 col-sm-12">
                                                    <x-backend.input id="twitter" type="text" :value="$themeSetting->twitter" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="text-left col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                                    <x-label for="pinterest" text="Pinterest Adresi" class="col-form-label" />
                                                </div>
                                                <div class="col-lg-6 col-md-9 col-sm-12">
                                                    <x-backend.input id="pinterest" type="text" :value="$themeSetting->pinterest" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="text-left col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                                    <x-label for="youtube" text="Youtube Adresi" class="col-form-label" />
                                                </div>
                                                <div class="col-lg-6 col-md-9 col-sm-12">
                                                    <x-backend.input id="youtube" type="text" :value="$themeSetting->youtube" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="text-left col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                                    <x-label for="linkedin" text="Linkedin Adresi" class="col-form-label" />
                                                </div>
                                                <div class="col-lg-6 col-md-9 col-sm-12">
                                                    <x-backend.input id="linkedin" type="text" :value="$themeSetting->linkedin" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="text-left col-xl-3 col-lg-3 col-md-3 col-sm-12">
                                                    <x-label for="whatsapp" text="WhatsApp Numarası" class="col-form-label" />
                                                </div>
                                                <div class="col-lg-6 col-md-9 col-sm-12">
                                                    <x-backend.input id="whatsapp" type="text" :value="$themeSetting->whatsapp" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
