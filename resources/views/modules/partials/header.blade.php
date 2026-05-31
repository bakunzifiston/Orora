<div class="dash-page-header">
    <div>
        <h1 class="dash-welcome" style="margin-bottom: 0.35rem;">{{ $title }}</h1>
        @if (! empty($subtitle))
            <p style="color: #808080; font-size: 0.875rem;">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="dash-page-header__actions">
        @if (! empty($createRoute))
            <a href="{{ route($createRoute, $createRouteParams ?? []) }}" class="dash-btn-save">{{ $createLabel ?? '+ Add new' }}</a>
        @endif
        @if (! empty($backRoute))
            <a href="{{ route($backRoute, $backRouteParams ?? []) }}" class="dash-back-link">← Back</a>
        @endif
    </div>
</div>
