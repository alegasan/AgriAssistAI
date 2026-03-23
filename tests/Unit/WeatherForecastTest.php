<?php

use App\Services\WeatherService;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('getForecast returns expected days and structure', function () {
    // Build a fake list array for 7 days
    $list = [];
    $now = time();
    for ($i = 0; $i < 7; $i++) {
        $list[] = [
            'dt' => $now + ($i * 86400),
            'main' => [
                'temp_min' => 15 + $i,
                'temp_max' => 25 + $i,
                'humidity' => 60,
            ],
            'pop' => 0.1 * $i,
            'wind' => ['speed' => 5 + $i],
            'weather' => [[ 'main' => 'Clouds', 'description' => 'broken clouds', 'icon' => '03d' ]],
        ];
    }

    Http::fake([
        'api.openweathermap.org/*' => Http::response([
            'list' => $list,
        ], 200),
    ]);

    $user = User::factory()->create([
        'latitude' => 13.0827,
        'longitude' => 80.2707,
    ]);

    $service = new WeatherService();
    $forecast = $service->getForecast($user, 7);

    expect($forecast)->toBeArray();
    expect(count($forecast))->toBe(7);

    foreach ($forecast as $day) {
        expect($day)->toHaveKeys(['date','condition','description','temp_max','temp_min','humidity','chance_of_rain','wind_speed','icon']);
    }
});
