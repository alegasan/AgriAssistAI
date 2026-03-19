<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DiagnosesController;
use App\Http\Controllers\Admin\DiseasesController;

use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Client\DiagnoseController;
use App\Http\Controllers\Client\ReportController;
use App\Http\Controllers\Client\SupportController;
use App\Http\Controllers\Client\KnowledgeHubController;
use App\Http\Controllers\Client\ProfileController;


Route::inertia('/', 'Welcome')->name('home');


    Route::get('/login', [LoginController::class, 'showLoginForm'])
        ->name('login');
    Route::post('/login', [LoginController::class, 'login'])
        ->name('login.store')
        ->middleware('throttle:login');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])
        ->name('register');
    Route::post('/register', [RegisterController::class, 'register'])
        ->name('register.store')
        ->middleware('throttle:register');

    Route::get('/auth/google/redirect', [SocialAuthController::class, 'redirectToGoogle'])
        ->name('auth.google.redirect');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])
        ->name('auth.google.callback');


Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    //admin routes
    Route::middleware('admin')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('admin.dashboard');
        Route::get('/admin/users', [UserController::class, 'index'])
            ->name('admin.users.index');
        Route::get('/admin/diagnoses', [DiagnosesController::class, 'index'])
            ->name('admin.diagnoses.index');
        Route::get('/admin/diseases', [DiseasesController::class, 'index'])
            ->name('admin.diseases.index');
    });

    //client routes
    Route::middleware('client')->group(function () {
        Route::get('/client/dashboard', [ClientDashboardController::class, 'index'])
            ->name('client.dashboard');
        Route::get('/client/diagnose', [DiagnoseController::class, 'index'])
            ->name('client.diagnose');
        Route::post('/client/diagnose', [DiagnoseController::class, 'store'])
            ->middleware('throttle:diagnose-upload')
            ->name('client.diagnose.store');
        Route::get('/client/diagnose/{diagnosis}/status', [DiagnoseController::class, 'status'])
            ->name('client.diagnose.status');
        Route::get('/client/diagnose/{diagnosis}/image', [DiagnoseController::class, 'image'])
            ->name('client.diagnose.image');
        Route::get('/client/reports', [ReportController::class, 'index'])
            ->name('client.reports');
        Route::get('/client/support', [SupportController::class, 'index'])
            ->name('client.support');
        Route::get('/client/knowledgehub', [KnowledgeHubController::class, 'index'])
            ->name('client.knowledgehub');
        Route::get('/client/profile/{user}', [ProfileController::class, 'show'])
            ->name('client.profile');
    });
});
