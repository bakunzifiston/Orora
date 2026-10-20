@extends('layouts.dashboard')

@section('title', __('Notifications'))

@php
    $tones = config('releases.category_tones', []);
@endphp

@section('content')
    <div class="farm-dash farms-page health-page">
        <div class="dash-page-header">
            <div>
                <h1 class="dash-welcome" style="margin-bottom: 0.35rem;">{{ __('Notifications') }}</h1>
                <p style="color: #808080; font-size: 0.875rem;">
                    {{ $unreadCount > 0 ? trans_choice('{1} 1 unread notification|[2,*] :count unread notifications', $unreadCount, ['count' => $unreadCount]) : __('You are all caught up.') }}
                </p>
            </div>
            <div class="dash-page-header__actions">
                <a href="{{ route('whats-new.index') }}" class="dash-btn-cancel">{{ __("What's new") }}</a>
                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="dash-btn-save">{{ __('Mark all as read') }}</button>
                    </form>
                @endif
            </div>
        </div>
        @include('modules.partials.flash')

        <div class="dash-ops-toolbar farms-page__toolbar">
            <div class="dash-ops-toolbar__controls farms-page__filters">
                <div class="notif-tabs" role="tablist" aria-label="{{ __('Filter notifications') }}">
                    <a href="{{ route('notifications.index') }}" class="notif-tabs__tab {{ $filter === 'all' ? 'is-active' : '' }}" @if ($filter === 'all') aria-current="page" @endif>{{ __('All') }}</a>
                    <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="notif-tabs__tab {{ $filter === 'unread' ? 'is-active' : '' }}" @if ($filter === 'unread') aria-current="page" @endif>{{ __('Unread') }}</a>
                </div>
            </div>
        </div>

        @if ($notifications->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <p class="dash-empty">{{ $filter === 'unread' ? __('No unread notifications.') : __('No notifications yet. OroraFarm updates and alerts will appear here.') }}</p>
            </div>
        @else
            <div class="dash-panel notif-list">
                @foreach ($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $unread = $notification->read_at === null;
                    @endphp
                    <a href="{{ route('notifications.open', $notification->id) }}" class="dash-bell__item notif-list__item {{ $unread ? 'is-unread' : '' }}">
                        <span class="dash-bell__state" aria-hidden="true"></span>
                        <span class="dash-bell__body">
                            <span class="dash-bell__item-title">
                                {{ $data['title'] ?? __('Notification') }}
                                @if (! empty($data['version']))
                                    <span class="notif-list__version">{{ $data['version'] }}</span>
                                @endif
                            </span>
                            @if (! empty($data['message']))
                                <span class="dash-bell__message">{{ $data['message'] }}</span>
                            @endif
                            <span class="dash-bell__meta">
                                @if (! empty($data['category_label']))
                                    <span class="health-table__pill health-table__pill--{{ $tones[$data['category'] ?? ''] ?? 'muted' }}">{{ __($data['category_label']) }}</span>
                                @endif
                                <time datetime="{{ $notification->created_at->toIso8601String() }}" title="{{ $notification->created_at->format('M j, Y H:i') }}">{{ $notification->created_at->diffForHumans() }}</time>
                                <span>· {{ $unread ? __('Unread') : __('Read') }}</span>
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="dash-pagination">{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection

@push('scripts')
    <style>
        .notif-tabs { display: inline-flex; gap: 0.25rem; padding: 0.25rem; border-radius: 0.6rem; background: #f3f4f6; }
        .notif-tabs__tab { padding: 0.4rem 0.9rem; border-radius: 0.45rem; font-size: 0.85rem; font-weight: 600; color: #6b7280; text-decoration: none; }
        .notif-tabs__tab.is-active { background: #fff; color: #111; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06); }
        .notif-list { padding: 0; overflow: hidden; }
        .notif-list__item + .notif-list__item { border-top: 1px solid #f1f3f4; }
        .notif-list__item { padding: 1rem 1.25rem; }
        .notif-list .dash-bell__message { -webkit-line-clamp: 3; font-size: 0.85rem; }
        .notif-list__version { margin-left: 0.35rem; font-size: 0.72rem; font-weight: 600; color: #9ca3af; }
    </style>
@endpush
