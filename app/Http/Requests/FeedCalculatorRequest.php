<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesFarmRelations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeedCalculatorRequest extends FormRequest
{
    use ValidatesFarmRelations;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'level' => ['required', Rule::in(['individual', 'herd'])],
            'farm_id' => ['required', 'exists:farms,id'],
            'livestock_id' => ['required', $this->livestockBelongsToFarm()],
        ];

        if ($this->input('level') === 'individual') {
            $rules['animal_id'] = ['required', $this->animalBelongsToLivestock()];
        } else {
            $rules['animal_id'] = ['prohibited'];
        }

        return $rules;
    }
}
