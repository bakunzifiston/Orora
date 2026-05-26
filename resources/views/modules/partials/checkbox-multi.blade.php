@php
    $name = $name ?? 'items';
    $label = $label ?? 'Options';
    $options = $options ?? [];
    $selected = old($name, $selected ?? []);
    $selected = is_array($selected) ? $selected : [];
    $otherName = $otherName ?? null;
    $otherValue = old($otherName, $otherValue ?? '');
    $required = $required ?? true;
@endphp

<fieldset class="dash-checkbox-group" data-checkbox-group @if($otherName) data-other-name="{{ $otherName }}" @endif>
    <legend class="dash-checkbox-group__legend">
        {{ $label }}
        @if ($required)<span class="dash-required">*</span>@endif
    </legend>
    <p class="dash-checkbox-group__hint">Select all that apply.</p>
    <div class="dash-checkbox-grid">
        @foreach ($options as $option)
            <label class="dash-checkbox">
                <input
                    type="checkbox"
                    name="{{ $name }}[]"
                    value="{{ $option }}"
                    @checked(in_array($option, $selected, true))
                    @if($option === 'Other') data-other-trigger @endif
                >
                <span>{{ $option }}</span>
            </label>
        @endforeach
    </div>
    @if ($otherName)
        <div class="dash-form-field dash-other-field" data-other-input @if(! in_array('Other', $selected, true)) hidden @endif>
            <label for="{{ $otherName }}">Specify other <span class="dash-required">*</span></label>
            <input
                type="text"
                name="{{ $otherName }}"
                id="{{ $otherName }}"
                value="{{ $otherValue }}"
                placeholder="Enter custom value…"
                @if(in_array('Other', $selected, true)) required @endif
            >
        </div>
    @endif
</fieldset>
