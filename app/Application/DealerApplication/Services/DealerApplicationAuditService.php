<?php

namespace App\Application\DealerApplication\Services;

use Illuminate\Support\Facades\Log;

class DealerApplicationAuditService
{
    public function info(string $message, array $context = []): void
    {
        $this->write('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('error', $message, $context);
    }

    private function write(string $level, string $message, array $context = []): void
    {
        $channels = (array) config('logging.channels', []);
        if (array_key_exists('dealer_application_logs', $channels)) {
            Log::channel('dealer_application_logs')->{$level}($message, $context);
            return;
        }

        if (function_exists('logSession')) {
            logSession($message, $context, $level, 'dealer_application_logs');
            return;
        }

        Log::{$level}($message, $context);
    }
}
