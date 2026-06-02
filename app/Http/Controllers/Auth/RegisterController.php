<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\TenantAccountService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(private readonly TenantAccountService $tenantAccounts) {}

    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $provisioned = $this->tenantAccounts->registerAccount(
            $validated['name'],
            $validated['email'],
            $validated['password'],
        );

        $user = $provisioned['user'];

        event(new Registered($user));

        Auth::login($user);

        $request->session()->put('tenant_id', $provisioned['tenant']->id);
        $request->session()->put('auth_email', Str::lower($validated['email']));
        $this->tenantAccounts->rememberTenantCookies($provisioned['tenant']->id, $validated['email']);

        return redirect()->route('dashboard');
    }
}
