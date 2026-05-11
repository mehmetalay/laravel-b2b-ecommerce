<?php

namespace App\Application\Contract\Enums;

enum ContractActorType: string
{
    case DEALER = 'dealer';
    case SUBDEALER = 'subdealer';

    public static function tryFromMixed(string $value): ?self
    {
        return match (strtolower(trim($value))) {
            'dealer' => self::DEALER,
            'subdealer' => self::SUBDEALER,
            default => null,
        };
    }
}

