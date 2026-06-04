<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\TenantAccountService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(private readonly TenantAccountService $tenantAccounts) {}

    public function create(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if (TenantContext::isActive()) {
            $email = Str::lower($request->input('email'));
            $tenantId = TenantContext::id();

            $request->session()->put('tenant_id', $tenantId);
            $request->session()->put('auth_email', $email);
            $this->tenantAccounts->rememberTenantCookies($tenantId, $email);
        } else {
            $request->session()->forget(['tenant_id', 'auth_email']);
            $this->tenantAccounts->forgetTenantCookies();
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();
        $this->tenantAccounts->forgetTenantCookies();

        return redirect()->route('login');
    }
}
