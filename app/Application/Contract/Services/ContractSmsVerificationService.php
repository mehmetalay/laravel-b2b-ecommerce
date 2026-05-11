<?php

namespace App\Application\Contract\Services;

use App\Application\Contract\Enums\ContractSignatureStatus;
use App\Application\Contract\Events\ContractSmsSent;
use App\Application\Contract\Exceptions\ContractAlreadyApprovedException;
use App\Application\Contract\Exceptions\ContractSmsExpiredException;
use App\Application\Contract\Exceptions\ContractSmsValidationException;
use App\Application\Contract\Repositories\ContractSignatureRepository;
use App\Application\Contract\Support\ContractCacheKeys;
use App\Models\ContractSignature;
use App\Models\ContractTemplate;
use App\Services\SmsExplorerService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ContractSmsVerificationService
{
    private const SMS_SEND_COOLDOWN_SECONDS = 60;
    private const SMS_CODE_EXPIRE_MINUTES = 5;
    private const MAX_FAILED_ATTEMPTS = 5;

    public function __construct(
        private SmsExplorerService $smsExplorerService,
        private ContractSignatureRepository $contractSignatureRepository,
        private ContractPersistenceService $contractPersistenceService
    ) {}

    public function prepareAcceptButtonHtml(array $actorContext, ContractTemplate $template): string
    {
        $signature = $this->contractSignatureRepository->firstOrCreatePending(
            (int) $actorContext['contract_user_id'],
            $actorContext['type']->value,
            (int) $template->id
        );

        $url = route('contract.send-sms-code', [
            'actor_type' => $actorContext['type']->value,
            'actor_id' => $actorContext['route_actor_id'],
            'template' => $template->id,
            'key' => $signature->token,
        ]);

        return '<a href="javascript:;" data-js="approve-contract" data-url="' . e($url) . '" class="btn theme-bg-color btn-md fw-bold mt-4 text-white float-end">Onaylıyorum</a>';
    }

    public function sendSmsCode(array $actorContext, ContractTemplate $template, string $token): void
    {
        $signature = $this->contractSignatureRepository->findByContext(
            (int) $actorContext['contract_user_id'],
            $actorContext['type']->value,
            (int) $template->id
        );

        if (!$signature || (string) $signature->token !== (string) $token) {
            $this->audit('warning', 'Contract SMS token validation failed', [
                'actor_type' => $actorContext['type']->value,
                'actor_id' => $actorContext['route_actor_id'],
                'template_id' => $template->id,
            ]);

            throw new ContractSmsValidationException(trans('translations.contract_controller.token_hatali_lutfen_tekrar_deneyiniz'));
        }

        if ((string) $signature->status === ContractSignatureStatus::VERIFIED->value) {
            $this->audit('info', 'Contract SMS blocked because signature already verified', [
                'signature_id' => $signature->id,
            ]);

            throw new ContractAlreadyApprovedException('Bu sözleşme zaten onaylanmış.');
        }

        if ($this->isSendOnCooldown($signature)) {
            $this->audit('warning', 'Contract SMS blocked by cooldown', [
                'signature_id' => $signature->id,
                'cooldown_seconds' => self::SMS_SEND_COOLDOWN_SECONDS,
            ]);

            throw new ContractSmsValidationException('Onay kodu kısa süre önce gönderildi. Lütfen tekrar deneyiniz.');
        }

        $contract = $this->contractPersistenceService->findContractByContext($actorContext);
        if (!$contract || empty($contract->mobile_phone)) {
            throw new ContractSmsValidationException(trans('translations.contract_controller.lutfen_gsm_no_giriniz'));
        }

        $plainSmsCode = (string) random_int(1000, 9999);
        $signature->update([
            'sms_code' => $this->hashSmsCode($signature, $plainSmsCode),
        ]);

        $message = trans('translations.contract_controller.sozlesme_onayini_gerceklestirmek_icin_kodunuz_smscode', ['sms_code' => $plainSmsCode]);
        $recipient = '90' . $contract->mobile_phone;
        $recipients = [$recipient];

        $this->audit('info', 'Contract SMS sending started', [
            'signature_id' => $signature->id,
            'recipients' => $recipients,
        ]);

        $response = $this->smsExplorerService->sendSms($message, $recipients);
        if ((int) ($response['Code'] ?? 0) !== 200) {
            $signature->update(['sms_code' => null]);

            $this->audit('error', 'Contract SMS sending failed', [
                'signature_id' => $signature->id,
                'response' => $response,
            ]);

            throw new ContractSmsValidationException(trans('translations.contract_controller.istek_sirasinda_bir_hata_olustu_lutfen_daha_sonra_tekrar_deneyiniz'));
        }

        $signature->update([
            'sms_message_id' => (string) ($response['MessageId'] ?? ''),
        ]);

        Cache::put(ContractCacheKeys::smsCooldown((int) $signature->id), 1, now()->addSeconds(self::SMS_SEND_COOLDOWN_SECONDS));
        Cache::forget(ContractCacheKeys::smsAttempts((int) $signature->id));

        ContractSmsSent::dispatch(
            (int) $signature->id,
            (string) $actorContext['type']->value,
            (int) $actorContext['route_actor_id'],
            (int) $template->id,
            (string) $signature->sms_message_id,
            $recipient
        );

        $this->audit('info', 'Contract SMS sending completed', [
            'signature_id' => $signature->id,
            'sms_message_id' => $signature->sms_message_id,
        ]);
    }

    public function validateSmsCodeOrFail(ContractSignature $signature, string $smsCode): void
    {
        if ((string) $signature->status === ContractSignatureStatus::VERIFIED->value) {
            return;
        }

        if ($this->isCodeExpired($signature)) {
            $this->audit('warning', 'Contract SMS code expired', [
                'signature_id' => $signature->id,
            ]);

            throw new ContractSmsExpiredException('Onay kodunun süresi doldu. Lütfen yeni kod isteyiniz.');
        }

        if (!$this->verifySmsCode($signature, $smsCode)) {
            $attemptCount = $this->incrementFailedAttempt($signature);
            $this->audit('warning', 'Contract SMS code validation failed', [
                'signature_id' => $signature->id,
                'attempt_count' => $attemptCount,
            ]);

            if ($attemptCount >= self::MAX_FAILED_ATTEMPTS) {
                throw new ContractSmsValidationException('Çok fazla hatalı kod denendi. Lütfen yeni SMS kodu isteyiniz.');
            }

            throw new ContractSmsValidationException(trans('translations.contract_controller.onay_kodu_hatali'));
        }

        Cache::forget(ContractCacheKeys::smsAttempts((int) $signature->id));
    }

    public function clearVerificationState(ContractSignature $signature): void
    {
        Cache::forget(ContractCacheKeys::smsAttempts((int) $signature->id));
        Cache::forget(ContractCacheKeys::smsCooldown((int) $signature->id));
    }

    private function isCodeExpired(ContractSignature $signature): bool
    {
        if (empty($signature->sms_code)) {
            return true;
        }

        $updatedAt = $signature->updated_at;
        if (!$updatedAt) {
            return true;
        }

        return $updatedAt->copy()->addMinutes(self::SMS_CODE_EXPIRE_MINUTES)->isPast();
    }

    private function isSendOnCooldown(ContractSignature $signature): bool
    {
        return Cache::has(ContractCacheKeys::smsCooldown((int) $signature->id));
    }

    private function incrementFailedAttempt(ContractSignature $signature): int
    {
        $key = ContractCacheKeys::smsAttempts((int) $signature->id);
        $attempts = (int) Cache::get($key, 0) + 1;
        Cache::put($key, $attempts, now()->addMinutes(30));

        return $attempts;
    }

    private function hashSmsCode(ContractSignature $signature, string $plainCode): string
    {
        return hash_hmac('sha256', $plainCode, (string) config('app.key'));
    }

    private function verifySmsCode(ContractSignature $signature, string $inputCode): bool
    {
        $stored = (string) ($signature->sms_code ?? '');
        if ($stored === '') {
            return false;
        }

        if (preg_match('/^[a-f0-9]{64}$/', $stored) === 1) {
            $calculatedHmac = $this->hashSmsCode($signature, $inputCode);
            $calculatedLegacy = hash('sha256', $inputCode . '|' . $signature->id . '|' . config('app.key'));

            return hash_equals($stored, $calculatedHmac) || hash_equals($stored, $calculatedLegacy);
        }

        return hash_equals($stored, $inputCode);
    }

    private function audit(string $level, string $message, array $context = []): void
    {
        match ($level) {
            'debug' => Log::debug($message, $context),
            'info' => Log::info($message, $context),
            'notice' => Log::notice($message, $context),
            'warning' => Log::warning($message, $context),
            'error' => Log::error($message, $context),
            'critical' => Log::critical($message, $context),
            'alert' => Log::alert($message, $context),
            'emergency' => Log::emergency($message, $context),
            default => Log::info($message, $context),
        };

        if (function_exists('logSession')) {
            logSession($message, $context, $level, 'contract_logs');
        }
    }
}
