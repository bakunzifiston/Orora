<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MilkBulkRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'yields' => ['nullable', 'array'],
            'yields.*' => ['nullable', 'numeric', 'min:0.01'],
            'bulk_lines' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $yields = collect($this->input('yields', []))->filter(fn ($v) => $v !== null && $v !== '');
            $lines = trim((string) $this->input('bulk_lines', ''));

            if ($yields->isEmpty() && $lines === '') {
                $validator->errors()->add('bulk', 'Enter at least one yield in the table or paste lines.');
            }
        });
    }
}
