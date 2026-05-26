<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LivestockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'farm_id' => ['required', 'exists:farms,id'],
            'herd_groups' => ['required', 'array', 'min:1'],
            'herd_groups.*' => ['string', Rule::in(config('modules.herd_groups'))],
            'herd_group_other' => [
                Rule::requiredIf(fn () => $this->hasOther('herd_groups')),
                'nullable',
                'string',
                'max:255',
            ],
            'livestock_types' => ['required', 'array', 'min:1'],
            'livestock_types.*' => ['string', Rule::in(config('modules.livestock_types'))],
            'livestock_type_other' => [
                Rule::requiredIf(fn () => $this->hasOther('livestock_types')),
                'nullable',
                'string',
                'max:255',
            ],
            'production_purposes' => ['required', 'array', 'min:1'],
            'production_purposes.*' => ['string', Rule::in(config('modules.production_purposes'))],
            'production_purpose_other' => [
                Rule::requiredIf(fn () => $this->hasOther('production_purposes')),
                'nullable',
                'string',
                'max:255',
            ],
            'farming_methods' => ['required', 'array', 'min:1'],
            'farming_methods.*' => ['string', Rule::in(config('modules.farming_methods'))],
            'farming_method_other' => [
                Rule::requiredIf(fn () => $this->hasOther('farming_methods')),
                'nullable',
                'string',
                'max:255',
            ],
            'feeding_methods' => ['required', 'array', 'min:1'],
            'feeding_methods.*' => ['string', Rule::in(config('modules.feeding_methods'))],
            'feeding_method_other' => [
                Rule::requiredIf(fn () => $this->hasOther('feeding_methods')),
                'nullable',
                'string',
                'max:255',
            ],
            'breed' => ['nullable', 'string', 'max:255'],
            'head_count' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(config('modules.record_statuses'))],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'farm_id' => 'farm',
            'herd_groups' => 'group / herd',
            'herd_group_other' => 'other group / herd',
            'livestock_types' => 'livestock types',
            'livestock_type_other' => 'other livestock type',
            'production_purposes' => 'production purposes',
            'production_purpose_other' => 'other production purpose',
            'farming_methods' => 'farming methods',
            'farming_method_other' => 'other farming method',
            'feeding_methods' => 'feeding methods',
            'feeding_method_other' => 'other feeding method',
        ];
    }

    public function livestockAttributes(): array
    {
        $attributes = $this->only([
            'farm_id',
            'herd_groups',
            'herd_group_other',
            'livestock_types',
            'livestock_type_other',
            'production_purposes',
            'production_purpose_other',
            'farming_methods',
            'farming_method_other',
            'feeding_methods',
            'feeding_method_other',
            'breed',
            'head_count',
            'status',
            'notes',
        ]);

        $attributes['name'] = $this->herdGroupsDisplayName($attributes);

        return $attributes;
    }

    private function herdGroupsDisplayName(array $attributes): string
    {
        $groups = $attributes['herd_groups'] ?? [];
        $other = $attributes['herd_group_other'] ?? null;

        $labels = array_map(function (string $value) use ($other) {
            if ($value === 'Other' && $other) {
                return 'Other: '.$other;
            }

            return $value;
        }, $groups);

        $label = implode(', ', $labels);

        return $label !== '' ? mb_substr($label, 0, 255) : 'Livestock group';
    }

    private function hasOther(string $field): bool
    {
        return in_array('Other', $this->input($field, []), true);
    }
}
