<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Diagnosis extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'image_path',
        'status',
        'plant_name',
        'disease_name',
        'confidence_score',
        'symptoms',
        'treatment',
        'failure_reason',
        'attempted_at',
        'completed_at',
        'raw_ai_response',
    ];

    protected function casts(): array
    {
        return [
            'raw_ai_response' => 'array',
            'confidence_score' => 'decimal:2',
            'attempted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function scopeForListing(Builder $query): void
    {
        $query->select([
            'id', 'user_id', 'plant_name', 'disease_name', 'status',
            'confidence_score', 'symptoms', 'treatment',
            'failure_reason', 'created_at', 'completed_at',
        ]);
    }

    public function scopeRecentForUser(Builder $query, int $userId, int $limit = 4): void
    {
        $query->forListing()
            ->where('user_id', $userId)
            ->latest('created_at')
            ->limit($limit);
    }

    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeSearch(Builder $query, string $term): void
    {
        $escaped = addcslashes($term, "%_\\");
        $pattern = "%{$escaped}%";

        $query->where(function (Builder $innerQuery) use ($pattern) {
            $innerQuery->whereRaw("disease_name like ? escape '\\\\'", [$pattern])
                ->orWhereRaw("plant_name like ? escape '\\\\'", [$pattern])
                ->orWhereRaw("symptoms like ? escape '\\\\'", [$pattern]);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
