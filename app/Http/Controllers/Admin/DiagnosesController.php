<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Diagnosis;
use App\Http\Resources\DiagnosisResource;
use App\Services\DiagnosisImageService;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DiagnosesController extends Controller
{
    private const PER_PAGE = 12;

    public function index(): Response
    {
        $diagnoses = Diagnosis::query()
            ->forListing()
            ->with(['user:id,name,email'])
            ->orderByDesc('created_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return Inertia::render('Admin/Diagnoses/Index', [
            'diagnoses' => DiagnosisResource::collection($diagnoses),
        ]);
    }

    public function image(Diagnosis $diagnosis, DiagnosisImageService $imageService): StreamedResponse
    {
        return $imageService->stream($diagnosis);
    }
}
