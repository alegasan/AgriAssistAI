<?php

use App\Models\Disease;
use App\Services\Admin\DashboardStatsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('disease stats returns total and new_today', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-25 10:00:00'));

    $pdo = DB::connection()->getPdo();
    if (method_exists($pdo, 'sqliteCreateFunction')) {
        $pdo->sqliteCreateFunction('CURDATE', fn () => Carbon::now()->toDateString());
    }

    Disease::forceCreate([
        'name' => 'Leaf Spot',
        'normalized_name' => 'leaf-spot',
        'created_at' => Carbon::now()->subDay(),
        'updated_at' => Carbon::now()->subDay(),
    ]);

    Disease::forceCreate([
        'name' => 'Rust',
        'normalized_name' => 'rust',
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);

    Disease::forceCreate([
        'name' => 'Blight',
        'normalized_name' => 'blight',
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);

    Cache::flush();

    $service = new DashboardStatsService();
    $stats = $service->getStats();

    expect($stats['diseases']['total'])->toBe(3);
    expect((int) $stats['diseases']['new_today'])->toBe(2);

    Carbon::setTestNow();
});
