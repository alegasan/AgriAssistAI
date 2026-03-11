<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardStatsService
{
    public function getStats(): array
    {
        return Cache::remember('dashboard.stats', now()->addMinutes(5), function () {
            return [
                'farmers'   => $this->farmerStats(),
                // 'diseases'  => $this->diseaseStats(),
                // 'diagnoses' => $this->diagnosisStats(),
                // 'pending'   => $this->pendingStats(),
            ];
        });
    }

    private function farmerStats(): array
    {
        $data = User::where('role', 'farmer')
            ->selectRaw('COUNT(*) as total, SUM(DATE(created_at) = CURDATE()) as new_today')
            ->first();

        return ['total' => $data->total, 'new_today' => $data->new_today ?? 0];
    }

    // private function diseaseStats(): array
    // {
    //     $data = Disease::selectRaw('COUNT(*) as total, SUM(DATE(created_at) = CURDATE()) as new_today')
    //         ->first();

    //     return ['total' => $data->total, 'new_today' => $data->new_today ?? 0];
    // }

    // private function diagnosisStats(): array
    // {
    //     $data = Diagnosis::selectRaw('COUNT(*) as total, SUM(DATE(created_at) = CURDATE()) as new_today')
    //         ->first();

    //     return ['total' => $data->total, 'new_today' => $data->new_today ?? 0];
    // }

    // private function pendingStats(): array
    // {
    //     $data = Diagnosis::where('status', 'pending')
    //         ->selectRaw('COUNT(*) as total, SUM(DATE(created_at) = CURDATE()) as new_today')
    //         ->first();

    //     return ['total' => $data->total, 'new_today' => $data->new_today ?? 0];
    // }
}