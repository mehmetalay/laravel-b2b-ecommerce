<?php

namespace App\Console\Commands;

use App\Application\Mail\Services\DealerApplicationMailDispatchService;
use Illuminate\Console\Command;

class MailSendPendingDealerApplicationsCommand extends Command
{
    protected $signature = 'mail:send-pending-dealer-applications {--limit=100} {--dry-run}';

    protected $description = 'Send pending dealer application mails';

    public function handle(DealerApplicationMailDispatchService $dealerApplicationMailDispatchService): int
    {
        try {
            $result = $dealerApplicationMailDispatchService->sendPendingDealerApplicationMails(
                limit: (int) $this->option('limit'),
                dryRun: (bool) $this->option('dry-run')
            );

            $this->info('Processed: ' . ($result['processed'] ?? 0));
            $this->info('Sent: ' . ($result['sent'] ?? 0));
            $this->info('Failed: ' . ($result['failed'] ?? 0));
            $this->info('Dry run: ' . (($result['dry_run'] ?? false) ? 'yes' : 'no'));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            report($e);
            $this->error('mail:send-pending-dealer-applications failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}

