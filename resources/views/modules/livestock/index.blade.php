@extends('layouts.dashboard')

@section('title', 'Livestock')

@section('content')
    @include('modules.partials.header', [
        'title' => 'Livestock',
        'subtitle' => 'Herd and flock groups by farm with production and feeding details.',
        'createRoute' => 'livestock.create',
        'createLabel' => '+ Add livestock',
    ])
    @include('modules.partials.flash')

    @component('modules.partials.index-toolbar')
        @slot('stats')
            <div class="dash-health-stats">
                <div class="dash-stat-card">
                    <div>
                        <div class="dash-stat-label">Total groups</div>
                        <div class="dash-stat-value">{{ number_format($stats['total']) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'livestock'])
                </div>
                <div class="dash-stat-card">
                    <div>
                        <div class="dash-stat-label">Active</div>
                        <div class="dash-stat-value accent">{{ number_format($stats['active']) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'health'])
                </div>
                <div class="dash-stat-card">
                    <div>
                        <div class="dash-stat-label">Total head count</div>
                        <div class="dash-stat-value">{{ number_format($stats['head_count']) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'feeding'])
                </div>
                <div class="dash-stat-card">
                    <div>
                        <div class="dash-stat-label">Animals registered</div>
                        <div class="dash-stat-value">{{ number_format($stats['animals']) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'animal'])
                </div>
            </div>
        @endslot
        @slot('filters')
            <form method="GET" class="dash-form-grid">
                <div class="dash-form-field">
                    <label for="farm_id">Farm</label>
                    <select name="farm_id" id="farm_id" onchange="this.form.submit()">
                        <option value="">All farms</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" @selected(request('farm_id') == $farm->id)>{{ $farm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="status">Status</label>
                    <select name="status" id="status" onchange="this.form.submit()">
                        <option value="">All statuses</option>
                        @foreach (config('modules.record_statuses') as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        @endslot
    @endcomponent

    @if ($livestock->isEmpty())
        <div class="dash-panel dash-entity-empty">
            <div class="dash-entity-empty__icon" aria-hidden="true">
                @include('layouts.partials.dashboard-nav-icon', ['icon' => 'livestock'])
            </div>
            <p class="dash-empty">No livestock groups yet.</p>
            <a href="{{ route('livestock.create') }}" class="dash-btn-save">Add livestock group</a>
        </div>
    @else
        <div class="dash-entity-grid">
            @foreach ($livestock as $group)
                @include('modules.livestock._livestock-card', ['group' => $group])
            @endforeach
        </div>
        <div class="dash-pagination">{{ $livestock->links() }}</div>
    @endif
@endsection
