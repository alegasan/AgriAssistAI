<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
class KnowledgeHubController extends Controller
{
    public function index()
    {
        return Inertia::render('Client/KnowledgeHubTab/Index');
    }
}
