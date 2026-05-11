<?php

namespace App\Services;

use App\Models\Currency;
use App\Http\Requests\CurrencyRequest;
use App\Repositories\CurrencyRepository;

class CurrencyService
{
    protected $repository;

    protected $currencies = [
        'TRY' => ['symbol' => '₺', 'position' => 'after'],
        'TL'  => ['symbol' => '₺', 'position' => 'after'],
        'USD' => ['symbol' => '$', 'position' => 'before'],
        'EUR' => ['symbol' => '€', 'position' => 'after'],
        'GBP' => ['symbol' => '£', 'position' => 'before'],
    ];

    public function __construct(CurrencyRepository $currencyRepository)
    {
        $this->repository = $currencyRepository;
    }

    public function getFirstByCode($code)
    {
        return $this->repository->getFirstByCode($code);
    }

    public function getAllCurrencies()
    {
        return $this->repository->getAllCurrencies();
    }

    public function update(CurrencyRequest $request, Currency $currency): Currency
    {
        return $this->repository->update($currency, $request->validated());
    }

    public function updateRaw($code, array $data)
    {
        $currency = $this->repository->getFirstByCode($code);
        return $this->repository->update($currency, $data);
    }
    
    public function convertToTL($price, string $code)
    {
        $currency = $this->getFirstByCode($code);
        if (!$currency) {
            return $price;
        }

        $sell = $currency->selling_price;
        if (!$sell || $sell <= 0) {
            return $price;
        }

        return ($price * $sell);
    }

    public function convertToUSD($price, string $code, $payment = false)
    {
        $currency = $this->getFirstByCode($code);
        if (!$currency) {
            return $price;
        }

        $sell = $currency->selling_price;

        if ($payment) {
            $sell = $currency->selling_price;
        }

        if (!$sell || $sell <= 0) {
            return $price;
        }

        return ($price / $sell);
    }

    public function formatPrice(float $amount, $currencyCode): string
    {
        $code = strtoupper($currencyCode);
        $symbol = isset($this->currencies[$code]) ? $this->currencies[$code]['symbol'] : '';
        $position = isset($this->currencies[$code]) ? $this->currencies[$code]['position'] : 'before';

        $formattedAmount = number_format($amount, additional_setting('decimal'));

        return $position === 'before'
            ? $symbol . $formattedAmount
            : $formattedAmount . ' ' . $symbol;
    }

    public function convert($price, $exchangeType, $product = null)
    {
        if ($exchangeType === null) {
            return $price;
        }

        $code = null;

        if ($exchangeType === 'USD_TO_TL' || $exchangeType === 'TL_TO_USD') {
            $code = 'USD';
        } elseif ($exchangeType === 'EUR_TO_TL' || $exchangeType === 'TL_TO_EUR') {
            $code = 'EUR';
        } elseif ($exchangeType === 'GBP_TO_TL' || $exchangeType === 'TL_TO_GBP') {
            $code = 'GBP';
        }

        if ($code === null) {
            return $price;
        }

        $sell = $this->getFirstByCode($code)->selling_price;

        if ($exchangeType === 'USD_TO_TL' || $exchangeType === 'EUR_TO_TL' || $exchangeType === 'GBP_TO_TL') {
            if ($product && $product->is_special_currency && $product->special_currency_rate != 0.00) {
                $sell = $product->special_currency_rate;
            }

            $convertedPrice = $price * $sell;
        } elseif ($exchangeType === 'TL_TO_USD' || $exchangeType === 'TL_TO_EUR' || $exchangeType === 'TL_TO_GBP') {
            $convertedPrice = $price / $sell;
        } else {
            $convertedPrice = $price;
        }

        return $convertedPrice;
    }
}
