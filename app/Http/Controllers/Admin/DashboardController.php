<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use App\Services\Admin\DashboardStatsService;

class DashboardController extends Controller
{
    public function index(DashboardStatsService $stats)
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats->getStats(),
        ]);
    }
}
