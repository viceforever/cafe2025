<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MoveMoveIo\DaData\Facades\DaDataAddress;
use MoveMoveIo\DaData\Enums\Language;

class DaDataController extends Controller
{
    /**
     * Получить подсказки адресов
     */
    public function getAddressSuggestions(Request $request)
    {
        $query = $request->get('query');
        
        \Log::info('[DaData] Получен запрос', [
            'query' => $query,
            'query_length' => strlen($query ?? ''),
            'config_token' => config('dadata.token') ? 'установлен' : 'не установлен',
            'config_secret' => config('dadata.secret') ? 'установлен' : 'не установлен',
        ]);
        
        if (empty($query) || strlen($query) < 3) {
            \Log::info('[DaData] Запрос слишком короткий или пустой');
            return response()->json([]);
        }

        try {
            \Log::info('[DaData] Отправляем запрос к API DaData');
            $suggestions = DaDataAddress::suggest($query, 10, Language::RU);
            
            \Log::info('[DaData] Получен ответ от API', [
                'suggestions_count' => count($suggestions),
                'raw_response' => $suggestions
            ]);
            
            $formattedSuggestions = [];
            foreach ($suggestions as $suggestion) {
                $formattedSuggestions[] = [
                    'value' => $suggestion['value'],
                    'unrestricted_value' => $suggestion['unrestricted_value'],
                    'data' => $suggestion['data']
                ];
            }
            
            \Log::info('[DaData] Отформатированные подсказки', [
                'formatted_count' => count($formattedSuggestions)
            ]);
            
            return response()->json($formattedSuggestions);
        } catch (\Exception $e) {
            \Log::error('[DaData] Ошибка при получении подсказок', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Ошибка получения подсказок: ' . $e->getMessage()], 500);
        }
    }
    
    public function testConfig()
    {
        return response()->json([
            'config' => [
                'token' => config('dadata.token') ? 'установлен (' . substr(config('dadata.token'), 0, 10) . '...)' : 'не установлен',
                'secret' => config('dadata.secret') ? 'установлен (' . substr(config('dadata.secret'), 0, 10) . '...)' : 'не установлен',
                'timeout' => config('dadata.timeout'),
            ],
            'env' => [
                'DADATA_TOKEN' => env('DADATA_TOKEN') ? 'установлен (' . substr(env('DADATA_TOKEN'), 0, 10) . '...)' : 'не установлен',
                'DADATA_SECRET' => env('DADATA_SECRET') ? 'установлен (' . substr(env('DADATA_SECRET'), 0, 10) . '...)' : 'не установлен',
                'DADATA_TIMEOUT' => env('DADATA_TIMEOUT'),
            ]
        ]);
    }
}
