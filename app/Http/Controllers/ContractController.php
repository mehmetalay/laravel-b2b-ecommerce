<?php

namespace App\Http\Controllers;

use App\Application\Contract\Actions\{AcceptContractAction, ApproveContractAction, SendSmsCodeAction, ShowContractAction, StoreContractAction};
use Illuminate\Http\{JsonResponse, Request};
use App\Application\Contract\Services\ContractWorkflowService;
use App\Models\ContractTemplate;
use Illuminate\Validation\ValidationException;
use Throwable;

class ContractController extends Controller
{
    public function __construct(
        private ShowContractAction $showContractAction,
        private StoreContractAction $storeContractAction,
        private AcceptContractAction $acceptContractAction,
        private SendSmsCodeAction $sendSmsCodeAction,
        private ApproveContractAction $approveContractAction,
        private ContractWorkflowService $contractWorkflowService
    ) {
        parent::__construct();
    }

    public function show(string $actorType, string $actorId, ContractTemplate $template)
    {
        $this->ensureContractFeatureEnabled();

        $payload = ($this->showContractAction)($actorType, $actorId, $template);

        return view('contract.show', $payload);
    }

    public function store(string $actorType, string $actorId, ContractTemplate $template, Request $request): JsonResponse
    {
        $this->ensureContractFeatureEnabled();

        try {
            $result = ($this->storeContractAction)($actorType, $actorId, $template, $request->all());

            return response()->json([
                'status' => 'success',
                'redirect' => route('contract.show', [
                    'approve' => true,
                    'actor_type' => $result['actor_type'],
                    'actor_id' => $result['actor_id'],
                    'template' => $result['template_id'],
                ]),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            $this->contractWorkflowService->reportException('store', $e);

            return response()->json([
                'status' => 'error',
                'message' => trans('translations.contract_controller.istek_sirasinda_bir_hata_olustu_lutfen_daha_sonra_tekrar_deneyiniz'),
            ], 400);
        }
    }

    public function acceptButton(string $actorType, string $actorId, ContractTemplate $template): JsonResponse
    {
        $this->ensureContractFeatureEnabled();

        try {
            $html = ($this->acceptContractAction)($actorType, $actorId, $template);

            return response()->json([
                'status' => 'success',
                'html' => $html,
            ]);
        } catch (Throwable $e) {
            $this->contractWorkflowService->reportException('accept-button', $e);

            return response()->json([
                'status' => 'error',
                'message' => trans('translations.contract_controller.istek_sirasinda_bir_hata_olustu_lutfen_daha_sonra_tekrar_deneyiniz'),
            ], 400);
        }
    }

    public function sendSmsCode(string $actorType, string $actorId, ContractTemplate $template, string $key): JsonResponse
    {
        $this->ensureContractFeatureEnabled();

        try {
            ($this->sendSmsCodeAction)($actorType, $actorId, $template, $key);

            return response()->json([
                'status' => 'success',
                'message' => trans('translations.contract_controller.onay_kodu_sms_olarak_gonderildi'),
            ]);
        } catch (Throwable $e) {
            $this->contractWorkflowService->reportException('send-sms-code', $e);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() ?: trans('translations.contract_controller.istek_sirasinda_bir_hata_olustu_lutfen_daha_sonra_tekrar_deneyiniz'),
            ], 400);
        }
    }

    public function approve(string $actorType, string $actorId, ContractTemplate $template, Request $request): JsonResponse
    {
        $this->ensureContractFeatureEnabled();

        try {
            ($this->approveContractAction)($actorType, $actorId, $template, $request->all());

            return response()->json([
                'status' => 'success',
                'message' => trans('translations.contract_controller.sozlesmeyi_basariyla_onayladiniz_giris_yapabilirsiniz'),
                'redirect' => route('login.form'),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            $this->contractWorkflowService->reportException('approve', $e);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() ?: trans('translations.contract_controller.istek_sirasinda_bir_hata_olustu_lutfen_daha_sonra_tekrar_deneyiniz'),
            ], 400);
        }
    }

    private function ensureContractFeatureEnabled(): void
    {
        abort_if(!additional_setting('use_contract_approval'), 404);
    }
}
