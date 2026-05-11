<?php

namespace App\Console\Commands;

use App\Application\Mail\Services\PaymentLinkMailDispatchService;
use Illuminate\Console\Command;

class MailSendPendingPaymentLinkSuccessesCommand extends Command
{
    protected $signature = 'mail:send-pending-payment-link-successes {--limit=100} {--dry-run}';

    protected $description = 'Send pending payment link success mails';

    public function handle(PaymentLinkMailDispatchService $paymentLinkMailDispatchService): int
    {
        try {
            $result = $paymentLinkMailDispatchService->sendPendingPaymentLinkSuccesses(
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
            $this->error('mail:send-pending-payment-link-successes failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}

