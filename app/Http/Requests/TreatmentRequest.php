<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesFarmRelations;
use App\Models\Animal;
use App\Models\Farm;
use App\Models\Flock;
use App\Models\Livestock;
use App\Models\Medicine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TreatmentRequest extends FormRequest
{
    use ValidatesFarmRelations;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('treatment_method') === '') {
            $this->merge(['treatment_method' => null]);
        }

        foreach ([
            'medicine_id', 'quantity_used', 'dosage', 'new_medicine_name', 'new_medicine_quantity',
            'livestock_id', 'animal_id', 'flock_id', 'farm_id',
        ] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }

        if (! in_array($this->input('medicine_mode'), ['existing', 'new'], true)) {
            $this->merge(['medicine_mode' => 'existing']);
        }

        if ($this->input('medicine_mode') === 'new') {
            $this->merge(['medicine_id' => null]);
        }

        $farm = $this->filled('farm_id')
            ? Farm::query()->find($this->input('farm_id'))
            : null;

        $target = $this->input('treatment_target');

        if ($farm?->isPoultry()) {
            $this->merge([
                'treatment_target' => 'flock',
                'livestock_id' => null,
                'animal_id' => null,
            ]);
        } else {
            $this->merge(['flock_id' => null]);

            if (! in_array($target, ['group', 'animal'], true)) {
                $target = $this->filled('animal_id') ? 'animal' : 'group';
                $this->merge(['treatment_target' => $target]);
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
        $isNewMedicine = $this->input('medicine_mode') === 'new';
        $currentMedicineId = $this->route('treatment')?->medicine_id;
        $isPoultry = $this->filled('farm_id')
            && Farm::query()->whereKey($this->input('farm_id'))->where('primary_species', 'poultry')->exists();
        $target = $isPoultry ? 'flock' : ($this->input('treatment_target', 'group'));

        $medicineIdRules = $isNewMedicine
            ? ['nullable']
            : [
                'required',
                Rule::exists('medicines', 'id')->where(function ($query) use ($currentMedicineId) {
                    $query->where(function ($inner) use ($currentMedicineId) {
                        $inner->where('is_active', true)
                            ->where('quantity_on_hand', '>', 0)
                            ->where(function ($expiry) {
                                $expiry->whereNull('expiry_date')
                                    ->orWhereDate('expiry_date', '>=', $this->input('start_date') ?: now()->toDateString());
                            });

                        if ($currentMedicineId) {
                            $inner->orWhere('id', $currentMedicineId);
                        }
                    });
                }),
            ];

        return array_merge([
            'farm_id' => ['required', 'exists:farms,id'],
            'treatment_target' => $isPoultry
                ? ['nullable', Rule::in(['flock', 'group', 'animal'])]
                : ['required', Rule::in(['group', 'animal'])],
            'livestock_id' => $this->livestockRulesForTarget($target),
            'animal_id' => $this->animalRulesForTarget($target),
            'flock_id' => $this->flockRulesForTarget($target),
            'disease_name' => ['required', 'string', 'max:255'],
            'medicine_mode' => ['required', Rule::in(['existing', 'new'])],
            'medicine_id' => $medicineIdRules,
            'new_medicine_name' => [$isNewMedicine ? 'required' : 'nullable', 'string', 'max:255'],
            'new_medicine_unit' => [$isNewMedicine ? 'required' : 'nullable', Rule::in(config('modules.medicine_units'))],
            'new_medicine_quantity' => [$isNewMedicine ? 'required' : 'nullable', 'numeric', 'min:0.01'],
            'medicine_name' => ['nullable', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:100'],
            'quantity_used' => ['required', 'numeric', 'min:0.01'],
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
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('quantity_used')) {
                return;
            }

            $quantityUsed = (float) $this->input('quantity_used');

            if ($this->input('medicine_mode') === 'new') {
                $opening = (float) $this->input('new_medicine_quantity');

                if ($opening > 0 && $quantityUsed > $opening) {
                    $validator->errors()->add(
                        'quantity_used',
                        __('Quantity used cannot be greater than the opening stock for the new medicine.')
                    );
                }

                return;
            }

            if (! $this->filled('medicine_id')) {
                return;
            }

            $medicine = Medicine::query()->find($this->input('medicine_id'));

            if (! $medicine) {
                return;
            }

            if ($medicine->isExpired($this->input('start_date'))) {
                $validator->errors()->add('medicine_id', __('This medicine has expired and cannot be used for treatments.'));
            }

            $available = (float) $medicine->quantity_on_hand;
            $currentTreatment = $this->route('treatment');

            if ($currentTreatment && (int) $currentTreatment->medicine_id === (int) $medicine->id) {
                $available += (float) ($currentTreatment->quantity_used ?? 0);
            }

            if ($quantityUsed > $available) {
                $validator->errors()->add(
                    'quantity_used',
                    __('Insufficient medicine stock for this treatment.')
                );
            }
        });
    }

    public function attributes(): array
    {
        return [
            'treatment_method_other' => 'other treatment method',
            'medicine_id' => 'medicine',
            'quantity_used' => 'quantity used',
            'new_medicine_name' => 'new medicine name',
            'new_medicine_unit' => 'unit',
            'new_medicine_quantity' => 'opening stock',
            'livestock_id' => 'livestock group',
            'treatment_target' => 'treatment target',
        ];
    }

    public function treatmentAttributes(): array
    {
        $attributes = $this->safe()->except([
            'attachment',
            'treatment_method_other',
            'medicine_mode',
            'new_medicine_name',
            'new_medicine_unit',
            'new_medicine_quantity',
            'treatment_target',
            'farm_id',
            'livestock_id',
            'animal_id',
            'flock_id',
        ]);

        if (($attributes['treatment_method'] ?? null) === 'Other') {
            $attributes['treatment_method'] = $this->input('treatment_method_other');
        }

        if ($this->filled('medicine_id')) {
            $medicine = Medicine::query()->find($this->input('medicine_id'));
            $attributes['medicine_name'] = $medicine?->name ?? ($attributes['medicine_name'] ?? null);
        }

        if ($this->input('medicine_mode') === 'new') {
            $attributes['medicine_name'] = $this->input('new_medicine_name');
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

        if ($this->input('treatment_target') === 'group' || ($this->filled('livestock_id') && ! $this->filled('animal_id'))) {
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

    public function wantsNewMedicine(): bool
    {
        return $this->input('medicine_mode') === 'new';
    }

    /**
     * @return array{name: string, unit: string, quantity_on_hand: float, is_active: bool}
     */
    public function newMedicineAttributes(): array
    {
        return [
            'name' => (string) $this->input('new_medicine_name'),
            'unit' => (string) $this->input('new_medicine_unit'),
            'quantity_on_hand' => (float) $this->input('new_medicine_quantity'),
            'is_active' => true,
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
