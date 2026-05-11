<?php

namespace App\Services;

use App\Application\Payment\Services\PaymentStateMachine;
use App\Application\Payment\Enums\PaymentStatus;
use App\Models\Payment;
use App\Mail\PaymentMail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\CurrencyService;
use App\Application\Payment\Services\PaymentSensitiveDataMasker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Application\Payment\Repositories\PaymentRepository;
use RuntimeException;

class PaymentService
{
    protected $repository;
    protected AccountTransactionService $accountTransactionService;
    protected PaymentStateMachine $paymentStateMachine;
    protected PaymentSensitiveDataMasker $paymentSensitiveDataMasker;

    public function __construct(
        PaymentRepository $paymentRepository,
        AccountTransactionService $accountTransactionService,
        PaymentStateMachine $paymentStateMachine,
        PaymentSensitiveDataMasker $paymentSensitiveDataMasker
    )
    {
        $this->repository = $paymentRepository;
        $this->accountTransactionService = $accountTransactionService;
        $this->paymentStateMachine = $paymentStateMachine;
        $this->paymentSensitiveDataMasker = $paymentSensitiveDataMasker;
    }

    public function getFirst($id)
    {
        return $this->repository->getFirst($id);
    }

    public function getAllPayments()
    {
        return $this->repository->getAllPayments();
    }

    public function create(Request $request)
    {
        return $this->repository->create($request->all());
    }

    public function update(Request $request, Payment $payment): Payment
    {
        return $this->repository->update($payment, $request->all());
    }

    public function updateRaw($id, array $data)
    {
        $payment = $this->repository->getFirst($id);

        return $this->repository->update($payment, $data);
    }

    public function handleSuccess(Payment $payment, array $payload = []): Payment
    {
        return $this->applySuccessTransition($payment, $payload)['payment'];
    }

    public function handleFailure(Payment $payment, array $payload = []): Payment
    {
        return $this->applyFailureTransition($payment, $payload)['payment'];
    }

    public function refund(Payment $payment, array $payload = []): Payment
    {
        return $this->applyRefundTransition($payment, $payload)['payment'];
    }

    public function applySuccessTransition(Payment $payment, array $payload = []): array
    {
        $transition = DB::transaction(function () use ($payment, $payload) {
            $lockedPayment = $this->lockPayment($payment);
            $currentStatus = $this->paymentStateMachine->resolveState(
                (string) $lockedPayment->status,
                $lockedPayment->refund_status
            );
            $this->assertTransitionSafety($lockedPayment, $payload);

            if ($this->paymentStateMachine->isSuccessTerminal($currentStatus)) {
                return $this->buildTransitionResult($lockedPayment, $currentStatus, $currentStatus, false, $payload);
            }

            if (!$this->paymentStateMachine->canTransitionToSuccess($currentStatus)) {
                return $this->buildTransitionResult($lockedPayment, $currentStatus, $currentStatus, false, $payload);
            }

            $updateData = array_merge(
                [
                    'status' => $this->paymentStateMachine->toStoredStatus(PaymentStatus::SUCCESS),
                    'completed_at' => $payload['completed_at'] ?? now(),
                    'failure_reason' => null,
                ],
                $this->extractProviderFields($payload)
            );

            $lockedPayment->update($updateData);
            $lockedPayment = $lockedPayment->fresh();

            $this->accountTransactionService->createCreditForPayment($lockedPayment);

            return $this->buildTransitionResult(
                $lockedPayment,
                $currentStatus,
                $this->paymentStateMachine->resolveState(
                    (string) $lockedPayment->status,
                    $lockedPayment->refund_status
                ),
                true,
                $payload
            );
        });

        $resolvedPayment = $transition['payment'] ?? null;
        if ($resolvedPayment instanceof Payment) {
            $providerReference = trim((string) ($resolvedPayment->provider_reference ?? ''));
            if ($providerReference === '') {
                logSession(
                    "Payment success without provider_reference. paymentId {$resolvedPayment->id}",
                    [
                        'payment_id' => $resolvedPayment->id,
                        'bank_integration_id' => $resolvedPayment->bank_integration_id,
                        'oid' => $resolvedPayment->oid,
                        'status' => $resolvedPayment->status,
                    ],
                    'warning',
                    'payment_logs'
                );
            }
        }

        return $transition;
    }

    public function applyFailureTransition(Payment $payment, array $payload = []): array
    {
        $transition = DB::transaction(function () use ($payment, $payload) {
            $lockedPayment = $this->lockPayment($payment);
            $currentStatus = $this->paymentStateMachine->resolveState(
                (string) $lockedPayment->status,
                $lockedPayment->refund_status
            );
            $this->assertTransitionSafety($lockedPayment, $payload);

            if ($this->paymentStateMachine->isFailureTerminal($currentStatus)) {
                return $this->buildTransitionResult($lockedPayment, $currentStatus, $currentStatus, false, $payload);
            }

            if (!$this->paymentStateMachine->canTransitionToFailure($currentStatus)) {
                return $this->buildTransitionResult($lockedPayment, $currentStatus, $currentStatus, false, $payload);
            }

            $updateData = array_merge(
                [
                    'status' => $this->paymentStateMachine->toStoredStatus(PaymentStatus::FAILED),
                    'completed_at' => $payload['completed_at'] ?? now(),
                ],
                $this->extractProviderFields($payload)
            );

            if (isset($payload['failure_reason'])) {
                $updateData['failure_reason'] = $payload['failure_reason'];
            }

            $lockedPayment->update($updateData);
            $lockedPayment = $lockedPayment->fresh();

            return $this->buildTransitionResult(
                $lockedPayment,
                $currentStatus,
                $this->paymentStateMachine->resolveState(
                    (string) $lockedPayment->status,
                    $lockedPayment->refund_status
                ),
                true,
                $payload
            );
        });

        return $transition;
    }

    public function applyRefundTransition(Payment $payment, array $payload = []): array
    {
        $transition = DB::transaction(function () use ($payment, $payload) {
            $lockedPayment = $this->lockPayment($payment);
            $currentStatus = $this->paymentStateMachine->resolveState(
                (string) $lockedPayment->status,
                $lockedPayment->refund_status
            );
            $this->assertTransitionSafety($lockedPayment, $payload);

            if ($this->paymentStateMachine->isRefundAlreadyApplied($currentStatus)) {
                if (!$lockedPayment->refund_status) {
                    $lockedPayment->update([
                        'refund_status' => 'refunded',
                        'refund_date' => $lockedPayment->refund_date ?? now(),
                    ]);

                    $lockedPayment = $lockedPayment->fresh();
                }

                $refundSourceKey = $this->accountTransactionService->sourceKeyForPaymentRefund($lockedPayment->id);

                if (!$this->accountTransactionService->transactionExists($refundSourceKey)) {
                    $this->accountTransactionService->createRefundForPayment($lockedPayment);
                }

                return $this->buildTransitionResult($lockedPayment, $currentStatus, $currentStatus, false, $payload);
            }

            if (!$this->paymentStateMachine->canTransitionToRefund($currentStatus)) {
                return $this->buildTransitionResult($lockedPayment, $currentStatus, $currentStatus, false, $payload);
            }

            $refundStatus = $this->paymentStateMachine->resolveRefundStatus($payload);
            $refundDate = $payload['refund_date'] ?? now();

            $updateData = [
                'status' => $this->paymentStateMachine->toStoredStatus($refundStatus),
                'refund_status' => $refundStatus->value,
                'refund_date' => $refundDate,
            ];

            $lockedPayment->update($updateData);
            $lockedPayment = $lockedPayment->fresh();

            // Idempotent by source key in AccountTransactionService.
            $this->accountTransactionService->createRefundForPayment($lockedPayment);

            return $this->buildTransitionResult(
                $lockedPayment,
                $currentStatus,
                $this->paymentStateMachine->resolveState(
                    (string) $lockedPayment->status,
                    $lockedPayment->refund_status
                ),
                true,
                $payload
            );
        });

        return $transition;
    }

    public function generatePaymentReceiptPdf($model, $modelName, $download = false)
    {
        if ($modelName == 'payment') {
            $view = 'pdf.payment';
            $prefix = 'online-odeme-makbuzu';
        } else {
            $view = 'pdf.payment-link';
            $prefix = 'odeme-linki-makbuzu';
        }

        $currencyService = app(CurrencyService::class);

        $formattedInstallment = $model->formatted_installment;
        $amountPaid = $model->amount_paid;
        $monthlyPaymentAmount = $model->monthly_payment_amount;
        $amountPaidUSD = $currencyService->convertToUSD($amountPaid, 'USD', true);

        $USDExchangeRate = $model->usd_exchange_rate;

        $spokenPrice = convert_price_to_text($amountPaid);

        $pdf = PDF::loadView($view, compact('model', 'formattedInstallment', 'amountPaid', 'monthlyPaymentAmount', 'amountPaidUSD', 'USDExchangeRate', 'spokenPrice'))
            ->setPaper('a4', 'landscape');

        $fileName = "{$prefix}-{$model->id}.pdf";

        if ($download) {
            $directory = public_path('xml/tahsilat-makbuzlari');

            create_directory($directory);

            $filePath = $directory . '/' . $fileName;

            file_put_contents($filePath, $pdf->output());

            return true;
        }

        $pdfContent = $pdf->output();

        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="' . $fileName . '"');

        return $response;
    }

    public function generateBulkPaymentReceiptPdf($models)
    {
        $currencyService = app(CurrencyService::class);

        $receipts = [];

        foreach ($models as $model) {
            $receipts[] = [
                'model' => $model,
                'formattedInstallment' => $model->formatted_installment,
                'amountPaid' => $model->amount_paid,
                'monthlyPaymentAmount' => $model->monthly_payment_amount,
                'amountPaidUSD' => $currencyService->convertToUSD(
                    $model->amount_paid,
                    'USD',
                    true
                ),
                'USDExchangeRate' => $model->usd_exchange_rate,
                'spokenPrice' => convert_price_to_text($model->amount_paid),
            ];
        }

        $pdf = PDF::loadView('pdf.payment-bulk', compact('receipts'))
            ->setPaper('a4', 'landscape');

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header(
                'Content-Disposition',
                'inline; filename="toplu-odeme-dekontu.pdf"'
            );
    }

    protected function imageToBase64Uri($path)
    {
        $imagePath = public_path($path);
        $imageType = pathinfo($imagePath, PATHINFO_EXTENSION);
        $imageData = file_get_contents($imagePath);

        return 'data:image/' . $imageType . ';base64,' . base64_encode($imageData);
    }

    public function sendPending(int $limit = 200): int
    {
        $fifteenMinutesAgo = now()->subMinutes(15);
        $sent = 0;

        Payment::query()
            ->where('email_sent', 0)
            ->whereIn('status', ['SUCCESS', 'success'])
            ->where(function ($q) use ($fifteenMinutesAgo) {
                $q->where('created_at', '<', $fifteenMinutesAgo)
                  ->orWhere('updated_at', '<', $fifteenMinutesAgo);
            })
            ->orderBy('id')
            ->limit($limit)
            ->chunkById(200, function ($payments) use (&$sent) {
                foreach ($payments as $payment) {
                    try {
                        Mail::send(new PaymentMail($payment));

                        $payment->update([
                            'email_sent' => 1,
                            'email_sent_at' => now(),
                        ]);

                        $sent++;
                    } catch (\Throwable $e) {
                        logException($e, 'PaymentMailService::sendPending paymentId='.$payment->id);
                    }
                }
            });

        return $sent;
    }

    public function markAsProcessing($id)
    {
        return $this->repository->markAsProcessing($id);
    }

    public function markAsSent($id, $documentNo = null)
    {
        return $this->repository->markAsSent($id, $documentNo);
    }

    public function resetErpSync($id)
    {
        return $this->repository->resetErpSync($id);
    }

    public function markAsFailed($id, $error = null)
    {
        if ($error !== null && !is_string($error)) {
            $error = json_encode($error, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        } elseif (is_string($error)) {
            $error = json_encode(['message' => $error], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        }

        return $this->repository->markAsFailed($id, $error);
    }

    public function clearCache($id)
    {
        return $this->repository->clearCache($id);
    }

    private function buildTransitionResult(
        Payment $payment,
        ?PaymentStatus $fromStatus,
        ?PaymentStatus $toStatus,
        bool $transitioned,
        array $payload
    ): array {
        $result = [
            'payment' => $payment,
            'transitioned' => $transitioned,
            'from_status' => $this->statusToString($fromStatus),
            'to_status' => $this->statusToString($toStatus),
        ];

        $this->logTransition($payment->id, $result['from_status'], $result['to_status'], $payload);

        return $result;
    }

    private function statusToString(?PaymentStatus $status): string
    {
        return $status?->value ?? 'unknown';
    }

    private function logTransition(int $paymentId, string $fromStatus, string $toStatus, array $payload): void
    {
        $maskedPayload = $this->paymentSensitiveDataMasker->mask($payload);

        logSession(
            "Payment transition processed. paymentId {$paymentId}",
            [
                'payment_id' => $paymentId,
                'previous_status' => $fromStatus,
                'new_status' => $toStatus,
                'payload' => $maskedPayload,
            ],
            'info',
            'payment_logs'
        );
    }

    private function assertTransitionSafety(Payment $payment, array $payload): void
    {
        if (!$payment->user_id || !$payment->user) {
            throw new RuntimeException("Orphan payment detected: payment_id {$payment->id}");
        }

        $oidFromPayload = $this->extractOidFromPayload($payload);
        if ($oidFromPayload !== null && (string) $payment->oid !== '' && (string) $payment->oid !== $oidFromPayload) {
            throw new RuntimeException("OID mismatch for payment {$payment->id}");
        }
    }

    private function extractOidFromPayload(array $payload): ?string
    {
        foreach (['oid', 'OrderId', 'orderid', 'OrderID', 'MerchantOrderId', 'merchantorderid'] as $key) {
            if (isset($payload[$key]) && $payload[$key] !== '') {
                return (string) $payload[$key];
            }
        }

        return null;
    }

    private function lockPayment(Payment $payment): Payment
    {
        return Payment::query()
            ->whereKey($payment->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function extractProviderFields(array $payload): array
    {
        $mapping = [
            'provider_reference' => ['provider_reference', 'TransactionId', 'transaction_id'],
            'provider_auth_code' => ['provider_auth_code', 'AuthCode', 'authcode', 'authCode'],
            'provider_rrn' => ['provider_rrn', 'HostRefNum', 'hostrefnum', 'rrn', 'Rrn'],
        ];

        $updateData = [];

        foreach ($mapping as $field => $keys) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $payload) && $payload[$key] !== null && $payload[$key] !== '') {
                    $updateData[$field] = $payload[$key];
                    break;
                }
            }
        }

        return $updateData;
    }

}
