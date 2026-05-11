<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use App\Services\AccountTransactionService;

class TransactionsBackfillCommand extends Command
{
    protected $signature = 'transactions:backfill
                            {--dry-run : Simulate only, do not write account_transactions}
                            {--chunk=500 : Chunk size for batch processing}
                            {--only=orders,payments,refunds : Comma separated modules to backfill}';

    protected $description = 'Backfill account_transactions from existing orders/payments data';

    public function handle(AccountTransactionService $accountTransactionService): int
    {
        if (!Schema::hasTable('account_transactions')) {
            $this->error('Table account_transactions does not exist. Run migrations in a safe environment first.');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = max((int) $this->option('chunk'), 1);
        $only = collect(explode(',', (string) $this->option('only')))
            ->map(fn ($item) => trim(strtolower($item)))
            ->filter()
            ->values()
            ->all();

        if (empty($only)) {
            $only = ['orders', 'payments', 'refunds'];
        }

        $allowed = ['orders', 'payments', 'refunds'];
        $invalid = array_diff($only, $allowed);

        if (!empty($invalid)) {
            $this->error('Invalid --only options: ' . implode(', ', $invalid));
            return self::FAILURE;
        }

        $stats = [
            'orders' => ['candidates' => 0, 'created' => 0, 'skipped' => 0],
            'payments' => ['candidates' => 0, 'created' => 0, 'skipped' => 0],
            'refunds' => ['candidates' => 0, 'created' => 0, 'skipped' => 0],
        ];

        $this->info('transactions:backfill started' . ($dryRun ? ' [DRY-RUN]' : ''));
        $this->line('Chunk size: ' . $chunkSize);
        $this->line('Modules: ' . implode(', ', $only));

        if (in_array('orders', $only, true)) {
            Order::query()
                ->where('status', 'approved')
                ->whereNull('deleted_at')
                ->with(['user:id,current_account_id,currency'])
                ->orderBy('id')
                ->chunkById($chunkSize, function ($orders) use ($accountTransactionService, $dryRun, &$stats) {
                    foreach ($orders as $order) {
                        $stats['orders']['candidates']++;
                        $sourceKey = $accountTransactionService->sourceKeyForOrder($order->id);

                        if ($accountTransactionService->transactionExists($sourceKey)) {
                            $stats['orders']['skipped']++;
                            continue;
                        }

                        if ($dryRun) {
                            $stats['orders']['created']++;
                            continue;
                        }

                        $accountTransactionService->createDebitForOrder($order);
                        $stats['orders']['created']++;
                    }
                });
        }

        if (in_array('payments', $only, true)) {
            Payment::query()
                ->where('status', 'SUCCESS')
                ->with(['user:id,current_account_id,currency'])
                ->orderBy('id')
                ->chunkById($chunkSize, function ($payments) use ($accountTransactionService, $dryRun, &$stats) {
                    foreach ($payments as $payment) {
                        $stats['payments']['candidates']++;
                        $sourceKey = $accountTransactionService->sourceKeyForPayment($payment->id);

                        if ($accountTransactionService->transactionExists($sourceKey)) {
                            $stats['payments']['skipped']++;
                            continue;
                        }

                        if ($dryRun) {
                            $stats['payments']['created']++;
                            continue;
                        }

                        $accountTransactionService->createCreditForPayment($payment);
                        $stats['payments']['created']++;
                    }
                });
        }

        if (in_array('refunds', $only, true)) {
            Payment::query()
                ->where('status', 'SUCCESS')
                ->whereNotNull('refund_status')
                ->with(['user:id,current_account_id,currency'])
                ->orderBy('id')
                ->chunkById($chunkSize, function ($payments) use ($accountTransactionService, $dryRun, &$stats) {
                    foreach ($payments as $payment) {
                        $stats['refunds']['candidates']++;
                        $sourceKey = $accountTransactionService->sourceKeyForPaymentRefund($payment->id);

                        if ($accountTransactionService->transactionExists($sourceKey)) {
                            $stats['refunds']['skipped']++;
                            continue;
                        }

                        if ($dryRun) {
                            $stats['refunds']['created']++;
                            continue;
                        }

                        $accountTransactionService->createRefundForPayment($payment);
                        $stats['refunds']['created']++;
                    }
                });
        }

        $this->newLine();
        $this->table(
            ['module', 'candidates', $dryRun ? 'would_create' : 'created', 'skipped'],
            [
                ['orders', $stats['orders']['candidates'], $stats['orders']['created'], $stats['orders']['skipped']],
                ['payments', $stats['payments']['candidates'], $stats['payments']['created'], $stats['payments']['skipped']],
                ['refunds', $stats['refunds']['candidates'], $stats['refunds']['created'], $stats['refunds']['skipped']],
            ]
        );

        $this->info('transactions:backfill completed.');

        return self::SUCCESS;
    }
}
