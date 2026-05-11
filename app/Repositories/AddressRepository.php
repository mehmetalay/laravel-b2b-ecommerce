<?php

namespace App\Repositories;

use App\Models\CustomerAddress;
use Illuminate\Support\Facades\Cache;

class AddressRepository
{
    protected function cacheKey(int $id, string $type): array
    {
        return [
            "customer_addresses:{$type}:{$id}",
        ];
    }

    public function listByUser(int $id, string $type)
    {
        return Cache::rememberForever(
            "customer_addresses:{$type}:{$id}",
            fn () => CustomerAddress::with(['city', 'district', 'neighborhood'])
                ->when($type === 'dealer', fn ($q) => $q->where('dealer_id', $id))
                ->when($type === 'subdealer', fn ($q) => $q->where('sub_dealer_id', $id))
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->get()
        );
    }

    public function findByUser(int $id, int $ownerId, string $type): CustomerAddress
    {
        return CustomerAddress::when(
                $type === 'dealer',
                fn ($q) => $q->where('dealer_id', $ownerId),
                fn ($q) => $q->where('sub_dealer_id', $ownerId)
            )
            ->findOrFail($id);
    }

    public function unsetDefault(int $ownerId, string $type): void
    {
        CustomerAddress::when(
            $type === 'dealer',
            fn ($q) => $q->where('dealer_id', $ownerId),
            fn ($q) => $q->where('sub_dealer_id', $ownerId)
        )->update(['is_default' => 0]);
    }

    public function create(array $data, int $ownerId, string $type): CustomerAddress
    {
        $data[$type === 'dealer' ? 'dealer_id' : 'sub_dealer_id'] = $ownerId;

        $address = CustomerAddress::create($data);
        $this->clearCache($ownerId, $type);

        return $address;
    }

    public function update(CustomerAddress $address, array $data, int $ownerId, string $type): CustomerAddress
    {
        $address->update($data);
        $this->clearCache($ownerId, $type);

        return $address;
    }

    public function delete(CustomerAddress $address, int $ownerId, string $type): void
    {
        $address->delete();
        $this->clearCache($ownerId, $type);
    }

    protected function clearCache(int $id, string $type): void
    {
        forget_cache_keys($this->cacheKey($id, $type));
    }
}
