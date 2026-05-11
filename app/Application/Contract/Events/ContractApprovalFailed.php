<?php

namespace App\Application\Contract\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContractApprovalFailed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ?int $signatureId,
        public readonly string $actorType,
        public readonly int $actorId,
        public readonly int $templateId,
        public readonly string $reason
    ) {}
}
