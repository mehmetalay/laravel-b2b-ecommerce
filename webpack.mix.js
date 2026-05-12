const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/frontend/modules/products/index.js', 'public/js/frontend/modules/products')
    .js('resources/js/frontend/modules/auth/login.js', 'public/js/frontend/modules/auth')
    .js('resources/js/frontend/modules/auth/forgot-password.js', 'public/js/frontend/modules/auth')
    .js('resources/js/frontend/modules/auth/reset-password.js', 'public/js/frontend/modules/auth')
    .js('resources/js/frontend/modules/auth/subdealer-login.js', 'public/js/frontend/modules/auth')
    .js('resources/js/frontend/modules/auth/subdealer-forgot-password.js', 'public/js/frontend/modules/auth')
    .js('resources/js/frontend/modules/auth/subdealer-reset-password.js', 'public/js/frontend/modules/auth')
    .js('resources/js/frontend/modules/order/orders-index.js', 'public/js/frontend/modules/order')
    .js('resources/js/frontend/modules/order/delivery.js', 'public/js/frontend/modules/order')
    .js('resources/js/frontend/modules/order/order.js', 'public/js/frontend/modules/order')
    .js('resources/js/frontend/modules/cart/index.js', 'public/js/frontend/modules/cart')
    .js('resources/js/frontend/modules/cart/add-to-cart.js', 'public/js/frontend/modules/cart')
    .js('resources/js/frontend/modules/collections/cashes-index.js', 'public/js/frontend/modules/collections')
    .js('resources/js/frontend/modules/collections/cheques-create.js', 'public/js/frontend/modules/collections')
    .js('resources/js/frontend/modules/collections/promissories-create.js', 'public/js/frontend/modules/collections')
    .js('resources/js/frontend/modules/mail/send-modal.js', 'public/js/frontend/modules/mail')
    .js('resources/js/frontend/modules/search/autocomplete.js', 'public/js/frontend/modules/search')
    .js('resources/js/frontend/modules/home/index.js', 'public/js/frontend/modules/home')
    .js('resources/js/backend/modules/campaigns/index.js', 'public/js/backend/modules/campaigns')
    .js('resources/js/backend/modules/homepage-blocks/index.js', 'public/js/backend/modules/homepage-blocks')
    .js('resources/js/backend/modules/import/erp-import.js', 'public/js/backend/modules/import')
    .js('resources/js/backend/modules/payments/index.js', 'public/js/backend/modules/payments')
    .js('resources/js/backend/modules/settings/additional-settings-tagify.js', 'public/js/backend/modules/settings')
    .js('resources/js/shared/index.js', 'public/js/shared')
    .version();
