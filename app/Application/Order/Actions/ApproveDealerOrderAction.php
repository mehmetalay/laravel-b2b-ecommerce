<?php

namespace App\Application\Order\Actions;

use App\Models\Order;

class ApproveDealerOrderAction
{
    public function handle(Order $order, int $approvedByDealerId): bool
    {
        if ($order->status !== 'pending') {
            return false;
        }

        $order->update([
            'status' => 'approved',
            'approved_by_dealer' => $approvedByDealerId,
        ]);

        return true;
    }
}

