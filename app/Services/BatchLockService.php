<?php

namespace App\Services;

use App\Models\BatchLock;
use Carbon\Carbon;

class BatchLockService
{
    public function __construct(
        protected string $jobName,
        protected int $timeoutMinutes = 60
    ) {}

    /**
     * Cron lock alır. Eğer işlem devam ediyorsa false döner.
     */
    public function acquire(): bool
    {
        $lock = BatchLock::firstOrCreate(['job_name' => $this->jobName]);

        if ($lock->is_running) {
            $started = $lock->started_at ? Carbon::parse($lock->started_at) : Carbon::now()->subMinutes($this->timeoutMinutes + 1);

            if ($started->diffInMinutes(now()) < $this->timeoutMinutes) {
                return false;
            }

            $lock->update([
                'is_running' => 0,
                'finished_at' => null,
            ]);
        }

        $lock->update([
            'is_running' => 1,
            'started_at' => now(),
            'finished_at' => null,
        ]);

        return true;
    }

    /**
     * Cron lock bırakır
     */
    public function release(): void
    {
        $lock = BatchLock::firstOrCreate(['job_name' => $this->jobName]);

        $lock->update([
            'is_running' => 0,
            'finished_at' => now(),
        ]);
    }
}