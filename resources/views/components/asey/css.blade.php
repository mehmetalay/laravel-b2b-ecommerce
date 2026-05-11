<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="{{ versioned_asset('asey/css/confirm.css') }}" rel="stylesheet" type="text/css"/>
<link href="{{ versioned_asset('asey/css/notifications.css') }}" rel="stylesheet" type="text/css"/>
<link href="{{ versioned_asset('asey/css/btn-loading.css') }}" rel="stylesheet" type="text/css"/>

<style>
    .cart-drop .product-box {
        animation: drop-product 0.6s cubic-bezier(.4,1.6,.6,1) forwards;
        transform-origin: center;
    }

    @keyframes drop-product {
        0% {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        70% {
            transform: translateY(9px) scale(1);
            opacity: 1;
        }
        100% {
            transform: translateY(9px) scale(0.2);
            opacity: 0;
        }
    }

    /* Sepete hafif bounce */
    .cart-drop .cart-path {
        animation: cart-bounce 0.25s ease-out forwards;
        animation-delay: 0.45s;
    }

    @keyframes cart-bounce {
        0%   { transform: scale(1); }
        50%  { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
</style>