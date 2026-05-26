<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Models\Animal;
use App\Models\Certificate;
use App\Models\Farm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificateController extends Controller
{
    use ProvidesModuleNavigation;

    public function index(): View
    {
        $certificates = Certificate::query()->with(['farm', 'animal'])->orderByDesc('issued_on')->paginate(15);

        return view('modules.certificates.index', $this->moduleViewData('certificates', compact('certificates')));
    }

    public function create(): View
    {
        return view('modules.certificates.create', $this->moduleViewData('certificates', $this->formOptions()));
    }

    public function store(Request $request): RedirectResponse
    {
        Certificate::create($request->validate($this->rules()));

        return redirect()->route('certificates.index')->with('success', 'Certificate created successfully.');
    }

    public function edit(Certificate $certificate): View
    {
        return view('modules.certificates.edit', $this->moduleViewData('certificates', array_merge($this->formOptions(), compact('certificate'))));
    }

    public function update(Request $request, Certificate $certificate): RedirectResponse
    {
        $certificate->update($request->validate($this->rules()));

        return redirect()->route('certificates.index')->with('success', 'Certificate updated successfully.');
    }

    public function destroy(Certificate $certificate): RedirectResponse
    {
        $certificate->delete();

        return redirect()->route('certificates.index')->with('success', 'Certificate removed successfully.');
    }

    private function formOptions(): array
    {
        return [
            'farms' => Farm::query()->orderBy('name')->get(),
            'animals' => Animal::query()->orderBy('tag_number')->get(),
        ];
    }

    private function rules(): array
    {
        return [
            'farm_id' => ['required', 'exists:farms,id'],
            'animal_id' => ['nullable', 'exists:animals,id'],
            'certificate_type' => ['required', 'in:'.implode(',', config('modules.certificate_types'))],
            'certificate_number' => ['nullable', 'string', 'max:255'],
            'issuing_authority' => ['nullable', 'string', 'max:255'],
            'issued_on' => ['required', 'date'],
            'expires_on' => ['nullable', 'date', 'after_or_equal:issued_on'],
            'status' => ['required', 'in:'.implode(',', config('modules.certificate_statuses'))],
            'notes' => ['nullable', 'string'],
        ];
    }
}
