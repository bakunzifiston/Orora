<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\AnimalImportRequest;
use App\Services\Export\AnimalCsvExporter;
use App\Services\Import\AnimalCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnimalImportExportController extends Controller
{
    use ProvidesModuleNavigation;

    public function export(Request $request, AnimalCsvExporter $exporter): StreamedResponse
    {
        return $exporter->export($request);
    }

    public function template(AnimalCsvExporter $exporter): StreamedResponse
    {
        return $exporter->template();
    }

    public function create(): View
    {
        return view('modules.animals.import', $this->moduleViewData('animals'));
    }

    public function store(AnimalImportRequest $request, AnimalCsvImporter $importer): RedirectResponse
    {
        $result = $importer->import($request->file('file'));

        $summary = [
            'created' => $result['created'],
            'failed' => $result['failed'],
            'total' => $result['total'],
        ];

        if ($result['created'] === 0 && $result['failed'] > 0) {
            return redirect()
                ->route('animals.import')
                ->with('error', __('No animals were imported. :failed of :total rows failed. Fix the errors below and try again.', [
                    'failed' => $result['failed'],
                    'total' => max($result['total'], $result['failed']),
                ]))
                ->with('import_summary', $summary)
                ->with('import_errors', $result['errors'])
                ->with('import_warnings', $result['warnings']);
        }

        $message = __(':created of :total animals imported successfully.', [
            'created' => $result['created'],
            'total' => $result['total'],
        ]);

        if ($result['failed'] > 0) {
            $message .= ' '.__(':failed rows failed.', ['failed' => $result['failed']]);
        }

        if ($result['failed'] > 0 || $result['warnings'] !== []) {
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
}
