<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientDashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Client/Dashboard');
    }
}
