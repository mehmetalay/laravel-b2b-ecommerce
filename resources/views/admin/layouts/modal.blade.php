<div class="modal fade bd-example-modal-xl" id="cropper-modal" tabindex="-1" role="dialog" aria-labelledby="cropper-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cropper-modal-label">Fotoğrafı Düzenle</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img src="" id="image-cropper" style="width: 100%;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Vazgeç</button>
                <button type="button" class="btn btn-primary" id="crop-button">Kaydet</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="add-edit-modal" add-edit-modal tabindex="-1" role="dialog" aria-labelledby="add-edit-modal-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" add-edit-modal-title></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" add-edit-modal-body></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">İptal</button>
                <button type="submit" class="btn btn-primary" form="add-edit-modal-form" modal-submit-button data-submit-type="save">Kaydet ve Kapat</button>
                <button type="submit" class="btn btn-success" form="add-edit-modal-form" modal-submit-button data-submit-type="save_new">Kaydet ve Yeni</button>
            </div>
        </div>
    </div>
</div>
@if (Route::is('admin.campaigns.create') || Route::is('admin.campaigns.edit') || Route::is('admin.catalog.homepage-blocks.products'))
    <div class="modal fade" id="product-modal" data-js="campaign-product-modal" tabindex="-1" role="dialog" aria-labelledby="product-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ürünleri Seçin</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body switch-outer-container">
                    <div class="form-group">
                        <label for="search-product">Ürün Ara</label>
                        <x-backend.input id="search-product" data-js="campaign-product-search" type="text" class="form-control" placeholder="Ürün adı, ürün kodu, ürün grup kodu, barkod.." />
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="filter-category">Kategori</label>
                                <select id="filter-category" data-js="campaign-filter-category" class="selectpicker w-100" data-live-search="true">
                                    <option value="">Tümü</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="filter-brand">Marka</label>
                                <select id="filter-brand" data-js="campaign-filter-brand" class="selectpicker w-100" data-live-search="true">
                                    <option value="">Tümü</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button id="select-all" data-js="campaign-select-all" type="button" class="btn btn-warning btn-sm">Tümünü Seç</button>
                    </div>
                    <div style="overflow-y: scroll; height:400px;">
                        <table class="table table-hover table-sm" id="product-list" data-js="campaign-product-list">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Ürün</th>
                                    <th>Ürün Kodu / Ürün Grup Kodu</th>
                                    <th>Marka</th>
                                    <th>Kategori</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Kapat</button>
                    <button id="transfer-selected" data-js="campaign-transfer-selected" type="button" class="btn btn-success btn-sm" disabled>Seçilen Ürünleri Aktar</button>
                </div>
            </div>
        </div>
    </div>
@endif

@if (Route::is('admin.orders.index'))
    <div class="modal fade" data-element="log-modal" tabindex="-1" role="dialog" aria-labelledby="logs-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Log Dosyası <small>(Son 1 hafta)</small></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <pre data-element="log-content" style="background:#f5f5f5; max-height:500px; overflow:auto;"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Kapat</button>
                </div>
            </div>
        </div>
    </div>
@endif