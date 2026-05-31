<div class="dash-stat-icon" @if(! empty($label)) aria-label="{{ $label }}" @else aria-hidden="true" @endif>
    @include('layouts.partials.dashboard-nav-icon', ['icon' => $icon ?? 'grid'])
</div>
