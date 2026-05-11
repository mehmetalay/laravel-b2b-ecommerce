<form action="{{ route('admin.catalog.product-attributes.attribute-groups.store') }}" method="POST" id="add-edit-modal-form">
    @csrf
    <div class="row g-2">
        <div class="col-12">
            <div class="form-group">
                <x-backend.input id="name" label="Adı" type="text" :required="true" autofocus/>
            </div>
        </div>
    </div>
</form>
