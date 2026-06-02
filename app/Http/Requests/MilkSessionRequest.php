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
        $sessionId = $session?->id;

        return [
            'farm_id' => ['required', 'exists:farms,id'],
            'livestock_id' => [
                'required',
                Rule::exists('livestock', 'id')->where(fn ($q) => $q->where('farm_id', (int) $this->input('farm_id'))),
            ],
            'session_date' => ['required', 'date', 'before_or_equal:today'],
            'session_shift' => [
                'required',
                Rule::in(config('modules.milk_session_shifts')),
                Rule::unique('milk_sessions')
                    ->ignore($sessionId)
                    ->where(fn ($q) => $q
                        ->where('livestock_id', (int) $this->input('livestock_id'))
                        ->whereDate('session_date', (string) $this->input('session_date'))),
            ],
            'milked_by' => ['required', 'string', 'max:255'],
            'milking_method' => ['required', Rule::in(config('modules.milking_methods'))],
            'destination_storage_id' => ['nullable', 'exists:milk_storage,id'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'session_shift.unique' => 'A milk session already exists for this herd, date, and shift.',
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
