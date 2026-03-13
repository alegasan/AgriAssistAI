<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UploadQuotaService
{
    public function ensureWithinDiagnoseQuota(User $user, UploadedFile $file): void
    {
        $disk = (string) config('services.diagnose_uploads.disk', 'local');
        $maxMb = (int) config('services.diagnose_uploads.max_user_storage_mb', 50);
        $maxBytes = max(0, $maxMb) * 1024 * 1024;

        if ($maxBytes === 0) {
            throw ValidationException::withMessages([
                'image' => 'Upload quota exceeded. Please remove older uploads or contact support.',
            ]);
        }

        $directory = "diagnoses/{$user->id}";
        $usedBytes = $this->directorySizeInBytes($disk, $directory);
        $incomingBytes = (int) ($file->getSize() ?? 0);

        if (($usedBytes + $incomingBytes) > $maxBytes) {
            throw ValidationException::withMessages([
                'image' => 'Upload quota exceeded. Please remove older uploads or contact support.',
            ]);
        }
    }

    private function directorySizeInBytes(string $disk, string $directory): int
    {
        $storageDisk = Storage::disk($disk);
        $paths = $storageDisk->allFiles($directory);
        $bytes = 0;

        foreach ($paths as $path) {
            $bytes += (int) ($storageDisk->size($path) ?? 0);
        }

        return $bytes;
    }
}
