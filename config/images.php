<?php

return [
    'paths' => [
        'products' => [
            'small' => 'assets/images/products/small/',
            'large' => 'assets/images/products/large/',
        ],
        'categories' => 'assets/images/categories/',
        'brands' => 'assets/images/brands/',
        'slider' => [
            'desktop' => 'assets/images/slider/',
            'tablet' => 'assets/images/slider/tablet/',
            'mobile' => 'assets/images/slider/mobile/'
        ],
        'payment_slider' => [
            'desktop' => 'assets/images/slider/payment/',
            'tablet' => 'assets/images/slider/payment/tablet/',
            'mobile' => 'assets/images/slider/payment/mobile/',
        ],
        'category_slider' => [
            'desktop' => 'assets/images/slider/category/',
            'tablet' => 'assets/images/slider/category/tablet/',
            'mobile' => 'assets/images/slider/category/mobile/',
        ],
        'campaign_slider' => [
            'desktop' => 'assets/images/slider/campaign/',
            'tablet' => 'assets/images/slider/campaign/tablet/',
            'mobile' => 'assets/images/slider/campaign/mobile/',
        ],
        'slider_payment' => [
            'desktop' => 'assets/images/slider/payment/',
            'tablet' => 'assets/images/slider/payment/tablet/',
            'mobile' => 'assets/images/slider/payment/mobile/'
        ],
        'images' => 'assets/images/',
        'favicon' => 'assets/images/favicons/',
    ],
    'sizes' => [
        'large' => [
            'width' => 1280,
            'height' => 1280
        ],
        'small' => [
            'width' => 310,
            'height' => 310
        ],
        'slider' => [
            'desktop' => [
                'width' => 1920,
                'height' => 600,
                'recommended_resolution' => '1920 x 600',
            ],
            'tablet' => [
                'width' => 1024,
                'height' => 500,
                'recommended_resolution' => '1024 x 500',
            ],
            'mobile' => [
                'width' => 768,
                'height' => 1000,
                'recommended_resolution' => '768 x 1000',
            ],
        ],

        'payment_slider' => [
            'desktop' => [
                'width' => 1920,
                'height' => 600,
                'recommended_resolution' => '1920 x 600',
            ],
            'tablet' => [
                'width' => 1024,
                'height' => 500,
                'recommended_resolution' => '1024 x 500',
            ],
            'mobile' => [
                'width' => 768,
                'height' => 1000,
                'recommended_resolution' => '768 x 1000',
            ],
        ],

        'category_slider' => [
            'desktop' => [
                'width' => 436,
                'height' => 545,
                'recommended_resolution' => '436 × 545 px',
            ],
            'tablet' => [
                'width' => 512,
                'height' => 640,
                'recommended_resolution' => '512 × 640 px',
            ],
            'mobile' => [
                'width' => 768,
                'height' => 960,
                'recommended_resolution' => '768 × 960 px',
            ],
        ],

        'campaign_slider' => [
            'desktop' => [
                'width' => 436,
                'height' => 545,
                'recommended_resolution' => '436 × 545 px',
            ],
            'tablet' => [
                'width' => 512,
                'height' => 640,
                'recommended_resolution' => '512 × 640 px',
            ],
            'mobile' => [
                'width' => 768,
                'height' => 960,
                'recommended_resolution' => '768 × 960 px',
            ],
        ],

        'slider_payment' => [
            'width' => 1920,
            'height' => 600,
            'recommended_resolution' => '1920 x 600'
        ],
        'category' => [
            'width' => 300,
            'height' => 300,
            'recommended_resolution' => '300 x 300'
        ],
        'brand' => [
            'width' => 360,
            'height' => 75,
            'recommended_resolution' => '360 x 75'
        ],
        'logo' => [
            'width' => 500,
            'height' => 225,
            'recommended_resolution' => '500 x 225'
        ],
        'footer_logo' => [
            'width' => 500,
            'height' => 225,
            'recommended_resolution' => '500 x 225'
        ],
        'footer_ssl_image' => [
            'width' => null,
            'height' => null,
            'recommended_resolution' => 'İstediğiniz ebatı ayarlayabilirsiniz'
        ],
        'image_og' => [
            'width' => 500,
            'height' => 500
        ],
        'lazy' => [
            'width' => 50,
            'height' => 50
        ],
        'no_image' => [
            'recommended_resolution' => '900 x 900'
        ],
        'favicon' => [
            'android' => [
                'width' => 192,
                'height' => 192,
                'background' => '#ffffff'
            ],
            'android_512' => [
                'width' => 512,
                'height' => 512,
                'background' => '#ffffff'
            ],
            'apple' => [
                'width' => 180,
                'height' => 180,
                'background' => '#ffffff'
            ],
            'favicon_16' => [
                'width' => 16,
                'height' => 16,
                'background' => 'transparent'
            ],
            'favicon_32' => [
                'width' => 32,
                'height' => 32,
                'background' => 'transparent'
            ],
            'mstile_150' => [
                'width' => 150,
                'height' => 150,
                'background' => '#ffffff'
            ],
            'maskable_192' => [
                'width' => 192,
                'height' => 192,
                'background' => '#ffffff'
            ],
            'maskable_512' => [
                'width' => 512,
                'height' => 512,
                'background' => '#ffffff'
            ],
            'recommended_resolution' => '512 x 512'
        ]
    ],
    'quality' => [
        'high' => 100,
        'medium' => 70,
        'low' => 25,
    ],
    'default' => [
        'logo' => 'logo.png',
        'footer_logo' => 'footer-logo.png',
        'footer_ssl_image' => 'footer-ssl-logo.png',
        'no_image' => 'urun-gorseli-hazirlaniyor.jpg',
        'image_og' => 'image-og.jpg',
        'country' => [
            'tr' => 'tr.png',
            'en' => 'en.png',
        ],
        '404' => '404.png',
        'lazy_load' => 'urun-gorseli-hazirlaniyor.jpg',
        'favicon' => [
            'android' => 'android-chrome-192x192.png',
            'android_512' => 'android-chrome-512x512.png',
            'apple' => 'apple-touch-icon.png',
            'favicon_16' => 'favicon-16x16.png',
            'favicon_32' => 'favicon-32x32.png',
            'mstile_150' => 'mstile-150x150.png',
            'maskable_192' => 'maskable-icon-192x192.png',
            'maskable_512' => 'maskable-icon-512x512.png',
        ]
    ],
];
