<?php

namespace App\Services;

use App\Models\Diagnosis;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DiagnosisImageService
{
    public function stream(Diagnosis $diagnosis): StreamedResponse
    {
        $disk = (string) config('services.diagnose_uploads.disk', 'local');
        $storage = Storage::disk($disk);

        if (empty($diagnosis->image_path)) {
            abort(404);
        }

        abort_unless($storage->exists($diagnosis->image_path), 404);

        $filename = preg_replace('/[^\w\.\-]/', '_', basename($diagnosis->image_path));
        $mimeType = $storage->mimeType($diagnosis->image_path) ?: 'application/octet-stream';

        return $storage->response($diagnosis->image_path, $filename, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function delete(Diagnosis $diagnosis): void
    {
        if (empty($diagnosis->image_path)) {
            return;
        }

        $disk = (string) config('services.diagnose_uploads.disk', 'local');
        Storage::disk($disk)->delete($diagnosis->image_path);
    }
}
