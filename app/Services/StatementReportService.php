<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StatementReportService
{
    public function __construct(private AccountTransactionService $accountTransactionService) {}

    public function buildForCurrentAccount(object $currentAccount, array $userQuery, ?string $startDate, ?string $endDate): array
    {
        $scope = [
            'user_id' => $currentAccount->id ?? null,
            'sub_dealer_id' => $userQuery['sub_dealer_id'] ?? null,
            'plasiyer_id' => $userQuery['plasiyer_id'] ?? null,
            'current_account_id' => $currentAccount->current_account_id ?? ($userQuery['user_id'] ?? null),
            'legacy_user_id' => $userQuery['user_id'] ?? null,
        ];

        return $this->buildReportRows(
            $scope,
            $currentAccount->currency ?? 'TL',
            $startDate,
            $endDate
        );
    }

    public function buildForDealer(object $dealer, ?string $startDate = null, ?string $endDate = null): array
    {
        $scope = [
            'user_id' => $dealer->id ?? null,
            'current_account_id' => $dealer->current_account_id ?? null,
            'legacy_user_id' => $dealer->current_account_id ?? null,
        ];

        return $this->buildReportRows(
            $scope,
            $dealer->currency ?? 'TL',
            $startDate,
            $endDate
        );
    }

    private function buildReportRows(array $scope, string $defaultCurrency, ?string $startDate, ?string $endDate): array
    {
        if ((bool) config('features.transactions_only', false)) {
            $transactionReport = $this->buildFromTransactions($scope, $defaultCurrency, $startDate, $endDate);

            return $transactionReport ?? $this->emptyReport($defaultCurrency);
        }

        return $this->buildLegacyReportRows($scope, $defaultCurrency, $startDate, $endDate);
    }

    private function buildFromTransactions(array $scope, string $defaultCurrency, ?string $startDate, ?string $endDate): ?array
    {
        $items = $this->accountTransactionService->queryStatement($scope, $startDate, $endDate)
            ->map(function ($transaction) use ($defaultCurrency) {
                $date = $transaction->transaction_date
                    ? Carbon::parse($transaction->transaction_date)
                    : now();

                $dueDate = $transaction->due_date
                    ? Carbon::parse($transaction->due_date)
                    : $date;

                $amount = (float) $transaction->amount;
                $isDebit = $transaction->direction === 'debit';

                return [
                    'FISTARIHI' => $date->toDateString(),
                    'ISLEMTIPI' => $this->resolveTypeLabel($transaction->type),
                    'ACIKLAMA' => $transaction->description ?: '-',
                    'VADE' => $dueDate->toDateString(),
                    'BORC' => $isDebit ? $amount : 0.0,
                    'ALACAK' => $isDebit ? 0.0 : $amount,
                    'DOVKOD' => $transaction->currency ?: $defaultCurrency,
                ];
            })
            ->values();

        if ($items->isEmpty()) {
            return null;
        }

        $debtTotal = (float) $items->sum('BORC');
        $receivableTotal = (float) $items->sum('ALACAK');

        return [
            'items' => $items,
            'debtTotal' => $debtTotal,
            'receivableTotal' => $receivableTotal,
            'balance' => $debtTotal - $receivableTotal,
            'currency' => $defaultCurrency,
        ];
    }

    private function buildLegacyReportRows(array $scope, string $defaultCurrency, ?string $startDate, ?string $endDate): array
    {
        $orderRows = $this->orderRows($scope, $defaultCurrency, $startDate, $endDate);
        $paymentRows = $this->paymentRows($scope, $defaultCurrency, $startDate, $endDate);

        $items = $orderRows
            ->merge($paymentRows)
            ->sort(function (array $a, array $b) {
                return [$a['sort_date'], $a['sort_order']] <=> [$b['sort_date'], $b['sort_order']];
            })
            ->values()
            ->map(function (array $item) {
                unset($item['sort_date'], $item['sort_order']);
                return $item;
            });

        $debtTotal = (float) $items->sum('BORC');
        $receivableTotal = (float) $items->sum('ALACAK');

        return [
            'items' => $items,
            'debtTotal' => $debtTotal,
            'receivableTotal' => $receivableTotal,
            'balance' => $debtTotal - $receivableTotal,
            'currency' => $defaultCurrency,
        ];
    }

    private function orderRows(array $scope, string $defaultCurrency, ?string $startDate, ?string $endDate): Collection
    {
        $query = Order::query()
            ->where('status', 'approved');

        $this->applyLegacyScope($query, $scope);
        $this->applyDateRange($query, 'created_at', $startDate, $endDate);

        return $query
            ->orderBy('created_at')
            ->get()
            ->map(function (Order $order) use ($defaultCurrency) {
                $date = $order->created_at ? Carbon::parse($order->created_at) : now();
                $currency = $order->currency ?: $defaultCurrency;

                return [
                    'FISTARIHI' => $date->toDateString(),
                    'ISLEMTIPI' => 'Siparis',
                    'ACIKLAMA' => 'Siparis #' . $order->id,
                    'VADE' => $date->toDateString(),
                    'BORC' => (float) ($order->total_price ?? 0),
                    'ALACAK' => 0.0,
                    'DOVKOD' => $currency,
                    'sort_date' => $date->timestamp,
                    'sort_order' => 10,
                ];
            });
    }

    private function paymentRows(array $scope, string $defaultCurrency, ?string $startDate, ?string $endDate): Collection
    {
        $query = Payment::query()->where('status', 'SUCCESS');

        $this->applyLegacyScope($query, $scope);

        if ($startDate || $endDate) {
            $query->where(function ($innerQuery) use ($startDate, $endDate) {
                if ($startDate) {
                    $innerQuery->where(function ($dateQuery) use ($startDate) {
                        $dateQuery
                            ->whereDate('completed_at', '>=', $startDate)
                            ->orWhere(function ($fallbackQuery) use ($startDate) {
                                $fallbackQuery
                                    ->whereNull('completed_at')
                                    ->whereDate('created_at', '>=', $startDate);
                            });
                    });
                }

                if ($endDate) {
                    $innerQuery->where(function ($dateQuery) use ($endDate) {
                        $dateQuery
                            ->whereDate('completed_at', '<=', $endDate)
                            ->orWhere(function ($fallbackQuery) use ($endDate) {
                                $fallbackQuery
                                    ->whereNull('completed_at')
                                    ->whereDate('created_at', '<=', $endDate);
                            });
                    });
                }
            });
        }

        return $query
            ->orderBy('created_at')
            ->get()
            ->map(function (Payment $payment) use ($defaultCurrency) {
                $dateValue = $payment->completed_at ?: $payment->created_at;
                $date = $dateValue ? Carbon::parse($dateValue) : now();

                return [
                    'FISTARIHI' => $date->toDateString(),
                    'ISLEMTIPI' => 'Odeme',
                    'ACIKLAMA' => $payment->explanation ?: ('Odeme #' . $payment->id),
                    'VADE' => $date->toDateString(),
                    'BORC' => 0.0,
                    'ALACAK' => (float) ($payment->amount_paid ?? 0),
                    'DOVKOD' => $defaultCurrency,
                    'sort_date' => $date->timestamp,
                    'sort_order' => 20,
                ];
            });
    }

    private function applyLegacyScope($query, array $scope): void
    {
        if (!empty($scope['legacy_user_id'])) {
            $query->where('user_id', $scope['legacy_user_id']);
        }

        if (!empty($scope['sub_dealer_id'])) {
            $query->where('sub_dealer_id', $scope['sub_dealer_id']);
        }

        if (!empty($scope['plasiyer_id'])) {
            $query->where('plasiyer_id', $scope['plasiyer_id']);
        }
    }

    private function applyDateRange($query, string $column, ?string $startDate, ?string $endDate): void
    {
        if ($startDate) {
            $query->whereDate($column, '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate($column, '<=', $endDate);
        }
    }

    private function resolveTypeLabel(?string $type): string
    {
        return match ($type) {
            'order' => 'Siparis',
            'payment' => 'Odeme',
            'refund' => 'Iade',
            'adjustment' => 'Duzeltme',
            default => ucfirst((string) $type),
        };
    }

    private function emptyReport(string $defaultCurrency): array
    {
        return [
            'items' => collect(),
            'debtTotal' => 0.0,
            'receivableTotal' => 0.0,
            'balance' => 0.0,
            'currency' => $defaultCurrency,
        ];
    }
}
