@extends('layouts.admin')

@section('title', $user->name)

@section('content')
    @php
        $hasFarm = $farms->isNotEmpty();
        $related = $related ?? [];
        $relatedTotal = $relatedTotal ?? collect($related)->sum('count');
        $countFor = fn (string $key) => (int) (collect($related)->firstWhere('key', $key)['count'] ?? 0);
    @endphp

    <div class="farm-dash farms-page health-page admin-dash admin-users">
        <div class="admin-users__top">
            <div class="admin-dash__header-text">
                <a href="{{ route('central.accounts.index') }}" class="dash-back-link">← {{ __('Users') }}</a>
                <div class="admin-users__title-row">
                    <h1 class="admin-dash__title">{{ $user->name }}</h1>
                    <span class="health-table__pill health-table__pill--{{ $hasFarm ? 'ok' : 'warn' }}">
                        {{ $hasFarm ? __('Has farm') : __('No farm') }}
                    </span>
                </div>
                <p class="admin-dash__period">{{ $user->email }}</p>
            </div>
            @include('central.accounts.partials.delete-form', [
                'user' => $user,
                'buttonClass' => 'dash-farm-card__btn dash-farm-card__btn--danger',
                'label' => __('Delete user'),
                'confirm' => 'Delete '.$user->name.' and all related records (farms, animals, milk, sales, and the rest of this workspace)? This cannot be undone.',
            ])
        </div>

        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <div class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'chart'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Related records') }}</div>
                        <div class="farm-kpi__value">{{ number_format($relatedTotal) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--farms">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'farm'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Farms') }}</div>
                        <div class="farm-kpi__value">{{ number_format($countFor('farms')) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--production">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Animals') }}</div>
                        <div class="farm-kpi__value">{{ number_format($countFor('animals')) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--sales">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Sales') }}</div>
                        <div class="farm-kpi__value">{{ number_format($countFor('sales')) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--revenue">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'milk'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Milk sessions') }}</div>
                        <div class="farm-kpi__value">{{ number_format($countFor('milk')) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'customer'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Joined') }}</div>
                        <div class="farm-kpi__value admin-users__joined">{{ $user->created_at?->format('M j, Y') ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="farm-dash__section" aria-label="{{ __('Account details') }}">
            <div class="farm-dash__charts farm-dash__charts--2">
                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Account') }}</h2>
                        </div>
                    </header>
                    <dl class="admin-users__detail">
                        @include('modules.farms._detail-row', ['label' => __('Name'), 'value' => $user->name])
                        @include('modules.farms._detail-row', ['label' => __('Email'), 'value' => $user->email])
                        @include('modules.farms._detail-row', ['label' => __('Verified'), 'value' => $user->email_verified_at?->format('M j, Y') ?: __('Not verified')])
                        @include('modules.farms._detail-row', ['label' => __('Joined'), 'value' => $user->created_at?->format('M j, Y g:i A')])
                        @include('modules.farms._detail-row', ['label' => __('Last sign in'), 'value' => $user->last_login_at?->format('M j, Y g:i A') ?: __('Never')])
                    </dl>
                </article>

                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Workspace') }}</h2>
                        </div>
                    </header>
                    <dl class="admin-users__detail">
                        @include('modules.farms._detail-row', ['label' => __('Name'), 'value' => $user->tenant?->name])
                        @include('modules.farms._detail-row', ['label' => __('ID'), 'value' => $user->tenant_id])
                    </dl>
                </article>
            </div>
        </section>

        <section class="farm-dash__section" aria-label="{{ __('Related records') }}">
            <article class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Related records') }}</h2>
                        <p class="farm-panel__desc">{{ number_format($relatedTotal) }} {{ __('total') }}</p>
                    </div>
                </header>
                @php
                    $relatedVisible = collect($related)->filter(fn (array $item) => ($item['count'] ?? 0) > 0)->values();
                @endphp
                @if ($relatedVisible->isEmpty())
                    <p class="dash-empty">{{ __('No related records yet.') }}</p>
                @else
                    <ul class="admin-users__related">
                        @foreach ($relatedVisible as $item)
                            <li class="admin-users__related-item">
                                <span class="admin-users__related-icon" aria-hidden="true">
                                    @include('layouts.partials.dashboard-nav-icon', ['icon' => $item['icon']])
                                </span>
                                <span class="admin-users__related-label">{{ $item['label'] }}</span>
                                <span class="admin-users__related-count">{{ number_format($item['count']) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </article>
        </section>

        <section class="farm-dash__section" aria-label="{{ __('Farms') }}">
            <article class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Farms') }}</h2>
                        <p class="farm-panel__desc">{{ number_format($farms->count()) }} {{ __('total') }}</p>
                    </div>
                </header>
                @if ($farms->isEmpty())
                    <p class="dash-empty">{{ __('This user has not registered a farm yet.') }}</p>
                @else
                    <div class="dash-table-wrap">
                        <table class="health-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Farm') }}</th>
                                    <th>{{ __('Location') }}</th>
                                    <th>{{ __('Groups') }}</th>
                                    <th>{{ __('Animals') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($farms as $farm)
                                    @php
                                        $farmTone = match ($farm->status) {
                                            'active' => 'ok',
                                            'pending' => 'warn',
                                            default => 'muted',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="health-table__primary">
                                                @include('modules.health.partials.table-icon', ['icon' => 'farm', 'tone' => $farmTone])
                                                <a href="{{ route('central.farms.show', $farm) }}" class="health-table__title health-table__title--link">{{ $farm->name }}</a>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="health-table__value">{{ Str::limit($farm->location_label ?: '—', 42) }}</span>
                                        </td>
                                        <td>
                                            <span class="health-table__value">{{ number_format($farm->livestock_count) }}</span>
                                        </td>
                                        <td>
                                            <span class="health-table__value">{{ number_format($farm->animals_count) }}</span>
                                        </td>
                                        <td>
                                            @if ($farm->status)
                                                <span class="health-table__pill health-table__pill--{{ $farmTone }}">{{ $farm->status }}</span>
                                            @else
                                                <span class="health-table__meta">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>
        </section>
    </div>
@endsection

@push('styles')
    @include('central.dashboard.partials.styles')
    <style>
        .admin-users__top {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem 1.5rem;
        }
        .admin-users__title-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.65rem;
            margin-top: 0.35rem;
        }
        .admin-users__joined {
            font-size: 1.15rem;
        }
        .admin-users__detail {
            margin: 0;
            padding: 0 1.25rem 1.25rem;
        }
        .admin-users__related {
            list-style: none;
            margin: 0;
            padding: 0 1.25rem 1.25rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(11.5rem, 1fr));
            gap: 0.55rem;
        }
        .admin-users__related-item {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            min-width: 0;
            padding: 0.55rem 0.7rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.65rem;
            background: #fafafa;
        }
        .admin-users__related-icon {
            display: inline-flex;
            width: 1rem;
            height: 1rem;
            flex: 0 0 auto;
            color: var(--orora-sidebar, #002B2B);
        }
        .admin-users__related-icon svg {
            width: 100%;
            height: 100%;
        }
        .admin-users__related-label {
            flex: 1 1 auto;
            min-width: 0;
            font-size: 0.75rem;
            color: var(--farm-muted, #64748b);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .admin-users__related-count {
            flex: 0 0 auto;
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--farm-ink, #0f172a);
        }
        .admin-users .farm-kpi:not(a) {
            cursor: default;
        }
        .admin-users .farm-kpi:not(a):hover {
            transform: none;
            box-shadow: none;
        }
        .admin-users .dash-back-link {
            display: inline-block;
            margin-bottom: 0.15rem;
        }
    </style>
@endpush
