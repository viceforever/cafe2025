<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DaDataService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AddressController extends Controller
{
    private $daDataService;

    public function __construct(DaDataService $daDataService)
    {
        $this->daDataService = $daDataService;
    }

    /**
     * Получить подсказки адресов
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

        $suggestions = $this->daDataService->suggestAddresses($query, $count);

        // Форматируем результаты для фронтенда
        $formattedSuggestions = array_map(function ($suggestion) {
            return [
                'value' => $this->daDataService->formatAddress($suggestion),
                'data' => $this->daDataService->getAddressDetails($suggestion),
                'unrestricted_value' => $suggestion['unrestricted_value'] ?? '',
            ];
        }, $suggestions);

        return response()->json([
            'success' => true,
            'suggestions' => $formattedSuggestions
        ]);
    }
}
