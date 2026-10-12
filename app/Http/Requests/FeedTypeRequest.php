<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeedTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'feed_supplier_id' => ['nullable', 'exists:feed_suppliers,id'],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', Rule::in(config('modules.feed_units'))],
            'category' => ['nullable', Rule::in(config('modules.feed_type_categories'))],
            'category_other' => [
                Rule::requiredIf(fn () => $this->input('category') === 'Other'),
                'nullable',
                'string',
                'max:255',
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'category_other' => __('custom category'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function feedTypeAttributes(): array
    {
        $attributes = $this->safe()->except(['category_other']);

        if (($attributes['category'] ?? null) === 'Other') {
            $attributes['category'] = $this->input('category_other');
        }

        return $attributes;
    }

    protected function prepareForValidation(): void
    {
        $merge = ['is_active' => $this->boolean('is_active')];

        if ($this->input('feed_supplier_id') === '') {
            $merge['feed_supplier_id'] = null;
        }

        if ($this->input('category') === '') {
            $merge['category'] = null;
        }

        $this->merge($merge);
    }
}
