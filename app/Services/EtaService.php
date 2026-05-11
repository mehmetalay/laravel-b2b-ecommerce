<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class EtaService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.erp.base_url');
    }

    public function getlistall()
    {
        return $this->get('/Stok/getlistall');
    }

    public function importOrder(array $json)
    {
        return $this->post('/Siparis/insert', $json);
    }

    public function importPosTransaction(array $json)
    {
        return $this->post('/Banka/InsertBankaFis', $json);
    }

    protected function get(string $endpoint)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->get($this->baseUrl . $endpoint);

        Log::info('EtaService JSON Request', [
            'endpoint' => $this->baseUrl . $endpoint,
            'status' => $response->status(),
            'success' => $response->successful(),
        ]);

        return $response->successful();
    }

    protected function post(string $endpoint, array $data)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($this->baseUrl . $endpoint, $data);

        Log::info('EtaService JSON Request', [
            'endpoint' => $this->baseUrl . $endpoint,
            'status' => $response->status(),
            'success' => $response->successful(),
            'body' => $response->body(),
        ]);

        if (!$response->successful()) {
            return null;
        }

        return $response->json();
    }
}
