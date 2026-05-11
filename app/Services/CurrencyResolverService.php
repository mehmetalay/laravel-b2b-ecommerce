<?php

namespace App\Services;

use App\Models\Product;

class CurrencyResolverService
{
    public function resolve(
        Product $product,
        string $paymentType,
        $currentAccountService
    ): array {

        $currentAccount = $currentAccountService->currentAccount();

        $productCurrency = $product->getProductCurrency($paymentType);
        $accountCurrency = $currentAccount->currency;

        $exchangeType = null;
        $orderSeparately = 0;

        if ($accountCurrency !== $productCurrency) {
            $groupUsers = $currentAccountService->getGroupUsers($currentAccount);
            $hasProductCurrencyAccount = $groupUsers->count() === 1;

            if ($hasProductCurrencyAccount) {
                $orderSeparately = 1;
            } else {
                $exchangeType = "{$productCurrency}_TO_{$accountCurrency}";
                $productCurrency = $accountCurrency;
            }
        }

        return [
            'productCurrency' => $productCurrency,
            'accountCurrency' => $accountCurrency,
            'exchangeType' => $exchangeType,
            'orderSeparately' => $orderSeparately,
        ];
    }
}
