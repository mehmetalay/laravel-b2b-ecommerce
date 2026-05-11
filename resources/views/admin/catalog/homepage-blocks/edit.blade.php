<form action="{{ route('admin.catalog.homepage-blocks.update', ['homepage_block' => $model->id]) }}" method="POST" id="add-edit-modal-form" class="switch-outer-container">
    @csrf
    @method('PATCH')
    <span>TR</span>
    <div class="form-group">
        <x-backend.input id="title_tr" label="Başlık Adı" type="text" :value="$model->title_tr" :required="true" autofocus />
    </div>
    <div class="form-group">
        <x-backend.textarea name="subtitle_tr" label="Alt Başlık Adı" rows="2" :value="$model->subtitle_tr" />
    </div>
    <hr>
    <span>EN</span>
    <div class="form-group">
        <x-backend.input id="title_en" label="Başlık Adı" type="text" :value="$model->title_en" :required="true" />
    </div>
    <div class="form-group">
        <x-backend.textarea name="subtitle_en" label="Alt Başlık Adı" rows="2" :value="$model->subtitle_en" />
    </div>
    <hr>
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                <x-backend.input id="sort_order" label="Sıra" type="number" :value="$model->sort_order" />
            </div>
        </div>
    </div>
    <div class="form-group row align-items-center">
        <label class="col-3 col-form-label" for="is_active">Durum</label>
        <div class="col-3">
            <span class="switch">
                <label>
                    <input type="checkbox" name="is_active" id="is_active" {{ $model->is_active ? 'checked' : '' }}>
                    <span></span>
                </label>
            </span>
        </div>
    </div>
</form>