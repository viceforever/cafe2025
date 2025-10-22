<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AddressController extends Controller
{
    /**
     * Получить подсказки адресов (GET метод для фронтенда)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSuggestions(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query');
            
            Log::info('[v0] Address suggestions request', [
                'query' => $query,
                'query_length' => strlen($query ?? '')
            ]);
            
            if (!$query || strlen($query) < 3) {
                Log::info('[v0] Query too short, returning empty array');
                return response()->json([]);
            }

            $token = env('DADATA_TOKEN');
            
            Log::info('[v0] DaData credentials check', [
                'token_exists' => !empty($token),
                'token_length' => strlen($token ?? ''),
            ]);

            if (empty($token)) {
                Log::error('[v0] DaData token is not configured');
                return response()->json([
                    ['value' => 'Ошибка: API токен DaData не настроен. Добавьте DADATA_TOKEN в .env']
                ]);
            }

            Log::info('[v0] Making request to DaData API');
            
            $response = Http::withOptions([
                'verify' => false, // Отключаем проверку SSL сертификата для разработки
            ])->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Token ' . $token,
            ])->post('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', [
                'query' => $query,
                'count' => 10
            ]);

            Log::info('[v0] DaData API response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
            ]);

            if (!$response->successful()) {
                Log::error('[v0] DaData API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json([
                    ['value' => 'Ошибка API DaData. Проверьте токен и подключение к интернету.']
                ]);
            }

            $data = $response->json();
            
            if (!isset($data['suggestions']) || empty($data['suggestions'])) {
                Log::info('[v0] No suggestions found');
                return response()->json([]);
            }

            Log::info('[v0] Found suggestions', [
                'count' => count($data['suggestions'])
            ]);

            // Форматируем результаты для фронтенда
            $formattedSuggestions = array_map(function ($suggestion) {
                return [
                    'value' => $suggestion['value'] ?? '',
                    'unrestricted_value' => $suggestion['unrestricted_value'] ?? '',
                    'data' => $suggestion['data'] ?? []
                ];
            }, $data['suggestions']);

            return response()->json($formattedSuggestions);
            
        } catch (\Exception $e) {
            Log::error('[v0] Address suggestions exception', [
                'message' => $e->getMessage(),
            ]);
            
            return response()->json([
                ['value' => 'Ошибка: ' . $e->getMessage()]
            ]);
        }
    }

    /**
     * Получить подсказки адресов (POST метод)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function suggest(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:3|max:100',
            'count' => 'nullable|integer|min:1|max:20'
        ]);

        $query = $request->input('query');
        $count = $request->input('count', 10);

        try {
            $token = env('DADATA_TOKEN');

            if (empty($token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'API токен DaData не настроен'
                ]);
            }

            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Token ' . $token,
            ])->post('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', [
                'query' => $query,
                'count' => $count
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка API DaData'
                ]);
            }

            $data = $response->json();
            
            if (!isset($data['suggestions']) || empty($data['suggestions'])) {
                return response()->json([
                    'success' => true,
                    'suggestions' => []
                ]);
            }

            $formattedSuggestions = array_map(function ($suggestion) {
                return [
                    'value' => $suggestion['value'] ?? '',
                    'unrestricted_value' => $suggestion['unrestricted_value'] ?? '',
                    'data' => $suggestion['data'] ?? []
                ];
            }, $data['suggestions']);

            return response()->json([
                'success' => true,
                'suggestions' => $formattedSuggestions
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Тестовый метод для проверки работы API
     *
     * @return JsonResponse
     */
    public function test(): JsonResponse
    {
        try {
            $testQuery = 'Иркутск Ленина';
            $token = env('DADATA_TOKEN');

            if (empty($token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'API токен DaData не настроен'
                ]);
            }

            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Token ' . $token,
            ])->post('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', [
                'query' => $testQuery,
                'count' => 5
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка API DaData'
                ]);
            }

            $data = $response->json();
            
            if (!isset($data['suggestions']) || empty($data['suggestions'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'DaData API не вернул результаты'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'test_query' => $testQuery,
                'suggestions_count' => count($data['suggestions']),
                'first_suggestion' => $data['suggestions'][0]['value'] ?? null,
                'message' => 'DaData API работает корректно'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage()
            ], 500);
        }
    }
}
