<?php

namespace App\Application\Payment\Services;

class PaymentSensitiveDataMasker
{
    private const FULL_MASK_KEYS = [
        'cvc',
        'cvv',
        'cvv2',
        'security_code',
    ];

    private const CARD_MASK_KEYS = [
        'card_number',
        'credit_card_number',
        'creditcardnumber',
        'pan',
        'kart_no',
        'kartno',
        'number',
    ];

    public function mask(mixed $data): mixed
    {
        if (is_array($data)) {
            $masked = [];
            foreach ($data as $key => $value) {
                $masked[$key] = $this->maskByKey((string) $key, $value);
            }

            return $masked;
        }

        if (is_object($data)) {
            $array = json_decode(json_encode($data), true);
            if (!is_array($array)) {
                return $data;
            }

            return $this->mask($array);
        }

        return $data;
    }

    private function maskByKey(string $key, mixed $value): mixed
    {
        $normalizedKey = strtolower($key);

        if (in_array($normalizedKey, self::FULL_MASK_KEYS, true)) {
            return $this->maskFull($value);
        }

        if (in_array($normalizedKey, self::CARD_MASK_KEYS, true)) {
            return $this->maskCard($value);
        }

        if (is_array($value) || is_object($value)) {
            return $this->mask($value);
        }

        return $value;
    }

    private function maskFull(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return '***';
    }

    private function maskCard(mixed $value): string
    {
        $raw = preg_replace('/\D+/', '', (string) $value);
        if ($raw === '') {
            return '';
        }

        $length = strlen($raw);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        $last4 = substr($raw, -4);
        return str_repeat('*', $length - 4) . $last4;
    }
}
