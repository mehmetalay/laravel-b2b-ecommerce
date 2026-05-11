<?php

namespace App\Services;

use App\Models\EntityLastUpdate;
use Illuminate\Support\Facades\Cache;

class EntityLastUpdateService
{
    public function touch(string $entityType): void
    {
        EntityLastUpdate::updateOrCreate(
            [
                'entity_type' => $entityType
            ],
            [
                'last_update_date' => now()
            ]
        );

        forget_cache_keys([
            self::cacheKey($entityType),
            'all_entity_last_updates'
        ]);
    }

    public function get(string $entityType)
    {
        return Cache::rememberForever(self::cacheKey($entityType), function () use ($entityType) {
            $record = EntityLastUpdate::where('entity_type', $entityType)->first();

            return $record ? $record->last_update_date : null;
        });
    }

    private static function cacheKey(string $entityType): string
    {
        return "entity_last_update:{$entityType}";
    }
}
