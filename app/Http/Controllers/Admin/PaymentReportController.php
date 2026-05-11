<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunExportJob;
use App\Models\BankIntegration;
use App\Models\ExportJob;
use App\Models\Payment;
use App\Models\User;
use App\Services\Exports\PaymentExportQueryService;
use App\Services\PaymentService;
use App\Services\RefundService;
use Illuminate\Http\Request;

class PaymentReportController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private PaymentExportQueryService $paymentExportQueryService,
        private RefundService $refundService
    ) {
        $this->middleware('auth:admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.payments.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function tableData(Request $request)
    {
        $filters = $this->resolveTableFilters($request);
        $perPage = max(1, (int) $request->input('per_page', 100));
        $page = max(1, (int) $request->input('page', 1));

        $items = $this->paymentExportQueryService
            ->build($filters)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $items->getCollection()->map(function (Payment $item) {
            $yesNoBadge = $this->resolveYesNoBadge((int) ($item->option_3d_payment ?? 0));
            $statusBadge = $this->resolveStatusBadge((string) $item->status);
            $emailBadge = $this->resolveEmailBadge((string) $item->status, (int) ($item->email_sent ?? 0));
            $erpBadge = $this->resolveErpBadge((string) ($item->erp_status ?? ''));
            $refundBadge = $this->resolveRefundBadge($item->refund_status);

            $isSuccess = strtoupper((string) $item->status) === 'SUCCESS';
            $actionType = $item->action_type;
            $canRefund = $isSuccess && !$item->refund_status && in_array($actionType, ['cancel', 'refund'], true);

            return [
                'id' => $item->id,
                'salesman_name' => $item->plasiyer?->name ?? '-',
                'dealer_name' => $item->subDealer?->name ?? ($item->user?->name ?? '-'),
                'formatted_phone_number' => $item->formatted_phone_number,
                'bank_integration_name' => $item->bankIntegration?->full_name ?? '-',
                'oid' => $item->oid,
                'formatted_amount_paid' => number_format((float) ($item->amount_paid ?? 0), 2, ',', '.'),
                'amount_paid_usd' => number_format((float) ($item->amount_paid_usd ?? 0), 2, ',', '.'),
                'installment' => (int) ($item->installment ?? 0),
                'commission_rate' => (int) ($item->commission_rate ?? 0),
                'formatted_commission_amount' => number_format((float) ($item->commission_amount ?? 0), 2, ',', '.'),
                'usd_rate_info' => number_format((float) ($item->usd_exchange_rate ?? 0), 6, ',', '.'),
                'card_name' => $item->card_name,
                'card_number' => $item->card_number,
                'explanation' => $item->explanation,
                'option_3d_payment_label' => $yesNoBadge['label'],
                'option_3d_payment_class' => $yesNoBadge['class'],
                'status_label' => $statusBadge['label'],
                'status_class' => $statusBadge['class'],
                'status_is_success' => $isSuccess,
                'can_view_receipt' => in_array(strtoupper((string) $item->status), ['SUCCESS', 'REFUNDED'], true),
                'failure_reason' => $item->failure_reason,
                'email_sent_label' => $emailBadge['label'],
                'email_sent_class' => $emailBadge['class'],
                'erp_status_label' => $erpBadge['label'],
                'erp_status_class' => $erpBadge['class'],
                'formatted_completed_at' => $item->formatted_completed_at ?: $item->formatted_created_at,
                'refund_status' => $item->refund_status,
                'refund_status_label' => $refundBadge['label'],
                'refund_status_class' => $refundBadge['class'],
                'formatted_refund_date' => $item->formatted_refund_date,
                'action_type' => $actionType,
                'can_refund' => $canRefund,
                'receipt_url' => route('pdf.payment-receipt.payment', [$item->id]),
                'refund_url' => route('admin.payments.refund', [$item->id]),
            ];
        })->values();

        $salesmanOptions = User::query()
            ->salesman()
            ->active()
            ->orderBy('name', 'asc')
            ->get(['current_account_id', 'name'])
            ->map(fn (User $user) => [
                'value' => (string) $user->current_account_id,
                'label' => (string) $user->name,
            ])
            ->values()
            ->all();

        $bankOptions = BankIntegration::query()
            ->with('company:id,name')
            ->active()
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'company_id'])
            ->map(fn (BankIntegration $bankIntegration) => [
                'value' => (string) $bankIntegration->id,
                'label' => (string) $bankIntegration->full_name,
            ])
            ->values()
            ->all();

        return response()->json([
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
                'statusOptions' => [
                    ['value' => '', 'label' => 'Tümü'],
                    ['value' => 'SUCCESS', 'label' => 'Başarılı'],
                    ['value' => 'FAILED', 'label' => 'Başarısız'],
                ],
                'stockStatusOptions' => [
                    ['value' => '', 'label' => 'Tümü'],
                    ['value' => 'payment', 'label' => 'Ödeme'],
                    ['value' => 'refunded', 'label' => 'İade'],
                    ['value' => 'cancelled', 'label' => 'İptal'],
                ],
                'salesmanOptions' => $salesmanOptions,
                'bankOptions' => $bankOptions,
            ],
        ]);
    }

    public function filter()
    {
        $s = '';
        $name = '';
        $status = '';
        $salesmanId = '';
        $bankIntegrationId = '';
        $dateFrom = '';
        $dateTo = '';
        $refundStatus = '';

        if ($nameValue = request('name')) {
            $name = '&name=' . $nameValue;
            $s = '?';
        }

        $statusValue = request('status');
        if ($statusValue != '') {
            $status = '&status=' . $statusValue;
            $s = '?';
        }

        if ($salesmanFilter = request('salesman_id')) {
            $salesmanId = '&salesmanId=' . implode(',', $salesmanFilter);
            $s = '?';
        }

        if ($bankFilter = request('bank_integration_id')) {
            $bankIntegrationId = '&bankIntegrationId=' . implode(',', $bankFilter);
            $s = '?';
        }

        if ($dateFromValue = request('date_from')) {
            $dateFrom = '&date_from=' . $dateFromValue;
            $s = '?';
        }

        if ($dateToValue = request('date_to')) {
            $dateTo = '&date_to=' . $dateToValue;
            $s = '?';
        }

        if ($processType = request('process_type', request('refund_status', request('stock_status')))) {
            $refundStatus = '&process_type=' . $processType;
            $s = '?';
        }

        return redirect()->to(route('admin.payments.index') . $s . $name . $status . $salesmanId . $bankIntegrationId . $dateFrom . $dateTo . $refundStatus);
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'scope' => ['required', 'in:filtered,selected'],
            'ids' => ['required_if:scope,selected', 'array'],
            'ids.*' => ['required_if:scope,selected', 'integer', 'exists:payments,id'],
        ]);

        $scope = (string) $validated['scope'];
        $filters = $scope === 'selected' ? [] : $this->resolveTableFilters($request);
        $filename = 'payments-' . $scope . '-' . now()->format('Ymd-His') . '.csv';
        $selectedIds = $scope === 'selected'
            ? collect($validated['ids'] ?? [])->map(fn ($id) => (int) $id)->values()->all()
            : null;

        $query = $this->paymentExportQueryService->build($filters, $selectedIds)->orderBy('id', 'asc');

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'wb');
            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'ID',
                'PLASİYER',
                'BAYİ',
                'BANKA',
                'İŞLEM_NO',
                'TUTAR',
                'USD_TUTAR',
                'TAKSİT',
                'KOMİSYON_ORANI',
                'KOMİSYON_TUTARI',
                'KART_SAHİBİ',
                'KART_NO',
                'AÇIKLAMA',
                'ÜÇ_D_ÖDEME',
                'DURUM',
                'MAİL_DURUMU',
                'ERP_DURUMU',
                'İŞLEM_TİPİ',
                'TARİH',
            ]);

            $query->chunkById(500, function ($payments) use ($output) {
                foreach ($payments as $item) {
                    $status = strtoupper((string) $item->status);

                    fputcsv($output, [
                        $item->id,
                        $item->plasiyer?->name ?? '-',
                        $item->subDealer?->name ?? ($item->user?->name ?? '-'),
                        $item->bankIntegration?->full_name ?? '-',
                        $item->oid,
                        round((float) ($item->amount_paid ?? 0), 2),
                        round((float) ($item->amount_paid_usd ?? 0), 2),
                        (int) ($item->installment ?? 0),
                        (int) ($item->commission_rate ?? 0),
                        round((float) ($item->commission_amount ?? 0), 2),
                        (string) ($item->card_name ?? ''),
                        (string) ($item->card_number ?? ''),
                        (string) ($item->explanation ?? ''),
                        (int) ($item->option_3d_payment ?? 0) === 1 ? 'Evet' : 'Hayır',
                        $this->resolveStatusBadge($status)['label'],
                        $this->resolveEmailBadge($status, (int) ($item->email_sent ?? 0))['label'],
                        $this->resolveErpBadge((string) $item->erp_status)['label'],
                        $this->resolveProcessTypeLabel($item->refund_status),
                        $item->formatted_completed_at ?: $item->formatted_created_at,
                    ]);
                }
            });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function createExport(Request $request)
    {
        $validated = $request->validate([
            'format' => ['required', 'in:xlsx,csv'],
            'scope' => ['required', 'in:filtered,selected'],
            'filters' => ['nullable', 'array'],
            'ids' => ['required_if:scope,selected', 'array'],
            'ids.*' => ['required_if:scope,selected', 'integer', 'exists:payments,id'],
        ]);

        $scope = (string) $validated['scope'];
        $selectedIds = $scope === 'selected'
            ? collect($validated['ids'] ?? [])->map(fn ($id) => (int) $id)->values()->all()
            : null;
        $filters = $scope === 'selected'
            ? []
            : (is_array($validated['filters'] ?? null) ? $validated['filters'] : []);
        $adminUser = $request->user('admin');

        $exportJob = ExportJob::query()->create([
            'user_type' => $adminUser ? 'admin' : null,
            'user_id' => $adminUser?->id,
            'type' => 'payments',
            'format' => (string) $validated['format'],
            'scope' => $scope,
            'filters' => $filters,
            'selected_ids' => $selectedIds,
            'status' => 'pending',
        ]);

        RunExportJob::dispatch((int) $exportJob->id);

        return response()->json([
            'success' => true,
            'message' => 'Dışa aktarma kuyruğa alındı',
            'export_job_id' => $exportJob->id,
        ]);
    }

    public function updateRefundStatus(Request $request, Payment $payment)
    {
        $request->validate([
            'status' => 'required|in:cancelled,refunded',
        ]);

        if ($payment->refund_status !== null || strtolower((string) $payment->status) === 'refunded') {
            return response()->json([
                'success' => false,
                'message' => 'Bu işlem zaten yapılmış.',
            ]);
        }

        if (strtolower((string) $payment->status) !== 'success') {
            return response()->json([
                'success' => false,
                'message' => 'Sadece başarılı ödemeler iptal/iade edilebilir.',
            ]);
        }

        $action = $request->status === 'cancelled' ? 'cancel' : 'refund';

        $result = $this->refundService->process($payment, $action);

        if (!$result['success']) {
            logSession('PaymentReportController iptal/iade başarısız.', [
                'paymentId' => $payment->id,
                'action' => $action,
                'message' => $result['message'],
            ], 'error', 'payment_logs');

            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ]);
        }

        try {
            $payment = $this->paymentService->refund($payment, [
                'refund_status' => $request->status,
                'refund_date' => now(),
                'action' => $action,
            ]);
        } catch (\Throwable $e) {
            logException($e, 'PaymentReportController::updateRefundStatus', true);

            return response()->json([
                'success' => false,
                'message' => 'İade/iptal kaydı güncellenemedi.',
            ]);
        }

        logSession('PaymentReportController iptal/iade başarılı.', [
            'paymentId' => $payment->id,
            'action' => $action,
            'refund_status' => $payment->refund_status,
        ], 'info', 'payment_logs');

        return response()->json([
            'success' => true,
            'message' => $request->status === 'cancelled'
                ? 'İptal işlemi başarıyla gerçekleşti.'
                : 'İade işlemi başarıyla gerçekleşti.',
        ]);
    }

    private function resolveTableFilters(Request $request): array
    {
        return [
            'search' => $request->input('search', $request->input('q', $request->input('name'))),
            'status' => $request->input('status'),
            'process_type' => $request->input('process_type', $request->input('refund_status', $request->input('stock_status'))),
            'salesman_id' => $request->input('salesman_id', $request->input('salesmanId')),
            'bank_integration_id' => $request->input('bank_integration_id', $request->input('bankIntegrationId')),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];
    }

    private function resolveYesNoBadge(int $value): array
    {
        return $value === 1
            ? ['label' => 'Evet', 'class' => 'badge bg-success']
            : ['label' => 'Hayır', 'class' => 'badge bg-danger'];
    }

    private function resolveStatusBadge(string $value): array
    {
        $status = strtoupper($value);

        return match ($status) {
            'SUCCESS' => ['label' => 'Başarılı', 'class' => 'badge bg-success'],
            'FAILED' => ['label' => 'Başarısız', 'class' => 'badge bg-danger'],
            'REFUNDED' => ['label' => 'İade Edildi', 'class' => 'badge bg-info'],
            default => ['label' => 'Bekleyen', 'class' => 'badge bg-secondary'],
        };
    }

    private function resolveEmailBadge(string $status, int $value): array
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

    private function resolveRefundBadge(?string $value): array
    {
        return match ($value) {
            'refunded' => ['label' => 'İade Edildi', 'class' => 'badge bg-info'],
            'cancelled' => ['label' => 'İptal Edildi', 'class' => 'badge bg-primary'],
            default => ['label' => null, 'class' => null],
        };
    }

    private function resolveProcessTypeLabel(?string $refundStatus): string
    {
        return match ($refundStatus) {
            'refunded' => 'İade',
            'cancelled' => 'İptal',
            default => 'Ödeme',
        };
    }
}
