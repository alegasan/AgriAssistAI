<?php

use App\Jobs\ProcessDiagnosisJob;
use App\Models\Diagnosis;
use App\Models\User;
use App\Services\DiagnoseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

it('allows a client user to submit an image and stores diagnosis data', function () {
    Storage::fake('local');
    Queue::fake();

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
    expect($diagnosis->status)->toBe(Diagnosis::STATUS_PENDING);
    expect($diagnosis->disease_name)->toBeNull();

    Queue::assertPushed(ProcessDiagnosisJob::class, function (ProcessDiagnosisJob $job) use ($diagnosis): bool {
        return $job->diagnosisId === $diagnosis->id;
    });

    Storage::disk('local')->assertExists($diagnosis->image_path);

    $this->actingAs($user)
        ->get(route('client.diagnose.image', $diagnosis))
        ->assertSuccessful()
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});

it('processes a queued diagnosis and stores ai result fields', function () {
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

    $user = User::factory()->create([
        'role' => 'user',
        'username' => 'diagnose-job-success',
    ]);

    $image = UploadedFile::fake()->image('leaf-job.jpg');
    $path = $image->store("diagnoses/{$user->id}", 'local');

    $diagnosis = Diagnosis::query()->create([
        'user_id' => $user->id,
        'image_path' => $path,
        'status' => Diagnosis::STATUS_PENDING,
        'plant_name' => 'Tomato',
    ]);

    (new ProcessDiagnosisJob($diagnosis->id))->handle(app(DiagnoseService::class));

    $diagnosis->refresh();

    expect($diagnosis->status)->toBe(Diagnosis::STATUS_COMPLETED);
    expect($diagnosis->disease_name)->toBe('Powdery Mildew');
    expect((float) $diagnosis->confidence_score)->toBe(87.5);
    expect($diagnosis->completed_at)->not->toBeNull();
});

it('marks diagnosis as failed when queued analysis throws', function () {
    Storage::fake('local');

    Http::fake([
        'https://generativelanguage.googleapis.com/*' => Http::response([], 500),
    ]);

    config()->set('services.gemini.api_key', 'test-key');
    config()->set('services.diagnose_uploads.disk', 'local');

    $user = User::factory()->create([
        'role' => 'user',
        'username' => 'diagnose-job-failure',
    ]);

    $image = UploadedFile::fake()->image('leaf-job-failed.jpg');
    $path = $image->store("diagnoses/{$user->id}", 'local');

    $diagnosis = Diagnosis::query()->create([
        'user_id' => $user->id,
        'image_path' => $path,
        'status' => Diagnosis::STATUS_PENDING,
        'plant_name' => 'Tomato',
    ]);

    expect(fn () => (new ProcessDiagnosisJob($diagnosis->id))->handle(app(DiagnoseService::class)))
        ->toThrow(\RuntimeException::class);

    $diagnosis->refresh();

    expect($diagnosis->status)->toBe(Diagnosis::STATUS_FAILED);
    expect($diagnosis->failure_reason)->not->toBeNull();
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

it('returns diagnosis status for the owner and forbids others', function () {
    $owner = User::factory()->create([
        'role' => 'user',
        'username' => 'diagnose-status-owner',
    ]);

    $otherUser = User::factory()->create([
        'role' => 'user',
        'username' => 'diagnose-status-other',
    ]);

    $diagnosis = Diagnosis::query()->create([
        'user_id' => $owner->id,
        'image_path' => "diagnoses/{$owner->id}/leaf-status.jpg",
        'status' => Diagnosis::STATUS_PROCESSING,
        'plant_name' => 'Tomato',
        'disease_name' => null,
        'confidence_score' => null,
        'symptoms' => null,
        'treatment' => null,
        'raw_ai_response' => null,
    ]);

    $this->actingAs($owner)
        ->get(route('client.diagnose.status', $diagnosis))
        ->assertSuccessful()
        ->assertJson([
            'id' => $diagnosis->id,
            'status' => Diagnosis::STATUS_PROCESSING,
            'plant_name' => 'Tomato',
        ]);

    $this->actingAs($otherUser)
        ->get(route('client.diagnose.status', $diagnosis))
        ->assertForbidden();
});
