<?php

namespace App\Application\DealerApplication\Repositories;

use App\Models\DealerApplication;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DealerApplicationRepository
{
    public function paginateForAdmin(?string $name, int $page = 1, ?int $perPage = null): LengthAwarePaginator
    {
        $resolvedPerPage = $perPage ?? 20;

        return DealerApplication::query()
            ->when($name, function ($query) use ($name) {
                $query->where('company_name', 'like', '%' . $name . '%');
            })
            ->orderByDesc('id')
            ->paginate($resolvedPerPage, ['*'], 'page', $page);
    }

    public function findWithDocumentsOrFail(int $id): DealerApplication
    {
        return DealerApplication::query()
            ->with('documents')
            ->findOrFail($id);
    }

    public function create(array $payload): DealerApplication
    {
        return DealerApplication::query()->create($payload);
    }

    public function attachDocuments(DealerApplication $application, array $paths): void
    {
        foreach ($paths as $path) {
            $application->documents()->create(['path' => $path]);
        }
    }

    public function delete(DealerApplication $application): bool
    {
        return (bool) $application->delete();
    }

    public function markEmailSent(DealerApplication $application): void
    {
        $application->update(['email_sent' => 1]);
    }

    public function listNotEmailed()
    {
        return DealerApplication::query()
            ->where('email_sent', 0)
            ->orderBy('id')
            ->get();
    }
}
