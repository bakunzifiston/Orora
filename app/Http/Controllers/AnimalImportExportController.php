<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\AnimalImportConfirmRequest;
use App\Http\Requests\AnimalImportRequest;
use App\Services\Export\AnimalCsvExporter;
use App\Services\Import\AnimalCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnimalImportExportController extends Controller
{
    use ProvidesModuleNavigation;

    private const SESSION_KEY = 'animal_import';

    public function export(Request $request, AnimalCsvExporter $exporter): StreamedResponse
    {
        return $exporter->export($request);
    }

    public function template(AnimalCsvExporter $exporter): StreamedResponse
    {
        return $exporter->template();
    }

    public function create(Request $request): View
    {
        return view('modules.animals.import', $this->moduleViewData('animals') + [
            'pendingImport' => $request->session()->get(self::SESSION_KEY),
        ]);
    }

    public function store(AnimalImportRequest $request, AnimalCsvImporter $importer): RedirectResponse
    {
        $this->forgetPendingImport($request);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'csv');
        $token = (string) Str::uuid();
        $storedPath = $file->storeAs('animal-imports', $token.'.'.$extension, 'local');

        $preview = $importer->preview(
            $this->uploadedFileFromStorage($storedPath, $file->getClientOriginalName())
        );

        // File-level failures (bad headers, empty file, etc.) — nothing to confirm.
        if ($preview['total'] === 0 && $preview['failed_count'] > 0) {
            $this->deleteStoredImport($storedPath);

            return redirect()
                ->route('animals.import')
                ->with('error', __('The file could not be imported. Fix the errors below and try again.'))
                ->with('import_errors', $preview['errors']);
        }

        $request->session()->put(self::SESSION_KEY, [
            'token' => $token,
            'path' => $storedPath,
            'original_name' => $file->getClientOriginalName(),
            'preview' => $preview,
            'pending_action' => null,
        ]);

        return redirect()->route('animals.import.preview');
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! is_array($pending) || empty($pending['preview'])) {
            return redirect()
                ->route('animals.import')
                ->with('error', __('Upload a file to preview the import.'));
        }

        return view('modules.animals.import-preview', $this->moduleViewData('animals') + [
            'pending' => $pending,
            'preview' => $pending['preview'],
        ]);
    }

    public function confirm(AnimalImportConfirmRequest $request, AnimalCsvImporter $importer): RedirectResponse|View
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! is_array($pending) || empty($pending['path']) || empty($pending['preview'])) {
            return redirect()
                ->route('animals.import')
                ->with('error', __('Your import preview expired. Upload the file again.'));
        }

        $action = $request->validated('duplicate_action');

        if ($action === 'cancel') {
            $this->forgetPendingImport($request);

            return redirect()
                ->route('animals.import')
                ->with('success', __('Import cancelled. No changes were made.'));
        }

        $preview = $pending['preview'];

        if (($preview['existing_count'] ?? 0) === 0 && $action === AnimalCsvImporter::ACTION_REPLACE) {
            $action = AnimalCsvImporter::ACTION_KEEP;
        }

        if ($action === AnimalCsvImporter::ACTION_REPLACE) {
            $pending['pending_action'] = AnimalCsvImporter::ACTION_REPLACE;
            $request->session()->put(self::SESSION_KEY, $pending);

            return redirect()->route('animals.import.confirm-replace');
        }

        return $this->executeImport($request, $importer, AnimalCsvImporter::ACTION_KEEP);
    }

    public function confirmReplace(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (
            ! is_array($pending)
            || ($pending['pending_action'] ?? null) !== AnimalCsvImporter::ACTION_REPLACE
            || empty($pending['preview'])
        ) {
            return redirect()
                ->route('animals.import')
                ->with('error', __('Your import preview expired. Upload the file again.'));
        }

        return view('modules.animals.import-confirm-replace', $this->moduleViewData('animals') + [
            'pending' => $pending,
            'preview' => $pending['preview'],
            'existingCount' => (int) ($pending['preview']['existing_count'] ?? 0),
        ]);
    }

    public function executeReplace(Request $request, AnimalCsvImporter $importer): RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (
            ! is_array($pending)
            || ($pending['pending_action'] ?? null) !== AnimalCsvImporter::ACTION_REPLACE
        ) {
            return redirect()
                ->route('animals.import')
                ->with('error', __('Your import preview expired. Upload the file again.'));
        }

        return $this->executeImport($request, $importer, AnimalCsvImporter::ACTION_REPLACE);
    }

    public function cancel(Request $request): RedirectResponse
    {
        $this->forgetPendingImport($request);

        return redirect()
            ->route('animals.import')
            ->with('success', __('Import cancelled. No changes were made.'));
    }

    protected function executeImport(Request $request, AnimalCsvImporter $importer, string $duplicateAction): RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! is_array($pending) || empty($pending['path'])) {
            return redirect()
                ->route('animals.import')
                ->with('error', __('Your import preview expired. Upload the file again.'));
        }

        if (! Storage::disk('local')->exists($pending['path'])) {
            $this->forgetPendingImport($request);

            return redirect()
                ->route('animals.import')
                ->with('error', __('The uploaded file is no longer available. Upload it again.'));
        }

        $result = $importer->import(
            $this->uploadedFileFromStorage($pending['path'], $pending['original_name'] ?? 'animals.csv'),
            $duplicateAction
        );

        $this->forgetPendingImport($request);

        $summary = [
            'created' => $result['created'],
            'updated' => $result['updated'],
            'skipped' => $result['skipped'],
            'failed' => $result['failed'],
            'total' => $result['total'],
        ];

        $message = $this->resultMessage($summary);

        if ($result['created'] === 0 && $result['updated'] === 0 && $result['failed'] > 0) {
            return redirect()
                ->route('animals.import')
                ->with('error', $message)
                ->with('import_summary', $summary)
                ->with('import_errors', $result['errors'])
                ->with('import_warnings', $result['warnings']);
        }

        if ($result['failed'] > 0 || $result['warnings'] !== [] || $result['skipped'] > 0 || $result['updated'] > 0) {
            return redirect()
                ->route('animals.import')
                ->with('success', $message)
                ->with('import_summary', $summary)
                ->with('import_errors', $result['errors'])
                ->with('import_warnings', $result['warnings']);
        }

        return redirect()
            ->route('animals.index')
            ->with('success', $message);
    }

    /**
     * @param  array{created: int, updated: int, skipped: int, failed: int, total: int}  $summary
     */
    protected function resultMessage(array $summary): string
    {
        $parts = [
            __('Created: :count', ['count' => $summary['created']]),
            __('Updated: :count', ['count' => $summary['updated']]),
            __('Skipped: :count', ['count' => $summary['skipped']]),
            __('Failed: :count', ['count' => $summary['failed']]),
        ];

        return implode(' · ', $parts);
    }

    protected function uploadedFileFromStorage(string $storedPath, string $originalName): UploadedFile
    {
        $absolute = Storage::disk('local')->path($storedPath);

        return new UploadedFile(
            $absolute,
            $originalName,
            null,
            null,
            true
        );
    }

    protected function forgetPendingImport(Request $request): void
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (is_array($pending) && ! empty($pending['path'])) {
            $this->deleteStoredImport($pending['path']);
        }

        $request->session()->forget(self::SESSION_KEY);
    }

    protected function deleteStoredImport(string $path): void
    {
        Storage::disk('local')->delete($path);
    }
}
