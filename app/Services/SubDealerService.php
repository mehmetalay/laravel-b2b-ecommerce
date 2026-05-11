<?php

namespace App\Services;

use App\Models\SubDealer;
use Illuminate\Http\Request;
use App\Repositories\SubDealerRepository;
use Illuminate\Support\Facades\Hash;

class SubDealerService
{
    protected $repository;

    public function __construct(SubDealerRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create($dealerId, array $data)
    {
        $data['dealer_id'] = $dealerId;
        $data['name'] = mb_strtoupper($data['name'], 'utf-8');
        $data['password'] = Hash::make($data['password']);

        $data['phone'] = str_replace(['(', ')', ' ', '-'], '', $data['phone']);
        $data['status'] = 1;
        $data['can_place_order'] = isset($data['can_place_order']) ? 1 : 0;
        $data['can_approve_order'] = isset($data['can_approve_order']) ? 1 : 0;
        $data['can_record_payment'] = isset($data['can_record_payment']) ? 1 : 0;
        $data['can_view_prices'] = isset($data['can_view_prices']) ? 1 : 0;

        return $this->repository->create($data);
    }

    public function update(array $data, $subDealer)
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['name'] = mb_strtoupper($data['name'], 'utf-8');
        $data['phone'] = str_replace(['(', ')', ' ', '-'], '', $data['phone']);
        $data['status'] = isset($data['status']) ? 1 : 0;
        $data['can_place_order'] = isset($data['can_place_order']) ? 1 : 0;
        $data['can_approve_order'] = isset($data['can_approve_order']) ? 1 : 0;
        $data['can_record_payment'] = isset($data['can_record_payment']) ? 1 : 0;
        $data['can_view_prices'] = isset($data['can_view_prices']) ? 1 : 0;

        return $this->repository->update($subDealer, $data);
    }

    public function delete(SubDealer $subDealer)
    {
        return $this->repository->delete($subDealer);
    }

    public function getFirst($id)
    {
        return $this->repository->getFirst($id);
    }

    public function getAllSubDealers()
    {
        return $this->repository->getAllSubDealers();
    }

    public function getActiveSubDealers()
    {
        return $this->repository->getActiveSubDealers();
    }

    public function getByDealer($dealerId)
    {
        return $this->repository->getByDealer($dealerId);
    }
}
