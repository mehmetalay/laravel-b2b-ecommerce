<?php

namespace App\Application\Survey\Services;

use Illuminate\Support\Facades\Log;

class SurveyAuditService
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
        if (function_exists('logSession')) {
            logSession($message, $context, $level, 'survey_logs');
            return;
        }

        Log::{$level}($message, $context);
    }
}

