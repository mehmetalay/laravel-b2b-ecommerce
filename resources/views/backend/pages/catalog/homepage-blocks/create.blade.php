<form action="{{ route('admin.catalog.homepage-blocks.store') }}" method="POST" id="add-edit-modal-form">
    @csrf
    <span>TR</span>
    <div class="form-group">
        <x-backend.input id="title_tr" label="Başlık Adı" type="text" :required="true" autofocus />
    </div>
    <div class="form-group">
        <x-backend.textarea name="subtitle_tr" label="Alt Başlık Adı" rows="2" />
    </div>
    <hr>
    <span>EN</span>
    <div class="form-group">
        <x-backend.input id="title_en" label="Başlık Adı" type="text" :required="true" />
    </div>
    <div class="form-group">
        <x-backend.textarea name="subtitle_en" label="Alt Başlık Adı" rows="2" />
    </div>
    <hr>
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                <x-backend.input id="sort_order" label="Sıra" type="number" />
            </div>
        </div>
    </div>
</form>