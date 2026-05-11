<?php

namespace App\Console\Commands;

use App\Application\Order\Actions\SyncPendingOrdersAction;
use Illuminate\Console\Command;

class SyncPendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:sync-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync pending orders to the ERP systems';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(SyncPendingOrdersAction $syncPendingOrdersAction)
    {
        return $syncPendingOrdersAction->handle($this);
    }
}
