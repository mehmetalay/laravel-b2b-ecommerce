<?php

namespace App\Application\DealerApplication\Services;

use App\Application\DealerApplication\Exceptions\DealerApplicationDocumentNotFoundException;
use App\Application\DealerApplication\Repositories\DealerApplicationRepository;
use App\Models\DealerApplication;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class DealerApplicationAdminService
{
    public function __construct(
        private DealerApplicationRepository $dealerApplicationRepository,
        private DealerApplicationDocumentService $dealerApplicationDocumentService,
        private DealerApplicationAuditService $dealerApplicationAuditService
    ) {}

    public function paginate(?string $name, int $page = 1, ?int $perPage = null): LengthAwarePaginator
    {
        return $this->dealerApplicationRepository->paginateForAdmin($name, $page, $perPage);
    }

    public function findById(int $id): DealerApplication
    {
        return $this->dealerApplicationRepository->findWithDocumentsOrFail($id);
    }

    public function delete(DealerApplication $application, ?int $adminId = null): bool
    {
        $documentCount = $application->documents()->count();
        $deleted = $this->dealerApplicationRepository->delete($application);

        $this->dealerApplicationAuditService->warning('Dealer application deleted', [
            'dealer_application_id' => (int) $application->id,
            'admin_id' => $adminId,
            'documents_count' => $documentCount,
            'deleted' => $deleted,
        ]);

        return $deleted;
    }

    /**
     * @throws DealerApplicationDocumentNotFoundException
     */
    public function downloadByPath(string $path)
    {
        $resolvedPath = $this->dealerApplicationDocumentService->resolveDownloadPathOrFail($path);

        $this->dealerApplicationAuditService->info('Dealer application document downloaded', [
            'path' => $resolvedPath,
            'admin_id' => auth('admin')->id(),
        ]);

        return Storage::download($resolvedPath);
    }
}
