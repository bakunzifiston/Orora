<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesStockSubject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MortalityRequest extends FormRequest
{
    use ValidatesStockSubject;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [
            'postmortem_done' => $this->boolean('postmortem_done'),
        ];

        if ($this->input('disposal_method') === '') {
            $merge['disposal_method'] = null;
        }

        if (! $this->filled('deaths_count')) {
            $merge['deaths_count'] = $this->filled('flock_id') ? null : 1;
        }

        $this->merge($merge);
        $this->prepareStockSubject();
    }

    public function rules(): array
    {
        return array_merge($this->stockSubjectRules(), [
            'death_date' => ['required', 'date'],
            'deaths_count' => [
                Rule::requiredIf(fn () => $this->filled('flock_id')),
                'nullable',
                'integer',
                'min:1',
            ],
            'cause_of_death' => ['nullable', 'string', 'max:255'],
            'reported_by' => ['nullable', 'string', 'max:255'],
            'veterinarian_name' => ['nullable', 'string', 'max:255'],
            'disposal_method' => ['nullable', Rule::in(config('modules.disposal_methods'))],
            'disposal_method_other' => [
                Rule::requiredIf(fn () => $this->input('disposal_method') === 'Other'),
                'nullable',
                'string',
                'max:255',
            ],
            'postmortem_done' => ['boolean'],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ];
    }

    public function attributes(): array
    {
        return [
            'disposal_method_other' => 'other disposal method',
        ];
    }

    public function mortalityAttributes(): array
    {
        $attributes = $this->safe()->except(['attachment', 'disposal_method_other']);

        if (($attributes['disposal_method'] ?? null) === 'Other') {
            $attributes['disposal_method'] = $this->input('disposal_method_other');
        }

        if (! $this->filled('flock_id')) {
            $attributes['deaths_count'] = 1;
        }

        return $attributes;
    }
}
