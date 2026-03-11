<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DiseasesController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Diseases/Index');
    }
}
