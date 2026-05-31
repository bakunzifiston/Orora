<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MilkSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $session = $this->route('milkSession');

        return [
            'farm_id' => ['required', 'exists:farms,id'],
            'livestock_id' => ['required', 'exists:livestock,id'],
            'session_date' => ['required', 'date', 'before_or_equal:today'],
            'session_shift' => ['required', Rule::in(config('modules.milk_session_shifts'))],
            'milked_by' => ['required', 'string', 'max:255'],
            'milking_method' => ['required', Rule::in(config('modules.milking_methods'))],
            'destination_storage_id' => ['nullable', 'exists:milk_storage,id'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function sessionAttributes(): array
    {
        return $this->safe()->only([
            'farm_id',
            'livestock_id',
            'session_date',
            'session_shift',
            'milked_by',
            'milking_method',
            'destination_storage_id',
            'notes',
        ]);
    }
}
