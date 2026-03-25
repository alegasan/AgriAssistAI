<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disease extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'normalized_name',
        'image_path',
        'symptoms',
        'treatment',
        'total_diagnoses',
        'last_diagnosed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_diagnoses' => 'integer',
            'last_diagnosed_at' => 'datetime',
        ];
    }

    public function scopeForListing(Builder $query): void
    {
        $query->select([
            'id',
            'name',
            'normalized_name',
            'image_path',
            'symptoms',
            'treatment',
            'total_diagnoses',
            'last_diagnosed_at',
            'updated_at',
        ]);
    }

    public function scopeSearch(Builder $query, string $term): void
    {
        $escapeChar = '!';
        $escaped = str_replace($escapeChar, $escapeChar . $escapeChar, $term);
        $escaped = str_replace('%', $escapeChar . '%', $escaped);
        $escaped = str_replace('_', $escapeChar . '_', $escaped);
        $pattern = "%{$escaped}%";

        $query->where(function (Builder $innerQuery) use ($pattern, $escapeChar) {
            $innerQuery->whereRaw("name like ? escape '{$escapeChar}'", [$pattern])
                ->orWhereRaw("symptoms like ? escape '{$escapeChar}'", [$pattern])
                ->orWhereRaw("treatment like ? escape '{$escapeChar}'", [$pattern]);
        });
    }
}
