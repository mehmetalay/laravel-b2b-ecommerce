<style>
    .table-responsive {
        border-radius: 10px;
        /* overflow: hidden; */
        background: #fff;
        border: 1px solid #e5e7eb;
    }

    .table thead th {
        background: #f8fafc !important;
        font-weight: 600;
        color: #1f2937;
        font-size: 13px;
        border-bottom: 1px solid #e5e7eb !important;
    }

    .table tbody tr {
        transition: background 0.2s ease;
    }

    .table tbody tr:hover {
        background: #f3f4f6;
    }

    .table td img {
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: #fff;
        padding: 2px;
        transition: transform .2s ease;
        width: 60px;
        height: auto;
    }

    .table td img:hover {
        transform: scale(1.08);
    }

    .table td a strong {
        font-weight: 600;
        font-size: 14px;
        color: #111827;
    }

    .table td .text-muted {
        font-size: 12px;
        color: #6b7280 !important;
    }

    .table .price-box {
        display: flex !important;
        flex-direction: column !important;
        width: 220px;
        margin: 0;
    }

    .table .price-box .price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 6px;
        overflow: hidden;
        font-size: 12px;
        height: 32px;
    }

    .table .price-box .price-row .label {
        flex: 0 0 55%;
        padding: 4px 6px;
        color: #fff;
        font-weight: 500;
    }

    .table .price-box .price-row .label span {
        font-size: 10px;
        opacity: 0.9;
    }

    .table .price-box .price-row .value {
        flex: 0 0 45%;
        padding: 4px 6px;
        text-align: right;
        font-weight: 700;
    }

    .table .price-box .price-row .value .text-theme {
        color: #fff;
    }

    .table .price-box .price-row .label.yellow,
    .table .price-box .price-row .value.yellow .text-theme {
        color: #000;
    }

    .table .price-box .blue { background-color: #263681; }
    .table .price-box .gray { background-color: #5e5e5e; }
    .table .price-box .green { background-color: #4caf50; }
    .table .price-box .orange { background-color: #ff5722; }
    .table .price-box .yellow { background-color: #fad02c; }
    .table .price-box .red { background-color: #ff3131; }

    @media (max-width: 992px) {
        .table .price-box {
            width: 190px;
        }
        .table .price-box .price-row {
            font-size: 11px;
            height: 28px;
        }
    }

    td:nth-child(3),
    td:nth-child(4),
    td:nth-child(6),
    td:nth-child(8) {
        font-size: 12px;
        font-weight: 500;
        color: #1f2937;
    }

    .badge {
        font-size: 12px;
        padding: 6px 10px;
        border-radius: 6px;
    }

    .badge.bg-warning {
        background-color: #fef3c7 !important;
        color: #92400e !important;
    }

    .badge.bg-danger {
        background-color: #fee2e2 !important;
        color: #b91c1c !important;
    }

    .badge.bg-success {
        background-color: #dcfce7 !important;
        color: #166534 !important;
    }

    .table .list-qty-box {
        width: 160px;
        margin: auto;
        background-color: #f5f5f5;
        border-radius: 50px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .table .list-qty-box .qty-left-minus,
    .table .list-qty-box .qty-right-plus {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 100%;
        border: none;
        background-color: var(--theme-color);
        color: #fff;
        font-size: 14px;
        transition: 0.15s ease-in-out;
    }

    .table .list-qty-box .qty-left-minus:hover,
    .table .list-qty-box .qty-right-plus:hover {
        background-color: #253040;
    }

    .table .list-qty-box .qty-input {
        width: 65px;
        border: none;
        background: transparent;
        text-align: center;
        font-size: 14px;
        font-weight: 600;
        color: #4a5568;
        margin: 0 4px;
    }

    .table .list-qty-box .qty-input:focus {
        outline: none;
        box-shadow: none;
    }

    @media (max-width: 768px) {
        .table .list-qty-box {
            width: 120px;
            padding: 3px;
        }
        .table .list-qty-box .qty-left-minus,
        .table .list-qty-box .qty-right-plus {
            width: 28px;
            height: 28px;
            font-size: 12px;
        }
        .table .list-qty-box .qty-input {
            font-size: 14px;
            width: 40px;
        }
    }

    /* === SEPET BUTONU === */
    .table .list-add-to-cart {
        border-radius: 8px;
        font-weight: 600;
        padding: 8px 0;
        background-color: var(--theme-color);
        color: #fff;
        border: none;
        width: 100%;
        text-align: center;
        position: relative;
        min-width: 155px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: background 0.2s ease, color 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .table .list-add-to-cart:hover:not(:disabled) {
        background-color: #253040;
        color: #fff;
    }

    .table .list-add-to-cart:disabled,
    .table .list-add-to-cart.disabled {
        background-color: #9ca3af !important;
        cursor: not-allowed;
        color: #f3f4f6;
    }

    /* === TABLET DİKEY (768px - 991px) === */
    @media (max-width: 991px) and (min-width: 768px) {
        .table thead th,
        .table tbody td {
            padding: 8px 4px;
            font-size: 11px;
            white-space: nowrap !important;
        }

        .table td img {
            width: 50px;
        }

        .table td a strong {
            font-size: 12px;
        }

        .table .price-box {
            width: 160px;
        }

        .table .price-box .price-row {
            font-size: 10px;
            height: 26px;
        }

        .table .list-add-to-cart {
            min-width: 100px;
            font-size: 11px;
            padding: 6px 4px;
        }

        .table .list-qty-box {
            width: 110px;
        }

        .table .list-qty-box .qty-left-minus,
        .table .list-qty-box .qty-right-plus {
            width: 26px;
            height: 26px;
            font-size: 10px;
        }

        .table .list-qty-box .qty-input {
            width: 35px;
            font-size: 12px;
        }

        /* Adet ve İşlem sütunları arasına boşluk */
        .table thead th:nth-child(9),
        .table tbody td:nth-child(9) {
            padding-right: 15px;
        }

        .table thead th:nth-child(10),
        .table tbody td:nth-child(10) {
            padding-left: 15px;
        }
    }

    /* === MOBİL (max 767px) === */
    @media (max-width: 767px) {
        .table-responsive {
            -webkit-overflow-scrolling: touch;
        }

        .table {
            min-width: 900px;
        }

        .table thead th,
        .table tbody td {
            padding: 6px 4px;
            font-size: 11px;
            white-space: nowrap !important;
        }

        .table td img {
            width: 45px;
        }

        .table td a strong {
            font-size: 11px;
        }

        .table td .text-muted {
            font-size: 9px;
        }

        .table .price-box {
            width: 140px;
        }

        .table .price-box .price-row {
            font-size: 10px;
            height: 24px;
        }

        .table .price-box .price-row .label,
        .table .price-box .price-row .value {
            padding: 3px 4px;
        }

        .table .list-add-to-cart {
            min-width: 90px;
            font-size: 11px;
            padding: 6px 4px;
        }

        .table .list-qty-box {
            width: 100px;
            padding: 2px;
        }

        .table .list-qty-box .qty-left-minus,
        .table .list-qty-box .qty-right-plus {
            width: 24px;
            height: 24px;
            font-size: 10px;
        }

        .table .list-qty-box .qty-input {
            width: 32px;
            font-size: 12px;
        }

        /* Adet ve İşlem sütunları arasına boşluk */
        .table thead th:nth-child(9),
        .table tbody td:nth-child(9) {
            padding-right: 12px;
        }

        .table thead th:nth-child(10),
        .table tbody td:nth-child(10) {
            padding-left: 12px;
        }
    }

    /* === MOBİL KÜÇÜK EKRAN (max 480px) === */
    @media (max-width: 480px) {
        .table {
            min-width: 850px;
        }

        .table td img {
            width: 40px;
        }

        .table td a strong {
            font-size: 10px;
        }

        .table .price-box {
            width: 130px;
        }

        .table .price-box .price-row {
            font-size: 9px;
            height: 22px;
        }

        .table .list-add-to-cart {
            min-width: 80px;
            font-size: 10px;
            padding: 5px 3px;
        }

        .table .list-qty-box {
            width: 90px;
        }

        .table .list-qty-box .qty-left-minus,
        .table .list-qty-box .qty-right-plus {
            width: 22px;
            height: 22px;
            font-size: 9px;
        }

        .table .list-qty-box .qty-input {
            width: 28px;
            font-size: 11px;
        }
    }
</style>

<div class="table-responsive">
    <table class="table table-hover table-sm align-middle">
        <thead class="table-light">
            <tr>
                <th style="width: 80px;">Resim</th>
                <th>Ürün</th>
                <th class="text-center">Marka</th>
                <th class="text-center">Kategori</th>
                <th>Fiyat</th>
                <th class="text-center">KDV</th>
                <th class="text-center">Stok</th>
                <th class="text-center">Koli/Paket Ad.</th>
                <th class="text-center" style="width: 160px;">Adet</th>
                <th class="text-center" style="width: 120px;">İşlem</th>
            </tr>
        </thead>

        <tbody>
            @forelse($products as $product)
                <tr data-code="{{ $product->encoded_code }}">
                    <td>
                        <a href="{{ $product->detail_url }}">
                            <img class="img-fluid" src="{{ $product->image_lazy_load }}" data-src="{{ $product->image_small_url_1 }}">
                        </a>
                    </td>
                    <td>
                        <a href="{{ $product->detail_url }}">
                            <strong>{{ $product->product_name_short }}</strong>
                        </a>
                        <br>
                        <span class="text-muted small">{{ $product->code }}</span>
                    </td>
                    <td class="text-center">
                        <a href="{{ $product->brand_slug }}">{{ str_limit($product->brand_name, 30, '..') }}</a>
                    </td>
                    <td class="text-center">
                        <a href="{{ $product->category_slug }}">{{ str_limit($product->category_name, 30, '..') }}</a>
                    </td>
                    <td class="d-flex" style="gap: 8px;">
                        <x-price-box :product="$product" viewType="list" />
                    </td>
                    <td class="text-center">%{{ $product->vat_rate }}</td>
                    <td class="text-center">
                        {!! $product->stock_status !!}
                    </td>
                    <td class="text-center">{{ $product->case_package }}</td>
                    <td>
                        <div class="list-qty-box" data-selector="quantity-container" data-box-quantity="{{ $product->box_quantity_value }}" data-box-exact="{{ $product->box_quantity_exact }}">
                            <button type="button" class="btn btn-outline-secondary btn-sm qty-left-minus" data-selector="qty-minus">
                                <i class="fa fa-minus"></i>
                            </button>
                            <input class="form-control form-control-sm qty-input" type="text" name="quantity" value="{{ $product->quantity_value }}" data-selector="qty-value">
                            <button type="button" class="btn btn-outline-secondary btn-sm qty-right-plus" data-selector="qty-plus">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </td>
                    <td class="text-center">
                        <a href="javascript:;" class="list-add-to-cart btn btn-sm"
                            @if ($product->can_add_to_cart)
                                data-js="add-to-cart" data-product-id="{{ $product->id }}" data-view-type="list"
                            @endif
                        >
                            {{ $product->can_add_to_cart ? trans('translations.product.sepete_ekle') : trans('translations.product.tukendi') }}
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">
                        {{ trans('translations.product.urun_yok') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $products->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
