<?php

namespace App\Enums;

use Illuminate\Database\Eloquent\Builder;

enum DiagnosisRisk: string
{
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';

    public static function fromConfidence(float|string|null $confidence): self
    {
        $score = (float) ($confidence ?? 0);

        if ($score >= 80) {
            return self::High;
        }

        if ($score >= 50) {
            return self::Medium;
        }

        return self::Low;
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function applyToQuery(Builder $query): void
    {
        if ($this === self::High) {
            $query->where('confidence_score', '>=', 80);

            return;
        }

        if ($this === self::Medium) {
            $query->where('confidence_score', '>=', 50)
                ->where('confidence_score', '<', 80);

            return;
        }

        $query->where('confidence_score', '<', 50);
    }
}
