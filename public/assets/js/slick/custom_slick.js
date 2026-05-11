$('.theme-slider').slick({
    dots: true,
    infinite: true,
    slidesToShow: 1,
    slidesToScroll: 1,
    autoplay: true,
    autoplaySpeed: 7500,
});

$('.slider-5_3').slick({
    arrows: false,
    infinite: true,
    slidesToShow: 5,
    slidesToScroll: 1,
    dots: true,
    autoplay: true,
    autoplaySpeed: 4000,
    responsive: [{
        breakpoint: 1430,
        settings: {
            slidesToShow: 4,
        }
    },
    {
        breakpoint: 1199,
        settings: {
            slidesToShow: 3,
        }
    },
    {
        breakpoint: 767,
        settings: {
            slidesToShow: 2,
        }
    },
    ]
});

$('.product-main').slick({
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: false,
    fade: true,
    asNavFor: '.left-slider-image'
});

$('.left-slider-image').slick({
    slidesToShow: 4,
    slidesToScroll: 1,
    asNavFor: '.product-main',
    dots: false,
    focusOnSelect: true,
    // vertical: false,
    responsive: [{
        breakpoint: 1400,
        settings: {
            vertical: false,
        }
    },
    {
        breakpoint: 992,
        settings: {
            vertical: false,
        }
    },
    {
        breakpoint: 768,
        settings: {
            vertical: false,
        }
    }, {
        breakpoint: 430,
        settings: {
            slidesToShow: 3,
            vertical: false,
        }
    },
    ]
});

window.initProductSliders = function () {
    if (typeof $ === 'undefined' || !$.fn.slick) {
        return;
    }

    $('.product-box-slider').not('.slick-initialized').slick({
        dots: false,
        infinite: true,
        slidesToShow: 5,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 4000,
        responsive: [
            {
                breakpoint: 1680,
                settings: { slidesToShow: 4 }
            },
            {
                breakpoint: 1400,
                settings: { slidesToShow: 3 }
            },
            {
                breakpoint: 1200,
                settings: { slidesToShow: 4 }
            },
            {
                breakpoint: 992,
                settings: { slidesToShow: 3 }
            },
            {
                breakpoint: 660,
                settings: { slidesToShow: 2 }
            },
            {
                breakpoint: 480,
                settings: { slidesToShow: 1 }
            }
        ]
    });
};

window.initCategorySliders = function () {
    if (typeof $ === 'undefined' || !$.fn.slick) {
        return;
    }

    $('.category-slider').slick({
        dots: false,
        arrows: true,
        infinite: true,
        slidesToShow: 4,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 4000,
        speed: 600,
        responsive: [{
            breakpoint: 1586,
            settings: {
                slidesToShow: 3,
            }
        },
        {
            breakpoint: 1140,
            settings: {
                slidesToShow: 2,
            }
        },
        {
            breakpoint: 710,
            settings: {
                slidesToShow: 1,
                fade: true,
            }
        },
        ]
    });
};

window.initCampaignSliders = function () {
    if (typeof $ === 'undefined' || !$.fn.slick) {
        return;
    }

    $('.campaign-slider').slick({
        dots: false,
        arrows: true,
        infinite: true,
        slidesToShow: 4,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 4000,
        speed: 600,
        responsive: [{
            breakpoint: 1586,
            settings: {
                slidesToShow: 3,
            }
        },
        {
            breakpoint: 1140,
            settings: {
                slidesToShow: 2,
            }
        },
        {
            breakpoint: 710,
            settings: {
                slidesToShow: 1,
                fade: true,
            }
        },
        ]
    });
};

window.initBrandSliders = function () {
    if (typeof $ === 'undefined' || !$.fn.slick) {
        return;
    }

    $('.brand-slider').slick({
        dots: false,
        arrows: true,
        infinite: true,
        slidesToShow: 7,
        slidesToScroll: 1,
        autoplay: true,
        responsive: [{
            breakpoint: 1745,
            settings: {
                slidesToShow: 6,
                autoplay: true,
                autoplaySpeed: 2500,
            }
        },
        {
            breakpoint: 1540,
            settings: {
                slidesToShow: 5,
                autoplay: true,
                autoplaySpeed: 2500,
            }
        },
        {
            breakpoint: 910,
            settings: {
                slidesToShow: 4,
                autoplay: true,
                autoplaySpeed: 2500,
            }
        },
        {
            breakpoint: 730,
            settings: {
                slidesToShow: 3,
                autoplay: true,
                autoplaySpeed: 2500,
            }
        },
        {
            breakpoint: 410,
            settings: {
                slidesToShow: 2,
                autoplay: true,
                autoplaySpeed: 2500,
            }
        },
        ]
    });
};

window.initGeneralSliders = function () {
    if (typeof $ === 'undefined' || !$.fn.slick) {
        return;
    }

    $('.theme-slider').slick({
        dots: false,
        infinite: true,
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 7500,
    });
};
