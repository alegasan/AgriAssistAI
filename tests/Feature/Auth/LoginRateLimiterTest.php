<?php

use App\Models\User;

it('throttles repeated failed login attempts', function () {
    $payload = [
        'login' => 'unknown-user',
        'password' => 'wrong-password',
    ];

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login.store'), $payload)
            ->assertRedirect();
    }

    $this->post(route('login.store'), $payload)
        ->assertRedirect()
        ->assertSessionHasErrors('login');
});

it('uses separate throttle buckets for different login identifiers', function () {
    User::factory()->create([
        'username' => 'rate-limit-user',
        'password' => 'correct-password',
    ]);

    $firstPayload = [
        'login' => 'rate-limit-user',
        'password' => 'wrong-password',
    ];

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login.store'), $firstPayload)
            ->assertRedirect();
    }

    $this->post(route('login.store'), $firstPayload)
        ->assertRedirect()
        ->assertSessionHasErrors('login');

    $this->post(route('login.store'), [
        'login' => 'another-user',
        'password' => 'wrong-password',
    ])->assertRedirect();
});

it('returns a clean json payload when throttled', function () {
    $payload = [
        'login' => 'unknown-user',
        'password' => 'wrong-password',
    ];

    foreach (range(1, 5) as $attempt) {
        $this->postJson(route('login.store'), $payload)
            ->assertRedirect();
    }

    $this->postJson(route('login.store'), $payload)
        ->assertStatus(429)
        ->assertJsonStructure(['message', 'retry_after']);
});
