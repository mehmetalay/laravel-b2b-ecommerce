<section class="section-small-space">
    <div class="container-fluid-lg">
        <div class="title">
            <h2>{{ trans('translations.index.markalar') }}</h2>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="brand-slider arrow-slider">
                    @foreach ($brands as $brand)
                        <div>
                            <div class="shop-category-box border-0 wow fadeIn">
                                <a href="{{ route('product.brand', ['slug' => $brand->slug]) }}">
                                    <img src="{{ image_url($brand->image, 'brand') }}" class="img-fluid blur-up lazyload" alt="{{ $brand->name }}">
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
