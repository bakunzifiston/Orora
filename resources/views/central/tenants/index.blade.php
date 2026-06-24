@extends('layouts.admin')

@section('title', 'Tenants')

@section('content')
    @include('modules.partials.header', [
        'title' => 'Tenants',
        'subtitle' => 'Farmer workspaces registered on the platform.',
        'createRoute' => 'central.tenants.create',
        'createLabel' => '+ New tenant',
    ])

    <div class="dash-panel">
        @if ($tenants->isEmpty())
            <p class="dash-empty">No tenants yet. Farmers can register on your site, or <a href="{{ route('central.tenants.create') }}">create one manually</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Domains</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tenants as $tenant)
                            <tr>
                                <td><code>{{ $tenant->id }}</code></td>
                                <td>{{ $tenant->name ?? '—' }}</td>
                                <td>
                                    @if ($tenant->domains->isEmpty())
                                        <span style="color: var(--orora-gray);">—</span>
                                    @else
                                        @foreach ($tenant->domains as $domain)
                                            {{ $domain->domain }}@if (! $loop->last), @endif
                                        @endforeach
                                    @endif
                                </td>
                                <td>{{ $tenant->created_at?->format('M j, Y') }}</td>
                                <td>
                                    <div class="dash-table-actions">
                                        <form action="{{ route('central.tenants.destroy', $tenant) }}" method="post" onsubmit="return confirm('Delete this tenant? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="color: #b91c1c;">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
