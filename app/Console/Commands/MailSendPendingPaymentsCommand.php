<?php

namespace App\Console\Commands;

use App\Application\Mail\Services\PaymentMailDispatchService;
use Illuminate\Console\Command;

class MailSendPendingPaymentsCommand extends Command
{
    protected $signature = 'mail:send-pending-payments {--limit=100} {--dry-run}';

    protected $description = 'Send pending payment mails';

    public function handle(PaymentMailDispatchService $paymentMailDispatchService): int
    {
        try {
            $result = $paymentMailDispatchService->sendPendingPayments(
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
            $this->error('mail:send-pending-payments failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}

