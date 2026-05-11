<?php

namespace App\Console\Commands;

use App\Application\Mail\Services\PaymentMailDispatchService;
use Illuminate\Console\Command;

class SendPaymentMails extends Command
{
    protected $signature = 'payments:send-mails {--limit=200}';

    protected $description = 'Send payment success mails that are not sent yet';

    public function handle(PaymentMailDispatchService $service): int
    {
        logSession('payments:send-mails basladi', ['limit' => (int) $this->option('limit')], 'info', 'mail_logs');

        $result = $service->sendPendingPayments((int) $this->option('limit'), false);
        $count = (int) ($result['sent'] ?? 0);

        logSession('payments:send-mails bitti', ['sent' => $count], 'info', 'mail_logs');

        $this->info("Sent: {$count}");
        return self::SUCCESS;
    }
}

