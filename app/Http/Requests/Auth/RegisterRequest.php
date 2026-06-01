<?php

namespace App\Http\Requests\Auth;

use App\Models\TenantAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('email')) {
            $this->merge(['email' => Str::lower($this->string('email')->toString())]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(TenantAccount::class, 'email'),
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
