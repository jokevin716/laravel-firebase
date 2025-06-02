<?php

namespace App\Services;

class MarketstackService {
    private string $apiKey;
    private string $baseUrl = 'http://api.marketstack.com/v1/';

    public function __construct() {
        $this->apiKey = env('MARKETSTACK_API_KEY');
    }

    public function getStockData(string $symbol = 'AAPL', int $limit = 30): array {
        $url = $this->baseUrl . 'eod';
        $params = http_build_query([
            'access_key' => $this->apiKey,
            'symbols' => $symbol,
            'limit' => $limit,
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url . '?' . $params,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if($error) {
            throw new \Exception('CURL Error: ' . $error);
        }

        if($httpCode !== 200) {
            throw new \Exception('API Error: HTTP ' . $httpCode);
        }

        $data = json_decode($response, true);

        if(json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response');
        }

        return $data;
    }

    public function getMultipleStocks(array $symbols = ['AAPL', 'GOOGL', 'MSFT'], int $limit = 10): array {
        $symbolString = implode(',', $symbols);
        return $this->getStockData($symbolString, $limit);
    }
}
