<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SmsExplorerService
{
    public function __construct(
        protected ?string $username = null,
        protected ?string $password = null,
        protected ?string $from = null,
        protected ?string $endpoint = null,
    ) {
        $this->username = $username ?? config('services.sms_explorer.username');
        $this->password = $password ?? config('services.sms_explorer.password');
        $this->from = $from ?? config('services.sms_explorer.from');
        $this->endpoint = $endpoint ?? config('services.sms_explorer.endpoint');
    }

    public function sendSms(string $message, array $recipients): array
    {
        if (empty($recipients)) {
            throw new RuntimeException('SMS recipient list is empty.');
        }

        $xml = $this->buildXmlRequest($message, $recipients);

        $response = Http::withHeaders([
            'Content-Type' => 'application/xml',
        ])
            ->timeout(15)
            ->withBody($xml, 'application/xml')
            ->post($this->endpoint);

        if (!$response->successful()) {
            Log::error('SmsExplorer HTTP request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'MessageId' => '',
                'Code' => $response->status(),
                'Description' => 'HTTP request failed',
            ];
        }

        return $this->parseResponse($response->body());
    }

    protected function buildXmlRequest(string $message, array $recipients): string
    {
        $recipientsXml = '';

        foreach ($recipients as $recipient) {
            $recipient = htmlspecialchars((string) $recipient, ENT_XML1 | ENT_COMPAT, 'UTF-8');
            $recipientsXml .= "<d2p1:string>{$recipient}</d2p1:string>";
        }

        $username = htmlspecialchars((string) $this->username, ENT_XML1 | ENT_COMPAT, 'UTF-8');
        $password = htmlspecialchars((string) $this->password, ENT_XML1 | ENT_COMPAT, 'UTF-8');
        $from = htmlspecialchars((string) $this->from, ENT_XML1 | ENT_COMPAT, 'UTF-8');
        $message = htmlspecialchars($message, ENT_XML1 | ENT_COMPAT, 'UTF-8');

        return <<<XML
<Submit xmlns:i="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://schemas.maradit.net/api/types">
    <Credential>
        <Password>{$password}</Password>
        <Username>{$username}</Username>
    </Credential>
    <DataCoding>Default</DataCoding>
    <Header>
        <From>{$from}</From>
        <Route>0</Route>
        <ScheduledDeliveryTime>0001-01-01T00:00:00</ScheduledDeliveryTime>
        <ValidityPeriod>1440</ValidityPeriod>
    </Header>
    <Message>{$message}</Message>
    <To xmlns:d2p1="http://schemas.microsoft.com/2003/10/Serialization/Arrays">
        {$recipientsXml}
    </To>
</Submit>
XML;
    }

    protected function parseResponse(string $responseBody): array
    {
        $xml = simplexml_load_string($responseBody);

        if (!$xml || !isset($xml->Response)) {
            Log::error('SmsExplorer invalid XML response', [
                'body' => $responseBody,
            ]);

            return [
                'MessageId' => '',
                'Code' => 500,
                'Description' => 'Invalid SMS provider response',
            ];
        }

        $response = $xml->Response;
        $status = $response->Status;

        return [
            'MessageId' => (string) ($response->MessageId ?? ''),
            'Code' => (int) ($status->Code ?? 500),
            'Description' => (string) ($status->Description ?? ''),
        ];
    }
}