<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeDocumentRequest;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;

class EmployeeDocumentController extends Controller
{
    public function __construct(private readonly EmployeeService $employeeService) {}

    public function store(EmployeeDocumentRequest $request, Employee $employee): RedirectResponse
    {
        $employee->documents()->create([
            ...$request->validated(),
            'uploaded_by' => auth()->id(),
        ]);

        $this->employeeService->log($employee, 'updated', null, null, 'Document recorded.');

        return redirect()->route('employees.show', $employee)->with('success', 'Document recorded.');
    }

    public function destroy(Employee $employee, EmployeeDocument $document): RedirectResponse
    {
        abort_unless($document->employee_id === $employee->id, 404);

        $document->delete();
        $this->employeeService->log($employee, 'updated', null, null, 'Document removed.');

        return redirect()->route('employees.show', $employee)->with('success', 'Document removed.');
    }
}
