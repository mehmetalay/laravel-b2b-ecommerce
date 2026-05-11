<?php

namespace App\Application\Contract\Support;

class ContractCacheKeys
{
    public static function smsCooldown(int $signatureId): string
    {
        return 'contract:sms:cooldown:' . $signatureId;
    }

    public static function smsAttempts(int $signatureId): string
    {
        return 'contract:sms:attempts:' . $signatureId;
    }
}
