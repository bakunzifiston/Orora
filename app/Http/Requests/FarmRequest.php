<?php

namespace App\Http\Requests;

use App\Services\RwandaLocationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class FarmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! in_array($this->input('ownership_type'), ['cooperative', 'company'], true)) {
            $this->merge([
                'members' => [],
                'organization_name' => null,
                'tax_id' => null,
            ]);
        }
    }

    public function rules(): array
    {
        $farmId = $this->route('farm')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'registration_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('farms', 'registration_number')->ignore($farmId),
            ],
            'country' => ['required', 'string', 'max:100'],
            'province_code' => ['required', 'integer'],
            'district_code' => ['required', 'integer'],
            'sector_code' => ['required', 'string', 'max:20'],
            'cell_code' => ['required', 'integer'],
            'village_code' => ['required', 'integer'],
            'farm_size_hectares' => ['required', 'numeric', 'min:0'],
            'registration_date' => ['required', 'date'],
            'status' => ['required', Rule::in(config('modules.farm_statuses'))],
            'ownership_type' => ['required', Rule::in(array_keys(config('modules.ownership_types')))],
            'owner_first_name' => ['required', 'string', 'max:255'],
            'owner_last_name' => ['required', 'string', 'max:255'],
            'owner_national_id' => ['required', 'string', 'max:50'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'contact_email' => ['required', 'email', 'max:255'],
            'owner_emergency_contact' => ['required', 'string', 'max:30'],
            'organization_name' => [
                Rule::requiredIf(fn () => $this->requiresOrganization()),
                'nullable',
                'string',
                'max:255',
            ],
            'tax_id' => [
                Rule::requiredIf(fn () => $this->requiresOrganization()),
                'nullable',
                'string',
                'max:100',
            ],
            'owner_dob' => ['required', 'date', 'before:today'],
            'owner_gender' => ['required', Rule::in(config('modules.genders'))],
            'notes' => ['nullable', 'string'],
            'members' => [
                Rule::excludeIf(fn () => ! $this->requiresOrganization()),
                'required',
                'array',
                'min:1',
            ],
            'members.*.first_name' => ['required', 'string', 'max:255'],
            'members.*.last_name' => ['required', 'string', 'max:255'],
            'members.*.date_of_birth' => ['required', 'date', 'before:today'],
            'members.*.phone' => ['required', 'string', 'max:30'],
            'members.*.gender' => ['required', Rule::in(config('modules.genders'))],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $locations = app(RwandaLocationService::class);

            if (! $locations->isValidSelection(
                (int) $this->input('province_code'),
                (int) $this->input('district_code'),
                (string) $this->input('sector_code'),
                (int) $this->input('cell_code'),
                (int) $this->input('village_code'),
            )) {
                $validator->errors()->add('village_code', 'The selected Rwanda location is invalid.');
            }

            if ($this->requiresOrganization() && $this->memberRows() === []) {
                $validator->errors()->add('members', 'At least one member is required for cooperatives and companies.');
            }
        });
    }

    public function farmAttributes(): array
    {
        $locations = app(RwandaLocationService::class);

        $province = $locations->findProvince((int) $this->input('province_code'));
        $district = $locations->findDistrict((int) $this->input('district_code'));
        $sector = $locations->findSector((string) $this->input('sector_code'));
        $cell = $locations->findCell((int) $this->input('cell_code'));
        $village = $locations->findVillage((int) $this->input('village_code'));

        return [
            'name' => $this->input('name'),
            'registration_number' => $this->input('registration_number'),
            'country' => $this->input('country', 'Rwanda'),
            'province_code' => (int) $this->input('province_code'),
            'province' => $province['name'] ?? null,
            'district_code' => (int) $this->input('district_code'),
            'district' => $district['name'] ?? null,
            'sector_code' => (string) $this->input('sector_code'),
            'sector' => $sector['name'] ?? null,
            'cell_code' => (int) $this->input('cell_code'),
            'cell' => $cell['name'] ?? null,
            'village_code' => (int) $this->input('village_code'),
            'village' => $village['name'] ?? null,
            'farm_size_hectares' => $this->input('farm_size_hectares'),
            'registration_date' => $this->input('registration_date'),
            'status' => $this->input('status'),
            'ownership_type' => $this->input('ownership_type'),
            'owner_first_name' => $this->input('owner_first_name'),
            'owner_last_name' => $this->input('owner_last_name'),
            'owner_national_id' => $this->input('owner_national_id'),
            'contact_phone' => $this->input('contact_phone'),
            'contact_email' => $this->input('contact_email'),
            'owner_emergency_contact' => $this->input('owner_emergency_contact'),
            'organization_name' => $this->input('organization_name'),
            'tax_id' => $this->input('tax_id'),
            'owner_dob' => $this->input('owner_dob'),
            'owner_gender' => $this->input('owner_gender'),
            'notes' => $this->input('notes'),
        ];
    }

    public function memberRows(): array
    {
        if (! $this->requiresOrganization()) {
            return [];
        }

        return collect($this->input('members', []))
            ->filter(fn ($member) => ! empty($member['first_name']) || ! empty($member['last_name']))
            ->values()
            ->all();
    }

    private function requiresOrganization(): bool
    {
        return in_array($this->input('ownership_type'), ['cooperative', 'company'], true);
    }
}
