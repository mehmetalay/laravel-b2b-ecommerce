<?php

namespace App\Helpers;

use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\CurrentAccountService;

class Logger
{
    protected static function getDynamicLogger(?string $channel = null)
    {
        $path = $channel ? storage_path("logs/{$channel}/" . date('d-m-Y') . '.log') : storage_path("logs/" . date('d-m-Y') . '.log');

        return Log::build([
            'driver' => 'single',
            'path' => $path,
            'level' => 'debug',
        ]);
    }

    public static function exception(Throwable $e, ?string $context = null, ?bool $sendEmail = false, ?string $channel = null): void
    {
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();

        $logMessage = "[Exception]";
        if ($context) {
            $logMessage .= " [$context]";
        }
        $logMessage .= " $message in $file on line $line";

        $logger = self::getDynamicLogger($channel);
        $logger->error($logMessage);

        if ($sendEmail) {
            try {
                Mail::raw($logMessage, function ($message) {
                    $message->to(config('services.notifications.error_mail'))->subject(request()->server('SERVER_NAME') . ' Hata');
                });
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    }

    public static function session(string $message, $data = null, string $level = 'info', ?string $channel = null): void
    {
        $logMessage = $message;

        try {
            if (auth('web')->check() || auth('subdealer')->check()) {
                $userQuery = (new CurrentAccountService())->userQuery();

                $prefix = trim(
                    (isset($userQuery['plasiyer_id']) ? ' salesmanId: ' . $userQuery['plasiyer_id'] : null) .
                    (isset($userQuery['user_id']) ? ' dealerId: ' . $userQuery['user_id'] : null) .
                    (isset($userQuery['sub_dealer_id']) ? ' subDealerId: ' . $userQuery['sub_dealer_id'] : null)
                );

                $logMessage = $prefix . ' | ' . $message;
            }
        } catch (\Throwable $e) {
            //
        }

        if ($data) {
            $encoded = json_encode($data);
            if ($encoded === false) {
                $encoded = 'json_encode_error: ' . json_last_error_msg();
            }
            $logMessage .= ' | ' . $encoded;
        }

        $logger = self::getDynamicLogger($channel);

        switch ($level) {
            case 'error':
                $logger->error($logMessage);
                break;
            case 'warning':
                $logger->warning($logMessage);
                break;
            case 'debug':
                $logger->debug($logMessage);
                break;
            case 'notice':
                $logger->notice($logMessage);
                break;
            default:
                $logger->info($logMessage);
        }
    }
}
