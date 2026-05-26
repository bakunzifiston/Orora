@extends('layouts.central')

@section('title', 'Central')

@section('content')
    <div class="card">
        <h2 style="margin-top: 0;">Multi-tenant control plane</h2>
        <p class="muted">
            This app uses <strong>database-per-tenant</strong> isolation on MySQL.
            Each tenant gets its own database (<code>tenant{id}</code>) and is reached by domain.
        </p>
        <p>
            <a href="{{ route('central.tenants.index') }}" class="btn btn-primary">Manage tenants</a>
        </p>
    </div>

    <div class="card">
        <h3 style="margin-top: 0;">Local development</h3>
        <ol class="muted">
            <li>Add tenant domains to <code>/etc/hosts</code> (e.g. <code>127.0.0.1 acme.localhost</code>).</li>
            <li>Create a tenant with domain <code>acme.localhost</code>.</li>
            <li>Visit <code>http://acme.localhost:8000</code> while <code>php artisan serve</code> is running.</li>
        </ol>
    </div>
@endsection
