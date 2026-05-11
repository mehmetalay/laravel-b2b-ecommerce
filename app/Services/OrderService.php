<?php

namespace App\Services;

use App\Models\Order;

use App\Repositories\OrderRepository;
use Illuminate\Http\Request;

class OrderService
{
    protected $repository;

    public function __construct(OrderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @deprecated Use OrderRepository directly from Application layer use-cases.
     */
    public function getFirst($id)
    {
        return $this->repository->getFirst($id);
    }

    /**
     * @deprecated Use OrderRepository directly from Application layer use-cases.
     */
    public function createRaw(array $data)
    {
        return $this->repository->create($data);
    }

    /**
     * @deprecated Use OrderRepository directly from Application layer use-cases.
     */
    public function update(Request $request, Order $order): Order
    {
        return $this->repository->update($order, $request->all());
    }

    /**
     * @deprecated Use OrderRepository directly from Application layer use-cases.
     */
    public function updateRaw(array $data, Order $order): Order
    {
        return $this->repository->update($order, $data);
    }

    /**
     * @deprecated Use OrderRepository directly from Application layer use-cases.
     */
    public function delete(Order $order)
    {
        return $this->repository->delete($order);
    }

    /**
     * @deprecated Use OrderRepository directly from Application layer use-cases.
     */
    public function markAsProcessing($id)
    {
        return $this->repository->markAsProcessing($id);
    }

    /**
     * @deprecated Use OrderRepository directly from Application layer use-cases.
     */
    public function markAsSent($id, $documentNo = null)
    {
        return $this->repository->markAsSent($id, $documentNo);
    }

    public function markAsFailed($id, $error = null)
    {
        if ($error !== null && !is_string($error)) {
            $error = json_encode($error, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        } elseif (is_string($error)) {
            $error = json_encode(['message' => $error], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        }

        return $this->repository->markAsFailed($id, $error);
    }

    /**
     * @deprecated Use OrderRepository directly from Application layer use-cases.
     */
    public function resetErpSync($id)
    {
        return $this->repository->resetErpSync($id);
    }

    /**
     * @deprecated Use OrderRepository directly from Application layer use-cases.
     */
    public function clearCache($id)
    {
        return $this->repository->clearCache($id);
    }
}
