<style>
    .free-gift-thumb{
        width: 54px;
        height: 54px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #eee;
        background: #fff;
        display:flex;
        align-items:center;
        justify-content:center;
    }
    .free-gift-thumb img{
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    .free-gift-item{
        gap: 12px;
    }
    .gift-qty-btn{
        width: 34px;
        height: 34px;
        display:flex;
        align-items:center;
        justify-content:center;
        border-radius: 10px;
    }
</style>

<div class="modal fade theme-modal" id="freeProductGiftModal" data-js="free-product-gift-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hediye Ürün Seçimi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body">
                <p class="text-muted mb-2">
                    Bu kampanyada toplam <strong>{{ $giftLimit }}</strong> adet hediye seçmelisiniz.
                </p>

                <form id="free-product-gift-form" data-role="free-product-gift-form">
                    @csrf
                    <input type="hidden" name="campaign_id" value="{{ $campaign->id }}">
                    <input type="hidden" id="gift-limit" data-role="gift-limit" value="{{ (int)$giftLimit }}">

                    <ul class="list-group free-gift-list">
                        @foreach($gifts as $gift)
                            <li class="list-group-item d-flex justify-content-between align-items-center free-gift-item">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="free-gift-thumb">
                                        <img src="{{ $gift->image_small_url_1 }}" alt="{{ $gift->name }}" class="img-fluid">
                                    </div>

                                    <div class="free-gift-info">
                                        <strong class="d-block">{{ $gift->name }}</strong>
                                        <div class="text-muted small">{{ $gift->code }}</div>
                                    </div>
                                </div>

                                <div class="free-gift-qty d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-light btn-sm gift-qty-btn" data-role="gift-qty-button" data-action="minus" data-gift-id="{{ $gift->id }}">
                                        <i class="fa-solid fa-minus"></i>
                                    </button>

                                    @php
                                        $val = (int) ($selectedGifts[$gift->id] ?? 0);
                                    @endphp

                                    <input type="number" name="gifts[{{ $gift->id }}]" class="form-control form-control-sm gift-qty-input" data-role="gift-qty-input" min="0" value="{{ $val }}" readonly style="width:80px; text-align:center;" data-gift-id="{{ $gift->id }}">

                                    <button type="button" class="btn btn-light btn-sm gift-qty-btn" data-role="gift-qty-button" data-action="plus" data-gift-id="{{ $gift->id }}">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </form>

                <div class="mt-2 small text-muted d-flex justify-content-between align-items-center">
                    <div>
                        Toplam seçilen adet:
                        <strong>
                            <span id="gift-total-selected" data-role="gift-total-selected">0</span> / {{ $giftLimit }}
                        </strong>
                        <span id="gift-remaining-text" data-role="gift-remaining-text" class="ms-2 text-warning"></span>
                    </div>

                    <div id="gift-selection-warning" data-role="gift-selection-warning" class="text-danger" style="display:none;">
                        Toplam hediye adedini aşamazsınız.
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-danger btn-sm text-white" data-bs-dismiss="modal">Vazgeç</button>
                <button class="btn btn-success btn-sm text-white" id="save-free-product-gifts" data-role="save-free-product-gifts">Hediyeleri Ekle</button>
            </div>
        </div>
    </div>
</div>
