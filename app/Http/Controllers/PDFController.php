<?php

namespace App\Http\Controllers;

use App\Models\{Payment, PaymentLink};
use App\Services\{PaymentService, CurrentAccountService, StatementReportService};
use Barryvdh\DomPDF\Facade\Pdf;

class PDFController extends Controller
{
    public function paymentReceiptPayment(Payment $payment)
    {
        return app(PaymentService::class)->generatePaymentReceiptPdf($payment, 'payment');
    }

    public function paymentReceiptPaymentLink(PaymentLink $paymentLink)
    {
        return app(PaymentService::class)->generatePaymentReceiptPdf($paymentLink, 'paymentLink');
    }

    public function customerStatement(CurrentAccountService $currentAccountService, StatementReportService $statementReportService)
    {
        abort_unless(auth('web')->check() || auth('subdealer')->check(), 403);

        $currentAccount = $currentAccountService->currentAccount();
        abort_if(!$currentAccount, 404);

        $statement = $statementReportService->buildForCurrentAccount(
            $currentAccount,
            $currentAccountService->userQuery(),
            request()->get('startDate'),
            request()->get('endDate')
        );

        $pdf = Pdf::loadView('pdf.customer-statement', [
            'dealer' => $currentAccount,
            'items' => $statement['items']->all(),
            'totals' => [
                'debit' => $statement['debtTotal'],
                'credit' => $statement['receivableTotal'],
                'balance' => $statement['balance'],
                'currency' => $statement['currency'],
            ],
        ]);

        return $pdf->download('cari-hareket-' . $currentAccount->id . '.pdf');
    }
}
