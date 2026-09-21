@php
    $deleteConfirm = $deleteConfirm ?? __('Delete this record?');
@endphp

<div class="health-table__action-btns">
    <a href="{{ route($editRoute, $model) }}{{ ! empty($section) ? '?section='.$section : '' }}" class="dash-farm-card__btn">{{ __('Edit') }}</a>
    <form method="POST" action="{{ route($destroyRoute, $model) }}{{ ! empty($section) ? '?section='.$section : '' }}" onsubmit="return confirm(@js($deleteConfirm));">
        @csrf
        @method('DELETE')
        <button type="submit" class="dash-farm-card__btn dash-farm-card__btn--danger">{{ __('Delete') }}</button>
    </form>
</div>
