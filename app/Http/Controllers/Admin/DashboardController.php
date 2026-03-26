<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Services\Admin\DashboardStatsService;
use App\Services\Admin\DashboardActivityService;

class DashboardController extends Controller
{
    public function index(DashboardStatsService $stats, DashboardActivityService $activity)
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats->getStats(),
            'recentActivity' => $activity->recent(10),
        ]);
    }
}
