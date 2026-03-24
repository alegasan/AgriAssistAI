<?php

namespace App\Services\Admin;

use App\Models\User;
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
        $data = User::where(function ($query) {
            $query->whereNull('role')
                ->orWhere('role', '!=', 'admin');
        })
            ->selectRaw('COUNT(*) as total, SUM(DATE(created_at) = CURDATE()) as new_today')
            ->first();

        return ['total' => $data->total, 'new_today' => $data->new_today ?? 0];
    }

    private function diseaseStats(): array
    {
        return ['total' => null, 'new_today' => null];
    }

    private function diagnosisStats(): array
    {
        $data = Diagnosis::selectRaw('COUNT(*) as total, SUM(DATE(created_at) = CURDATE()) as new_today')
            ->first();

        return ['total' => $data->total, 'new_today' => $data->new_today ?? 0];
    }


}