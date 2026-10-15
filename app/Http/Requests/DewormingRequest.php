<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\LinkedExpenseRules;
use App\Http\Requests\Concerns\ValidatesFarmRelations;
use App\Models\Animal;
use App\Models\Farm;
use App\Models\Flock;
use App\Models\Livestock;
use App\Models\Medicine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class DewormingRequest extends FormRequest
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

        if ($this->input('product_type') === '') {
            $merge['product_type'] = null;
        }

        if ($this->input('administration_method') === '') {
            $merge['administration_method'] = null;
        }

        foreach (['livestock_id', 'animal_id', 'flock_id', 'farm_id', 'medicine_id', 'quantity_used'] as $field) {
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
                    'deworming_target' => 'animal',
                ]);
                $farm = $animal->farm;
            }
        }

        if (! $farm && $this->filled('flock_id')) {
            $flock = Flock::query()->find($this->input('flock_id'));

            if ($flock) {
                $this->merge([
                    'farm_id' => $flock->farm_id,
                    'deworming_target' => 'flock',
                ]);
                $farm = $flock->farm;
            }
        }

        $target = $this->input('deworming_target');

        if ($farm?->isPoultry()) {
            $this->merge([
                'deworming_target' => 'flock',
                'livestock_id' => null,
                'animal_id' => null,
            ]);
        } else {
            $this->merge(['flock_id' => null]);

            if (! in_array($target, ['group', 'animal'], true)) {
                $target = $this->filled('animal_id') ? 'animal' : 'group';
                $this->merge(['deworming_target' => $target]);
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
        $target = $isPoultry ? 'flock' : ($this->input('deworming_target', 'group'));

        return array_merge([
            'farm_id' => ['required', 'exists:farms,id'],
            'deworming_target' => $isPoultry
                ? ['nullable', Rule::in(['flock', 'group', 'animal'])]
                : ['required', Rule::in(['group', 'animal'])],
            'livestock_id' => $this->livestockRulesForTarget($target),
            'animal_id' => $this->animalRulesForTarget($target),
            'flock_id' => $this->flockRulesForTarget($target),
            'product_name' => ['required', 'string', 'max:255'],
            'medicine_id' => ['nullable', 'exists:medicines,id'],
            'quantity_used' => ['nullable', 'numeric', 'min:0.01'],
            'product_type' => ['nullable', Rule::in(config('modules.dewormer_types'))],
            'product_type_other' => [
                Rule::requiredIf(fn () => $this->input('product_type') === 'Other'),
                'nullable',
                'string',
                'max:255',
            ],
            'active_ingredient' => ['nullable', 'string', 'max:255'],
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
            'deworming_date' => ['required', 'date'],
            'next_due_date' => ['nullable', 'date', 'after_or_equal:deworming_date'],
            'status' => ['required', Rule::in(config('modules.vaccination_statuses'))],
            'veterinarian_name' => ['nullable', 'string', 'max:255'],
            'administered_by' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ], $this->linkedExpenseRules());
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('medicine_id')) {
                return;
            }

            $medicine = Medicine::query()->find($this->input('medicine_id'));

            if (! $medicine) {
                return;
            }

            if (! $this->filled('quantity_used')) {
                $validator->errors()->add('quantity_used', __('Quantity used is required when a medicine is selected.'));
                return;
            }

            if ($medicine->isExpired($this->input('deworming_date'))) {
                $validator->errors()->add('medicine_id', __('This medicine has expired and cannot be used for deworming.'));
            }

            if (! $medicine->is_active) {
                $validator->errors()->add('medicine_id', __('This medicine is inactive and cannot be used for deworming.'));
            }

            $available = (float) $medicine->quantity_on_hand;
            $currentDeworming = $this->route('deworming');

            if ($currentDeworming && (int) $currentDeworming->medicine_id === (int) $medicine->id) {
                $available += (float) ($currentDeworming->quantity_used ?? 0);
            }

            if ((float) $this->input('quantity_used') > $available) {
                $validator->errors()->add('quantity_used', __('Insufficient medicine stock for this deworming.'));
            }
        });
    }

    public function attributes(): array
    {
        return [
            'product_type_other' => 'other product type',
            'administration_method_other' => 'other administration method',
            'medicine_id' => 'medicine',
            'quantity_used' => 'quantity used',
            'livestock_id' => 'livestock group',
            'deworming_target' => 'deworming target',
        ];
    }

    public function dewormingAttributes(): array
    {
        $attributes = $this->safe()->except([
            'attachment', 'product_type_other', 'administration_method_other',
            'log_expense', 'expense_amount', 'expense_currency', 'expense_vendor_id',
            'expense_vendor_name', 'expense_payment_method', 'expense_paid_by',
            'expense_notes', 'expense_attachment',
            'deworming_target', 'farm_id', 'livestock_id', 'animal_id', 'flock_id',
        ]);

        if (($attributes['product_type'] ?? null) === 'Other') {
            $attributes['product_type'] = $this->input('product_type_other');
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

        if ($this->input('deworming_target') === 'group' || ($this->filled('livestock_id') && ! $this->filled('animal_id'))) {
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
