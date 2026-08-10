<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EmployeeSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\EmployeeImportRequest;
use App\Services\Export\EmployeeCsvExporter;
use App\Services\Import\EmployeeCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeImportExportController extends Controller
{
    use EmployeeSectionViews;
    use ProvidesModuleNavigation;

    public function export(Request $request, EmployeeCsvExporter $exporter): StreamedResponse
    {
        return $exporter->export($request);
    }

    public function template(EmployeeCsvExporter $exporter): StreamedResponse
    {
        return $exporter->template();
    }

    public function create(): View
    {
        return view('modules.employees.import', $this->employeeSectionData('directory'));
    }

    public function store(EmployeeImportRequest $request, EmployeeCsvImporter $importer): RedirectResponse
    {
        $result = $importer->import($request->file('file'));

        if ($result['created'] === 0 && $result['failed'] > 0) {
            return redirect()
                ->route('employees.import')
                ->with('error', __('No employees were imported. Fix the errors below and try again.'))
                ->with('import_errors', $result['errors']);
        }

        $message = __(':count employees imported.', ['count' => $result['created']]);

        if ($result['failed'] > 0) {
            $message .= ' '.__(':count rows failed.', ['count' => $result['failed']]);

            return redirect()
                ->route('employees.import')
                ->with('success', $message)
                ->with('import_errors', $result['errors']);
        }

        return redirect()
            ->route('employees.directory')
            ->with('success', $message);
    }
}
