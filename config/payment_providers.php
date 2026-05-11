<?php

return [
    'providers' => [
        App\Infrastructure\Payment\BankProviders\EstBankPaymentProvider::class,
        App\Infrastructure\Payment\BankProviders\AkbankPaymentProvider::class,
        App\Infrastructure\Payment\BankProviders\GarantiPaymentProvider::class,
        App\Infrastructure\Payment\BankProviders\VakifbankPaymentProvider::class,
        App\Infrastructure\Payment\BankProviders\VakifKatilimPaymentProvider::class,
        App\Infrastructure\Payment\BankProviders\FinansbankPaymentProvider::class,
        App\Infrastructure\Payment\BankProviders\DenizbankPaymentProvider::class,
        App\Infrastructure\Payment\BankProviders\YapiKrediPaymentProvider::class,
    ],
];

