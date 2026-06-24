@php
    $activeNav = $activeNav ?? 'dashboard';
    $navigation = $navigation ?? config('admin.navigation', []);
    $admin = auth('admin')->user();
    $initials = collect(explode(' ', $admin->name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
@endphp

<aside class="dash-sidebar">
    <div class="dash-sidebar-inner">
        <div class="dash-logo-wrap">
            @include('layouts.partials.admin-brand')
        </div>

        <nav class="dash-nav" aria-label="Admin navigation">
            <div class="dash-nav-group dash-nav-group--solo">
                @foreach ($navigation as $item)
                    @if (Route::has($item['route']))
                        <a href="{{ route($item['route']) }}" class="{{ $activeNav === $item['key'] ? 'active' : '' }}">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => $item['icon'] ?? 'grid'])
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </div>
        </nav>

        <div class="dash-sidebar-footer">
            <div class="dash-user">
                <div class="dash-user-avatar">{{ $initials }}</div>
                <div class="dash-user-info">
                    <div class="dash-user-name">{{ $admin->name }}</div>
                    <div class="dash-user-role">{{ $admin->email }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('central.logout') }}" class="dash-logout-form">
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
