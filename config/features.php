<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Toggle progressive rollouts without changing application code paths.
    |
    */
    'transactions_only' => env('TRANSACTIONS_ONLY', false),
];
