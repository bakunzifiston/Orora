@extends('layouts.central')

@section('title', 'Tenants')

@section('content')
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2 style="margin: 0;">Tenants</h2>
            <a href="{{ route('central.tenants.create') }}" class="btn btn-primary">New tenant</a>
        </div>

        @if ($tenants->isEmpty())
            <p class="muted">No tenants yet. Create one to provision a MySQL database and domain.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Domains</th>
                        <th>Database</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tenants as $tenant)
                        <tr>
                            <td><code>{{ $tenant->id }}</code></td>
                            <td>{{ $tenant->name ?? '—' }}</td>
                            <td>
                                @foreach ($tenant->domains as $domain)
                                    <a href="http://{{ $domain->domain }}:8000" target="_blank" rel="noopener">{{ $domain->domain }}</a>@if (! $loop->last), @endif
                                @endforeach
                            </td>
                            <td><code>tenant{{ $tenant->id }}</code></td>
                            <td>
                                <form action="{{ route('central.tenants.destroy', $tenant) }}" method="post" style="display: inline;" onsubmit="return confirm('Delete this tenant and its database?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
