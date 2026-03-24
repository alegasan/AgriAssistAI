<?php

use App\Models\Diagnosis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('scopeSearch escapes wildcard characters', function () {
    $user = User::factory()->create();

    $underscore = Diagnosis::create([
        'user_id' => $user->id,
        'image_path' => 'images/underscore.jpg',
        'disease_name' => 'blight_1',
    ]);

    Diagnosis::create([
        'user_id' => $user->id,
        'image_path' => 'images/underscore-other.jpg',
        'disease_name' => 'blightA1',
    ]);

    $percent = Diagnosis::create([
        'user_id' => $user->id,
        'image_path' => 'images/percent.jpg',
        'disease_name' => 'powdery%mix',
    ]);

    Diagnosis::create([
        'user_id' => $user->id,
        'image_path' => 'images/percent-other.jpg',
        'disease_name' => 'powderymix',
    ]);

    $escape = Diagnosis::create([
        'user_id' => $user->id,
        'image_path' => 'images/escape.jpg',
        'disease_name' => 'leaf!spot',
    ]);

    Diagnosis::create([
        'user_id' => $user->id,
        'image_path' => 'images/escape-other.jpg',
        'disease_name' => 'leafspot',
    ]);

    $underscoreResults = Diagnosis::query()->search('blight_1')->pluck('id');
    expect($underscoreResults)->toHaveCount(1);
    expect($underscoreResults->first())->toBe($underscore->id);

    $percentResults = Diagnosis::query()->search('powdery%mix')->pluck('id');
    expect($percentResults)->toHaveCount(1);
    expect($percentResults->first())->toBe($percent->id);

    $escapeResults = Diagnosis::query()->search('leaf!spot')->pluck('id');
    expect($escapeResults)->toHaveCount(1);
    expect($escapeResults->first())->toBe($escape->id);
});
