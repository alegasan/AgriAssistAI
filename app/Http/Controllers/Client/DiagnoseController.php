<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiagnoseRequest;
use App\Jobs\ProcessDiagnosisJob;
use App\Models\Diagnosis;
use App\Services\DiagnoseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DiagnoseController extends Controller
{
    public function index()
    {
        return Inertia::render('Client/DiagnoseTab/Index');
    }

    public function store(DiagnoseRequest $request, DiagnoseService $diagnoseService): RedirectResponse
    {
        try {
            $diagnosis = $diagnoseService->createPendingDiagnosis(
                user: $request->user(),
                image: $request->file('image'),
                plantName: $request->input('plant_name')
            );

            ProcessDiagnosisJob::dispatch($diagnosis->id);

            return back()->with('success', 'Image uploaded. Analysis is now running. Results will appear shortly.')->with('diagnosis', [
                'id' => $diagnosis->id,
                'status' => $diagnosis->status,
                'plant_name' => $diagnosis->plant_name,
                'disease_name' => $diagnosis->disease_name,
                'confidence_score' => $diagnosis->confidence_score,
                'symptoms' => $diagnosis->symptoms,
                'treatment' => $diagnosis->treatment,
                'failure_reason' => $diagnosis->failure_reason,
                'image_url' => route('client.diagnose.image', $diagnosis),
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'diagnosis' => 'Diagnosis failed. Please try again in a moment.',
            ])->withInput();
        }
    }

    public function image(Request $request, Diagnosis $diagnosis): StreamedResponse
    {
        abort_unless($diagnosis->user_id === $request->user()?->id, 403);

        $disk = (string) config('services.diagnose_uploads.disk', 'local');
        $storage = Storage::disk($disk);

        abort_unless($storage->exists($diagnosis->image_path), 404);

        $filename = basename($diagnosis->image_path);
        $mimeType = $storage->mimeType($diagnosis->image_path) ?: 'application/octet-stream';

        return $storage->response($diagnosis->image_path, $filename, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function status(Request $request, Diagnosis $diagnosis): JsonResponse
    {
        abort_unless($diagnosis->user_id === $request->user()?->id, 403);

        return response()->json([
            'id' => $diagnosis->id,
            'status' => $diagnosis->status,
            'plant_name' => $diagnosis->plant_name,
            'disease_name' => $diagnosis->disease_name,
            'confidence_score' => $diagnosis->confidence_score,
            'symptoms' => $diagnosis->symptoms,
            'treatment' => $diagnosis->treatment,
            'failure_reason' => $diagnosis->failure_reason,
            'image_url' => route('client.diagnose.image', $diagnosis),
        ]);
    }
}
