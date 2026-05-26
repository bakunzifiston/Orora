@extends('layouts.dashboard')

@section('title', 'Certificates')

@section('content')
    @include('modules.partials.header', [
        'title' => 'Certificates',
        'subtitle' => 'Health, vaccination, and compliance documents.',
        'createRoute' => 'certificates.create',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($certificates->isEmpty())
            <p class="dash-empty">No certificates. <a href="{{ route('certificates.create') }}">Add certificate</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Number</th>
                            <th>Farm</th>
                            <th>Issued</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($certificates as $certificate)
                            <tr>
                                <td><strong>{{ ucfirst($certificate->certificate_type) }}</strong></td>
                                <td>{{ $certificate->certificate_number ?? '—' }}</td>
                                <td>{{ $certificate->farm->name }}</td>
                                <td>{{ $certificate->issued_on->format('M j, Y') }}</td>
                                <td>{{ $certificate->expires_on?->format('M j, Y') ?? '—' }}</td>
                                <td><span class="dash-badge">{{ ucfirst($certificate->status) }}</span></td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $certificate,
                                        'editRoute' => 'certificates.edit',
                                        'destroyRoute' => 'certificates.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $certificates->links() }}</div>
        @endif
    </div>
@endsection
