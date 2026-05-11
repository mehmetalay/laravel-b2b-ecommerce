<?php

namespace App\Application\Order\Actions;

use App\Models\Order;
use App\Services\EtaOrderService;
use Illuminate\Console\Command;

class SyncPendingOrdersAction
{
    public function __construct(
        private EtaOrderService $etaOrderService
    ) {}

    public function handle(Command $command): int
    {
        $orders = Order::where('status', 'approved')
            ->where('erp_status', 'pending')
            ->where('erp_attempts', '<', 5)
            ->where(function ($query) {
                $query->whereNull('erp_processing_at')
                    ->orWhere('erp_processing_at', '<', now()->subMinutes(15));
            })
            ->get();

        $command->info("Found {$orders->count()} pending orders to sync.");

        foreach ($orders as $order) {
            $command->info("Processing Order ID: {$order->id}");

            try {
                $this->etaOrderService->sendOrder($order->id);
            } catch (\Exception $e) {
                $command->error("Failed to sync pending order ID {$order->id}");
            }
        }

        $command->info('Pending orders sync command completed.');

        return 0;
    }
}

