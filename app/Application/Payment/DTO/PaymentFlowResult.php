<?php

namespace App\Application\Payment\DTO;

class PaymentFlowResult
{
    public const TYPE_JSON = 'json';
    public const TYPE_HTML = 'html';
    public const TYPE_POST_MESSAGE = 'post_message';

    public function __construct(
        public string $type,
        public array $payload = []
    ) {}

    public static function json(array $payload): self
    {
        return new self(self::TYPE_JSON, $payload);
    }

    public static function html(string $html): self
    {
        return new self(self::TYPE_HTML, ['html' => $html]);
    }

    public static function postMessage(string $messageType, string $message, ?string $url = null): self
    {
        $payload = [
            'type' => $messageType,
            'message' => $message,
        ];

        if ($url !== null) {
            $payload['url'] = $url;
        }

        return new self(self::TYPE_POST_MESSAGE, $payload);
    }
}

