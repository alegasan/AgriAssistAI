<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Disease;
use App\Models\Diagnosis;
use Illuminate\Support\Facades\Cache;

class DashboardStatsService
{
    public function getStats(): array
    {
        return Cache::remember('dashboard.stats.v2', now()->addMinute(), function () {
            return [
                'farmers'   => $this->farmerStats(),
                'diseases'  => $this->diseaseStats(),
                'diagnoses' => $this->diagnosisStats(),
            ];
        });
    }

    private function farmerStats(): array
    {
        $query = User::query()->where(function ($query) {
            $query->whereNull('role')
                ->orWhere('role', '!=', 'admin');
        });

        $total = (clone $query)->count();
        $newToday = (clone $query)->whereDate('created_at', today())->count();

        return ['total' => $total, 'new_today' => $newToday];
    }

    private function diseaseStats(): array
    {
        $total = Disease::query()->count();
        $newToday = Disease::query()->whereDate('created_at', today())->count();

        return ['total' => $total, 'new_today' => $newToday];
    }

    private function diagnosisStats(): array
    {
        $total = Diagnosis::query()->count();
        $newToday = Diagnosis::query()->whereDate('created_at', today())->count();

        return ['total' => $total, 'new_today' => $newToday];
    }


}