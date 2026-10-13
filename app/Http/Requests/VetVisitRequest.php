<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\LinkedExpenseRules;
use App\Http\Requests\Concerns\ValidatesFarmRelations;
use App\Models\Animal;
use App\Models\Farm;
use App\Models\Flock;
use App\Models\Livestock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VetVisitRequest extends FormRequest
{
    use LinkedExpenseRules;
    use ValidatesFarmRelations;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->input('treatment_method') === '') {
            $merge['treatment_method'] = null;
        }

        foreach (['livestock_id', 'animal_id', 'flock_id', 'farm_id'] as $field) {
            if ($this->input($field) === '') {
                $merge[$field] = null;
            }
        }

        $this->merge($merge);
        $this->prepareLinkedExpenseValidation();

        $farm = $this->filled('farm_id')
            ? Farm::query()->find($this->input('farm_id'))
            : null;

        if (! $farm && $this->filled('animal_id') && ! $this->filled('flock_id')) {
            $animal = Animal::query()->find($this->input('animal_id'));

            if ($animal) {
                $this->merge([
                    'farm_id' => $animal->farm_id,
                    'livestock_id' => $animal->livestock_id,
                    'visit_target' => 'animal',
                ]);
                $farm = $animal->farm;
            }
        }

        if (! $farm && $this->filled('flock_id')) {
            $flock = Flock::query()->find($this->input('flock_id'));

            if ($flock) {
                $this->merge([
                    'farm_id' => $flock->farm_id,
                    'visit_target' => 'flock',
                ]);
                $farm = $flock->farm;
            }
        }

        $target = $this->input('visit_target');

        if ($farm?->isPoultry()) {
            $this->merge([
                'visit_target' => 'flock',
                'livestock_id' => null,
                'animal_id' => null,
            ]);
        } else {
            $this->merge(['flock_id' => null]);

            if (! in_array($target, ['group', 'animal'], true)) {
                $target = $this->filled('animal_id') ? 'animal' : 'group';
                $this->merge(['visit_target' => $target]);
            }

            if ($target === 'group') {
                $this->merge(['animal_id' => null]);
            }

            if ($target === 'animal' && $this->filled('animal_id') && ! $this->filled('livestock_id')) {
                $animal = Animal::query()->find($this->input('animal_id'));

                if ($animal?->livestock_id) {
                    $this->merge(['livestock_id' => $animal->livestock_id]);
                }
            }
        }
    }

    public function rules(): array
    {
        $isPoultry = $this->filled('farm_id')
            && Farm::query()->whereKey($this->input('farm_id'))->where('primary_species', 'poultry')->exists();
        $target = $isPoultry ? 'flock' : ($this->input('visit_target', 'group'));

        return array_merge([
            'farm_id' => ['required', 'exists:farms,id'],
            'visit_target' => $isPoultry
                ? ['nullable', Rule::in(['flock', 'group', 'animal'])]
                : ['required', Rule::in(['group', 'animal'])],
            'livestock_id' => $this->livestockRulesForTarget($target),
            'animal_id' => $this->animalRulesForTarget($target),
            'flock_id' => $this->flockRulesForTarget($target),
            'disease_name' => ['required', 'string', 'max:255'],
            'medicine_name' => ['required', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:100'],
            'treatment_method' => ['nullable', Rule::in(config('modules.treatment_methods'))],
            'treatment_method_other' => [
                Rule::requiredIf(fn () => $this->input('treatment_method') === 'Other'),
                'nullable',
                'string',
                'max:255',
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(config('modules.treatment_statuses'))],
            'veterinarian_name' => ['nullable', 'string', 'max:255'],
            'symptoms' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ], $this->linkedExpenseRules());
    }

    public function attributes(): array
    {
        return [
            'treatment_method_other' => 'other treatment method',
            'livestock_id' => 'livestock group',
            'visit_target' => 'visit target',
        ];
    }

    public function vetVisitAttributes(): array
    {
        $attributes = $this->safe()->except([
            'attachment', 'treatment_method_other',
            'log_expense', 'expense_amount', 'expense_currency', 'expense_vendor_id',
            'expense_vendor_name', 'expense_payment_method', 'expense_paid_by',
            'expense_notes', 'expense_attachment',
            'visit_target', 'farm_id', 'livestock_id', 'animal_id', 'flock_id',
        ]);

        if (($attributes['treatment_method'] ?? null) === 'Other') {
            $attributes['treatment_method'] = $this->input('treatment_method_other');
        }

        return $attributes;
    }

    /**
     * @return array{farm_id: int, livestock_id: int|null, animal_id: int|null, flock_id: int|null}
     */
    public function resolvedStockAttributes(): array
    {
        if ($this->filled('flock_id')) {
            $flock = Flock::query()->findOrFail($this->input('flock_id'));

            return [
                'farm_id' => $flock->farm_id,
                'livestock_id' => $flock->livestock_id,
                'animal_id' => null,
                'flock_id' => $flock->id,
            ];
        }

        if ($this->input('visit_target') === 'group' || ($this->filled('livestock_id') && ! $this->filled('animal_id'))) {
            $group = Livestock::query()->findOrFail($this->input('livestock_id'));

            return [
                'farm_id' => $group->farm_id,
                'livestock_id' => $group->id,
                'animal_id' => null,
                'flock_id' => null,
            ];
        }

        $animal = Animal::query()->findOrFail($this->input('animal_id'));

        return [
            'farm_id' => $animal->farm_id,
            'livestock_id' => $animal->livestock_id,
            'animal_id' => $animal->id,
            'flock_id' => null,
        ];
    }

    /**
     * @return list<string|\Illuminate\Validation\Rules\Exists>
     */
    private function livestockRulesForTarget(string $target): array
    {
        if ($target === 'flock') {
            return ['nullable'];
        }

        $rules = $this->optionalLivestockBelongsToFarm();
        $rules[0] = 'required';

        return $rules;
    }

    /**
     * @return list<string|\Illuminate\Validation\Rules\Exists>
     */
    private function animalRulesForTarget(string $target): array
    {
        if ($target !== 'animal') {
            return ['nullable', 'prohibits:flock_id'];
        }

        if ($this->filled('livestock_id') && $this->filled('farm_id')) {
            return ['required', $this->animalBelongsToLivestock(), 'prohibits:flock_id'];
        }

        $rules = $this->optionalAnimalBelongsToFarm();
        $rules[0] = 'required';
        $rules[] = 'prohibits:flock_id';

        return $rules;
    }

    /**
     * @return list<string|\Illuminate\Validation\Rules\Exists>
     */
    private function flockRulesForTarget(string $target): array
    {
        if ($target !== 'flock') {
            return ['nullable'];
        }

        $rules = $this->optionalFlockBelongsToFarm();
        $rules[0] = 'required';

        return $rules;
    }
}
