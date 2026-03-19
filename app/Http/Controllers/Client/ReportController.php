<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Diagnosis;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    private const PER_PAGE = 6;

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $risk = $this->normalizeRisk((string) $request->query('risk', 'all'));

        $diagnoses = Diagnosis::query()
            ->where('user_id', auth()->id())
            ->where('status', Diagnosis::STATUS_COMPLETED)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('disease_name', 'like', "%{$search}%")
                        ->orWhere('plant_name', 'like', "%{$search}%")
                        ->orWhere('symptoms', 'like', "%{$search}%");
                });
            })
            ->when($risk !== 'all', function ($query) use ($risk) {
                $this->applyRiskFilter($query, $risk);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(fn (Diagnosis $diagnosis) => [
                'id' => $diagnosis->id,
                'title' => $diagnosis->disease_name ?: 'Unknown diagnosis',
                'crop' => $diagnosis->plant_name ?: 'Unknown plant',
                'submitted_ago' => $diagnosis->created_at?->diffForHumans() ?? 'Unknown date',
                'created_at' => $diagnosis->created_at?->toIso8601String(),
                'risk' => $this->riskFromConfidence($diagnosis->confidence_score),
                'notes' => $diagnosis->symptoms ?: 'No symptom details were captured for this report.',
                'image_url' => route('client.diagnose.image', $diagnosis),
            ]);

        return Inertia::render('Client/ReportsTab/Index', [
            'diagnoses' => $diagnoses,
            'filters' => [
                'search' => $search,
                'risk' => $risk,
            ],
        ]);
    }

    private function normalizeRisk(string $risk): string
    {
        $normalizedRisk = strtolower(trim($risk));

        if (in_array($normalizedRisk, ['low', 'medium', 'high'], true)) {
            return $normalizedRisk;
        }

        return 'all';
    }

    private function riskFromConfidence(null|string|float $confidence): string
    {
        $score = (float) ($confidence ?? 0);

        if ($score >= 80) {
            return 'High';
        }

        if ($score >= 50) {
            return 'Medium';
        }

        return 'Low';
    }

    private function applyRiskFilter(Builder $query, string $risk): void
    {
        if ($risk === 'high') {
            $query->where('confidence_score', '>=', 80);

            return;
        }

        if ($risk === 'medium') {
            $query->where('confidence_score', '>=', 50)
                ->where('confidence_score', '<', 80);

            return;
        }

        if ($risk === 'low') {
            $query->where('confidence_score', '<', 50);
        }
    }


    public function destroy(Diagnosis $diagnosis)
    {
        
       
        if (! empty($diagnosis->image_path)) {
            $disk = (string) config('services.diagnose_uploads.disk', 'local');
            Storage::disk($disk)->delete($diagnosis->image_path);
        }

        $diagnosis->delete();

        return redirect()->route('client.reports')->with('success', 'Report deleted successfully.');
    }

}
