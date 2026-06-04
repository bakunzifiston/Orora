<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::with('domains')->orderBy('created_at', 'desc')->get();

        return view('central.tenants.index', compact('tenants'));
    }

    public function create(): View
    {
        return view('central.tenants.create');
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
        ]);

        $tenant = Tenant::create([
            'id' => $validated['id'],
            'name' => $validated['name'],
        ]);

        if (! empty($validated['domain'])) {
            $tenant->domains()->create([
                'domain' => $validated['domain'],
            ]);
        }

        return redirect()
            ->route('central.tenants.index')
            ->with('success', "Tenant \"{$tenant->name}\" created. Database: tenant{$tenant->id}");
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $name = $tenant->name ?? $tenant->id;
        $tenant->delete();

        return redirect()
            ->route('central.tenants.index')
            ->with('success', "Tenant \"{$name}\" and its database were removed.");
    }
}
