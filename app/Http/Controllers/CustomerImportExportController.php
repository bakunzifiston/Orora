<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CustomerSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\CustomerImportRequest;
use App\Services\Export\CustomerCsvExporter;
use App\Services\Import\CustomerCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerImportExportController extends Controller
{
    use CustomerSectionViews;
    use ProvidesModuleNavigation;

    public function export(Request $request, CustomerCsvExporter $exporter): StreamedResponse
    {
        return $exporter->export($request);
    }

    public function template(CustomerCsvExporter $exporter): StreamedResponse
    {
        return $exporter->template();
    }

    public function create(): View
    {
        return view('modules.customers.import', $this->customerSectionData('directory'));
    }

    public function store(CustomerImportRequest $request, CustomerCsvImporter $importer): RedirectResponse
    {
        $result = $importer->import($request->file('file'));

        if ($result['created'] === 0 && $result['failed'] > 0) {
            return redirect()
                ->route('customers.import')
                ->with('error', __('No customers were imported. Fix the errors below and try again.'))
                ->with('import_errors', $result['errors']);
        }

        $message = __(':count customers imported.', ['count' => $result['created']]);

        if ($result['failed'] > 0) {
            $message .= ' '.__(':count rows failed.', ['count' => $result['failed']]);

            return redirect()
                ->route('customers.import')
                ->with('success', $message)
                ->with('import_errors', $result['errors']);
        }

        return redirect()
            ->route('customers.directory')
            ->with('success', $message);
    }
}
