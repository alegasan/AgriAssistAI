<?php
namespace App\Http\Resources;

use App\Models\Diagnosis;
use Illuminate\Http\Resources\Json\JsonResource;

class DiagnosisSummaryResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Diagnosis $this */
        $labelParts = array_filter([
            $this->plant_name,
            $this->disease_name,
        ]);

        return [
            'id'               => $this->id,
            'title'            => $labelParts
                ? implode(' - ', $labelParts)
                : 'Diagnosis submitted',
            'status'           => $this->status,
            'submitted_at'     => $this->created_at?->diffForHumans(),
            'confidence_score' => $this->confidence_score !== null
                ? (float) $this->confidence_score
                : null,
            'confidence_label' => $this->confidence_score !== null
                ? number_format((float) $this->confidence_score, 1) . '%'
                : null,
        ];
    }
}