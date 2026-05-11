<?php

namespace App\Services;

use App\Models\GeneralInfo;
use App\Repositories\GeneralInfoRepository;
use Illuminate\Http\Request;

class GeneralInfoService
{
    protected $repository;

    public function __construct(GeneralInfoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getFirst()
    {
        return $this->repository->getFirst();
    }

    public function update(Request $request, GeneralInfo $generalInfo): GeneralInfo
    {
        return $this->repository->update($generalInfo, $request->all());
    }
}
