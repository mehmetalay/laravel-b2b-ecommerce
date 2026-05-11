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

mix.js('resources/js/frontend/products.js', 'public/js/frontend')
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
    .js('resources/js/frontend/modules/homepage/index.js', 'public/js/frontend/modules/homepage')
    .js('resources/js/admin/modules/campaigns/index.js', 'public/js/admin/modules/campaigns')
    .js('resources/js/admin/modules/homepage-blocks/index.js', 'public/js/admin/modules/homepage-blocks')
    .js('resources/js/admin/modules/import/erp-import.js', 'public/js/admin/modules/import')
    .js('resources/js/admin/modules/payments/index.js', 'public/js/admin/modules/payments')
    .js('resources/js/admin/modules/settings/additional-settings-tagify.js', 'public/js/admin/modules/settings')
    .js('resources/js/shared/index.js', 'public/js/shared')
    .version();
