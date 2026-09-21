@extends('layouts.dashboard')

@section('title', __('Certificates'))

@section('content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Certificates'),
            'createRoute' => 'certificates.create',
            'createLabel' => '+ '.__('Add certificate'),
        ])
        @include('modules.partials.flash')

        @if ($certificates->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'certificate'])
                </div>
                <p class="dash-empty">{{ __('No certificates yet.') }}</p>
                <a href="{{ route('certificates.create') }}" class="dash-btn-save">{{ __('Add certificate') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Certificate') }}</th>
                                <th>{{ __('Farm') }}</th>
                                <th>{{ __('Issued') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($certificates as $certificate)
                                @php
                                    $statusTone = match (strtolower((string) $certificate->status)) {
                                        'valid', 'active' => 'ok',
                                        'expiring', 'pending' => 'warn',
                                        'expired', 'revoked' => 'bad',
                                        default => 'muted',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'certificate', 'tone' => $statusTone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ ucfirst($certificate->certificate_type) }}</span>
                                                <span class="health-table__meta">
                                                    {{ $certificate->certificate_number ?: __('No number') }}
                                                    @if ($certificate->expires_on)
                                                        <span aria-hidden="true">·</span>
                                                        {{ __('Expires') }} {{ $certificate->expires_on->format('M j, Y') }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $certificate->farm->name }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $certificate->issued_on->format('M j, Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $statusTone }}">{{ ucfirst($certificate->status) }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $certificate,
                                            'editRoute' => 'certificates.edit',
                                            'destroyRoute' => 'certificates.destroy',
                                            'deleteConfirm' => __('Delete this certificate?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $certificates->links() }}</div>
        @endif
    </div>
@endsection
