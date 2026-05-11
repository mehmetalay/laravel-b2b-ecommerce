<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Hard Limits
    |--------------------------------------------------------------------------
    |
    | If max_amount is null or <= 0, no upper amount limit is enforced.
    |
    */
    'max_amount' => env('PAYMENT_MAX_AMOUNT') !== null
        ? (float) env('PAYMENT_MAX_AMOUNT')
        : null,

];
