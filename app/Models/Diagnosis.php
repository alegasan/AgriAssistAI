<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diagnosis extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image_path',
        'plant_name',
        'disease_name',
        'confidence_score',
        'symptoms',
        'treatment',
        'raw_ai_response',
    ];

    protected function casts(): array
    {
        return [
            'raw_ai_response' => 'array',
            'confidence_score' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
