<section class="section-small-space pt-0">
    <div class="container-fluid">
        <div class="theme-slider">
            @foreach($sliders as $slider)
                <div>
                    <div class="banner-contain">
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
                                    data-srcset="{{ image_url($mobileImage['file'], 'slider.' . $mobileImage['device']) }}">

                                <source
                                    media="(max-width: 1024px)"
                                    data-srcset="{{ image_url($tabletImage['file'], 'slider.' . $tabletImage['device']) }}">

                                <img
                                    src="{{ image_url($desktopImage['file'], 'slider.' . $desktopImage['device']) }}"
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
