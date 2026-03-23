<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiagnosisResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plant_name' => $this->plant_name,
            'disease_name' => $this->disease_name,
            'status' => $this->status,
            'confidence_score' => $this->confidence_score,
            'symptoms' => $this->symptoms,
            'treatment' => $this->treatment,
            'failure_reason' => $this->failure_reason,
            'submitted_at' => $this->created_at?->toDateTimeString(),
            'completed_at' => $this->completed_at?->toDateTimeString(),
            'image_url' => route('admin.diagnoses.image', $this->resource),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
        ];
    }
}
