<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\EtaBankService;
use Illuminate\Console\Command;

class SyncPendingPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:sync-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync pending payments to the ERP system (ETA)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(EtaBankService $etaBankService)
    {
        $payments = Payment::where('status', 'SUCCESS')
                    ->where('erp_status', 'pending')
                    ->where('erp_attempts', '<', 5)
                    ->where(function ($query) {
                        $query->whereNull('erp_processing_at')
                              ->orWhere('erp_processing_at', '<', now()->subMinutes(15));
                    })
                    ->get();

        $this->info("Found {$payments->count()} pending payments to sync.");

        foreach ($payments as $payment) {
            $this->info("Processing Payment ID: {$payment->id}");

            try {
                $etaBankService->sendPosTransaction($payment->id);
            } catch (\Exception $e) {
                $this->error("Failed to sync pending payment ID {$payment->id}");
            }
        }

        $this->info('Pending payments sync command completed.');

        return 0;
    }
}
