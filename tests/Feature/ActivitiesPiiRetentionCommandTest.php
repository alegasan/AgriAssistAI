<?php

use App\Models\Activity;
use Illuminate\Support\Facades\Artisan;

it('anonymizes activity ip_address and user_agent after retention period', function () {
    config()->set('activity.pii_retention_days', 30);
    config()->set('activity.pii_retention_action', 'anonymize');

    $old = Activity::query()->create([
        'action' => 'test',
        'occurred_at' => now()->subDays(31),
        'properties' => null,
        'ip_address' => '203.0.113.1',
        'user_agent' => 'Test UA',
    ]);

    $new = Activity::query()->create([
        'action' => 'test',
        'occurred_at' => now()->subDays(10),
        'properties' => null,
        'ip_address' => '203.0.113.2',
        'user_agent' => 'New UA',
    ]);

    Artisan::call('activities:apply-pii-retention');

    expect($old->fresh())
        ->ip_address->toBeNull()
        ->user_agent->toBeNull();

    expect($new->fresh())
        ->ip_address->toBe('203.0.113.2')
        ->user_agent->toBe('New UA');
});

it('deletes activity rows after retention period when configured', function () {
    config()->set('activity.pii_retention_days', 30);
    config()->set('activity.pii_retention_action', 'delete');

    $old = Activity::query()->create([
        'action' => 'test',
        'occurred_at' => now()->subDays(31),
        'properties' => null,
        'ip_address' => '203.0.113.1',
        'user_agent' => 'Test UA',
    ]);

    $new = Activity::query()->create([
        'action' => 'test',
        'occurred_at' => now()->subDays(10),
        'properties' => null,
        'ip_address' => '203.0.113.2',
        'user_agent' => 'New UA',
    ]);

    Artisan::call('activities:apply-pii-retention');

    expect(Activity::query()->whereKey($old->id)->exists())->toBeFalse();
    expect(Activity::query()->whereKey($new->id)->exists())->toBeTrue();
});
