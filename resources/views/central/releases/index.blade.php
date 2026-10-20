@extends('layouts.admin')

@section('title', __('Releases'))

@section('content')
    <div class="farm-dash farms-page health-page admin-dash">
        <div class="dash-ops-toolbar farms-page__toolbar admin-dash__toolbar">
            <div class="admin-dash__header-text dash-ops-toolbar__brand">
                <h1 class="admin-dash__title">{{ __('Releases') }}</h1>
                <p class="admin-dash__period">{{ __('Release notes shown on tenants\' What\'s New page. Publishing notifies the chosen tenants once.') }}</p>
            </div>
            <a href="{{ route('central.releases.create') }}" class="dash-btn-save">+ {{ __('New release') }}</a>
        </div>

        @if (session('success'))
            <div class="dash-alert dash-alert--success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="dash-alert dash-alert--error">{{ $errors->first() }}</div>
        @endif
        @unless ($canPublish)
            <div class="dash-alert">{{ __('You can create and edit drafts. A super admin must publish them.') }}</div>
        @endunless

        <section class="farm-dash__section">
            <article class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('All releases') }}</h2>
                        <p class="farm-panel__desc">{{ number_format($releases->total()) }} {{ __('total') }}</p>
                    </div>
                    <form method="GET" action="{{ route('central.releases.index') }}">
                        <select name="status" onchange="this.form.submit()" aria-label="{{ __('Status') }}" style="padding: 0.45rem 0.6rem; border: 1px solid #cbd5e1; border-radius: 0.5rem;">
                            <option value="">{{ __('All statuses') }}</option>
                            @foreach (config('releases.statuses') as $key => $label)
                                <option value="{{ $key }}" @selected($selectedStatus === $key)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </form>
                </header>

                @if ($releases->isEmpty())
                    <p class="dash-empty">{{ __('No releases yet. Create one after your next deployment.') }}</p>
                @else
                    <div class="dash-table-wrap">
                        <table class="health-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Release') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Release date') }}</th>
                                    <th>{{ __('Audience') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Notified') }}</th>
                                    <th class="health-table__actions">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($releases as $release)
                                    @php $stats = $notified[$release->id] ?? null; @endphp
                                    <tr>
                                        <td>
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $release->title }}</span>
                                                <span class="health-table__meta">{{ $release->version }}{{ $release->moduleLabel() ? ' · '.$release->moduleLabel() : '' }}</span>
                                            </div>
                                        </td>
                                        <td><span class="health-table__pill health-table__pill--{{ $release->categoryTone() }}">{{ __($release->categoryLabel()) }}</span></td>
                                        <td><span class="health-table__value">{{ $release->released_on->format('M j, Y') }}</span></td>
                                        <td>
                                            <span class="health-table__value">
                                                {{ $release->audience === 'all' ? __('All tenants') : trans_choice('{1} 1 tenant|[2,*] :count tenants', $release->tenants_count, ['count' => $release->tenants_count]) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="health-table__stack">
                                                <span>
                                                    <span class="health-table__pill health-table__pill--{{ $release->isPublished() ? 'ok' : 'muted' }}">{{ __(config('releases.statuses.'.$release->status)) }}</span>
                                                </span>
                                                @if ($release->published_at)
                                                    <span class="health-table__meta">{{ $release->published_at->format('M j, Y H:i') }}{{ $release->publisher ? ' · '.$release->publisher->name : '' }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="health-table__value">
                                                @if ($stats)
                                                    {{ number_format($stats->total) }} <span class="health-table__meta">· {{ number_format($stats->seen) }} {{ __('read') }}</span>
                                                @else
                                                    —
                                                @endif
                                            </span>
                                        </td>
                                        <td class="health-table__actions">
                                            <div class="health-table__action-btns">
                                                @if (! $release->isPublished() && $canPublish)
                                                    <form method="POST" action="{{ route('central.releases.publish', $release) }}" onsubmit="return confirm(@js(__('Publish this release and notify its tenants?')));">
                                                        @csrf
                                                        <button type="submit" class="dash-farm-card__btn">{{ __('Publish') }}</button>
                                                    </form>
                                                @endif
                                                <a href="{{ route('central.releases.edit', $release) }}" class="dash-farm-card__btn">{{ __('Edit') }}</a>
                                                @if ($canPublish)
                                                    <form method="POST" action="{{ route('central.releases.destroy', $release) }}" onsubmit="return confirm(@js($release->isPublished() ? __('Delete this release? It will disappear from What\'s New and its notifications will be removed.') : __('Delete this draft?')));">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dash-farm-card__btn dash-farm-card__btn--danger">{{ __('Delete') }}</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="dash-pagination">{{ $releases->links() }}</div>
                @endif
            </article>
        </section>
    </div>
@endsection

@push('styles')
    @include('central.dashboard.partials.styles')
@endpush
