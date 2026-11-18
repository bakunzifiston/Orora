@php $currentType = $currentType ?? 'real'; $next = $currentType === 'testing' ? 'real' : 'testing'; @endphp
<form method="POST" action="{{ $action }}" class="acct-classify"
      onsubmit="return confirm(@json($next === 'testing'
          ? __('Mark this account as TESTING? It will be excluded from production statistics and the public marketplace.')
          : __('Mark this account as ACTIVE? It will be included in production statistics.')));">
    @csrf
    @method('PATCH')
    <input type="hidden" name="account_type" value="{{ $next }}">
    <button type="submit" class="{{ $buttonClass ?? 'dash-btn-save dash-btn-save--sm' }}">
        {{ $next === 'testing' ? __('Mark as testing') : __('Mark as active') }}
    </button>
</form>
