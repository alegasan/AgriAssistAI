<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WeatherController extends Controller
{
    public function __construct(private WeatherService $weatherService)
    {
    }

    public function current(): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        try {
            $weather = $this->weatherService->getCurrentWeather($user);
            return response()->json(['success' => true, 'data' => $weather]);
        } catch (\Throwable $e) {
            Log::error('WeatherController::current error: ' . $e->getMessage(), ['exception' => $e]);
            $message = config('app.debug') ? $e->getMessage() : 'Unable to fetch weather';
            return response()->json(['success' => false, 'message' => $message], 500);
        }
    }

    public function forecast(): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        try {
            $forecast = $this->weatherService->getForecast($user, 7);
            return response()->json(['success' => true, 'data' => $forecast]);
        } catch (\Throwable $e) {
            Log::error('WeatherController::forecast error: ' . $e->getMessage(), ['exception' => $e]);
            $message = config('app.debug') ? $e->getMessage() : 'Unable to fetch forecast';
            return response()->json(['success' => false, 'message' => $message], 500);
        }
    }
}
