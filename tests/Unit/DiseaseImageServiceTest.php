<?php

use App\Models\Disease;
use App\Services\DiseaseImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('stream aborts when image path is empty', function () {
    $disease = Disease::create([
        'name' => 'Leaf Spot',
        'normalized_name' => 'leaf-spot',
        'image_path' => null,
    ]);

    $service = new DiseaseImageService();

    $this->expectException(NotFoundHttpException::class);
    $service->stream($disease);
});

test('stream aborts when image is missing from storage', function () {
    config()->set('services.diagnose_uploads.disk', 'diagnose');
    Storage::fake('diagnose');

    $disease = Disease::create([
        'name' => 'Leaf Spot',
        'normalized_name' => 'leaf-spot',
        'image_path' => 'images/missing.png',
    ]);

    $service = new DiseaseImageService();

    $this->expectException(NotFoundHttpException::class);
    $service->stream($disease);
});

test('stream returns a response with headers', function () {
    config()->set('services.diagnose_uploads.disk', 'diagnose');
    Storage::fake('diagnose');

    $path = 'images/leaf @1.png';
    Storage::disk('diagnose')->put($path, 'image-bytes');

    $disease = Disease::create([
        'name' => 'Leaf Spot',
        'normalized_name' => 'leaf-spot',
        'image_path' => $path,
    ]);

    $service = new DiseaseImageService();
    $response = $service->stream($disease);

    $storage = Storage::disk('diagnose');
    $expectedMimeType = $storage->mimeType($path) ?: 'application/octet-stream';

    expect($response)->toBeInstanceOf(Symfony\Component\HttpFoundation\StreamedResponse::class);
    expect($response->headers->get('Content-Type'))->toBe($expectedMimeType);
    $cacheControl = (string) $response->headers->get('Cache-Control');
    expect($cacheControl)->toContain('private');
    expect($cacheControl)->toContain('max-age=300');
    expect($response->headers->get('X-Content-Type-Options'))->toBe('nosniff');
    expect($response->headers->get('Content-Disposition'))
        ->toContain('leaf__1.png');
});
