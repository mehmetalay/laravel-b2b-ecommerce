<?php

namespace App\Services;

use App\Repositories\AddressRepository;
use App\Services\CurrentAccountService;
use Illuminate\Support\Facades\DB;

class AddressService
{
    public function __construct(
        protected AddressRepository $repository
    ) {}

    /**
     * dealer / subdealer ayrımını tek noktada yapar
     */
    protected function resolveOwner(): ?array
    {
        $accountService = app(CurrentAccountService::class);
        $account = $accountService->currentAccount();
        $query = $accountService->userQuery();

        if (($account && $query['creator_type'] === 'salesman') || $query['creator_type'] === 'dealer') {
            return [
                'id' => $query['user_id'],
                'type' => 'dealer',
            ];
        }

        if ($query['creator_type'] === 'subdealer') {
            return [
                'id' => $query['sub_dealer_id'],
                'type' => 'subdealer',
            ];
        }

        return null;
    }

    public function listForUser()
    {
        $owner = $this->resolveOwner();
        if (!$owner) {
            return collect();
        }

        return $this->repository->listByUser($owner['id'], $owner['type']);
    }

    public function getForEdit(int $id)
    {
        $owner = $this->resolveOwner();
        if (!$owner) {
            abort(403);
        }

        return $this->repository->findByUser($id, $owner['id'], $owner['type']);
    }

    public function store(array $data)
    {
        $owner = $this->resolveOwner();
        if (!$owner) {
            abort(403);
        }

        return DB::transaction(function () use ($data, $owner) {

            if (!empty($data['is_default'])) {
                $this->repository->unsetDefault($owner['id'], $owner['type']);
            }

            if (!empty($data['id'])) {
                $address = $this->repository->findByUser($data['id'], $owner['id'], $owner['type']);
                return $this->repository->update($address, $data, $owner['id'], $owner['type']);
            }

            return $this->repository->create($data, $owner['id'], $owner['type']);
        });
    }

    public function delete(int $id): void
    {
        $owner = $this->resolveOwner();
        if (!$owner) {
            abort(403);
        }

        $address = $this->repository->findByUser($id, $owner['id'], $owner['type']);
        $this->repository->delete($address, $owner['id'], $owner['type']);
    }
}
