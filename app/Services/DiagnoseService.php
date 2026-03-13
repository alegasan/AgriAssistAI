<?php

namespace App\Services;

use App\Models\Diagnosis;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DiagnoseService
{
    public function __construct(
        private readonly UploadQuotaService $uploadQuotaService,
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

        return Diagnosis::create([
            'user_id' => $user->id,
            'image_path' => $imagePath,
            'plant_name' => $plantName ?: ($aiResult['plant_name'] ?? null),
            'disease_name' => $aiResult['disease_name'] ?? null,
            'confidence_score' => $aiResult['confidence_score'] ?? null,
            'symptoms' => $aiResult['symptoms'] ?? null,
            'treatment' => $aiResult['treatment'] ?? null,
            'raw_ai_response' => $aiResult['raw_ai_response'] ?? null,
        ]);
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
        $apiKey = config('services.gemini.api_key');
        $configuredModel = (string) config('services.gemini.model', '');

        if (! $apiKey) {
            throw new RuntimeException('Missing GEMINI_API_KEY configuration.');
        }

        $base64Image = base64_encode($image->getContent());
        $mimeType = $image->getMimeType() ?: 'image/jpeg';

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
