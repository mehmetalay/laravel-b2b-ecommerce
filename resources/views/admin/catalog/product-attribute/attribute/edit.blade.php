<form action="{{ route('admin.catalog.product-attributes.attribute-groups.attributes.update', ['attributeGroup' => $attributeGroup->id, 'attribute' => $model->id]) }}" method="POST" id="add-edit-modal-form">
    @csrf
    @method('PATCH')
    <div class="row g-2 switch-outer-container">
        <div class="col-12">
            <div class="form-group">
                <x-backend.input id="name" label="Adı" type="text" :value="$model->name" :required="true" autofocus/>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                <x-backend.input id="name_en" label="Adı (EN)" type="text" :value="$model->name_en" :required="true"/>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group row align-items-center">
                <label class="col-3 col-form-label" for="status">Durum</label>
                <div class="col-3">
                    <span class="switch">
                        <label>
                            <input type="checkbox" name="status" id="status" {{ $model->status ? 'checked' : '' }}>
                            <span></span>
                        </label>
                    </span>
                </div>
            </div>
        </div>
    </div>
</form>
