<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentLinkRequest;
use App\Models\PaymentLink;
use App\Services\PaymentService;
use App\Services\RefundService;
use Illuminate\Http\Request;

class PaymentLinkController extends Controller
{
    public function __construct(
        private RefundService $refundService,
        private PaymentService $paymentService
    ) {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $items = PaymentLink::with(['bankIntegration', 'plasiyer', 'user', 'admin'])
            ->when($name = request()->get('name'), function ($query) use ($name) {
                $query->where(function ($query) use ($name) {
                    $query->whereRelation('user', 'name', 'like', "%{$name}%")
                        ->orWhereRelation('user', 'code', 'like', "%{$name}%")
                        ->orWhere('email', 'like', "%{$name}%")
                        ->orWhere('phone', 'like', "%{$name}%");
                });
            })
            ->when($is_paid = request()->get('is_paid'), function ($query) use ($is_paid) {
                $query->where('is_paid', $is_paid);
            })
            ->orderBy('id', 'DESC')
            ->paginate(50);

        return view('admin.payment-links.index', compact('items'));
    }

    public function create()
    {
        return view('admin.payment-links.create');
    }

    public function store(PaymentLinkRequest $request)
    {
        $paymentLink = PaymentLink::create([
            'user_id' => $request->input('user_id'),
            'email' => $request->input('email') ?: null,
            'phone' => $request->input('phone') ?: null,
            'amount' => $request->input('amount'),
            'status' => $request->input('status'),
            'amount_locked' => $request->input('amount_locked'),
            'transaction_type' => $request->input('transaction_type'),
            'manual_bank_integration_id' => $request->input('manual_bank_integration_id'),
            'manual_installment' => $request->input('manual_installment'),
            'manual_lock_bank_installment' => $request->input('manual_lock_bank_installment'),
            'token' => strtoupper(uniqid()),
            'admin_id' => auth('admin')->user()->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Basariyla eklendi.',
            'redirect' => $this->resolveRedirect($request, $paymentLink),
        ]);
    }

    public function show($id)
    {
        //
    }

    public function edit(PaymentLink $paymentLink)
    {
        return view('admin.payment-links.edit', compact('paymentLink'));
    }

    public function update(PaymentLinkRequest $request, PaymentLink $paymentLink)
    {
        $paymentLink->update([
            'user_id' => $request->input('user_id'),
            'email' => $request->input('email') ?: null,
            'phone' => $request->input('phone') ?: null,
            'amount' => $request->input('amount'),
            'status' => $request->input('status'),
            'amount_locked' => $request->input('amount_locked'),
            'transaction_type' => $request->input('transaction_type'),
            'manual_bank_integration_id' => $request->input('manual_bank_integration_id'),
            'manual_installment' => $request->input('manual_installment'),
            'manual_lock_bank_installment' => $request->input('manual_lock_bank_installment'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Basariyla guncellendi.',
            'redirect' => $this->resolveRedirect($request, $paymentLink),
        ]);
    }

    public function destroy(PaymentLink $paymentLink)
    {
        $paymentLink->delete();

        return response()->json([
            'status' => 'success',
        ]);
    }

    private function resolveRedirect(Request $request, PaymentLink $paymentLink): string
    {
        if ((int) $request->input('save_and_pay') === 1) {
            return route('payments.payment-link', [$paymentLink->token]);
        }

        if ((int) $request->input('save_and_new') === 1) {
            return route('admin.payment-links.create');
        }

        if ((int) $request->input('save_and_go') === 1) {
            return route('admin.payment-links.edit', [$paymentLink->id]);
        }

        return route('admin.payment-links.index');
    }

    public function updateRefundStatus(Request $request, PaymentLink $paymentLink)
    {
        $request->validate([
            'status' => 'required|in:cancelled,refunded',
        ]);

        if ($paymentLink->refund_status !== null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bu islem zaten yapilmis.',
            ], 422);
        }

        $action = $request->status === 'cancelled' ? 'cancel' : 'refund';
        $paidPayment = $paymentLink->paidPayment;

        if ($paidPayment) {
            if ($paidPayment->refund_status !== null || strtolower((string) $paidPayment->status) === 'refunded') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bu islem zaten yapilmis.',
                ], 422);
            }

            if (strtolower((string) $paidPayment->status) !== 'success') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sadece basarili odemeler iptal/iade edilebilir.',
                ], 422);
            }

            $result = $this->refundService->process($paidPayment, $action);

            if (!$result['success']) {
                logSession('PaymentLinkController iptal/iade basarisiz.', [
                    'paymentLinkId' => $paymentLink->id,
                    'paymentId' => $paidPayment->id,
                    'action' => $action,
                    'message' => $result['message'],
                ], 'error', 'payment_logs');

                return response()->json([
                    'status' => 'error',
                    'message' => $result['message'],
                ], 422);
            }

            try {
                $this->paymentService->refund($paidPayment, [
                    'refund_status' => $request->status,
                    'refund_date' => now(),
                    'action' => $action,
                ]);
            } catch (\Throwable $e) {
                logException($e, 'PaymentLinkController::updateRefundStatus', true);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Iade/iptal kaydi guncellenemedi.',
                ], 500);
            }

            $paymentLink->update([
                'refund_status' => $request->status,
                'refund_date' => now(),
            ]);

            logSession('PaymentLinkController iptal/iade basarili.', [
                'paymentLinkId' => $paymentLink->id,
                'paymentId' => $paidPayment->id,
                'action' => $action,
                'refund_status' => $request->status,
            ], 'info', 'payment_logs');

            return response()->json([
                'status' => 'success',
                'message' => $request->status === 'cancelled'
                    ? 'Iptal islemi basariyla gerceklesti.'
                    : 'Iade islemi basariyla gerceklesti.',
            ]);
        }

        if ((int) $paymentLink->is_paid !== 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sadece basarili odemeler iptal/iade edilebilir.',
            ], 422);
        }

        $result = $this->refundService->process($paymentLink, $action);

        if (!$result['success']) {
            logSession('PaymentLinkController iptal/iade basarisiz.', [
                'paymentLinkId' => $paymentLink->id,
                'action' => $action,
                'message' => $result['message'],
            ], 'error', 'payment_logs');

            return response()->json([
                'status' => 'error',
                'message' => $result['message'],
            ], 422);
        }

        $paymentLink->update([
            'refund_status' => $request->status,
            'refund_date' => now(),
        ]);

        logSession('PaymentLinkController iptal/iade basarili.', [
            'paymentLinkId' => $paymentLink->id,
            'action' => $action,
            'refund_status' => $request->status,
        ], 'info', 'payment_logs');

        return response()->json([
            'status' => 'success',
            'message' => $request->status === 'cancelled'
                ? 'Iptal islemi basariyla gerceklesti.'
                : 'Iade islemi basariyla gerceklesti.',
        ]);
    }
}
