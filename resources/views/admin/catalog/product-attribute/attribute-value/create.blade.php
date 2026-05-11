<form action="{{ route('admin.catalog.product-attributes.attributes.attribute-values.store', ['attribute' => $attribute->id]) }}" method="POST" id="add-edit-modal-form">
    @csrf
    <div class="row g-2">
        <div class="col-12">
            <div class="form-group">
                <x-backend.input id="name" label="Adı" type="text" :required="true" autofocus/>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                <x-backend.input id="name_en" label="Adı (EN)" type="text" :required="true"/>
            </div>
        </div>
    </div>
</form>
