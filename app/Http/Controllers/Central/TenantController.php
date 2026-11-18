<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Support\AccountType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::with('domains')->orderBy('created_at', 'desc')->get();

        return view('central.tenants.index', [
            'activeNav' => 'tenants',
            'tenants' => $tenants,
        ]);
    }

    public function create(): View
    {
        return view('central.tenants.create', [
            'activeNav' => 'tenants',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $domainRules = config('tenancy.enable_domain_routes', false)
            ? ['required', 'string', 'max:255', 'unique:domains,domain']
            : ['nullable', 'string', 'max:255', 'unique:domains,domain'];

        $validated = $request->validate([
            'id' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:tenants,id'],
            'name' => ['required', 'string', 'max:255'],
            'domain' => $domainRules,
            'account_type' => ['nullable', 'in:'.implode(',', AccountType::TYPES)],
        ]);

        $attributes = [
            'id' => $validated['id'],
            'name' => $validated['name'],
        ];

        if (AccountType::ready()) {
            $attributes['account_type'] = AccountType::normalize($validated['account_type'] ?? null);
        }

        $tenant = Tenant::create($attributes);
        AccountType::flush();

        if (! empty($validated['domain'])) {
            $tenant->domains()->create([
                'domain' => $validated['domain'],
            ]);
        }

        return redirect()
            ->route('central.tenants.index')
            ->with('success', __('Tenant ":name" created. Database: tenant:id', [
                'name' => $tenant->name,
                'id' => $tenant->id,
            ]));
    }

    public function updateAccountType(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'account_type' => ['required', 'in:'.implode(',', AccountType::TYPES)],
        ]);

        if (! AccountType::ready()) {
            return back()->with('error', __('Run php artisan migrate --force to enable account types.'));
        }

        $tenant->forceFill(['account_type' => $data['account_type']])->save();
        AccountType::flush();

        return back()->with('success', __(':workspace is now classified as :type.', [
            'workspace' => $tenant->name ?: $tenant->id,
            'type' => strtolower(AccountType::label($data['account_type'])),
        ]));
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $name = $tenant->name ?? $tenant->id;
        $tenant->delete();

        return redirect()
            ->route('central.tenants.index')
            ->with('success', __('Tenant ":name" and its database were removed.', ['name' => $name]));
    }
}
