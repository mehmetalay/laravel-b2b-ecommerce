<?php

namespace App\Application\Payment\Repositories;

use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PaymentRepository
{
    protected $cacheKeyAll = 'payment:v3:all';
    protected $cacheKeyFirst = 'payment:v3:first:';
    protected $model;

    public function __construct(Payment $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Payment
    {
        $payment = $this->model->create($data);

        $this->clearCache($payment->id);

        return $payment;
    }

    public function update(Payment $payment, array $data): Payment
    {
        $payment->update($data);

        $this->clearCache($payment->id);

        return $payment;
    }

    public function delete(Payment $payment)
    {
        $payment->delete();

        $this->clearCache($payment->id);

        return true;
    }

    public function getFirst($id)
    {
        $paymentId = (int) $id;
        if ($paymentId <= 0) {
            return null;
        }

        $ttlSeconds = $this->cacheTtlSeconds();
        if ($ttlSeconds <= 0) {
            return $this->model->find($paymentId);
        }

        $cacheKey = $this->cacheKeyFirst . $paymentId;

        return Cache::remember($cacheKey, now()->addSeconds($ttlSeconds), function () use ($paymentId) {
            return $this->model->find($paymentId);
        });
    }

    public function getAllPayments()
    {
        // Payment domaini hizla degisen bir domain oldugu icin liste cache'i tutulmaz.
        return $this->model->get();
    }

    public function clearCache($id)
    {
        $cacheKey = $this->cacheKeyFirst . $id;

        forget_cache_keys([
            $this->cacheKeyAll,
            $cacheKey
        ]);
    }

    public function markAsProcessing(int $paymentId): bool
    {
        $updated = $this->model
            ->where('id', $paymentId)
            ->where('erp_status', 'pending')
            ->where('erp_attempts', '<', 5)
            ->update([
                'erp_status' => 'processing',
                'erp_processing_at' => now(),
                'erp_attempts' => DB::raw('erp_attempts + 1')
            ]);

        if ($updated > 0) {
            $this->clearCache($paymentId);
            return true;
        }

        return false;
    }

    public function markAsSent(int $paymentId, $documentNo = null): void
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

        $this->model->where('id', $paymentId)->update($updateData);

        $this->clearCache($paymentId);
    }

    public function resetErpSync(int $paymentId): void
    {
        $this->model->where('id', $paymentId)->update([
            'erp_status' => 'pending',
            'erp_attempts' => 0,
            'erp_processing_at' => null,
            'erp_last_error' => null,
            'erp_last_failed_at' => null,
        ]);

        $this->clearCache($paymentId);
    }

    public function markAsFailed(int $paymentId, $error = null): void
    {
        $this->model->where('id', $paymentId)->update([
            'erp_status' => 'pending',
            'erp_processing_at' => null,
            'erp_last_error' => $error ?: null,
            'erp_last_failed_at' => now(),
        ]);

        $this->clearCache($paymentId);
    }

    private function cacheTtlSeconds(): int
    {
        return max(0, (int) config('payment.cache.payment_ttl_seconds', 15));
    }
}
