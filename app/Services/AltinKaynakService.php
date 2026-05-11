<?php

namespace App\Services;

use SoapClient;

class AltinKaynakService
{
    private $username;
    private $password;
    private $wsdl;

    public function __construct()
    {
        $this->username = 'AltinkaynakWebServis';
        $this->password = 'AltinkaynakWebServis';
        $this->wsdl = 'http://data.altinkaynak.com/DataService.asmx?WSDL';
    }

    private function createSoapClient(): SoapClient
    {
        return new SoapClient($this->wsdl, [
            'trace' => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
        ]);
    }

    public function getCurrencies()
    {
        try {
            $client = $this->createSoapClient();

            $authHeader = [
                'Username' => $this->username,
                'Password' => $this->password,
            ];

            $headers = new \SoapHeader('http://data.altinkaynak.com/', 'AuthHeader', $authHeader);

            $client->__setSoapHeaders($headers);

            $response = $client->__soapCall('GetCurrency', []);

            return json_decode(json_encode($response), true);
        } catch (\SoapFault $e) {
            return [
                'error' => 'soap_fault',
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'exception',
                'message' => $e->getMessage(),
            ];
        }
    }
}
