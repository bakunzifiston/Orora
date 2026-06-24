@extends('layouts.admin')

@section('title', 'New tenant')

@section('content')
    @include('modules.partials.header', [
        'title' => 'New tenant',
        'subtitle' => 'Farmers normally sign up via /register — use this form only for manual provisioning.',
        'backRoute' => 'central.tenants.index',
    ])

    <div class="dash-panel dash-profile-panel">
        <form action="{{ route('central.tenants.store') }}" method="post">
            @csrf

            <div class="dash-form-grid">
                <div class="dash-form-field">
                    <label for="id">Tenant ID</label>
                    <input type="text" name="id" id="id" value="{{ old('id') }}" placeholder="acme" required pattern="[a-zA-Z0-9_-]+">
                    <p class="dash-field-hint">Unique identifier for this farm workspace.</p>
                </div>

                <div class="dash-form-field">
                    <label for="name">Display name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Acme Corp" required>
                </div>

                @if (config('tenancy.enable_domain_routes', false))
                    <div class="dash-form-field dash-form-field--full">
                        <label for="domain">Domain</label>
                        <input type="text" name="domain" id="domain" value="{{ old('domain') }}" placeholder="acme.localhost" required>
                        <p class="dash-field-hint">Add this host to <code>/etc/hosts</code> pointing to <code>127.0.0.1</code>.</p>
                    </div>
                @endif
            </div>

            <div class="dash-form-actions" style="margin-top: 1.25rem;">
                <button type="submit" class="dash-btn-save">Create tenant</button>
                <a href="{{ route('central.tenants.index') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
