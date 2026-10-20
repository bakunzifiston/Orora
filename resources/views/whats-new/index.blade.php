@extends('layouts.dashboard')

@section('title', __("What's new"))

@section('content')
    <div class="farm-dash farms-page health-page">
        <div class="dash-page-header">
            <div>
                <h1 class="dash-welcome" style="margin-bottom: 0.35rem;">{{ __("What's new") }}</h1>
                <p style="color: #808080; font-size: 0.875rem;">{{ __('New features, improvements, fixes and maintenance notices for OroraFarm.') }}</p>
            </div>
            <div class="dash-page-header__actions">
                <a href="{{ route('notifications.index') }}" class="dash-btn-cancel">{{ __('Notifications') }}</a>
            </div>
        </div>

        <div class="dash-ops-toolbar farms-page__toolbar">
            <div class="dash-ops-toolbar__controls farms-page__filters">
                <div class="notif-tabs" aria-label="{{ __('Filter by category') }}">
                    <a href="{{ route('whats-new.index') }}" class="notif-tabs__tab {{ $selectedCategory === null ? 'is-active' : '' }}">{{ __('All') }}</a>
                    @foreach (config('releases.categories') as $key => $label)
                        <a href="{{ route('whats-new.index', ['category' => $key]) }}" class="notif-tabs__tab {{ $selectedCategory === $key ? 'is-active' : '' }}">{{ __($label) }}</a>
                    @endforeach
                </div>
            </div>
        </div>

        @if ($releases->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <p class="dash-empty">{{ $selectedCategory ? __('No updates in this category yet.') : __('No updates have been published yet. Check back after the next release.') }}</p>
            </div>
        @else
            <ol class="whatsnew-list">
                @foreach ($releases as $release)
                    <li class="dash-panel whatsnew-item" id="release-{{ $release->id }}">
                        <div class="whatsnew-item__icon whatsnew-item__icon--{{ $release->categoryTone() }}" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => $release->icon()])
                        </div>
                        <div class="whatsnew-item__body">
                            <div class="whatsnew-item__meta">
                                <span class="health-table__pill health-table__pill--{{ $release->categoryTone() }}">{{ __($release->categoryLabel()) }}</span>
                                <span class="whatsnew-item__version">{{ $release->version }}</span>
                                <time datetime="{{ $release->released_on->toDateString() }}">{{ $release->released_on->format('M j, Y') }}</time>
                                @if ($release->moduleLabel())
                                    <span>· {{ __($release->moduleLabel()) }}</span>
                                @endif
                            </div>
                            <h2 class="whatsnew-item__title">{{ $release->title }}</h2>
                            <div class="whatsnew-item__desc">{!! nl2br(e($release->description)) !!}</div>
                            @php
                                $canExplore = $release->exploreUrl()
                                    && (! $release->module || app(\App\Services\WorkspaceAccessService::class)->can(auth()->user(), $release->module));
                            @endphp
                            @if ($canExplore)
                                <a href="{{ $release->exploreUrl() }}" class="dash-btn-save whatsnew-item__cta">{{ __('Explore feature') }} →</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
            <div class="dash-pagination">{{ $releases->links() }}</div>
        @endif
    </div>
@endsection

@push('scripts')
    <style>
        .notif-tabs { display: inline-flex; flex-wrap: wrap; gap: 0.25rem; padding: 0.25rem; border-radius: 0.6rem; background: #f3f4f6; }
        .notif-tabs__tab { padding: 0.4rem 0.9rem; border-radius: 0.45rem; font-size: 0.85rem; font-weight: 600; color: #6b7280; text-decoration: none; }
        .notif-tabs__tab.is-active { background: #fff; color: #111; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06); }
        .whatsnew-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 0.85rem; }
        .whatsnew-item { display: flex; gap: 1rem; padding: 1.25rem 1.35rem; scroll-margin-top: 6rem; }
        .whatsnew-item:target { box-shadow: 0 0 0 2px #a4d400; }
        .whatsnew-item__icon {
            flex-shrink: 0; width: 2.75rem; height: 2.75rem; border-radius: 0.75rem; display: grid; place-items: center;
            background: #f3f4f6; color: #374151;
        }
        .whatsnew-item__icon--ok { background: rgba(164, 212, 0, 0.18); color: #3f5200; }
        .whatsnew-item__icon--warn { background: #fff4e0; color: #8a5300; }
        .whatsnew-item__icon--bad { background: #fdf3f2; color: #b42318; }
        .whatsnew-item__body { min-width: 0; flex: 1; }
        .whatsnew-item__meta { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: #6b7280; }
        .whatsnew-item__version { font-weight: 700; color: #374151; }
        .whatsnew-item__title { margin: 0.45rem 0 0.35rem; font-size: 1.1rem; font-weight: 800; color: #111; }
        .whatsnew-item__desc { font-size: 0.9rem; line-height: 1.65; color: #4b5563; overflow-wrap: anywhere; }
        .whatsnew-item__cta { display: inline-flex; margin-top: 0.85rem; text-decoration: none; }
        @media (max-width: 640px) {
            .whatsnew-item { flex-direction: column; gap: 0.75rem; padding: 1rem; }
        }
    </style>
@endpush
