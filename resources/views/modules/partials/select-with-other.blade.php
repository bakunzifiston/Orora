@php
    $name = $name ?? 'field';
    $otherName = $otherName ?? $name.'_other';
    $id = $id ?? $name;
    $label = $label ?? 'Field';
    $options = $options ?? [];
    $required = $required ?? false;
    $allowEmpty = $allowEmpty ?? true;
    $emptyLabel = $emptyLabel ?? 'Not set';

    $storedValue = old($name, $value ?? '');
    $isCustom = $storedValue !== '' && $storedValue !== null && ! in_array($storedValue, $options, true);
    $selectValue = old($name, $isCustom ? 'Other' : $storedValue);
    $otherValue = old($otherName, $isCustom ? $storedValue : '');
    $showOther = $selectValue === 'Other';
@endphp

<div class="dash-form-field" data-select-other>
    <label for="{{ $id }}">
        {{ $label }}
        @if ($required)<span class="dash-required">*</span>@endif
    </label>
    <select name="{{ $name }}" id="{{ $id }}" @if($required) required @endif data-select-other-trigger>
        @if ($allowEmpty)
            <option value="" @selected($selectValue === '' || $selectValue === null)>{{ $emptyLabel }}</option>
        @endif
        @foreach ($options as $option)
            <option value="{{ $option }}" @selected($selectValue === $option)>{{ $option }}</option>
        @endforeach
    </select>
    <div class="dash-form-field dash-other-field" data-other-input @if(! $showOther) hidden @endif>
        <label for="{{ $otherName }}">Specify other <span class="dash-required">*</span></label>
        <input
            type="text"
            name="{{ $otherName }}"
            id="{{ $otherName }}"
            value="{{ $otherValue }}"
            placeholder="Enter custom value…"
            @if($showOther) required @endif
        >
    </div>
</div>
