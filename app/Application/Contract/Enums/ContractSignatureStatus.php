<?php

namespace App\Application\Contract\Enums;

enum ContractSignatureStatus: string
{
    case PENDING = 'pending';
    case VERIFIED = 'verified';
}
