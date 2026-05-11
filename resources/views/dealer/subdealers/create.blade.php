<form action="{{ route('dealers.sub-dealers.store') }}" method="POST" id="add-edit-modal-form">
    @csrf
    <div class="row g-2">
        <div class="col-12 mb-3">
            <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                <input type="text" class="form-control" name="name" id="name">
                <label for="name">Adı Soyadı / Ünvan Adı <span class="text-danger">*</span></label>
            </div>
        </div>
        <div class="col-12 mb-3">
            <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                <input type="text" class="form-control" name="email" id="email">
                <label for="email">E-posta Adresi <span class="text-danger">*</span></label>
            </div>
        </div>
        <div class="col-12 mb-3">
            <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                <input type="text" class="form-control" name="username" id="username">
                <label for="username">Kullanıcı Adı <span class="text-danger">*</span></label>
            </div>
        </div>
        <div class="col-12 mb-3">
            <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                <input type="text" class="form-control" name="phone" id="phone" data-mask-phone>
                <label for="phone">Telefon <span class="text-danger">*</span></label>
            </div>
        </div>
        <div class="col-12 mb-3">
            <div class="form-floating mb-lg-3 mb-2 theme-form-floating">
                <input type="text" class="form-control" name="password" id="password">
                <label for="password">Şifre <span class="text-danger">*</span></label>
            </div>
        </div>
        <div class="col-12">
            <div class="form-check ps-0 m-0 remember-box">
                <input class="checkbox_animated check-box" type="checkbox" name="can_place_order" id="can_place_order">
                <label class="form-check-label" for="can_place_order">Sipariş Verebilir</label>
            </div>
        </div>
        <div class="col-12">
            <div class="form-check ps-0 m-0 remember-box">
                <input class="checkbox_animated check-box" type="checkbox" name="can_approve_order" id="can_approve_order">
                <label class="form-check-label" for="can_approve_order">Sipariş Onayı</label>
            </div>
        </div>
        <div class="col-12">
            <div class="form-check ps-0 m-0 remember-box">
                <input class="checkbox_animated check-box" type="checkbox" name="can_record_payment" id="can_record_payment">
                <label class="form-check-label" for="can_record_payment">Tahsilat Girebilir</label>
            </div>
        </div>
        <div class="col-12">
            <div class="form-check ps-0 m-0 remember-box">
                <input class="checkbox_animated check-box" type="checkbox" name="can_view_prices" id="can_view_prices">
                <label class="form-check-label" for="can_view_prices">Tüm Fiyatları Görebilir</label>
            </div>
        </div>
    </div>
</form>
