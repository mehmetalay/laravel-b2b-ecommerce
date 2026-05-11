<?php

namespace App\Http\Controllers;

use App\Application\Mail\Services\DealerApplicationMailDispatchService;
use App\Application\Mail\Services\OrderMailDispatchService;
use App\Application\Mail\Services\PaymentLinkMailDispatchService;
use App\Application\Mail\Services\PaymentMailDispatchService;
use App\Mail\CustomerStatementMail;
use App\Models\DealerApplication;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentLink;
use App\Services\CurrentAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function __construct(
        private DealerApplicationMailDispatchService $dealerApplicationMailDispatchService,
        private PaymentMailDispatchService $paymentMailDispatchService,
        private OrderMailDispatchService $orderMailDispatchService,
        private PaymentLinkMailDispatchService $paymentLinkMailDispatchService,
        private CurrentAccountService $currentAccountService
    ) {
        parent::__construct();
    }

    public function payment(Payment $payment)
    {
        return $this->paymentMailDispatchService->sendPaymentMail($payment);
    }

    public function order(Order $order)
    {
        $status = $this->orderMailDispatchService->sendOrderMail($order);

        if (request()->get('message')) {
            return redirect()->back()->with(
                $status ? 'success' : 'error',
                $status ? 'Mail başarıyla gönderildi.' : 'Mail gönderilemedi. Lütfen site yöneticisiyle iletişime geçin.'
            );
        }

        return $status;
    }

    public function paymentLink(PaymentLink $payment_link)
    {
        $status = $this->paymentLinkMailDispatchService->sendPaymentLinkMail($payment_link);

        if (request()->get('message')) {
            return redirect()->back()->with(
                $status ? 'success' : 'error',
                $status ? 'Mail başarıyla gönderildi.' : 'Mail gönderilemedi. Lütfen site yöneticisiyle iletişime geçin.'
            );
        }

        return $status;
    }

    public function paymentLinkPaymentSuccess(PaymentLink $payment_link)
    {
        $status = $this->paymentLinkMailDispatchService->sendPaymentSuccessMail($payment_link);

        if (request()->get('message')) {
            return redirect()->back()->with(
                $status ? 'success' : 'error',
                $status ? 'Mail başarıyla gönderildi.' : 'Mail gönderilemedi. Lütfen site yöneticisiyle iletişime geçin.'
            );
        }

        return $status;
    }

    public function dealerApplication(DealerApplication $dealer_application)
    {
        $status = $this->dealerApplicationMailDispatchService->sendDealerApplicationMail($dealer_application);

        if (request()->get('message')) {
            return redirect()->back()->with(
                $status ? 'success' : 'error',
                $status ? 'Mail başarıyla gönderildi.' : 'Mail gönderilemedi. Lütfen site yöneticisiyle iletişime geçin.'
            );
        }

        return $status;
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'recipient_email' => ['required', 'email'],
            'type' => ['required', 'in:order,payment,statement'],
            'ref_id' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $status = false;

            if ($data['type'] === 'order') {
                $order = $this->resolveAuthorizedOrder((int) $data['ref_id']);
                if (!$order) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Bu sipariş için mail gönderme yetkiniz yok.',
                    ], 403);
                }

                $status = $this->orderMailDispatchService->sendOrderMail($order, $data['recipient_email'], false);
            } elseif ($data['type'] === 'payment') {
                $payment = $this->resolveAuthorizedPayment((int) $data['ref_id']);
                if (!$payment) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Bu ödeme için mail gönderme yetkiniz yok.',
                    ], 403);
                }

                $status = $this->paymentMailDispatchService->sendPaymentMail($payment, $data['recipient_email'], false);
            } elseif ($data['type'] === 'statement') {
                $dealer = $this->currentAccountService->currentAccount();
                if (!$dealer || (int) $dealer->id !== (int) $data['ref_id']) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Bu ekstre için mail gönderme yetkiniz yok.',
                    ], 403);
                }

                Mail::send(new CustomerStatementMail($dealer, $data['recipient_email']));
                $status = count(Mail::failures()) === 0;
            }

            Log::info('MailController::send - Mail gönderim durumu: ' . ($status ? 'success' : 'failed'));

            return response()->json([
                'status' => $status ? 'success' : 'error',
                'message' => $status ? 'Mail başarıyla gönderildi.' : 'Mail gönderilemedi.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Mail gönderilemedi.',
            ], 500);
        }
    }

    private function resolveAuthorizedOrder(int $orderId): ?Order
    {
        $userQuery = $this->currentAccountService->userQuery();

        return Order::query()
            ->when(auth('web')->check() && auth('web')->user()->role === 'salesman', function ($query) use ($userQuery) {
                $query->where(function ($scoped) use ($userQuery) {
                    $scoped->where('plasiyer_id', $userQuery['plasiyer_id'] ?? null)
                        ->orWhereRelation('user', 'plasiyer1', $userQuery['plasiyer_id'] ?? null)
                        ->orWhereRelation('user', 'plasiyer2', $userQuery['plasiyer_id'] ?? null)
                        ->orWhereRelation('user', 'plasiyer3', $userQuery['plasiyer_id'] ?? null)
                        ->orWhereRelation('user', 'plasiyer4', $userQuery['plasiyer_id'] ?? null)
                        ->orWhereRelation('user', 'plasiyer5', $userQuery['plasiyer_id'] ?? null);
                });
            })
            ->when(auth('web')->check() && auth('web')->user()->role === 'dealer', function ($query) use ($userQuery) {
                $query->where('user_id', $userQuery['user_id'] ?? null)
                    ->when($subDealerId = ($userQuery['sub_dealer_id'] ?? null), function ($subQuery) use ($subDealerId) {
                        $subQuery->where('sub_dealer_id', $subDealerId);
                    });
            })
            ->when(auth('subdealer')->check(), function ($query) use ($userQuery) {
                $query->where('sub_dealer_id', $userQuery['sub_dealer_id'] ?? null);
            })
            ->find($orderId);
    }

    private function resolveAuthorizedPayment(int $paymentId): ?Payment
    {
        $userQuery = $this->currentAccountService->userQuery();

        return Payment::query()
            ->when(auth('web')->check() && auth('web')->user()->role === 'salesman', function ($query) use ($userQuery) {
                $query->where(function ($scoped) use ($userQuery) {
                    $scoped->where('plasiyer_id', $userQuery['plasiyer_id'] ?? null)
                        ->orWhere(function ($related) use ($userQuery) {
                            $related->whereRelation('user', 'plasiyer1', $userQuery['plasiyer_id'] ?? null)
                                ->orWhereRelation('user', 'plasiyer2', $userQuery['plasiyer_id'] ?? null)
                                ->orWhereRelation('user', 'plasiyer3', $userQuery['plasiyer_id'] ?? null)
                                ->orWhereRelation('user', 'plasiyer4', $userQuery['plasiyer_id'] ?? null)
                                ->orWhereRelation('user', 'plasiyer5', $userQuery['plasiyer_id'] ?? null);
                        });
                });
            })
            ->when(auth('web')->check() && auth('web')->user()->role === 'dealer', function ($query) use ($userQuery) {
                $query->where('user_id', $userQuery['user_id'] ?? null);
            })
            ->when(auth('subdealer')->check(), function ($query) use ($userQuery) {
                $query->where('sub_dealer_id', $userQuery['sub_dealer_id'] ?? null);
            })
            ->find($paymentId);
    }
}
