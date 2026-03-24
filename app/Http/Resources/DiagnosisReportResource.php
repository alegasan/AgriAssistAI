<?php

namespace App\Http\Resources;

use App\Enums\DiagnosisRisk;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiagnosisReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->disease_name ?: 'Unknown diagnosis',
            'crop' => $this->plant_name ?: 'Unknown plant',
            'submitted_ago' => $this->created_at?->diffForHumans() ?? 'Unknown date',
            'created_at' => $this->created_at?->toIso8601String(),
            'risk' => $this->confidence_score !== null
                ? DiagnosisRisk::fromConfidence($this->confidence_score)->label()
                : 'Unknown',
            'notes' => $this->symptoms ?: 'No symptom details were captured for this report.',
            'treatment' => $this->treatment ?: 'No treatment details were captured for this report.',
            'image_url' => route('client.diagnose.image', $this->resource),
        ];
    }
}
