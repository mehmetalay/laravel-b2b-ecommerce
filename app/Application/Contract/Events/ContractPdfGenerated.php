<?php

namespace App\Application\Contract\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContractPdfGenerated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $signatureId,
        public readonly string $actorType,
        public readonly string $pdfPath
    ) {}
}
