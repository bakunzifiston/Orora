@php
    $name = $name ?? 'field';
    $label = $label ?? 'Field';
    $options = $options ?? [];
    $value = old($name, $value ?? '');
    $required = $required ?? false;
@endphp

<div class="dash-form-field">
    <label for="{{ $name }}">{{ $label }} @if($required)<span class="dash-required">*</span>@endif</label>
    <select name="{{ $name }}" id="{{ $name }}" @if($required) required @endif>
        @if (! $required)
            <option value="">Not set</option>
        @endif
        @foreach ($options as $option)
            @php $optionValue = is_array($option) ? $option['value'] : $option; @endphp
            @php $optionLabel = is_array($option) ? $option['label'] : $option; @endphp
            <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
</div>
