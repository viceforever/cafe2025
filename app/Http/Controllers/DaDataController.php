<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DaDataController extends Controller
{
    /**
     * Простой тест без DaData API
     */
    public function simpleTest()
    {
        try {
            return response()->json([
                'status' => 'success',
                'message' => 'Простой тест работает',
                'timestamp' => now()->toDateTimeString(),
                'controller' => 'DaDataController загружен успешно'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Проверка конфигурации
     */
    public function testConfig()
    {
        try {
            return response()->json([
                'status' => 'success',
                'config' => [
                    'token' => config('dadata.token') ? 'установлен (' . substr(config('dadata.token'), 0, 10) . '...)' : 'не установлен',
                    'secret' => config('dadata.secret') ? 'установлен (' . substr(config('dadata.secret'), 0, 10) . '...)' : 'не установлен',
                    'timeout' => config('dadata.timeout', 'не установлен')
                ],
                'env' => [
                    'DADATA_TOKEN' => env('DADATA_TOKEN') ? 'установлен (' . substr(env('DADATA_TOKEN'), 0, 10) . '...)' : 'не установлен',
                    'DADATA_SECRET' => env('DADATA_SECRET') ? 'установлен (' . substr(env('DADATA_SECRET'), 0, 10) . '...)' : 'не установлен',
                    'DADATA_TIMEOUT' => env('DADATA_TIMEOUT')
                ],
                'class_exists' => class_exists('MoveMoveIo\\DaData\\Facades\\DaDataAddress') ? 'да' : 'нет',
                'facade_available' => class_exists('MoveMoveIo\\DaData\\Facades\\DaDataAddress') ? 'да' : 'нет'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Тестирование API DaData через HTTP запрос
     */
    public function testApi()
    {
        try {
            $token = config('dadata.token');
            $secret = config('dadata.secret');
            
            if (!$token || !$secret) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Токен или секрет DaData не настроены',
                    'config_check' => [
                        'token' => $token ? 'установлен' : 'не установлен',
                        'secret' => $secret ? 'установлен' : 'не установлен'
                    ]
                ], 400);
            }

            $client = new \GuzzleHttp\Client([
                'verify' => app()->environment('production'), // Проверяем SSL только в продакшене
                'timeout' => config('dadata.timeout', 10),
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => app()->environment('production'),
                    CURLOPT_SSL_VERIFYHOST => app()->environment('production') ? 2 : 0,
                ]
            ]);
            
            $response = $client->post('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Token ' . $token,
                    'X-Secret' => $secret,
                ],
                'json' => [
                    'query' => 'Москва',
                    'count' => 5
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            return response()->json([
                'status' => 'success',
                'message' => 'DaData API работает',
                'count' => count($data['suggestions'] ?? []),
                'first_suggestion' => $data['suggestions'][0]['value'] ?? null,
                'config_check' => [
                    'token' => 'установлен',
                    'secret' => 'установлен'
                ]
            ]);
            
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка HTTP клиента: ' . $e->getMessage(),
                'response_code' => $e->getCode(),
                'response_body' => $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Общая ошибка: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Получить подсказки адресов через HTTP запрос
     */
    public function getAddressSuggestions(Request $request)
    {
        $query = $request->get('query');
        
        if (empty($query) || strlen($query) < 3) {
            return response()->json([]);
        }

        try {
            $token = config('dadata.token');
            $secret = config('dadata.secret');
            
            if (!$token || !$secret) {
                return response()->json(['error' => 'DaData не настроен'], 500);
            }

            $client = new \GuzzleHttp\Client([
                'verify' => app()->environment('production'), // Проверяем SSL только в продакшене
                'timeout' => config('dadata.timeout', 10),
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => app()->environment('production'),
                    CURLOPT_SSL_VERIFYHOST => app()->environment('production') ? 2 : 0,
                ]
            ]);
            
            $response = $client->post('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Token ' . $token,
                    'X-Secret' => $secret,
                ],
                'json' => [
                    'query' => $query,
                    'count' => 10,
                    'locations' => [
                        [
                            'city' => 'Иркутск'
                        ]
                    ]
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            $formattedSuggestions = [];
            foreach ($data['suggestions'] ?? [] as $suggestion) {
                $formattedSuggestions[] = [
                    'value' => $suggestion['value'],
                    'unrestricted_value' => $suggestion['unrestricted_value'],
                    'data' => $suggestion['data']
                ];
            }
            
            return response()->json($formattedSuggestions);
            
        } catch (\Exception $e) {
            \Log::error('DaData API Error: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка получения подсказок адресов: ' . $e->getMessage()], 500);
        }
    }
}
