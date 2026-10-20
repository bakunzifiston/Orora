<?php

namespace App\Http\Requests;

use App\Models\Animal;
use App\Models\Farm;
use App\Models\MilkDistribution;
use App\Models\MilkStorage;
use App\Services\Species\SpeciesProfile;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MilkDistributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->input('category');
        $farmId = $this->integer('farm_id') ?: null;

        return [
            'farm_id' => ['required', 'exists:farms,id', $this->farmProducesMilk()],
            'distributed_at' => ['required', 'date', 'before_or_equal:'.now()->addMinutes(5)->toDateTimeString()],
            'quantity_liters' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'category' => ['required', Rule::in(config('modules.milk_distribution_manual_categories'))],
            'recipient_type' => [
                'required',
                Rule::in(config('modules.milk_distribution_category_recipients.'.$category, [])),
            ],
            'other_use' => [
                Rule::requiredIf($category === 'other'),
                'nullable',
                'string',
                'max:255',
            ],
            'recipient_name' => [
                Rule::requiredIf(in_array($category, ['neighbor', 'relative_visitor'], true)),
                'nullable',
                'string',
                'max:255',
            ],
            'recipient_phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9 ()\-]{7,20}$/'],
            'animal_id' => [
                Rule::requiredIf($category === 'fed_to_calf'),
                'nullable',
                'integer',
                $this->eligibleCalf($farmId),
            ],
            'milk_storage_id' => [
                'nullable',
                Rule::exists('milk_storage', 'id')->where(fn ($q) => $q->where('farm_id', $farmId)),
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'animal_id.required' => __('Select the calf that was fed.'),
            'other_use.required' => __('Describe what the milk was used for.'),
            'recipient_name.required' => __('Enter the name of the person who received the milk.'),
            'recipient_phone.regex' => __('Enter a valid phone number.'),
            'milk_storage_id.exists' => __('The selected storage tank does not belong to this farm.'),
            'recipient_type.in' => __('The recipient type does not match the selected use.'),
            'distributed_at.before_or_equal' => __('The date and time cannot be in the future.'),
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty() || ! $this->filled('milk_storage_id')) {
                    return;
                }

                $storage = MilkStorage::query()->find($this->integer('milk_storage_id'));
                $available = (float) ($storage?->current_quantity_liters ?? 0);

                /** @var MilkDistribution|null $existing */
                $existing = $this->route('milkDistribution');
                if ($existing && $existing->storage_movement_id && (int) $existing->milk_storage_id === (int) $storage?->id) {
                    $available += (float) $existing->quantity_liters;
                }

                if ((float) $this->input('quantity_liters') > $available + 0.001) {
                    $validator->errors()->add(
                        'quantity_liters',
                        __('Only :litres L is available in this storage tank.', ['litres' => number_format($available, 2)]),
                    );
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'other_use' => $this->input('category') === 'other' && $this->filled('other_use') ? trim((string) $this->input('other_use')) : null,
            'recipient_name' => $this->filled('recipient_name') ? trim((string) $this->input('recipient_name')) : null,
            'recipient_phone' => $this->filled('recipient_phone') ? trim((string) $this->input('recipient_phone')) : null,
            'milk_storage_id' => $this->filled('milk_storage_id') ? $this->input('milk_storage_id') : null,
            'animal_id' => $this->input('category') === 'fed_to_calf' && $this->filled('animal_id') ? $this->input('animal_id') : null,
        ]);
    }

    private function farmProducesMilk(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $farm = Farm::query()->find($value);

            if ($farm && ! app(SpeciesProfile::class)->allows('milk', $farm)) {
                $fail(__('Milk is not tracked for this farm\'s species.'));
            }
        };
    }

    private function eligibleCalf(?int $farmId): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($farmId): void {
            if ($value === null || $value === '') {
                return;
            }

            $animal = Animal::query()->whereKey($value)->first();

            if (! $animal || (int) $animal->farm_id !== (int) $farmId) {
                $fail(__('The selected calf does not belong to this farm.'));

                return;
            }

            $eligible = Animal::query()->whereKey($animal->id)->calfFeedingEligible()->exists();

            if (! $eligible) {
                $fail(__('The selected animal is not an active calf (under :months months or in the calves group).', [
                    'months' => (int) config('modules.milk_calf_max_age_months', 12),
                ]));
            }
        };
    }
}
