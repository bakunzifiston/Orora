@php
    $current = $current ?? 'all';
    $baseQuery = request()->except(['account_type', 'page']);
    $options = ['real' => __('Active'), 'testing' => __('Testing'), 'all' => __('All')];
@endphp
<nav class="acct-switch" aria-label="{{ __('Account type') }}">
    @foreach ($options as $key => $label)
        <a
            href="{{ url()->current().'?'.http_build_query(array_merge($baseQuery, ['account_type' => $key])) }}"
            class="{{ $current === $key ? 'is-active' : '' }} {{ $key === 'testing' ? 'is-testing' : '' }}"
            @if ($current === $key) aria-current="page" @endif
        >{{ $label }}@if (isset($counts[$key]))<small>{{ number_format($counts[$key]) }}</small>@endif</a>
    @endforeach
</nav>
@if ($current === 'testing')
    <p class="acct-note">{{ __('Showing testing accounts only. These are not part of production statistics.') }}</p>
@elseif ($current === 'all')
    <p class="acct-note">{{ __('Showing active and testing accounts together. Totals include testing data.') }}</p>
@endif
