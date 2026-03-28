<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiagnosisReportResource;
use App\Models\Diagnosis;
use App\Enums\DiagnosisRisk;
use App\Services\DiagnosisImageService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {

        $paginationSize = 10;
        $search = trim((string) $request->query('search', ''));
        $risk = DiagnosisRisk::tryFrom(strtolower(trim((string) $request->query('risk', ''))));

        $diagnosesPaginator = Diagnosis::query()
            ->where('user_id', $request->user()->id)
            ->completed()
            ->when($search !== '', fn ($query) => $query->search($search))
            ->when($risk, fn ($query) => $risk->applyToQuery($query))
            ->orderByDesc('created_at')
            ->paginate($paginationSize)
            ->withQueryString();

        $diagnoses = [
            'data' => $diagnosesPaginator->getCollection()
                ->map(fn ($diagnosis) => (new DiagnosisReportResource($diagnosis))->toArray($request))
                ->values()
                ->all(),
            'current_page' => $diagnosesPaginator->currentPage(),
            'last_page' => $diagnosesPaginator->lastPage(),
            'total' => $diagnosesPaginator->total(),
            'from' => $diagnosesPaginator->firstItem(),
            'to' => $diagnosesPaginator->lastItem(),
            'links' => $diagnosesPaginator->linkCollection()->toArray(),
        ];

        return Inertia::render('Client/ReportsTab/Index', [
            'diagnoses' => $diagnoses,
            'filters' => [
                'search' => $search,
                'risk' => $risk?->value ?? 'all',
            ],
        ]);
    }

    public function destroy(Diagnosis $diagnosis, DiagnosisImageService $imageService): RedirectResponse
    {
        $imageService->delete($diagnosis);
        $diagnosis->delete();

        return redirect()->route('client.reports')->with('success', 'Report deleted successfully.');
    }
}
