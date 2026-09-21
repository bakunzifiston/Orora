@extends('layouts.admin')

@section('title', __('Contact inbox'))

@section('content')
    <div class="farm-dash farms-page health-page admin-dash admin-contact">
        <div class="dash-ops-toolbar farms-page__toolbar admin-dash__toolbar">
            <div class="admin-dash__header-text dash-ops-toolbar__brand">
                <h1 class="admin-dash__title">{{ __('Contact inbox') }}</h1>
                <p class="admin-dash__period">{{ __('Messages submitted from the public contact form.') }}</p>
            </div>
        </div>

        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <div class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'mail'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Messages') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['total'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'mail'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('New') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['new'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--production">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'mail'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Read') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['read'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--sales">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'mail'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Replied') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['replied'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="farm-dash__section" aria-label="{{ __('Inbox') }}">
            <article class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Inbox') }}</h2>
                        <p class="farm-panel__desc">{{ number_format($messages->total()) }} {{ __('total') }}</p>
                    </div>
                </header>

                @if (empty($contactReady))
                    <p class="dash-empty">{{ __('Contact messages table is not set up yet. Run php artisan migrate --force.') }}</p>
                @elseif ($messages->isEmpty())
                    <p class="dash-empty">{{ __('No contact messages yet.') }}</p>
                @else
                    <div class="dash-table-wrap">
                        <table class="health-table">
                            <thead>
                                <tr>
                                    <th>{{ __('From') }}</th>
                                    <th>{{ __('Subject') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Received') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($messages as $message)
                                    @php
                                        $statusTone = match ($message->status) {
                                            'new' => 'warn',
                                            'read' => 'muted',
                                            'replied' => 'ok',
                                            'archived' => 'muted',
                                            default => 'muted',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="health-table__primary">
                                                @include('modules.health.partials.table-icon', ['icon' => 'mail', 'tone' => $statusTone])
                                                <div class="health-table__stack">
                                                    <span class="health-table__title">{{ $message->name }}</span>
                                                    <span class="health-table__meta">{{ $message->email }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="health-table__stack">
                                                <span class="health-table__value">{{ $message->subject }}</span>
                                                <span class="health-table__meta">{{ Str::limit($message->message, 100) }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="health-table__value">{{ $message->inquiry_type ?? 'general' }}</span>
                                        </td>
                                        <td>
                                            <span class="health-table__meta">{{ $message->created_at?->format('M j, Y g:i A') }}</span>
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('central.contact-messages.update', $message) }}" class="admin-contact__status-form">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" class="admin-contact__status-select" onchange="this.form.submit()" aria-label="{{ __('Update status') }}">
                                                    @foreach (['new', 'read', 'replied', 'archived'] as $status)
                                                        <option value="{{ $status }}" @selected($message->status === $status)>{{ ucfirst($status) }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="dash-pagination">{{ $messages->links() }}</div>
                @endif
            </article>
        </section>
    </div>
@endsection

@push('styles')
    @include('central.dashboard.partials.styles')
    <style>
        .admin-contact .farm-kpi:not(a) {
            cursor: default;
        }
        .admin-contact .farm-kpi:not(a):hover {
            transform: none;
            box-shadow: none;
        }
        .admin-contact__status-form {
            margin: 0;
        }
        .admin-contact__status-select {
            margin: 0;
            width: auto;
            min-width: 7.5rem;
            padding: 0.4rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            background: #fff;
            color: var(--farm-ink, #0f172a);
        }
    </style>
@endpush
