<?php

namespace App\Infrastructure\Payment\BankProviders\Concerns;

trait MapsProviderFields
{
    protected function mapProviderFields(array $payload): array
    {
        $mapping = [
            'provider_reference' => ['provider_reference', 'TransactionId', 'transaction_id'],
            'provider_auth_code' => ['provider_auth_code', 'AuthCode', 'authcode', 'authCode'],
            'provider_rrn' => ['provider_rrn', 'HostRefNum', 'hostrefnum', 'rrn', 'Rrn'],
        ];

        $data = [];

        foreach ($mapping as $field => $keys) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $payload) && $payload[$key] !== null && $payload[$key] !== '') {
                    $data[$field] = $payload[$key];
                    break;
                }
            }
        }

        return $data;
    }
}

