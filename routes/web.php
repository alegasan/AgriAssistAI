<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
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


Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('admin')
        ->name('admin.dashboard');
    Route::get('/client/dashboard', [ClientDashboardController::class, 'index'])
        ->middleware('client')
        ->name('client.dashboard');
    Route::get('/client/diagnose', [DiagnoseController::class, 'index'])
        ->middleware('client')
        ->name('client.diagnose');
    Route::get('/client/reports', [ReportController::class, 'index'])
        ->middleware('client')
        ->name('client.reports');
    Route::get('/client/support', [SupportController::class, 'index'])
        ->middleware('client')
        ->name('client.support');
    Route::get('/client/knowledgehub', [KnowledgeHubController::class, 'index'])
        ->middleware('client')
        ->name('client.knowledgehub');

    Route::get('/client/profile/{user}', [ProfileController::class, 'show'])
        ->middleware('client')
        ->name('client.profile');
});
