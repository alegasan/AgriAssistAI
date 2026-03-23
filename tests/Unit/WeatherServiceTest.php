<?php

use App\Services\WeatherService;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('getCurrentWeather returns expected structure', function () {
    Http::fake([
        'api.openweathermap.org/*' => Http::response([
            'main' => ['temp' => 28, 'feels_like' => 30, 'humidity' => 45],
            'weather' => [['main' => 'Sunny', 'description' => 'clear sky', 'icon' => '01d']],
            'wind' => ['speed' => 12, 'deg' => 45],
        ], 200),
    ]);

    $user = User::factory()->create([
        'latitude' => 13.0827,
        'longitude' => 80.2707,
    ]);

    $service = new WeatherService();
    $weather = $service->getCurrentWeather($user);

    expect($weather)->toBeArray();
    expect($weather['temperature'])->toBe(28);
    expect($weather['condition'])->toBe('Sunny');
    expect($weather['humidity'])->toBe(45);
    expect($weather['wind_speed'])->toBe(12);
    expect($weather['icon'])->toBe('01d');
});
