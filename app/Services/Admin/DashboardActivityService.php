<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\ActivityResource;
use App\Models\Activity;

class DashboardActivityService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function recent(int $limit = 10): array
    {
        $activities = Activity::query()->recent($limit)->get();

        return ActivityResource::collection($activities)->resolve(request());
    }
}
