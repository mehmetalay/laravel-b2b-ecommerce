<?php

namespace App\Application\Mail\Services;

use App\Application\DealerApplication\Services\DealerApplicationNotificationService;
use App\Models\DealerApplication;

class DealerApplicationMailDispatchService
{
    public function __construct(
        private DealerApplicationNotificationService $dealerApplicationNotificationService
    ) {}

    public function sendDealerApplicationMail(DealerApplication $dealerApplication): bool
    {
        return $this->dealerApplicationNotificationService->sendApplicationMail($dealerApplication);
    }

    public function sendPendingDealerApplicationMails(int $limit = 100, bool $dryRun = false): array
    {
        $items = DealerApplication::query()
            ->where('email_sent', 0)
            ->orderBy('id')
            ->limit(max(1, $limit))
            ->get();

        if ($dryRun) {
            return [
                'processed' => $items->count(),
                'sent' => 0,
                'failed' => 0,
                'dry_run' => true,
            ];
        }

        $sent = 0;
        $failed = 0;

        foreach ($items as $item) {
            if ($this->sendDealerApplicationMail($item)) {
                $sent++;
                continue;
            }

            $failed++;
        }

        return [
            'processed' => $items->count(),
            'sent' => $sent,
            'failed' => $failed,
            'dry_run' => false,
        ];
    }
}

