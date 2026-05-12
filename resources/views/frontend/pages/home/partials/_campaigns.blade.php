<section class="section-small-space">
    <div class="container-fluid">
        <div class="campaign-slider">
            @foreach($sliders as $slider)
                <div>
                    <div class="banner-contain hover-effect">
                        <a href="{{ $slider->link ?? 'javascript:;' }}"
                        {{ $slider->target_blank ? 'target=_blank' : '' }}>

                        @php
                                $mobileImage = $slider->imageWithDevice('mobile');
                                $tabletImage = $slider->imageWithDevice('tablet');
                                $desktopImage = $slider->imageWithDevice('desktop');
                            @endphp

                            <picture>
                                <source
                                    media="(max-width: 767px)"
                                    data-srcset="{{ image_url($mobileImage['file'], 'campaign_slider.' . $mobileImage['device']) }}">

                                <source
                                    media="(max-width: 1024px)"
                                    data-srcset="{{ image_url($tabletImage['file'], 'campaign_slider.' . $tabletImage['device']) }}">

                                <img
                                    src="{{ image_url(config('images.default.lazy_load'), 'product.small') }}"
                                    data-src="{{ image_url($desktopImage['file'], 'campaign_slider.' . $desktopImage['device']) }}"
                                    class="bg-img blur-up lazyload w-100"
                                    alt="{{ general_info('company_name') }}">
                            </picture>

                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
