<?php

namespace App\Services;

use App\Enums\ActivityAction;
use App\Models\Diagnosis;
use App\Models\Disease;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use RuntimeException;

class DiagnoseService
{
    public function __construct(
        private readonly UploadQuotaService $uploadQuotaService,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    /**
     * Ordered model fallback list used when a request fails.
     *
     * @var array<int, string>
     */
    protected array $models = [
        'gemini-2.0-flash-lite',
        'gemini-2.0-flash',
        'gemini-2.5-flash',
        'gemini-pro-latest',
    ];

    public function diagnose(User $user, UploadedFile $image, ?string $plantName = null): Diagnosis
    {
        $this->uploadQuotaService->ensureWithinDiagnoseQuota($user, $image);

        $imagePath = $this->storeImage($user, $image);
        $aiResult = $this->requestAiDiagnosis($image, $plantName);

        $diagnosis = Diagnosis::create([
            'user_id' => $user->id,
            'image_path' => $imagePath,
            'status' => Diagnosis::STATUS_COMPLETED,
            'plant_name' => $plantName ?: ($aiResult['plant_name'] ?? null),
            'disease_name' => $aiResult['disease_name'] ?? null,
            'confidence_score' => $aiResult['confidence_score'] ?? null,
            'symptoms' => $aiResult['symptoms'] ?? null,
            'treatment' => $aiResult['treatment'] ?? null,
            'completed_at' => Carbon::now(),
            'raw_ai_response' => $aiResult['raw_ai_response'] ?? null,
        ]);

        $this->syncDiseaseCatalog($diagnosis);

        $this->activityLogger->log(
            action: ActivityAction::DiagnosisCompleted,
            properties: [
                'diagnosis_id' => $diagnosis->id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'plant_name' => $diagnosis->plant_name,
                'disease_name' => $diagnosis->disease_name,
                'confidence_score' => $diagnosis->confidence_score,
            ],
            subject: $diagnosis,
        );

        return $diagnosis;
    }

    public function createPendingDiagnosis(User $user, UploadedFile $image, ?string $plantName = null): Diagnosis
    {
        $this->uploadQuotaService->ensureWithinDiagnoseQuota($user, $image);

        $imagePath = $this->storeImage($user, $image);

        $diagnosis = Diagnosis::create([
            'user_id' => $user->id,
            'image_path' => $imagePath,
            'status' => Diagnosis::STATUS_PENDING,
            'plant_name' => $plantName,
        ]);

        $this->activityLogger->log(
            action: ActivityAction::DiagnosisSubmitted,
            properties: [
                'diagnosis_id' => $diagnosis->id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'plant_name' => $diagnosis->plant_name,
            ],
            subject: $diagnosis,
        );

        return $diagnosis;
    }

    public function completeDiagnosis(Diagnosis $diagnosis): Diagnosis
    {
        $disk = (string) config('services.diagnose_uploads.disk', 'local');
        $storage = Storage::disk($disk);

        if (! $storage->exists($diagnosis->image_path)) {
            throw new RuntimeException('Stored diagnosis image could not be found.');
        }

        $imageContent = $storage->get($diagnosis->image_path);

        if (! is_string($imageContent) || $imageContent === '') {
            throw new RuntimeException('Stored diagnosis image content is missing or unreadable.');
        }

        $mimeType = $storage->mimeType($diagnosis->image_path) ?: 'image/jpeg';

        $aiResult = $this->requestAiDiagnosisFromImageData(
            imageContent: $imageContent,
            mimeType: $mimeType,
            plantName: $diagnosis->plant_name,
        );

        $diagnosis->update([
            'plant_name' => $diagnosis->plant_name ?: ($aiResult['plant_name'] ?? null),
            'disease_name' => $aiResult['disease_name'] ?? null,
            'confidence_score' => $aiResult['confidence_score'] ?? null,
            'symptoms' => $aiResult['symptoms'] ?? null,
            'treatment' => $aiResult['treatment'] ?? null,
            'raw_ai_response' => $aiResult['raw_ai_response'] ?? null,
            'status' => Diagnosis::STATUS_COMPLETED,
            'failure_reason' => null,
            'completed_at' => Carbon::now(),
        ]);

        $diagnosis->refresh();
        $this->syncDiseaseCatalog($diagnosis);

        $this->activityLogger->log(
            action: ActivityAction::DiagnosisCompleted,
            properties: [
                'diagnosis_id' => $diagnosis->id,
                'user_id' => $diagnosis->user_id,
                'user_name' => $diagnosis->user?->name,
                'plant_name' => $diagnosis->plant_name,
                'disease_name' => $diagnosis->disease_name,
                'confidence_score' => $diagnosis->confidence_score,
            ],
            subject: $diagnosis,
        );

        return $diagnosis;
    }

    private function syncDiseaseCatalog(Diagnosis $diagnosis): void
    {
        if (! $diagnosis->disease_name) {
            return;
        }

        $normalizedName = $this->normalizeDiseaseName($diagnosis->disease_name);

        if ($normalizedName === '') {
            return;
        }

        $displayName = $this->normalizeDisplayName($diagnosis->disease_name);

        $updates = [
            'name' => $displayName,
            'last_diagnosed_at' => Carbon::now(),
        ];

        if ($diagnosis->symptoms) {
            $updates['symptoms'] = $diagnosis->symptoms;
        }

        if ($diagnosis->treatment) {
            $updates['treatment'] = $diagnosis->treatment;
        }

        $disease = Disease::query()->firstOrNew(['normalized_name' => $normalizedName]);

        if (! $disease->exists) {
            $disease->total_diagnoses = 0;
        }

        if ($diagnosis->image_path && empty($disease->image_path)) {
            $updates['image_path'] = $diagnosis->image_path;
        }

        $disease->fill($updates);
        $disease->save();
        $disease->increment('total_diagnoses');
    }

    private function normalizeDiseaseName(string $name): string
    {
        $collapsed = preg_replace('/\s+/', ' ', trim($name)) ?? trim($name);

        return mb_strtolower($collapsed);
    }

    private function normalizeDisplayName(string $name): string
    {
        return preg_replace('/\s+/', ' ', trim($name)) ?? trim($name);
    }

    private function storeImage(User $user, UploadedFile $image): string
    {
        $disk = (string) config('services.diagnose_uploads.disk', 'local');

        return $image->store("diagnoses/{$user->id}", $disk);
    }

    /**
     * @return array<string, mixed>
     */
    private function requestAiDiagnosis(UploadedFile $image, ?string $plantName = null): array
    {
        $mimeType = $image->getMimeType() ?: 'image/jpeg';

        return $this->requestAiDiagnosisFromImageData(
            imageContent: $image->getContent(),
            mimeType: $mimeType,
            plantName: $plantName,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function requestAiDiagnosisFromImageData(string $imageContent, string $mimeType, ?string $plantName = null): array
    {
        $apiKey = config('services.gemini.api_key');
        $configuredModel = (string) config('services.gemini.model', '');

        if (! $apiKey) {
            throw new RuntimeException('Missing GEMINI_API_KEY configuration.');
        }

        $base64Image = base64_encode($imageContent);

        $prompt = "You are a plant pathology assistant. Analyze the image and return only valid JSON with this exact shape: "
            . '{"plant_name":"string|null","disease_name":"string|null","confidence_score":0-100 number,"symptoms":"string|null","treatment":"string|null"}. '
            . 'If uncertain, use null values and lower confidence. '; 

        if ($plantName) {
            $prompt .= "User-provided plant name: {$plantName}. ";
        }

        $candidateModels = array_values(array_unique(array_filter([
            $configuredModel,
            ...$this->models,
        ])));

        $response = null;
        $lastException = null;

        foreach ($candidateModels as $model) {
            try {
                $response = Http::timeout(45)
                    ->withQueryParameters(['key' => $apiKey])
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                    [
                                        'inline_data' => [
                                            'mime_type' => $mimeType,
                                            'data' => $base64Image,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'responseMimeType' => 'application/json',
                        ],
                    ]);

                $response->throw();

                break;
            } catch (RequestException $exception) {
                $lastException = $exception;
                $status = $exception->response?->status();

                // Move to the next model only for recoverable/provider errors.
                if (! in_array($status, [404, 429, 500, 503], true)) {
                    throw $exception;
                }
            }
        }

        if (! $response) {
            throw new RuntimeException(
                'No compatible Gemini model was available for generateContent.',
                previous: $lastException
            );
        }

        $content = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException('AI response content is empty.');
        }

        $content = $this->stripCodeFence($content);

        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('AI response could not be parsed as JSON.');
        }

        return [
            'plant_name' => $decoded['plant_name'] ?? null,
            'disease_name' => $decoded['disease_name'] ?? null,
            'confidence_score' => isset($decoded['confidence_score'])
                ? max(0, min(100, (float) $decoded['confidence_score']))
                : null,
            'symptoms' => $decoded['symptoms'] ?? null,
            'treatment' => $decoded['treatment'] ?? null,
            'raw_ai_response' => $response->json(),
        ];
    }

    private function stripCodeFence(string $content): string
    {
        $trimmed = trim($content);

        if (str_starts_with($trimmed, '```') && str_ends_with($trimmed, '```')) {
            $trimmed = preg_replace('/^```(?:json)?\s*/', '', $trimmed) ?? $trimmed;
            $trimmed = preg_replace('/\s*```$/', '', $trimmed) ?? $trimmed;
        }

        return trim($trimmed);
    }
}
