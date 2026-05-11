<?php

namespace App\Application\DealerApplication\Services;

use App\Application\DealerApplication\Repositories\DealerApplicationRepository;
use App\Mail\DealerApplicationMail;
use App\Models\DealerApplication;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DealerApplicationNotificationService
{
    public function __construct(
        private DealerApplicationRepository $dealerApplicationRepository,
        private DealerApplicationAuditService $dealerApplicationAuditService
    ) {}

    public function sendApplicationMail(DealerApplication $application): bool
    {
        try {
            Mail::send(new DealerApplicationMail($application));
            $this->dealerApplicationRepository->markEmailSent($application);

            $this->dealerApplicationAuditService->info('Dealer application mail send attempt completed', [
                'dealer_application_id' => (int) $application->id,
                'success' => true,
            ]);

            return true;
        } catch (Throwable $e) {
            $this->dealerApplicationAuditService->error('Dealer application mail send failed', [
                'dealer_application_id' => (int) $application->id,
                'exception' => $e->getMessage(),
            ]);

            $this->sendFailureNotification((int) $application->id);

            return false;
        }
    }

    public function sendPendingApplicationMails(): bool
    {
        try {
            $applications = $this->dealerApplicationRepository->listNotEmailed();
            $total = $applications->count();
            $successCount = 0;
            $failedCount = 0;

            foreach ($applications as $application) {
                if ($this->sendApplicationMail($application)) {
                    $successCount++;
                } else {
                    $failedCount++;
                }
            }

            $this->dealerApplicationAuditService->info('Dealer application pending mail batch completed', [
                'total' => $total,
                'success_count' => $successCount,
                'failed_count' => $failedCount,
            ]);

            return true;
        } catch (Throwable $e) {
            $this->dealerApplicationAuditService->error('Dealer application pending mail batch failed', [
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function sendFailureNotification(int $dealerApplicationId): void
    {
        try {
            Mail::raw('Dealer application mail gönderimi başarısız oldu.', function ($message) {
                $message->to(config('services.notifications.error_mail'))->subject(config('app.name') . ' Hata');
            });
        } catch (Throwable $mailException) {
            $this->dealerApplicationAuditService->error('Dealer application fallback error mail failed', [
                'dealer_application_id' => $dealerApplicationId,
                'exception' => $mailException->getMessage(),
            ]);
        }
    }
}
