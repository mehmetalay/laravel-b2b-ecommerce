<?php

namespace App\Application\Order\Actions\Admin;

use App\Services\Exports\OrderExportQueryService;
use Illuminate\Http\Request;

class ExportOrdersAction
{
    public function __construct(
        private OrderExportQueryService $orderExportQueryService
    ) {}

    public function handle(Request $request)
    {
        $validated = $request->validate([
            'scope' => ['required', 'in:filtered,selected'],
            'ids' => ['required_if:scope,selected', 'array'],
            'ids.*' => ['required_if:scope,selected', 'integer', 'exists:orders,id'],
        ]);

        $scope = (string) $validated['scope'];
        $filters = $scope === 'selected' ? [] : $this->resolveTableFilters($request);
        $filename = 'orders-' . $scope . '-' . now()->format('Ymd-His') . '.csv';
        $selectedIds = $scope === 'selected'
            ? collect($validated['ids'] ?? [])->map(fn ($id) => (int) $id)->values()->all()
            : null;
        $query = $this->orderExportQueryService->build($filters, $selectedIds)->orderBy('id', 'asc');

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'wb');
            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'ID',
                'KULLANICI_TURU',
                'PLASIYER',
                'BAYI',
                'ALT_BAYI',
                'TOPLAM_TUTAR',
                'PARA_BIRIMI',
                'SIPARIS_TARIHI',
                'MAIL_DURUMU',
                'SIPARIS_DURUMU',
                'ERP_DURUMU',
            ]);

            $query->chunkById(500, function ($orders) use ($output) {
                foreach ($orders as $item) {
                    fputcsv($output, [
                        $item->id,
                        $this->resolveUserTypeBadge((string) $item->creator_type)['label'],
                        $item->plasiyer?->name ?? '-',
                        $item->user?->name ?? '-',
                        $item->subDealer?->name ?? '-',
                        round((float) ($item->total_price ?? 0), 2),
                        (string) ($item->currency ?? ''),
                        $item->formatted_created_at,
                        (int) ($item->email_sent ?? 0) === 1 ? 'Gönderildi' : 'Gönderilmedi',
                        $item->orderStatus?->name ?? '-',
                        $this->resolveErpBadge((string) ($item->erp_status ?? ''))['label'],
                    ]);
                }
            });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    private function resolveUserTypeBadge(string $value): array
    {
        return match ($value) {
            'salesman' => ['label' => 'Plasiyer', 'class' => 'badge bg-secondary'],
            'dealer' => ['label' => 'Bayi', 'class' => 'badge bg-primary'],
            'subdealer' => ['label' => 'Alt Bayi', 'class' => 'badge bg-info'],
            default => ['label' => '-', 'class' => 'badge bg-secondary'],
        };
    }

    private function resolveErpBadge(string $value): array
    {
        return match ($value) {
            'processing' => ['label' => 'İşleniyor', 'class' => 'badge bg-warning text-dark'],
            'pending' => ['label' => 'Beklemede', 'class' => 'badge bg-info'],
            'sent' => ['label' => 'Gönderildi', 'class' => 'badge bg-success'],
            'failed' => ['label' => 'Gönderilmedi', 'class' => 'badge bg-danger'],
            default => ['label' => '-', 'class' => 'badge bg-secondary'],
        };
    }

    private function resolveTableFilters(Request $request): array
    {
        return [
            'search' => $request->input('search', $request->input('q', '')),
            'status' => $request->input('status'),
            'stock_status' => $request->input('stock_status', $request->input('erp_status')),
            'first_date' => $request->input('first_date'),
            'last_date' => $request->input('last_date'),
        ];
    }
}
