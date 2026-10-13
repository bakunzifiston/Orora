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

class VaccinationRequest extends FormRequest
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

        if ($this->input('vaccine_type') === '') {
            $merge['vaccine_type'] = null;
        }

        if ($this->input('administration_method') === '') {
            $merge['administration_method'] = null;
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

        // Legacy animal-only posts (no farm_id): infer farm from the animal.
        if (! $farm && $this->filled('animal_id') && ! $this->filled('flock_id')) {
            $animal = Animal::query()->find($this->input('animal_id'));

            if ($animal) {
                $this->merge([
                    'farm_id' => $animal->farm_id,
                    'livestock_id' => $animal->livestock_id,
                    'vaccination_target' => 'animal',
                ]);
                $farm = $animal->farm;
            }
        }

        // Legacy flock-only posts: infer farm from the flock.
        if (! $farm && $this->filled('flock_id')) {
            $flock = Flock::query()->find($this->input('flock_id'));

            if ($flock) {
                $this->merge([
                    'farm_id' => $flock->farm_id,
                    'vaccination_target' => 'flock',
                ]);
                $farm = $flock->farm;
            }
        }

        $target = $this->input('vaccination_target');

        if ($farm?->isPoultry()) {
            $this->merge([
                'vaccination_target' => 'flock',
                'livestock_id' => null,
                'animal_id' => null,
            ]);
        } else {
            $this->merge(['flock_id' => null]);

            if (! in_array($target, ['group', 'animal'], true)) {
                $target = $this->filled('animal_id') ? 'animal' : 'group';
                $this->merge(['vaccination_target' => $target]);
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
        $target = $isPoultry ? 'flock' : ($this->input('vaccination_target', 'group'));

        return array_merge([
            'farm_id' => ['required', 'exists:farms,id'],
            'vaccination_target' => $isPoultry
                ? ['nullable', Rule::in(['flock', 'group', 'animal'])]
                : ['required', Rule::in(['group', 'animal'])],
            'livestock_id' => $this->livestockRulesForTarget($target),
            'animal_id' => $this->animalRulesForTarget($target),
            'flock_id' => $this->flockRulesForTarget($target),
            'vaccine_name' => ['required', 'string', 'max:255'],
            'vaccine_type' => ['nullable', Rule::in(config('modules.vaccine_types'))],
            'vaccine_type_other' => [
                Rule::requiredIf(fn () => $this->input('vaccine_type') === 'Other'),
                'nullable',
                'string',
                'max:255',
            ],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'batch_number' => ['nullable', 'string', 'max:100'],
            'dosage' => ['nullable', 'string', 'max:100'],
            'administration_method' => ['nullable', Rule::in(config('modules.administration_methods'))],
            'administration_method_other' => [
                Rule::requiredIf(fn () => $this->input('administration_method') === 'Other'),
                'nullable',
                'string',
                'max:255',
            ],
            'vaccination_date' => ['required', 'date'],
            'next_due_date' => ['nullable', 'date', 'after_or_equal:vaccination_date'],
            'status' => ['required', Rule::in(config('modules.vaccination_statuses'))],
            'veterinarian_name' => ['nullable', 'string', 'max:255'],
            'veterinary_clinic' => ['nullable', 'string', 'max:255'],
            'administered_by' => ['nullable', 'string', 'max:255'],
            'side_effects' => ['nullable', 'string'],
            'reaction_notes' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ], $this->linkedExpenseRules());
    }

    public function attributes(): array
    {
        return [
            'vaccine_type_other' => 'other vaccine type',
            'administration_method_other' => 'other administration method',
            'livestock_id' => 'livestock group',
            'vaccination_target' => 'vaccination target',
        ];
    }

    public function vaccinationAttributes(): array
    {
        $attributes = $this->safe()->except([
            'attachment', 'vaccine_type_other', 'administration_method_other',
            'log_expense', 'expense_amount', 'expense_currency', 'expense_vendor_id',
            'expense_vendor_name', 'expense_payment_method', 'expense_paid_by',
            'expense_notes', 'expense_attachment',
            'vaccination_target', 'farm_id', 'livestock_id', 'animal_id', 'flock_id',
        ]);

        if (($attributes['vaccine_type'] ?? null) === 'Other') {
            $attributes['vaccine_type'] = $this->input('vaccine_type_other');
        }

        if (($attributes['administration_method'] ?? null) === 'Other') {
            $attributes['administration_method'] = $this->input('administration_method_other');
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

        if ($this->input('vaccination_target') === 'group' || ($this->filled('livestock_id') && ! $this->filled('animal_id'))) {
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
