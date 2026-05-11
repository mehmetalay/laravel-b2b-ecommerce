<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    protected $model;
    private const ORDER_CACHE_TTL_SECONDS = 120;

    public function __construct(Order $model)
    {
        $this->model = $model;
    }

    protected $cacheKey = 'order:first:';

    public function getFirst($id)
    {
        $cacheKey = $this->cacheKey . $id;

        return Cache::remember($cacheKey, now()->addSeconds(self::ORDER_CACHE_TTL_SECONDS), function () use ($id) {
            return $this->model
                ->with(['orderProducts.product', 'user'])
                ->find($id);
        });
    }

    public function create(array $data): Order
    {
        $order = $this->model->create($data);

        $this->clearCache($order->id);

        return $order;
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        $this->clearCache($order->id);

        return $order;
    }

    public function delete(Order $order)
    {
        $order->delete();

        $this->clearCache($order->id);

        return true;
    }

    public function clearCache($id)
    {
        $cacheKey = $this->cacheKey . $id;

        forget_cache_keys([
            $cacheKey
        ]);
    }

    public function markAsProcessing(int $orderId): bool
    {
        $updated = $this->model
            ->where('id', $orderId)
            ->where('erp_status', 'pending')
            ->where('erp_attempts', '<', 5)
            ->update([
                'erp_status' => 'processing',
                'erp_processing_at' => now(),
                'erp_attempts' => DB::raw('erp_attempts + 1')
            ]);

        if ($updated > 0) {
            $this->clearCache($orderId);
            return true;
        }

        return false;
    }

    public function markAsSent(int $orderId, $documentNo = null): void
    {
        $updateData = [
            'erp_status' => 'sent',
            'erp_synced_at' => now(),
            'erp_last_error' => null,
            'erp_last_failed_at' => null,
            'erp_processing_at' => null
        ];

        if ($documentNo) {
            $updateData['erp_document_no'] = $documentNo;
        }

        $this->model->where('id', $orderId)->update($updateData);

        $this->clearCache($orderId);
    }

    public function markAsFailed(int $orderId, $error = null): void
    {
        $this->model->where('id', $orderId)->update([
            'erp_status' => 'pending',
            'erp_processing_at' => null,
            'erp_last_error' => $error ?: null,
            'erp_last_failed_at' => now(),
        ]);

        $this->clearCache($orderId);
    }

    public function resetErpSync(int $orderId): void
    {
        $this->model->where('id', $orderId)->update([
            'erp_status' => 'pending',
            'erp_attempts' => 0,
            'erp_processing_at' => null,
            'erp_last_error' => null,
            'erp_last_failed_at' => null,
        ]);

        $this->clearCache($orderId);
    }
}
