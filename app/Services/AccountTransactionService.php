<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Payment;
use App\Models\AccountTransaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class AccountTransactionService
{
    private ?bool $transactionsTableExists = null;

    public function createDebitForOrder(Order $order): ?AccountTransaction
    {
        if (!$this->isReady()) {
            return null;
        }

        [$userId, $currentAccountId, $currencySnapshot] = $this->resolveIdentitySnapshot(
            $order->user ?? null,
            $order->user_id,
            $order->currency
        );

        return $this->firstOrCreateBySourceKey(
            $this->sourceKeyForOrder($order->id),
            [
                'user_id' => $userId,
                'sub_dealer_id' => $order->sub_dealer_id,
                'plasiyer_id' => $order->plasiyer_id,
                'current_account_id' => $currentAccountId,
                'type' => 'order',
                'direction' => 'debit',
                'amount' => $this->normalizeAmount($order->total_price),
                'currency' => $currencySnapshot ?: 'TL',
                'transaction_date' => $order->created_at ?: now(),
                'due_date' => $order->created_at ?: now(),
                'description' => 'Siparis #' . $order->id,
                'reference_type' => Order::class,
                'reference_id' => $order->id,
                'status' => 'approved',
                'meta' => [
                    'order_status' => $order->status,
                    'order_status_id' => $order->order_status_id,
                    'creator_type' => $order->creator_type,
                    'raw_currency' => $order->currency,
                ],
            ]
        );
    }

    public function createCreditForPayment(Payment $payment): ?AccountTransaction
    {
        if (!$this->isReady()) {
            return null;
        }

        [$userId, $currentAccountId, $currencySnapshot] = $this->resolveIdentitySnapshot(
            $payment->user ?? null,
            $payment->user_id,
            null
        );

        return $this->firstOrCreateBySourceKey(
            $this->sourceKeyForPayment($payment->id),
            [
                'user_id' => $userId,
                'sub_dealer_id' => $payment->sub_dealer_id,
                'plasiyer_id' => $payment->plasiyer_id,
                'current_account_id' => $currentAccountId,
                'type' => 'payment',
                'direction' => 'credit',
                'amount' => $this->normalizeAmount($payment->amount_paid),
                'currency' => $currencySnapshot ?: 'TL',
                'transaction_date' => $payment->completed_at ?: ($payment->created_at ?: now()),
                'due_date' => $payment->completed_at ?: ($payment->created_at ?: now()),
                'description' => $payment->explanation ?: ('Odeme #' . $payment->id),
                'reference_type' => Payment::class,
                'reference_id' => $payment->id,
                'status' => 'approved',
                'meta' => [
                    'payment_status' => $payment->status,
                    'refund_status' => $payment->refund_status,
                    'raw_currency' => null,
                    'entered_amount' => $this->normalizeAmount($payment->entered_amount),
                ],
            ]
        );
    }

    public function createRefundForPayment(Payment $payment): ?AccountTransaction
    {
        if (!$this->isReady() || !$payment->refund_status) {
            return null;
        }

        [$userId, $currentAccountId, $currencySnapshot] = $this->resolveIdentitySnapshot(
            $payment->user ?? null,
            $payment->user_id,
            null
        );

        $creditTransaction = AccountTransaction::query()
            ->where('source_key', $this->sourceKeyForPayment($payment->id))
            ->first();

        return $this->firstOrCreateBySourceKey(
            $this->sourceKeyForPaymentRefund($payment->id),
            [
                'user_id' => $userId,
                'sub_dealer_id' => $payment->sub_dealer_id,
                'plasiyer_id' => $payment->plasiyer_id,
                'current_account_id' => $currentAccountId,
                'type' => 'refund',
                'direction' => 'debit',
                'amount' => $this->normalizeAmount($payment->amount_paid),
                'currency' => $currencySnapshot ?: 'TL',
                'transaction_date' => $payment->refund_date ?: now(),
                'due_date' => $payment->refund_date,
                'description' => 'Odeme iade/iptal #' . $payment->id,
                'reference_type' => Payment::class,
                'reference_id' => $payment->id,
                'status' => 'approved',
                'reversal_of_id' => $creditTransaction?->id,
                'meta' => [
                    'refund_status' => $payment->refund_status,
                    'refund_date' => $payment->refund_date,
                ],
            ]
        );
    }

    public function createManualAdjustment(array $payload): ?AccountTransaction
    {
        if (!$this->isReady()) {
            return null;
        }

        $sourceKey = $payload['source_key'] ?? ('manual-adjustment:' . uniqid());

        return $this->firstOrCreateBySourceKey(
            $sourceKey,
            [
                'user_id' => $payload['user_id'] ?? null,
                'sub_dealer_id' => $payload['sub_dealer_id'] ?? null,
                'plasiyer_id' => $payload['plasiyer_id'] ?? null,
                'current_account_id' => $payload['current_account_id'] ?? null,
                'type' => $payload['type'] ?? 'adjustment',
                'direction' => $payload['direction'] ?? 'debit',
                'amount' => $this->normalizeAmount($payload['amount'] ?? 0),
                'currency' => $payload['currency'] ?? 'TL',
                'transaction_date' => $payload['transaction_date'] ?? now(),
                'due_date' => $payload['due_date'] ?? null,
                'description' => $payload['description'] ?? null,
                'reference_type' => $payload['reference_type'] ?? null,
                'reference_id' => $payload['reference_id'] ?? null,
                'status' => $payload['status'] ?? 'approved',
                'reversal_of_id' => $payload['reversal_of_id'] ?? null,
                'meta' => $payload['meta'] ?? null,
            ]
        );
    }

    public function reverseTransaction(AccountTransaction $transaction, ?string $description = null): ?AccountTransaction
    {
        if (!$this->isReady()) {
            return null;
        }

        $reverseDirection = $transaction->direction === 'debit' ? 'credit' : 'debit';
        $sourceKey = 'reverse:account-transaction:' . $transaction->id;

        return $this->firstOrCreateBySourceKey(
            $sourceKey,
            [
                'user_id' => $transaction->user_id,
                'sub_dealer_id' => $transaction->sub_dealer_id,
                'plasiyer_id' => $transaction->plasiyer_id,
                'current_account_id' => $transaction->current_account_id,
                'type' => 'adjustment',
                'direction' => $reverseDirection,
                'amount' => $this->normalizeAmount($transaction->amount),
                'currency' => $transaction->currency,
                'transaction_date' => now(),
                'due_date' => null,
                'description' => $description ?: ('Reverse transaction #' . $transaction->id),
                'reference_type' => AccountTransaction::class,
                'reference_id' => $transaction->id,
                'status' => 'approved',
                'reversal_of_id' => $transaction->id,
                'meta' => [
                    'reversed_source_key' => $transaction->source_key,
                    'reversed_transaction_id' => $transaction->id,
                ],
            ]
        );
    }

    public function queryStatement(array $scope, ?string $startDate = null, ?string $endDate = null): Collection
    {
        if (!$this->isReady()) {
            return collect();
        }

        $query = AccountTransaction::query()->where('status', 'approved');
        $this->applyScope($query, $scope);

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        return $query
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();
    }

    public function transactionExists(string $sourceKey): bool
    {
        if (!$this->isReady()) {
            return false;
        }

        return AccountTransaction::withTrashed()
            ->where('source_key', $sourceKey)
            ->exists();
    }

    public function sourceKeyForOrder(int $orderId): string
    {
        return 'order:' . $orderId . ':debit';
    }

    public function sourceKeyForPayment(int $paymentId): string
    {
        return 'payment:' . $paymentId . ':credit';
    }

    public function sourceKeyForPaymentRefund(int $paymentId): string
    {
        return 'payment:' . $paymentId . ':refund';
    }

    private function firstOrCreateBySourceKey(string $sourceKey, array $attributes): ?AccountTransaction
    {
        $existing = AccountTransaction::withTrashed()
            ->where('source_key', $sourceKey)
            ->first();

        if ($existing) {
            return $existing;
        }

        try {
            return AccountTransaction::create(array_merge($attributes, ['source_key' => $sourceKey]));
        } catch (QueryException $exception) {
            return AccountTransaction::withTrashed()
                ->where('source_key', $sourceKey)
                ->first();
        }
    }

    private function resolveIdentitySnapshot($userRelation, $currentAccountValue, $fallbackCurrency = null): array
    {
        $user = $userRelation;

        if (!$user && $currentAccountValue) {
            $user = User::query()
                ->select(['id', 'current_account_id', 'currency'])
                ->where('current_account_id', $currentAccountValue)
                ->first();
        }

        return [
            $user?->id,
            $currentAccountValue ?: $user?->current_account_id,
            $user?->currency ?: $fallbackCurrency,
        ];
    }

    private function applyScope($query, array $scope): void
    {
        if (!empty($scope['current_account_id'])) {
            $query->where('current_account_id', $scope['current_account_id']);
        } elseif (!empty($scope['user_id'])) {
            $query->where('user_id', $scope['user_id']);
        }

        if (!empty($scope['sub_dealer_id'])) {
            $query->where('sub_dealer_id', $scope['sub_dealer_id']);
        }

        if (!empty($scope['plasiyer_id'])) {
            $query->where('plasiyer_id', $scope['plasiyer_id']);
        }
    }

    private function normalizeAmount($amount): string
    {
        return number_format((float) $amount, 4, '.', '');
    }

    private function isReady(): bool
    {
        if ($this->transactionsTableExists !== null) {
            return $this->transactionsTableExists;
        }

        return $this->transactionsTableExists = Schema::hasTable('account_transactions');
    }
}
