<?php

namespace App\Application\Order\Queries;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Services\Exports\OrderExportQueryService;
use Illuminate\Http\Request;

class AdminOrderTableDataQuery
{
    public function __construct(
        private OrderExportQueryService $orderExportQueryService
    ) {}

    public function handle(Request $request): array
    {
        $filters = $this->resolveTableFilters($request);
        $perPage = max(1, (int) $request->input('per_page', 100));
        $page = max(1, (int) $request->input('page', 1));

        $items = $this->orderExportQueryService
            ->build($filters)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $items->getCollection()->map(function (Order $item) {
            $userType = $this->resolveUserTypeBadge((string) $item->creator_type);
            $sentBadge = $this->resolveSentBadge((int) ($item->email_sent ?? 0));
            $erpBadge = $this->resolveErpBadge((string) ($item->erp_status ?? ''));

            return [
                'id' => $item->id,
                'creator_type' => $item->creator_type,
                'creator_type_label' => $userType['label'],
                'creator_type_class' => $userType['class'],
                'salesman_name' => $item->salesman_name,
                'dealer_name' => $item->dealer_name,
                'sub_dealer_name' => $item->subDealer ? $item->sub_dealer_name : null,
                'total_amount' => $item->total_amount,
                'formatted_created_at' => $item->formatted_created_at,
                'email_sent' => (int) ($item->email_sent ?? 0),
                'email_sent_label' => $sentBadge['label'],
                'email_sent_class' => $sentBadge['class'],
                'order_status' => $item->orderStatus?->name ?? '-',
                'order_status_class' => (string) ($item->orderStatus?->back_color_name ?? 'bg-secondary'),
                'erp_status' => (string) ($item->erp_status ?? ''),
                'erp_status_label' => $erpBadge['label'],
                'erp_status_class' => $erpBadge['class'],
                'show_url' => route('admin.orders.show', [$item->id]),
            ];
        })->values();

        $statusOptions = OrderStatus::query()
            ->orderBy('id', 'asc')
            ->get(['id', 'name'])
            ->map(fn (OrderStatus $statusItem) => [
                'value' => (string) $statusItem->id,
                'label' => (string) $statusItem->name,
            ])
            ->values()
            ->all();

        $erpStatusOptions = [
            ['value' => 'pending', 'label' => 'Beklemede'],
            ['value' => 'processing', 'label' => 'İşleniyor'],
            ['value' => 'sent', 'label' => 'Gönderildi'],
            ['value' => 'failed', 'label' => 'Gönderilmedi'],
        ];

        return [
            'data' => $data,
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ],
            'filters' => [
                'statusOptions' => array_merge([
                    ['value' => '', 'label' => 'Tümü'],
                ], $statusOptions),
                'stockStatusOptions' => array_merge([
                    ['value' => '', 'label' => 'Tümü'],
                ], $erpStatusOptions),
                'erpStatusOptions' => array_merge([
                    ['value' => '', 'label' => 'Tümü'],
                ], $erpStatusOptions),
            ],
        ];
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

    private function resolveSentBadge(int $value): array
    {
        return $value === 1
            ? ['label' => 'Gönderildi', 'class' => 'badge bg-success']
            : ['label' => 'Gönderilmedi', 'class' => 'badge bg-warning text-dark'];
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
