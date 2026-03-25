<?php

namespace App\Services;

use App\Models\Disease;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DiseaseImageService
{
    public function stream(Disease $disease): StreamedResponse
    {
        $disk = (string) config('services.diagnose_uploads.disk', 'local');
        $storage = Storage::disk($disk);

        if (empty($disease->image_path)) {
            abort(404);
        }

        abort_unless($storage->exists($disease->image_path), 404);

        $filename = preg_replace('/[^\w\.\-]/', '_', basename($disease->image_path));
        $mimeType = $storage->mimeType($disease->image_path) ?: 'application/octet-stream';

        return $storage->response($disease->image_path, $filename, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
