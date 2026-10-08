@extends('layouts.dashboard')

@section('title', __('Users & access'))

@section('content')
    <div class="farm-dash farms-page employees-page workspace-users-page">
        @include('modules.partials.header', [
            'title' => __('Users & access'),
            'subtitle' => __('Manage who can sign in and which modules they can use.'),
            'createRoute' => 'workspace.users.create',
            'createLabel' => '+ '.__('Add user'),
        ])
        @include('modules.partials.flash')

        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <div class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'employee'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Total users') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['total']) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--production">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Active') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['active']) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--farms">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'shield'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Admins') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['admins']) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'customer'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Members') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['members']) }}</div>
                    </div>
                </div>
            </div>
        </section>

        @if ($users->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'employee'])
                </div>
                <p class="dash-empty">{{ __('No users yet.') }}</p>
                <a href="{{ route('workspace.users.create') }}" class="dash-btn-save">{{ __('Add user') }}</a>
            </div>
        @else
            <div class="dash-panel employees-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="employees-table">
                        <thead>
                            <tr>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Role') }}</th>
                                <th>{{ __('Access') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="employees-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                @php
                                    $initials = collect(preg_split('/\s+/', trim((string) $user->name)) ?: [])
                                        ->filter()
                                        ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
                                        ->take(2)
                                        ->join('') ?: strtoupper(substr((string) $user->email, 0, 2));
                                    $statusTone = $user->is_active ? 'ok' : 'muted';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="employees-table__person">
                                            <span class="employees-table__avatar" aria-hidden="true">{{ $initials }}</span>
                                            <div class="employees-table__person-text">
                                                <span class="employees-table__name">{{ $user->name }}</span>
                                                <span class="employees-table__meta">{{ $user->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="employees-table__value">{{ $user->roleLabel() }}</span>
                                    </td>
                                    <td>
                                        @if ($user->isWorkspaceAdmin())
                                            <span class="employees-table__value">{{ __('All modules') }}</span>
                                        @elseif ($user->module_permissions_count > 0)
                                            <span class="employees-table__value">
                                                {{ trans_choice(':count module|:count modules', $user->module_permissions_count, ['count' => $user->module_permissions_count]) }}
                                            </span>
                                        @else
                                            <span class="employees-table__meta">{{ __('No modules') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="employees-table__pill employees-table__pill--{{ $statusTone }}">
                                            {{ $user->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                    </td>
                                    <td class="employees-table__actions">
                                        <div class="employees-table__action-btns">
                                            <a href="{{ route('workspace.users.edit', $user) }}" class="dash-btn-save dash-btn-save--sm">{{ __('Edit') }}</a>
                                            @if ($user->id !== auth()->id())
                                                @if ($user->is_active)
                                                    <form method="POST" action="{{ route('workspace.users.deactivate', $user) }}" onsubmit="return confirm(@js(__('Deactivate this user? They will not be able to sign in.')));">
                                                        @csrf
                                                        <button type="submit" class="dash-farm-card__btn dash-farm-card__btn--danger">{{ __('Deactivate') }}</button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('workspace.users.activate', $user) }}">
                                                        @csrf
                                                        <button type="submit" class="dash-btn-save dash-btn-save--sm">{{ __('Activate') }}</button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
