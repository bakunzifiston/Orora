@php
    $tones = config('releases.category_tones', []);
@endphp

<div class="dash-bell" data-notification-bell>
    <button
        type="button"
        class="dash-icon-btn dash-bell__toggle"
        data-bell-toggle
        aria-haspopup="true"
        aria-expanded="false"
        aria-controls="dash-bell-panel"
        aria-label="{{ $bellUnread > 0 ? trans_choice('{1} Notifications, 1 unread|[2,*] Notifications, :count unread', $bellUnread, ['count' => $bellUnread]) : __('Notifications') }}"
    >
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
        <span class="dash-bell__count" data-bell-count @if ($bellUnread === 0) hidden @endif>{{ $bellUnread > 99 ? '99+' : $bellUnread }}</span>
    </button>

    <div class="dash-bell__panel" id="dash-bell-panel" data-bell-panel hidden>
        <div class="dash-bell__head">
            <p class="dash-bell__title">{{ __('Notifications') }}</p>
            <form method="POST" action="{{ route('notifications.read-all') }}" data-bell-read-all @if ($bellUnread === 0) hidden @endif>
                @csrf
                <button type="submit" class="dash-bell__link">{{ __('Mark all as read') }}</button>
            </form>
        </div>

        @if ($bellNotifications->isEmpty())
            <div class="dash-bell__empty">
                <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                <p>{{ __("You're all caught up.") }}</p>
                <span>{{ __('New OroraFarm updates and alerts will appear here.') }}</span>
            </div>
        @else
            <ul class="dash-bell__list">
                @foreach ($bellNotifications as $notification)
                    @php
                        $data = $notification->data;
                        $unread = $notification->read_at === null;
                    @endphp
                    <li>
                        <a
                            href="{{ route('notifications.open', $notification->id) }}"
                            class="dash-bell__item {{ $unread ? 'is-unread' : '' }}"
                            data-bell-item
                        >
                            <span class="dash-bell__state" aria-hidden="true"></span>
                            <span class="dash-bell__body">
                                <span class="dash-bell__item-title">{{ $data['title'] ?? __('Notification') }}</span>
                                @if (! empty($data['message']))
                                    <span class="dash-bell__message">{{ $data['message'] }}</span>
                                @endif
                                <span class="dash-bell__meta">
                                    @if (! empty($data['category_label']))
                                        <span class="health-table__pill health-table__pill--{{ $tones[$data['category'] ?? ''] ?? 'muted' }}">{{ __($data['category_label']) }}</span>
                                    @endif
                                    <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                                    <span class="sr-only">{{ $unread ? __('Unread') : __('Read') }}</span>
                                </span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="dash-bell__foot">
            <a href="{{ route('notifications.index') }}" class="dash-bell__link">{{ __('View all notifications') }}</a>
            <a href="{{ route('whats-new.index') }}" class="dash-bell__link dash-bell__link--muted">{{ __("What's new") }}</a>
        </div>
    </div>
</div>
