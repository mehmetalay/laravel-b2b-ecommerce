<?php

namespace App\Jobs;

use App\Exports\OrdersExport;
use App\Exports\PaymentsExport;
use App\Exports\ProductsExport;
use App\Models\ExportJob;
use App\Services\Exports\OrderExportQueryService;
use App\Services\Exports\PaymentExportQueryService;
use App\Services\Exports\ProductExportQueryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class RunExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    private int $exportJobId;

    public function __construct(int $exportJobId)
    {
        $this->exportJobId = $exportJobId;
    }

    public function handle(
        ProductExportQueryService $productQueryService,
        PaymentExportQueryService $paymentQueryService,
        OrderExportQueryService $orderQueryService
    ): void
    {
        $exportJob = ExportJob::query()->find($this->exportJobId);

        if (!$exportJob) {
            return;
        }

        $exportJob->update([
            'status' => 'processing',
            'started_at' => now(),
            'error' => null,
        ]);

        try {
            if ($exportJob->type === 'products') {
                $this->handleProductsExport($exportJob, $productQueryService);
            } elseif ($exportJob->type === 'payments') {
                $this->handlePaymentsExport($exportJob, $paymentQueryService);
            } elseif ($exportJob->type === 'orders') {
                $this->handleOrdersExport($exportJob, $orderQueryService);
            } else {
                throw new \RuntimeException('Gecersiz export tipi');
            }
        } catch (\Throwable $e) {
            $exportJob->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }

    private function handleProductsExport(ExportJob $exportJob, ProductExportQueryService $queryService): void
    {
        $extension = strtolower((string) $exportJob->format) === 'csv' ? 'csv' : 'xlsx';
        $timestamp = now()->format('Y-m-d-His');
        $relativePath = "exports/products/products-{$timestamp}-{$exportJob->id}.{$extension}";
        $downloadName = "products-{$timestamp}.{$extension}";

        $filters = is_array($exportJob->filters) ? $exportJob->filters : [];
        $selectedIds = is_array($exportJob->selected_ids) ? $exportJob->selected_ids : null;

        $export = new ProductsExport($filters, $selectedIds);
        Excel::store($export, $relativePath, 'local');

        $totalRows = $queryService->build($filters, $selectedIds)->count();

        $exportJob->update([
            'status' => 'completed',
            'file_path' => $relativePath,
            'file_name' => $downloadName,
            'total_rows' => $totalRows,
            'completed_at' => now(),
        ]);
    }

    private function handlePaymentsExport(ExportJob $exportJob, PaymentExportQueryService $queryService): void
    {
        $extension = strtolower((string) $exportJob->format) === 'csv' ? 'csv' : 'xlsx';
        $timestamp = now()->format('Y-m-d-His');
        $relativePath = "exports/payments/payments-{$timestamp}-{$exportJob->id}.{$extension}";
        $downloadName = "payments-{$timestamp}.{$extension}";

        $filters = is_array($exportJob->filters) ? $exportJob->filters : [];
        $selectedIds = is_array($exportJob->selected_ids) ? $exportJob->selected_ids : null;

        $export = new PaymentsExport($filters, $selectedIds);
        Excel::store($export, $relativePath, 'local');

        $totalRows = $queryService->build($filters, $selectedIds)->count();

        $exportJob->update([
            'status' => 'completed',
            'file_path' => $relativePath,
            'file_name' => $downloadName,
            'total_rows' => $totalRows,
            'completed_at' => now(),
        ]);
    }

    private function handleOrdersExport(ExportJob $exportJob, OrderExportQueryService $queryService): void
    {
        $extension = strtolower((string) $exportJob->format) === 'csv' ? 'csv' : 'xlsx';
        $timestamp = now()->format('Y-m-d-His');
        $relativePath = "exports/orders/orders-{$timestamp}-{$exportJob->id}.{$extension}";
        $downloadName = "orders-{$timestamp}.{$extension}";

        $filters = is_array($exportJob->filters) ? $exportJob->filters : [];
        $selectedIds = is_array($exportJob->selected_ids) ? $exportJob->selected_ids : null;

        $export = new OrdersExport($filters, $selectedIds);
        Excel::store($export, $relativePath, 'local');

        $totalRows = $queryService->build($filters, $selectedIds)->count();

        $exportJob->update([
            'status' => 'completed',
            'file_path' => $relativePath,
            'file_name' => $downloadName,
            'total_rows' => $totalRows,
            'completed_at' => now(),
        ]);
    }
}
