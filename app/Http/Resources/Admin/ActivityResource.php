<?php

namespace App\Http\Resources\Admin;

use App\Enums\ActivityAction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $action
 * @property \Carbon\CarbonInterface|null $occurred_at
 * @property array|null $properties
 */
class ActivityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $action = ActivityAction::tryFrom((string) $this->action);
        $properties = is_array($this->properties) ? $this->properties : [];

        return [
            'id' => $this->id,
            'action' => $this->action,
            'message' => $this->messageFor($action, $properties),
            'icon_key' => $this->iconKeyFor($action),
            'occurred_at' => $this->occurred_at?->toISOString(),
            'occurred_at_human' => $this->occurred_at?->diffForHumans(),
        ];
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function messageFor(?ActivityAction $action, array $properties): string
    {
        return match ($action) {
            ActivityAction::FarmerRegistered => 'New farmer registered: '.($properties['name'] ?? 'Unknown'),
            ActivityAction::DiagnosisSubmitted => $this->diagnosisMessage('Diagnosis submitted', $properties),
            ActivityAction::DiagnosisCompleted => $this->diagnosisMessage('Diagnosis completed', $properties),
            ActivityAction::DiagnosisFailed => $this->diagnosisMessage('Diagnosis failed', $properties),
            ActivityAction::AdminUserStatusToggled => $this->adminToggleMessage($properties),
            default => 'Activity recorded',
        };
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function diagnosisMessage(string $prefix, array $properties): string
    {
        $plant = trim((string) ($properties['plant_name'] ?? ''));
        $disease = trim((string) ($properties['disease_name'] ?? ''));
        $userName = trim((string) ($properties['user_name'] ?? ''));

        if ($plant !== '' && $disease !== '') {
            return $prefix." for {$plant} - {$disease}";
        }

        if ($plant !== '' || $disease !== '') {
            return $prefix.' for '.($plant !== '' ? $plant : $disease);
        }

        if ($userName !== '') {
            return $prefix." by {$userName}";
        }

        return $prefix;
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function adminToggleMessage(array $properties): string
    {
        $name = (string) ($properties['target_user_name'] ?? 'Unknown user');
        $isActive = $properties['is_active'] ?? null;

        if ($isActive === null) {
            return "User status changed: {$name}";
        }

        return "User status changed: {$name} is now ".($isActive ? 'active' : 'inactive');
    }

    private function iconKeyFor(?ActivityAction $action): string
    {
        return match ($action) {
            ActivityAction::FarmerRegistered => 'users',
            ActivityAction::DiagnosisSubmitted,
            ActivityAction::DiagnosisCompleted,
            ActivityAction::DiagnosisFailed => 'clipboard',
            ActivityAction::AdminUserStatusToggled => 'user-cog',
            default => 'clock',
        };
    }
}
