<?php

namespace App\Application\Payment\Services;

use App\Models\Payment;
use App\Models\PaymentLink;

class PaymentCallbackSecurityService
{
    public function verifyPaymentSignature(Payment $payment, array $payload): bool
    {
        return $this->verifySignature($payment->bankIntegration?->json, $payload);
    }

    public function verifyPaymentLinkSignature(PaymentLink $paymentLink, array $payload): bool
    {
        return $this->verifySignature($paymentLink->bankIntegration?->json, $payload);
    }

    private function verifySignature($integrationJson, array $payload): bool
    {
        $signature = $this->value($payload, ['HASH', 'hash', 'Hash']);
        $hashParams = $this->value($payload, ['HASHPARAMS', 'hashparams', 'HashParams']);
        $storeKey = $this->resolveStoreKey($integrationJson);

        if ($signature === null || $hashParams === null) {
            return true;
        }

        if ($storeKey === null || $storeKey === '') {
            return false;
        }

        $params = array_filter(explode(':', (string) $hashParams), fn ($v) => $v !== '');
        $map = $this->lowercaseMap($payload);
        $computedParamsValue = '';

        foreach ($params as $param) {
            $computedParamsValue .= (string) ($map[strtolower($param)] ?? '');
        }

        $providedParamsValue = (string) ($this->value($payload, ['HASHPARAMSVAL', 'hashparamsval', 'HashParamsVal']) ?? '');

        if ($providedParamsValue !== '' && !hash_equals($providedParamsValue, $computedParamsValue)) {
            return false;
        }

        $plain = $computedParamsValue . $storeKey;
        $expectedSha1 = base64_encode(sha1($plain, true));
        $expectedSha512 = base64_encode(hash('sha512', $plain, true));

        return hash_equals($expectedSha1, (string) $signature)
            || hash_equals($expectedSha512, (string) $signature);
    }

    private function resolveStoreKey($integrationJson): ?string
    {
        if ($integrationJson === null || $integrationJson === '') {
            return null;
        }

        $info = is_string($integrationJson) ? json_decode($integrationJson, true) : json_decode(json_encode($integrationJson), true);
        if (!is_array($info)) {
            return null;
        }

        foreach (['store_key', 'storeKey', 'StoreKey', 'storekey'] as $key) {
            if (isset($info[$key]) && $info[$key] !== '') {
                return (string) $info[$key];
            }
        }

        return null;
    }

    private function value(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $payload) && $payload[$key] !== null && $payload[$key] !== '') {
                return (string) $payload[$key];
            }
        }

        return null;
    }

    private function lowercaseMap(array $payload): array
    {
        $result = [];
        foreach ($payload as $key => $value) {
            $result[strtolower((string) $key)] = $value;
        }

        return $result;
    }
}
