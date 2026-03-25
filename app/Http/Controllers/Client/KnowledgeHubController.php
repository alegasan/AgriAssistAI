<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiseaseResource;
use App\Models\Disease;
use App\Services\DiseaseImageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KnowledgeHubController extends Controller
{
    private const PER_PAGE = 12;

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        $search = $filters['search'] ?? null;

        $diseases = Disease::query()
            ->forListing()
            ->when($search, fn ($query) => $query->search($search))
            ->orderByDesc('last_diagnosed_at')
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return Inertia::render('Client/KnowledgeHubTab/Index', [
            'diseases' => DiseaseResource::collection($diseases),
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function image(Disease $disease, DiseaseImageService $imageService): StreamedResponse
    {
        return $imageService->stream($disease);
    }
}
