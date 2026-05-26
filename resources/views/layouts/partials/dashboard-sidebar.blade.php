@php
    $activeNav = $activeNav ?? 'dashboard';
    $user = auth()->user();
    $initials = collect(explode(' ', $user->name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
@endphp

<aside class="dash-sidebar">
    <div class="dash-sidebar-inner">
        <div class="dash-logo-wrap">
            @include('layouts.partials.dashboard-brand')
        </div>

        <nav class="dash-nav">
            @foreach ($navigation as $item)
                @if ($item['route'] && Route::has($item['route']))
                    <a href="{{ route($item['route']) }}" class="{{ $activeNav === $item['key'] ? 'active' : '' }}">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => $item['icon']])
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="disabled" title="Coming soon">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => $item['icon']])
                        {{ $item['label'] }}
                    </span>
                @endif
            @endforeach
        </nav>

        <div class="dash-sidebar-footer">
            <a href="{{ route('profile.edit') }}" class="dash-user" title="Edit profile">
                <div class="dash-user-avatar">{{ $initials }}</div>
                <div class="dash-user-info">
                    <div class="dash-user-name">{{ $user->name }}</div>
                    <div class="dash-user-role">{{ $user->email }}</div>
                </div>
            </a>

            <form method="POST" action="{{ route('logout') }}" class="dash-logout-form">
                @csrf
                <button type="submit" class="dash-logout">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                    </svg>
                    Sign out
                </button>
            </form>
        </div>
    </div>
</aside>
