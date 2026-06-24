<?php

namespace App\Http\Requests\Auth;

use App\Models\Central\AdminUser;
use App\Services\TenantAccountService;
use App\Support\SuperAdminSetup;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function __construct(private readonly TenantAccountService $tenantAccounts)
    {
        parent::__construct();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @return 'admin'|'web'
     */
    public function authenticate(): string
    {
        $this->ensureIsNotRateLimited();

        $email = Str::lower($this->string('email')->toString());

        if ($this->attemptAdminLogin($email)) {
            return 'admin';
        }

        $this->tenantAccounts->initializeForEmail($email);

        if (! Auth::guard('web')->attempt(
            ['email' => $email, 'password' => $this->input('password')],
            $this->boolean('remember')
        )) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return 'web';
    }

    private function attemptAdminLogin(string $email): bool
    {
        if (! SuperAdminSetup::tableReady()) {
            return false;
        }

        if (! AdminUser::query()->where('email', $email)->exists()) {
            return false;
        }

        if (! Auth::guard('admin')->attempt(
            ['email' => $email, 'password' => $this->input('password')],
            $this->boolean('remember')
        )) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return true;
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('Too many login attempts. Please try again in :seconds seconds.', [
                'seconds' => $seconds,
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
