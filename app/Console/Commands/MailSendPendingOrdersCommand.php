<?php

namespace App\Console\Commands;

use App\Application\Mail\Services\OrderMailDispatchService;
use Illuminate\Console\Command;

class MailSendPendingOrdersCommand extends Command
{
    protected $signature = 'mail:send-pending-orders {--limit=100} {--dry-run}';

    protected $description = 'Send pending order mails';

    public function handle(OrderMailDispatchService $orderMailDispatchService): int
    {
        try {
            $result = $orderMailDispatchService->sendPendingOrders(
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
            $this->error('mail:send-pending-orders failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}

