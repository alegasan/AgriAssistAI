<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiseaseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image_url' => $this->image_path ? route('client.knowledgehub.image', $this->resource) : null,
            'symptoms' => $this->symptoms,
            'treatment' => $this->treatment,
            'total_diagnoses' => $this->total_diagnoses,
            'last_diagnosed_at' => $this->last_diagnosed_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
