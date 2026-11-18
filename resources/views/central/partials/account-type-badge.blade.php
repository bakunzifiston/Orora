@php
    $isTesting = ($type ?? 'real') === 'testing';
@endphp
<span
    class="acct-badge {{ $isTesting ? 'acct-badge--testing' : 'acct-badge--real' }}"
    title="{{ $isTesting ? __('Testing account: excluded from production statistics') : __('Active production account') }}"
>{{ $isTesting ? __('Testing') : __('Active') }}</span>
