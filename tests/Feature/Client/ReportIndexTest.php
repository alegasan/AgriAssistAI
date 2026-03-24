<?php

use App\Models\Diagnosis;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('paginates client reports and scopes records to completed diagnoses of the authenticated user', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'username' => 'reports-pagination-user',
    ]);

    foreach (range(1, 8) as $index) {
        Diagnosis::query()->create([
            'user_id' => $user->id,
            'image_path' => "diagnoses/{$user->id}/report-{$index}.jpg",
            'status' => Diagnosis::STATUS_COMPLETED,
            'plant_name' => 'Tomato',
            'disease_name' => "Disease {$index}",
            'confidence_score' => 82,
            'symptoms' => 'Leaf spots',
            'created_at' => now()->subMinutes($index),
            'updated_at' => now()->subMinutes($index),
        ]);
    }

    Diagnosis::query()->create([
        'user_id' => $user->id,
        'image_path' => "diagnoses/{$user->id}/pending.jpg",
        'status' => Diagnosis::STATUS_PENDING,
        'plant_name' => 'Tomato',
        'disease_name' => 'Pending Disease',
        'confidence_score' => 90,
        'symptoms' => 'Pending record',
    ]);

    $otherUser = User::factory()->create([
        'role' => 'user',
        'username' => 'reports-pagination-other',
    ]);

    Diagnosis::query()->create([
        'user_id' => $otherUser->id,
        'image_path' => "diagnoses/{$otherUser->id}/other.jpg",
        'status' => Diagnosis::STATUS_COMPLETED,
        'plant_name' => 'Corn',
        'disease_name' => 'Other User Disease',
        'confidence_score' => 90,
        'symptoms' => 'Other user record',
    ]);

    $this->actingAs($user)
        ->get(route('client.reports'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Client/ReportsTab/Index')
            ->where('filters.search', '')
            ->where('filters.risk', 'all')
            ->has('diagnoses.data', 8)
            ->where('diagnoses.total', 8)
            ->where('diagnoses.current_page', 1)
            ->where('diagnoses.last_page', 1)
            ->where('diagnoses.data.0.title', 'Disease 1'));
});

it('filters client reports by search term across disease, plant, and symptoms', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'username' => 'reports-search-user',
    ]);

    Diagnosis::query()->create([
        'user_id' => $user->id,
        'image_path' => "diagnoses/{$user->id}/powder.jpg",
        'status' => Diagnosis::STATUS_COMPLETED,
        'plant_name' => 'Tomato',
        'disease_name' => 'Powdery Mildew',
        'confidence_score' => 70,
        'symptoms' => 'White powder on leaves',
    ]);

    Diagnosis::query()->create([
        'user_id' => $user->id,
        'image_path' => "diagnoses/{$user->id}/symptom.jpg",
        'status' => Diagnosis::STATUS_COMPLETED,
        'plant_name' => 'Wheat',
        'disease_name' => 'Nutrient Deficiency',
        'confidence_score' => 65,
        'symptoms' => 'Powder-like residue near stem',
    ]);

    Diagnosis::query()->create([
        'user_id' => $user->id,
        'image_path' => "diagnoses/{$user->id}/other.jpg",
        'status' => Diagnosis::STATUS_COMPLETED,
        'plant_name' => 'Corn',
        'disease_name' => 'Leaf Rust',
        'confidence_score' => 55,
        'symptoms' => 'Orange pustules',
    ]);

    $this->actingAs($user)
        ->get(route('client.reports', ['search' => 'powder']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Client/ReportsTab/Index')
            ->where('filters.search', 'powder')
            ->where('diagnoses.total', 2)
            ->has('diagnoses.data', 2));
});

it('filters client reports by risk thresholds', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'username' => 'reports-risk-user',
    ]);

    Diagnosis::query()->create([
        'user_id' => $user->id,
        'image_path' => "diagnoses/{$user->id}/low.jpg",
        'status' => Diagnosis::STATUS_COMPLETED,
        'plant_name' => 'Tomato',
        'disease_name' => 'Low Risk Disease',
        'confidence_score' => 40,
        'symptoms' => 'Minor spots',
    ]);

    Diagnosis::query()->create([
        'user_id' => $user->id,
        'image_path' => "diagnoses/{$user->id}/medium.jpg",
        'status' => Diagnosis::STATUS_COMPLETED,
        'plant_name' => 'Tomato',
        'disease_name' => 'Medium Risk Disease',
        'confidence_score' => 70,
        'symptoms' => 'Moderate spread',
    ]);

    Diagnosis::query()->create([
        'user_id' => $user->id,
        'image_path' => "diagnoses/{$user->id}/high.jpg",
        'status' => Diagnosis::STATUS_COMPLETED,
        'plant_name' => 'Tomato',
        'disease_name' => 'High Risk Disease',
        'confidence_score' => 90,
        'symptoms' => 'Severe lesions',
    ]);

    $this->actingAs($user)
        ->get(route('client.reports', ['risk' => 'medium']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Client/ReportsTab/Index')
            ->where('filters.risk', 'medium')
            ->where('diagnoses.total', 1)
            ->where('diagnoses.data.0.title', 'Medium Risk Disease')
            ->where('diagnoses.data.0.risk', 'Medium'));
});
