<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DaDataService
{
    private $apiKey;
    private $baseUrl = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address';

    public function __construct()
    {
        $this->apiKey = config('services.dadata.api_key');
        
        if (empty($this->apiKey)) {
            Log::error('DaData API key is not configured');
        }
    }

    /**
     * Получить подсказки адресов по запросу
     *
     * @param string $query
     * @param int $count
     * @return array
     */
    public function suggestAddresses(string $query, int $count = 10): array
    {
        if (empty($this->apiKey)) {
            Log::error('DaData API key is missing');
            return [];
        }

        try {
            Log::info('Making DaData API request', [
                'query' => $query,
                'count' => $count,
                'url' => $this->baseUrl
            ]);

            $response = Http::timeout(10)->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Token ' . $this->apiKey,
            ])->post($this->baseUrl, [
                'query' => $query,
                'count' => min($count, 20),
                'language' => 'ru',
                'locations' => [
                    [
                        'country' => 'Россия'
                    ]
                ]
            ]);

            Log::info('DaData API response', [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $suggestions = $data['suggestions'] ?? [];
                
                Log::info('DaData suggestions received', [
                    'count' => count($suggestions)
                ]);
                
                return $suggestions;
            }

            Log::error('DaData API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'query' => $query,
                'headers' => $response->headers()
            ]);

            return [];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('DaData API connection error', [
                'message' => $e->getMessage(),
                'query' => $query
            ]);
            return [];

        } catch (\Exception $e) {
            Log::error('DaData API exception', [
                'message' => $e->getMessage(),
                'query' => $query,
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Форматировать адрес для отображения
     *
     * @param array $suggestion
     * @return string
     */
    public function formatAddress(array $suggestion): string
    {
        return $suggestion['value'] ?? '';
    }

    /**
     * Получить детальную информацию об адресе
     *
     * @param array $suggestion
     * @return array
     */
    public function getAddressDetails(array $suggestion): array
    {
        $data = $suggestion['data'] ?? [];
        
        return [
            'postal_code' => $data['postal_code'] ?? '',
            'region' => $data['region'] ?? '',
            'city' => $data['city'] ?? '',
            'street' => $data['street'] ?? '',
            'house' => $data['house'] ?? '',
            'flat' => $data['flat'] ?? '',
            'geo_lat' => $data['geo_lat'] ?? null,
            'geo_lon' => $data['geo_lon'] ?? null,
        ];
    }
}
