<form action="{{ route('collections.cashes.store') }}" method="POST" id="add-edit-modal-form" data-js="cashes-add-edit-form">
    @csrf
    <div class="row g-2">
        <div class="col-12 mb-3">
            <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                <input type="date" class="form-control" name="collection_date" id="collection_date" value="{{ date('Y-m-d') }}">
                <label for="collection_date">{{ trans('translations.collections.cashes.tarih') }}&nbsp;<span class="text-danger">*</span></label>
            </div>
        </div>
        <div class="col-12 mb-3">
            <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                <input type="text" class="form-control" name="amount" id="amount" data-format="price">
                <label for="amount">{{ trans('translations.collections.cashes.tutar') }}&nbsp;<span class="text-danger">*</span></label>
            </div>
        </div>
        <div class="col-12 mb-3">
            <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                <select class="form-select theme-form-select" name="currency_type" id="currency_type" data-action="cashes-currency-change" data-note-prefix="Nakit">
                    <option value="" selected hidden>{{ trans('translations.collections.cashes.sec') }}</option>
                    <option value="TL">TL</option>
                    <option value="USD">USD</option>
                    {{-- <option value="EUR">EUR</option> --}}
                </select>
                <label for="currency_type">{{ trans('translations.collections.cashes.doviz_turu') }}&nbsp;<span class="text-danger">*</span></label>
            </div>
        </div>
        <div class="col-12 mb-3">
            <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                <textarea class="form-control" name="notes" id="notes" rows="4"></textarea>
                <label for="notes">{{ trans('translations.collections.cashes.aciklama') }}</label>
            </div>
        </div>
    </div>
</form>
