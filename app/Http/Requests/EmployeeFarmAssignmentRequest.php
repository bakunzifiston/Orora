<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeFarmAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'farm_id' => ['required', 'exists:farms,id'],
            'assignment_role' => ['nullable', Rule::in(array_keys(config('modules.employee_job_roles')))],
            'is_primary' => ['boolean'],
            'assigned_from' => ['nullable', 'date'],
            'assigned_until' => ['nullable', 'date', 'after_or_equal:assigned_from'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_primary' => $this->boolean('is_primary')]);
    }
}
