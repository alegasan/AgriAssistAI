<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

test('authenticated user can fetch current weather', function () {
    Http::fake([
        'api.openweathermap.org/*' => Http::response([
            'main' => ['temp' => 22, 'feels_like' => 23, 'humidity' => 55],
            'weather' => [['main' => 'Clouds', 'description' => 'broken clouds', 'icon' => '03d']],
            'wind' => ['speed' => 8, 'deg' => 90],
        ], 200),
    ]);

    $user = User::factory()->create([
        'latitude' => 13.0827,
        'longitude' => 80.2707,
    ]);

    $response = $this->actingAs($user)->get('/client/weather/current');

    $response->assertStatus(200);
    $response->assertJsonStructure(['success', 'data' => ['temperature', 'condition', 'humidity', 'wind_speed', 'icon']]);
    $this->assertTrue($response->json('success'));
});

test('unauthenticated user receives redirect to login', function () {
    $response = $this->get('/client/weather/current');
    $response->assertStatus(302);
});
