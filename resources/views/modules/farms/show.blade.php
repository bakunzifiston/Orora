@extends('layouts.dashboard')

@section('title', $farm->name)

@section('content')
    @php
        $statusClass = match ($farm->status) {
            'active' => 'dash-farm-card__badge--active',
            'pending' => 'dash-farm-card__badge--pending',
            'suspended' => 'dash-farm-card__badge--suspended',
            default => 'dash-farm-card__badge--inactive',
        };
        $ownershipLabel = config('modules.ownership_types')[$farm->ownership_type] ?? ucfirst(str_replace('_', ' ', (string) $farm->ownership_type));
    @endphp

    @include('modules.partials.header', [
        'title' => $farm->name,
        'subtitle' => $farm->registration_number,
        'backRoute' => 'farms.index',
    ])
    @include('modules.partials.flash')

    <div class="dash-page-header__actions" style="margin: -0.75rem 0 1.25rem; display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <span class="dash-farm-card__badge {{ $statusClass }}">{{ ucfirst($farm->status) }}</span>
        <a href="{{ route('farms.edit', $farm) }}" class="dash-btn-save">{{ __('Edit farm') }}</a>
    </div>

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div class="dash-stat-label">{{ __('Farm size') }}</div>
            <div class="dash-stat-value">{{ $farm->farm_size_hectares !== null ? number_format($farm->farm_size_hectares, 2).' ha' : '—' }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">{{ __('Livestock groups') }}</div>
            <div class="dash-stat-value accent">{{ number_format($farm->livestock_count) }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">{{ __('Animals') }}</div>
            <div class="dash-stat-value">{{ number_format($farm->animals_count) }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">{{ __('Registered') }}</div>
            <div class="dash-stat-value" style="font-size: 1rem;">{{ $farm->registration_date?->format('M j, Y') ?? '—' }}</div>
        </div>
    </div>

    <div class="dash-health-grid">
        <div class="dash-panel">
            <div class="dash-panel-title">{{ __('Registration') }}</div>
            <dl class="dash-farm-detail">
                @include('modules.farms._detail-row', ['label' => __('Farm name'), 'value' => $farm->name])
                @include('modules.farms._detail-row', ['label' => __('Registration no.'), 'value' => $farm->registration_number])
                @include('modules.farms._detail-row', ['label' => __('Registration date'), 'value' => $farm->registration_date?->format('M j, Y')])
                @include('modules.farms._detail-row', ['label' => __('Status'), 'value' => ucfirst($farm->status)])
                @include('modules.farms._detail-row', ['label' => __('Ownership'), 'value' => $ownershipLabel])
            </dl>
        </div>

        <div class="dash-panel">
            <div class="dash-panel-title">{{ __('Location') }}</div>
            <dl class="dash-farm-detail">
                @include('modules.farms._detail-row', ['label' => __('Country'), 'value' => $farm->country])
                @include('modules.farms._detail-row', ['label' => __('Province'), 'value' => $farm->province])
                @include('modules.farms._detail-row', ['label' => __('District'), 'value' => $farm->district])
                @include('modules.farms._detail-row', ['label' => __('Sector'), 'value' => $farm->sector])
                @include('modules.farms._detail-row', ['label' => __('Cell'), 'value' => $farm->cell])
                @include('modules.farms._detail-row', ['label' => __('Village'), 'value' => $farm->village])
            </dl>
        </div>

        <div class="dash-panel">
            <div class="dash-panel-title">{{ __('Owner & contact') }}</div>
            <dl class="dash-farm-detail">
                @if ($farm->requiresOrganizationDetails())
                    @include('modules.farms._detail-row', ['label' => __('Organization'), 'value' => $farm->organization_name])
                    @include('modules.farms._detail-row', ['label' => __('Tax ID'), 'value' => $farm->tax_id])
                @else
                    @include('modules.farms._detail-row', ['label' => __('Owner'), 'value' => $farm->owner_full_name])
                    @include('modules.farms._detail-row', ['label' => __('National ID'), 'value' => $farm->owner_national_id])
                    @include('modules.farms._detail-row', ['label' => __('Date of birth'), 'value' => $farm->owner_dob?->format('M j, Y')])
                    @include('modules.farms._detail-row', ['label' => __('Gender'), 'value' => $farm->owner_gender ? (config('modules.animal_genders')[$farm->owner_gender] ?? ucfirst($farm->owner_gender)) : null])
                @endif
                @include('modules.farms._detail-row', ['label' => __('Phone'), 'value' => $farm->contact_phone])
                @include('modules.farms._detail-row', ['label' => __('Email'), 'value' => $farm->contact_email])
                @include('modules.farms._detail-row', ['label' => __('Emergency'), 'value' => $farm->owner_emergency_contact])
            </dl>
        </div>
    </div>

    @if ($farm->requiresMembers() && $farm->members->isNotEmpty())
        <div class="dash-panel" style="margin-top: 1rem;">
            <div class="dash-panel-title">{{ __('Members') }}</div>
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Phone') }}</th>
                            <th>{{ __('Gender') }}</th>
                            <th>{{ __('Date of birth') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($farm->members as $member)
                            <tr>
                                <td>{{ trim($member->first_name.' '.$member->last_name) }}</td>
                                <td>{{ $member->phone ?: '—' }}</td>
                                <td>{{ $member->gender ? (config('modules.animal_genders')[$member->gender] ?? ucfirst($member->gender)) : '—' }}</td>
                                <td>{{ $member->date_of_birth?->format('M j, Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($farm->notes)
        <div class="dash-panel" style="margin-top: 1rem;">
            <div class="dash-panel-title">{{ __('Notes') }}</div>
            <p style="margin: 0; font-size: 0.875rem; color: #374151; white-space: pre-wrap;">{{ $farm->notes }}</p>
        </div>
    @endif
@endsection
