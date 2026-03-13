<?php

use App\Models\Diagnosis;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

it('allows a client user to submit an image and stores diagnosis data', function () {
    Storage::fake('local');

    Http::fake([
        'https://generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => json_encode([
                                    'plant_name' => 'Tomato',
                                    'disease_name' => 'Powdery Mildew',
                                    'confidence_score' => 87.5,
                                    'symptoms' => 'White powdery spots on leaves',
                                    'treatment' => 'Apply fungicide and remove affected leaves',
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    config()->set('services.gemini.api_key', 'test-key');
    config()->set('services.diagnose_uploads.disk', 'local');
    config()->set('services.diagnose_uploads.max_user_storage_mb', 50);

    $user = User::factory()->create([
        'role' => 'user',
        'username' => 'diagnose-user',
    ]);

    $image = UploadedFile::fake()->image('leaf.jpg');

    $response = $this->actingAs($user)
        ->post(route('client.diagnose.store'), [
            'plant_name' => 'Tomato',
            'image' => $image,
        ]);

    $response->assertRedirect();

    $diagnosis = Diagnosis::query()->first();

    expect($diagnosis)->not->toBeNull();
    expect($diagnosis->user_id)->toBe($user->id);
    expect($diagnosis->plant_name)->toBe('Tomato');
    expect($diagnosis->disease_name)->toBe('Powdery Mildew');

    Storage::disk('local')->assertExists($diagnosis->image_path);

    $this->actingAs($user)
        ->get(route('client.diagnose.image', $diagnosis))
        ->assertSuccessful()
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});

it('validates diagnose submission input', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'username' => 'diagnose-validation',
    ]);

    $this->actingAs($user)
        ->post(route('client.diagnose.store'), [
            'plant_name' => 'Tomato',
        ])
        ->assertSessionHasErrors('image');
});

it('rejects image uploads with non-whitelisted extensions', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'username' => 'diagnose-extension',
    ]);

    $image = UploadedFile::fake()->image('leaf.gif');

    $this->actingAs($user)
        ->post(route('client.diagnose.store'), [
            'plant_name' => 'Tomato',
            'image' => $image,
        ])
        ->assertSessionHasErrors('image');
});

it('rejects spoofed files that are not genuine images', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'username' => 'diagnose-spoof',
    ]);

    $spoofedImage = UploadedFile::fake()->create('leaf.jpg', 32, 'image/jpeg');

    $this->actingAs($user)
        ->post(route('client.diagnose.store'), [
            'plant_name' => 'Tomato',
            'image' => $spoofedImage,
        ])
        ->assertSessionHasErrors('image');
});

it('redirects guests to login when posting diagnose request', function () {
    $this->post(route('client.diagnose.store'), [
        'plant_name' => 'Tomato',
    ])->assertRedirect(route('login'));
});

it('prevents admins from using client diagnose endpoint', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'username' => 'diagnose-admin',
    ]);

    $this->actingAs($admin)
        ->post(route('client.diagnose.store'), [
            'plant_name' => 'Tomato',
        ])
        ->assertRedirect(route('admin.dashboard'));
});

it('rate limits diagnose submissions', function () {
    Storage::fake('local');

    Http::fake([
        'https://generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => json_encode([
                                    'plant_name' => 'Tomato',
                                    'disease_name' => 'Powdery Mildew',
                                    'confidence_score' => 87.5,
                                    'symptoms' => 'White powdery spots on leaves',
                                    'treatment' => 'Apply fungicide and remove affected leaves',
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    config()->set('services.gemini.api_key', 'test-key');
    config()->set('services.diagnose_uploads.disk', 'local');
    config()->set('services.diagnose_uploads.max_user_storage_mb', 50);
    config()->set('services.diagnose_uploads.rate_limit_per_minute', 1);

    $user = User::factory()->create([
        'role' => 'user',
        'username' => 'diagnose-throttle',
    ]);

    $firstImage = UploadedFile::fake()->image('leaf-1.jpg');
    $secondImage = UploadedFile::fake()->image('leaf-2.jpg');

    $this->actingAs($user)
        ->post(route('client.diagnose.store'), [
            'plant_name' => 'Tomato',
            'image' => $firstImage,
        ])
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('client.diagnose.store'), [
            'plant_name' => 'Tomato',
            'image' => $secondImage,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('image');
});

it('enforces per-user storage quota before upload persistence', function () {
    Storage::fake('local');

    config()->set('services.gemini.api_key', 'test-key');
    config()->set('services.diagnose_uploads.disk', 'local');
    config()->set('services.diagnose_uploads.max_user_storage_mb', 0);

    $user = User::factory()->create([
        'role' => 'user',
        'username' => 'diagnose-quota',
    ]);

    $image = UploadedFile::fake()->image('leaf.jpg');

    $this->actingAs($user)
        ->post(route('client.diagnose.store'), [
            'plant_name' => 'Tomato',
            'image' => $image,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('image');

    expect(Diagnosis::query()->count())->toBe(0);
});

it('forbids accessing another users diagnosis image endpoint', function () {
    Storage::fake('local');
    config()->set('services.diagnose_uploads.disk', 'local');

    $owner = User::factory()->create([
        'role' => 'user',
        'username' => 'diagnose-owner',
    ]);

    $otherUser = User::factory()->create([
        'role' => 'user',
        'username' => 'diagnose-other',
    ]);

    $path = "diagnoses/{$owner->id}/leaf.jpg";
    Storage::disk('local')->put($path, 'fake-image-data');

    $diagnosis = Diagnosis::query()->create([
        'user_id' => $owner->id,
        'image_path' => $path,
        'plant_name' => 'Tomato',
        'disease_name' => 'Powdery Mildew',
        'confidence_score' => 90,
        'symptoms' => 'Leaf spots',
        'treatment' => 'Isolate the plant',
        'raw_ai_response' => [],
    ]);

    $this->actingAs($otherUser)
        ->get(route('client.diagnose.image', $diagnosis))
        ->assertForbidden();
});
