<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Diagnosis;
use Inertia\Inertia;
use App\Http\Resources\DiagnosisSummaryResource;
use Inertia\Response;
use Illuminate\Http\Request;

class ClientDashboardController extends Controller
{
       public function index(Request $request): Response
    {
        $diagnoses = Diagnosis::query()
            ->recentForUser($request->user()->id)
            ->get();

        return Inertia::render('Client/Dashboard', [
            'recentDiagnoses' => DiagnosisSummaryResource::collection($diagnoses)->resolve(),
        ]);
    }
}
