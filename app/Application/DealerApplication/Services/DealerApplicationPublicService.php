<?php

namespace App\Application\DealerApplication\Services;

use App\Application\DealerApplication\DTO\DealerApplicationData;
use App\Application\DealerApplication\Exceptions\DealerApplicationValidationException;
use App\Application\DealerApplication\Repositories\DealerApplicationRepository;
use App\Application\DealerApplication\Validators\DealerApplicationFormValidator;
use App\Models\DealerApplication;
use Illuminate\Support\Facades\DB;
use Throwable;

class DealerApplicationPublicService
{
    public function __construct(
        private DealerApplicationFormValidator $dealerApplicationFormValidator,
        private DealerApplicationRepository $dealerApplicationRepository,
        private DealerApplicationDocumentService $dealerApplicationDocumentService,
        private DealerApplicationAuditService $dealerApplicationAuditService
    ) {}

    public function submit(array $payload, ?array $documents, string $ipAddress): DealerApplication
    {
        $validatorPayload = $payload;
        $validatorPayload['documents'] = $documents ?? [];

        $validator = $this->dealerApplicationFormValidator->make($validatorPayload);

        if ($validator->fails()) {
            $message = (string) ($validator->errors()->first() ?: 'Lütfen form alanlarını kontrol ediniz.');

            throw new DealerApplicationValidationException($message, $validator->errors()->toArray());
        }

        $validatedPayload = $validator->validated();
        unset($validatedPayload['documents']);

        $data = DealerApplicationData::fromRequestPayload($validatedPayload, $ipAddress);
        $storedPaths = [];

        try {
            $application = DB::transaction(function () use ($data, $documents, &$storedPaths) {
                $application = $this->dealerApplicationRepository->create($data->toDatabasePayload());
                $storedPaths = $this->dealerApplicationDocumentService->storeMany($documents);

                if (!empty($storedPaths)) {
                    $this->dealerApplicationRepository->attachDocuments($application, $storedPaths);
                }

                return $application;
            });
        } catch (Throwable $e) {
            if (!empty($storedPaths)) {
                $this->dealerApplicationDocumentService->deleteMany($storedPaths);
            }

            throw $e;
        }

        $this->dealerApplicationAuditService->info('Dealer application submitted', [
            'dealer_application_id' => (int) $application->id,
            'has_documents' => !empty($storedPaths),
            'documents_count' => count($storedPaths),
            'email_sent' => (int) ($application->email_sent ?? 0),
        ]);

        return $application;
    }
}
