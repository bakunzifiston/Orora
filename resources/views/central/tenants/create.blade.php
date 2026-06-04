@extends('layouts.central')

@section('title', 'New tenant')

@section('content')
    <div class="card">
        <h2 style="margin-top: 0;">Create tenant</h2>
        <p class="muted">A new MySQL database and tenant migrations will run automatically. Farmers normally sign up via <strong>/register</strong> on your public URL — use this form only for manual provisioning.</p>

        <form action="{{ route('central.tenants.store') }}" method="post">
            @csrf

            <label for="id">Tenant ID</label>
            <input type="text" name="id" id="id" value="{{ old('id') }}" placeholder="acme" required pattern="[a-zA-Z0-9_-]+">
            <p class="muted" style="margin-top: -0.75rem;">Used for database name: <code>tenant{id}</code></p>

            <label for="name">Display name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Acme Corp" required>

            @if (config('tenancy.enable_domain_routes', false))
                <label for="domain">Domain</label>
                <input type="text" name="domain" id="domain" value="{{ old('domain') }}" placeholder="acme.localhost" required>
                <p class="muted" style="margin-top: -0.75rem;">Add this host to <code>/etc/hosts</code> pointing to <code>127.0.0.1</code>.</p>
            @endif

            <button type="submit" class="btn btn-primary">Create tenant</button>
            <a href="{{ route('central.tenants.index') }}" class="muted" style="margin-left: 1rem;">Cancel</a>
        </form>
    </div>
@endsection
