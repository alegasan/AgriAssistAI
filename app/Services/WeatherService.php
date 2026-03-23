<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Carbon\Carbon;

class WeatherService
{
    public function getCurrentWeather(User $user): array
    {
        $cacheKey = "weather:user:{$user->id}:current";
        $ttl = config('services.weather.cache_ttl', 600);

        return Cache::remember($cacheKey, $ttl, function () use ($user) {
            $location = $this->getUserLocation($user);
            $apiKey = config('services.weather.api_key');

            if (empty($apiKey)) {
                throw new RuntimeException('Missing WEATHER_API_KEY');
            }

            $response = Http::timeout(10)
                ->get('https://api.openweathermap.org/data/2.5/weather', [
                    'lat' => $location['latitude'],
                    'lon' => $location['longitude'],
                    'appid' => $apiKey,
                    'units' => 'metric',
                ]);

            if ($response->failed()) {
                $status = $response->status();
                $body = $response->body();

                if ($status === 401) {
                    Log::warning('Weather provider current request unauthorized', [
                        'status' => $status,
                        'body' => $body,
                    ]);
                    throw new RuntimeException('Invalid WEATHER_API_KEY');
                }

                Log::warning('Weather provider current request failed', [
                    'status' => $status,
                    'body' => $body,
                ]);

                throw new RuntimeException('Weather provider request failed');
            }

            $json = $response->json();

            return [
                'temperature' => isset($json['main']['temp']) ? (int) round($json['main']['temp']) : null,
                'feels_like' => $json['main']['feels_like'] ?? null,
                'condition' => $json['weather'][0]['main'] ?? null,
                'description' => $json['weather'][0]['description'] ?? null,
                'humidity' => $json['main']['humidity'] ?? null,
                'wind_speed' => $json['wind']['speed'] ?? null,
                'wind_direction' => $json['wind']['deg'] ?? null,
                'uv_index' => null,
                'icon' => $json['weather'][0]['icon'] ?? null,
                'location' => [
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                ],
                'last_updated' => now()->toISOString(),
            ];
        });
    }

    public function getForecast(User $user, int $days = 5): array
    {
        $cacheKey = "weather:user:{$user->id}:forecast:{$days}";
        $ttl = config('services.weather.cache_ttl', 600);

        return Cache::remember($cacheKey, $ttl, function () use ($user, $days) {
            $location = $this->getUserLocation($user);
            $apiKey = config('services.weather.api_key');

            if (empty($apiKey)) {
                throw new RuntimeException('Missing WEATHER_API_KEY');
            }

            $response = Http::timeout(10)
                ->get('https://api.openweathermap.org/data/2.5/forecast', [
                    'lat' => $location['latitude'],
                    'lon' => $location['longitude'],
                    'appid' => $apiKey,
                    'units' => 'metric',
                ]);

            if ($response->failed()) {
                $status = $response->status();
                $body = $response->body();

                if ($status === 401) {
                    Log::warning('Weather provider forecast request unauthorized', [
                        'status' => $status,
                        'body' => $body,
                    ]);

                    throw new RuntimeException(
                        "Invalid WEATHER_API_KEY (status: {$status}, body: {$body})"
                    );
                }

                Log::warning('Weather provider forecast request failed', [
                    'status' => $status,
                    'body' => $body,
                ]);

                throw new RuntimeException(
                    "Weather provider forecast request failed (status: {$status}, body: {$body})"
                );
            }

            $json = $response->json();

            $list = $json['list'] ?? [];

            $dailyBuckets = [];

            foreach ($list as $entry) {
                if (!isset($entry['dt'])) {
                    continue;
                }

                $date = Carbon::createFromTimestamp($entry['dt'])->toDateString();

                if (!isset($dailyBuckets[$date])) {
                    $dailyBuckets[$date] = [
                        'temp_min' => $entry['main']['temp_min'] ?? null,
                        'temp_max' => $entry['main']['temp_max'] ?? null,
                        'humidity_sum' => $entry['main']['humidity'] ?? null,
                        'humidity_count' => isset($entry['main']['humidity']) ? 1 : 0,
                        'chance_of_rain' => $entry['pop'] ?? null,
                        'condition' => $entry['weather'][0]['main'] ?? null,
                        'description' => $entry['weather'][0]['description'] ?? null,
                        'icon' => $entry['weather'][0]['icon'] ?? null,
                        'wind_speed' => $entry['wind']['speed'] ?? null,
                    ];
                    continue;
                }

                $bucket = &$dailyBuckets[$date];

                if (isset($entry['main']['temp_min'])) {
                    $bucket['temp_min'] = $bucket['temp_min'] === null
                        ? $entry['main']['temp_min']
                        : min($bucket['temp_min'], $entry['main']['temp_min']);
                }

                if (isset($entry['main']['temp_max'])) {
                    $bucket['temp_max'] = $bucket['temp_max'] === null
                        ? $entry['main']['temp_max']
                        : max($bucket['temp_max'], $entry['main']['temp_max']);
                }

                if (isset($entry['main']['humidity'])) {
                    $bucket['humidity_sum'] = ($bucket['humidity_sum'] ?? 0) + $entry['main']['humidity'];
                    $bucket['humidity_count'] = ($bucket['humidity_count'] ?? 0) + 1;
                }

                if (isset($entry['pop'])) {
                    $bucket['chance_of_rain'] = $bucket['chance_of_rain'] === null
                        ? $entry['pop']
                        : max($bucket['chance_of_rain'], $entry['pop']);
                }

                if (isset($entry['wind']['speed'])) {
                    $bucket['wind_speed'] = $bucket['wind_speed'] === null
                        ? $entry['wind']['speed']
                        : max($bucket['wind_speed'], $entry['wind']['speed']);
                }

                unset($bucket);
            }

            $result = [];
            $dates = array_keys($dailyBuckets);
            $count = min($days, count($dates));

            for ($i = 0; $i < $count; $i++) {
                $date = $dates[$i];
                $bucket = $dailyBuckets[$date];
                $humidity = $bucket['humidity_count'] > 0
                    ? (int) round($bucket['humidity_sum'] / $bucket['humidity_count'])
                    : null;

                $result[] = [
                    'date' => $date,
                    'condition' => $bucket['condition'] ?? null,
                    'description' => $bucket['description'] ?? null,
                    'temp_max' => $bucket['temp_max'] ?? null,
                    'temp_min' => $bucket['temp_min'] ?? null,
                    'humidity' => $humidity,
                    'chance_of_rain' => $bucket['chance_of_rain'] ?? null,
                    'wind_speed' => $bucket['wind_speed'] ?? null,
                    'icon' => $bucket['icon'] ?? null,
                ];
            }

            return $result;
        });
    }

    private function getUserLocation(User $user): array
    {
        $lat = $user->latitude ?? config('services.weather.default_latitude');
        $lon = $user->longitude ?? config('services.weather.default_longitude');

        if ($lat === null || $lon === null) {
            // Fallback to a sensible default (0,0) if not set; callers should handle
            return ['latitude' => 0, 'longitude' => 0];
        }

        return ['latitude' => $lat, 'longitude' => $lon];
    }
}
