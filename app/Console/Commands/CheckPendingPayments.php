<?php

namespace App\Console\Commands;

use App\Application\Payment\DTO\PaymentStatusQueryResult;
use App\Application\Payment\Interfaces\BankPaymentStatusQueryableInterface;
use App\Application\Payment\Services\PaymentOrchestrationService;
use App\Application\Payment\Services\PaymentProviderRegistry;
use App\Models\Payment;
use App\Models\PaymentCallbackIdempotency;
use App\Application\Payment\Services\PaymentSensitiveDataMasker;
use App\Services\PaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckPendingPayments extends Command
{
    protected $signature = 'payments:check-pending
        {--minutes=15 : Minimum age (minutes) for pending payments}
        {--max-hours= : Optional maximum age window in hours}
        {--dry-run : Do not mutate status even if mark-failed is set}
        {--sync-bank-status : Query bank status and reconcile definitive success/failed}
        {--mark-failed : Mark eligible pending records as failed}
        {--limit=100 : Max record count}
        {--payment-id= : Check only one payment}';

    protected $description = 'Analyze pending payments and optionally reconcile stale records safely.';

    public function __construct(
        private PaymentProviderRegistry $paymentProviderRegistry,
        private PaymentOrchestrationService $paymentOrchestrationService,
        private PaymentService $paymentService,
        private PaymentSensitiveDataMasker $paymentSensitiveDataMasker
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $minutes = max(1, (int) $this->option('minutes'));
        $limit = max(1, (int) $this->option('limit'));
        $maxHours = $this->option('max-hours');
        $paymentId = $this->option('payment-id');
        $markFailedRequested = (bool) $this->option('mark-failed');
        $syncBankStatus = (bool) $this->option('sync-bank-status');
        $dryRun = (bool) $this->option('dry-run');
        $verbose = $this->output->isVerbose() || $this->output->isVeryVerbose() || $this->output->isDebug();

        $pendingCutoff = now()->subMinutes($minutes);
        $query = Payment::query()->where('status', 'PENDING');

        if ($paymentId !== null && $paymentId !== '') {
            $query->whereKey((int) $paymentId);
        } else {
            $query->where('created_at', '<', $pendingCutoff);
        }

        if ($maxHours !== null && $maxHours !== '') {
            $query->where('created_at', '>=', now()->subHours(max(1, (int) $maxHours)));
        }

        $paymentIds = $query
            ->orderBy('created_at')
            ->limit($limit)
            ->pluck('id');

        if ($paymentIds->isEmpty()) {
            $this->info('No pending payment found for given filters.');
            return self::SUCCESS;
        }

        $summary = [
            'analyzed' => 0,
            'provider_resolve_failed' => 0,
            'provider_status_check_supported' => 0,
            'provider_status_synced_attempted' => 0,
            'mark_failed_attempted' => 0,
            'mark_failed_skipped' => 0,
            'marked_failed' => 0,
            'reconciled_success' => 0,
            'reconciled_failed' => 0,
            'by_reason' => [
                'too_new' => 0,
                'no_provider_reference' => 0,
                'no_callback' => 0,
                'already_completed' => 0,
                'marked_failed' => 0,
                'skipped' => 0,
            ],
        ];

        logSession('payments:check-pending started', [
            'minutes' => $minutes,
            'max_hours' => $maxHours !== null && $maxHours !== '' ? (int) $maxHours : null,
            'limit' => $limit,
            'payment_id' => $paymentId !== null && $paymentId !== '' ? (int) $paymentId : null,
            'dry_run' => $dryRun,
            'mark_failed' => $markFailedRequested,
            'sync_bank_status' => $syncBankStatus,
        ], 'info', 'payment_logs');

        foreach ($paymentIds as $id) {
            $summary['analyzed']++;
            $result = $this->evaluatePendingPayment(
                paymentId: (int) $id,
                pendingCutoff: $pendingCutoff,
                markFailedRequested: $markFailedRequested,
                dryRun: $dryRun,
                syncBankStatus: $syncBankStatus
            );

            if (($result['provider_resolved'] ?? false) === false) {
                $summary['provider_resolve_failed']++;
            }

            if (($result['provider_status_check_supported'] ?? false) === true) {
                $summary['provider_status_check_supported']++;
            }

            if (($result['sync_bank_status_attempted'] ?? false) === true) {
                $summary['provider_status_synced_attempted']++;
            }

            if (($result['reconciled_result'] ?? null) === 'success') {
                $summary['reconciled_success']++;
            }

            if (($result['reconciled_result'] ?? null) === 'failed') {
                $summary['reconciled_failed']++;
            }

            if ($markFailedRequested) {
                $summary['mark_failed_attempted']++;
                if (($result['reason'] ?? '') === 'marked_failed') {
                    $summary['marked_failed']++;
                } else {
                    $summary['mark_failed_skipped']++;
                }
            }

            $reason = (string) ($result['reason'] ?? 'skipped');
            if (!array_key_exists($reason, $summary['by_reason'])) {
                $reason = 'skipped';
            }
            $summary['by_reason'][$reason]++;

            $record = $result;

            logSession('payments:check-pending analyzed payment', $record, 'info', 'payment_logs');

            if ($verbose) {
                $this->line(json_encode($record, JSON_UNESCAPED_UNICODE));
            }
        }

        logSession('payments:check-pending completed', $summary, 'info', 'payment_logs');
        $this->info('Analyzed: ' . $summary['analyzed']);
        $this->info('Provider resolved failed: ' . $summary['provider_resolve_failed']);
        $this->info('Provider status-check supported: ' . $summary['provider_status_check_supported']);
        $this->info('Provider status-sync attempted: ' . $summary['provider_status_synced_attempted']);
        $this->info('Reconciled success: ' . $summary['reconciled_success']);
        $this->info('Reconciled failed: ' . $summary['reconciled_failed']);
        $this->info('Marked failed: ' . $summary['marked_failed']);
        $this->info('Mark-failed skipped: ' . $summary['mark_failed_skipped']);
        $this->info('Reason counters: ' . json_encode($summary['by_reason'], JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }

    private function evaluatePendingPayment(
        int $paymentId,
        \Illuminate\Support\Carbon $pendingCutoff,
        bool $markFailedRequested,
        bool $dryRun,
        bool $syncBankStatus
    ): array {
        return DB::transaction(function () use ($paymentId, $pendingCutoff, $markFailedRequested, $dryRun, $syncBankStatus) {
            $payment = Payment::query()->whereKey($paymentId)->lockForUpdate()->first();
            if (!$payment) {
                return [
                    'payment_id' => $paymentId,
                    'reason' => 'skipped',
                    'skipped_reason' => 'payment_not_found',
                ];
            }
            $initialStatus = strtolower((string) $payment->status);

            $paymentIdempotencies = PaymentCallbackIdempotency::query()
                ->where('flow_type', 'payment')
                ->where('model_type', 'payment')
                ->where('model_id', $payment->id)
                ->orderByDesc('id')
                ->get();
            $latestIdempotency = $paymentIdempotencies->first();

            $providerResolved = false;
            $providerClass = null;
            $providerStatusCheckSupported = false;
            $providerResolveError = null;
            $statusQueryResult = PaymentStatusQueryResult::unsupported();
            $syncBankStatusAttempted = false;
            $reconciledResult = null;
            $reconcileSkippedReason = null;

            try {
                $provider = $this->paymentProviderRegistry->resolve((int) $payment->bank_integration_id);
                $providerResolved = true;
                $providerClass = get_class($provider);
                $providerStatusCheckSupported = $provider instanceof BankPaymentStatusQueryableInterface;

                if ($providerStatusCheckSupported) {
                    if (trim((string) ($payment->provider_reference ?? '')) === '') {
                        logSession(
                            "payments:check-pending provider_reference missing before status query. paymentId {$payment->id}",
                            [
                                'payment_id' => $payment->id,
                                'bank_integration_id' => $payment->bank_integration_id,
                                'oid' => $payment->oid,
                            ],
                            'warning',
                            'payment_logs'
                        );
                    }

                    $statusQueryResult = $this->paymentOrchestrationService->queryPaymentStatus($payment);
                    $statusQueryResult = $this->normalizeStatusQueryResult($statusQueryResult);
                    $syncBankStatusAttempted = $syncBankStatus;
                }
            } catch (\Throwable $e) {
                $providerResolveError = $e->getMessage();
            }

            $this->logStatusQueryEvent($statusQueryResult);

            $tooNew = $payment->created_at !== null && $payment->created_at->gte($pendingCutoff);
            $alreadyCompleted = $payment->completed_at !== null || strtoupper((string) $payment->status) !== 'PENDING';
            $hasProviderReference = trim((string) ($payment->provider_reference ?? '')) !== '';
            $hasCallback = $paymentIdempotencies->isNotEmpty();
            $abandonedPending = !$hasProviderReference && !$hasCallback;

            $reason = 'skipped';
            if ($alreadyCompleted) {
                $reason = 'already_completed';
            } elseif ($tooNew) {
                $reason = 'too_new';
            } elseif (!$hasProviderReference) {
                $reason = 'no_provider_reference';
            } elseif (!$hasCallback) {
                $reason = 'no_callback';
            }

            $markedFailed = false;
            $skipReason = null;

            if ($markFailedRequested) {
                if ($dryRun) {
                    $skipReason = 'dry_run_enabled';
                } elseif ($alreadyCompleted) {
                    $skipReason = 'already_completed';
                } elseif ($tooNew) {
                    $skipReason = 'too_new';
                } elseif (!$abandonedPending) {
                    $skipReason = 'not_abandoned_pending';
                } else {
                    // applyFailureTransition internally uses lockForUpdate and sets completed_at/failure_reason safely.
                    $this->paymentService->applyFailureTransition($payment, [
                        'failure_reason' => 'Pending payment marked as failed by payments:check-pending (no provider reference and no callback)',
                        'completed_at' => now(),
                    ]);
                    $markedFailed = true;
                    $reason = 'marked_failed';
                    $payment = $payment->fresh();
                }
            }

            if ($syncBankStatus && $providerStatusCheckSupported) {
                if ($alreadyCompleted) {
                    $reconcileSkippedReason = 'already_completed';
                } elseif (!$statusQueryResult->supported) {
                    $reconcileSkippedReason = 'provider_status_not_supported';
                } elseif ($statusQueryResult->status === 'success') {
                    if ($dryRun) {
                        $reconcileSkippedReason = 'dry_run_enabled';
                    } else {
                        $transition = $this->paymentService->applySuccessTransition($payment, [
                            'provider_reference' => $statusQueryResult->providerReference,
                            'AuthCode' => $statusQueryResult->authCode,
                            'Rrn' => $statusQueryResult->rrn,
                            'reconciled_by' => 'payments:check-pending',
                            'reconciled_at' => now(),
                        ]);

                        if ((bool) ($transition['transitioned'] ?? false)) {
                            $reconciledResult = 'success';
                            $payment = $transition['payment'];
                            $alreadyCompleted = true;
                            $reason = 'already_completed';

                        } else {
                            $reconcileSkippedReason = 'transition_not_applied';
                        }
                    }
                } elseif ($statusQueryResult->status === 'failed') {
                    if ($dryRun) {
                        $reconcileSkippedReason = 'dry_run_enabled';
                    } else {
                        $transition = $this->paymentService->applyFailureTransition($payment, [
                            'failure_reason' => $statusQueryResult->message ?: 'Bank status query returned failed',
                            'reconciled_by' => 'payments:check-pending',
                            'reconciled_at' => now(),
                        ]);

                        if ((bool) ($transition['transitioned'] ?? false)) {
                            $reconciledResult = 'failed';
                            $payment = $transition['payment'];
                            $alreadyCompleted = true;
                            $reason = 'already_completed';

                        } else {
                            $reconcileSkippedReason = 'transition_not_applied';
                        }
                    }
                } else {
                    $reconcileSkippedReason = 'bank_status_not_definitive';
                }
            }

            $record = [
                'payment_id' => (int) $payment->id,
                'status' => $payment->status,
                'oid' => $payment->oid,
                'bank_integration_id' => $payment->bank_integration_id,
                'provider_reference' => $payment->provider_reference,
                'created_at' => (string) $payment->created_at,
                'completed_at' => $payment->completed_at ? (string) $payment->completed_at : null,
                'failure_reason' => $payment->failure_reason,
                'refund_status' => $payment->refund_status,
                'callback_idempotency_count' => $paymentIdempotencies->count(),
                'callback_idempotency_last_status' => $latestIdempotency?->status,
                'provider_resolved' => $providerResolved,
                'provider_class' => $providerClass,
                'provider_status_check_supported' => $providerStatusCheckSupported,
                'provider_resolve_error' => $providerResolveError,
                'sync_bank_status_requested' => $syncBankStatus,
                'sync_bank_status_attempted' => $syncBankStatusAttempted,
                'reconciled_result' => $reconciledResult,
                'reconcile_skipped_reason' => $reconcileSkippedReason,
                'status_query_result' => [
                    'supported' => $statusQueryResult->supported,
                    'status' => $statusQueryResult->status,
                    'message' => $statusQueryResult->message,
                    'provider_reference' => $statusQueryResult->providerReference,
                    'auth_code' => $statusQueryResult->authCode,
                    'rrn' => $statusQueryResult->rrn,
                    'raw_payload' => $statusQueryResult->rawPayload,
                ],
                'abandoned_pending' => $abandonedPending,
                'no_provider_reference' => !$hasProviderReference,
                'no_callback' => !$hasCallback,
                'mark_failed_requested' => $markFailedRequested,
                'marked_failed' => $markedFailed,
                'reason' => $reason,
                'skipped_reason' => $skipReason,
            ];

            return $record;
        }, 3);
    }

    private function normalizeStatusQueryResult(PaymentStatusQueryResult $result): PaymentStatusQueryResult
    {
        $normalizedStatus = strtolower(trim($result->status));
        if (!in_array($normalizedStatus, ['success', 'failed', 'pending', 'unknown'], true)) {
            $normalizedStatus = 'unknown';
        }

        if ($normalizedStatus === 'failed' && $this->isRecordNotFoundMessage($result->message)) {
            $normalizedStatus = 'unknown';
        }

        return new PaymentStatusQueryResult(
            supported: $result->supported,
            status: $normalizedStatus,
            message: $result->message,
            providerReference: $result->providerReference,
            authCode: $result->authCode,
            rrn: $result->rrn,
            rawPayload: $result->rawPayload
        );
    }

    private function isRecordNotFoundMessage(?string $message): bool
    {
        $value = strtolower((string) $message);
        if ($value === '') {
            return false;
        }

        return str_contains($value, 'kayıt bulunamadı')
            || str_contains($value, 'kayit bulunamadi')
            || str_contains($value, 'not found')
            || str_contains($value, 'bulunamadi')
            || str_contains($value, 'order not found');
    }

    private function logStatusQueryEvent(PaymentStatusQueryResult $result): void
    {
        $context = [
            'supported' => $result->supported,
            'status' => $result->status,
            'message' => $result->message,
            'provider_reference' => $result->providerReference,
            'auth_code' => $result->authCode,
            'rrn' => $result->rrn,
            'raw_payload' => $result->rawPayload,
        ];

        logSession(
            'payments:check-pending provider status query result',
            $this->paymentSensitiveDataMasker->mask($context),
            'info',
            'payment_logs'
        );
    }
}
