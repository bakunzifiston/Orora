@php
    $milkDistribution = $milkDistribution ?? null;
    $selectedFarmId = (string) old('farm_id', $milkDistribution?->farm_id ?? ($farms->count() === 1 ? $farms->first()->id : ''));
    $selectedCategory = old('category', $milkDistribution?->category ?? $defaultCategory);
    $selectedRecipientType = old('recipient_type', $milkDistribution?->recipient_type ?? ($categoryRecipients[$selectedCategory][0] ?? 'other'));
    $selectedCalf = (string) old('animal_id', $milkDistribution?->animal_id);
    $selectedTank = (string) old('milk_storage_id', $milkDistribution?->milk_storage_id);
    $distributedAt = old('distributed_at', $milkDistribution?->distributed_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i'));
@endphp

<div class="dash-form-grid" data-milk-distribution-form data-category-recipients='@json($categoryRecipients)'>
    <div class="dash-form-field dash-form-field--full">
        <label for="category">{{ __('What happened to the milk?') }} <span class="dash-required">*</span></label>
        <select name="category" id="category" required data-md-category>
            @foreach ($categoryLabels as $value => $label)
                <option value="{{ $value }}" @selected($selectedCategory === $value)>{{ __($label) }}</option>
            @endforeach
        </select>
        <p class="dash-field-hint">
            {{ __('Selling milk?') }}
            <a href="{{ route('sales.transactions.create', ['type' => 'milk_sale']) }}">{{ __('Record it as a milk sale') }}</a>
            — {{ __('confirmed sales appear here automatically as "Sold".') }}
        </p>
    </div>

    <div class="dash-form-field dash-form-field--full" data-md-section="other">
        <label for="other_use">{{ __('Describe the use') }} <span class="dash-required">*</span></label>
        <input type="text" name="other_use" id="other_use" maxlength="255" autocomplete="off" value="{{ old('other_use', $milkDistribution?->other_use) }}" placeholder="{{ __('e.g. Made into yoghurt, spilled, given to farm workers') }}">
    </div>

    <div class="dash-form-field">
        <label for="farm_id">{{ __('Farm') }} <span class="dash-required">*</span></label>
        <select name="farm_id" id="farm_id" required data-md-farm>
            <option value="">{{ __('Select farm') }}</option>
            @foreach ($farms as $farm)
                <option value="{{ $farm->id }}" @selected($selectedFarmId === (string) $farm->id)>{{ $farm->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field">
        <label for="distributed_at">{{ __('Date and time') }} <span class="dash-required">*</span></label>
        <input type="datetime-local" name="distributed_at" id="distributed_at" value="{{ $distributedAt }}" max="{{ now()->format('Y-m-d\TH:i') }}" required>
    </div>
    <div class="dash-form-field">
        <label for="quantity_liters">{{ __('Quantity (liters)') }} <span class="dash-required">*</span></label>
        <input type="number" step="0.01" min="0.01" name="quantity_liters" id="quantity_liters" inputmode="decimal" value="{{ old('quantity_liters', $milkDistribution?->quantity_liters) }}" required>
    </div>
    <div class="dash-form-field">
        <label for="milk_storage_id">{{ __('Taken from storage tank') }} <span style="font-weight: 400; color: #808080;">({{ __('optional') }})</span></label>
        <select name="milk_storage_id" id="milk_storage_id" data-md-farm-filtered>
            <option value="">{{ __('Not from a tank (straight after milking)') }}</option>
            @foreach ($storageTanks as $tank)
                <option value="{{ $tank->id }}" data-farm-id="{{ $tank->farm_id }}" @selected($selectedTank === (string) $tank->id)>
                    {{ $tank->container_name }} — {{ number_format($tank->current_quantity_liters, 2) }} L {{ __('available') }}
                </option>
            @endforeach
        </select>
        <p class="dash-field-hint">{{ __('Leave empty if the milk did not come from a tank. If you pick one, the litres are deducted from it.') }}</p>
    </div>

    {{-- Calf feeding --}}
    <div class="dash-form-field dash-form-field--full" data-md-section="calf">
        <label for="animal_id">{{ __('Calf') }} <span class="dash-required">*</span></label>
        <select
            name="animal_id"
            id="animal_id"
            data-md-farm-filtered
            data-placeholder="{{ __('Select calf') }}"
            data-placeholder-no-farm="{{ __('Select a farm first') }}"
            data-placeholder-none="{{ __('No eligible calves on this farm') }}"
        >
            <option value="">{{ __('Select calf') }}</option>
            @foreach ($calves as $calf)
                <option value="{{ $calf->id }}" data-farm-id="{{ $calf->farm_id }}" @selected($selectedCalf === (string) $calf->id)>
                    {{ $calf->tag_number }}{{ $calf->name ? ' · '.$calf->name : '' }}{{ $calf->age_label ? ' · '.$calf->age_label : '' }}
                </option>
            @endforeach
        </select>
        <p class="dash-field-hint" data-md-calf-pick-farm hidden>{{ __('Select a farm first to see its calves.') }}</p>
        <div class="dash-alert dash-alert--error" data-md-calf-empty hidden style="margin-top: 0.5rem;">
            {{ __('This farm has no calves that can be fed milk. A calf must be active and either under :months months old (from its date of birth) or in a herd marked "Calves Group".', ['months' => $calfMaxAgeMonths]) }}
            <a href="{{ route('animals.create') }}">{{ __('Add an animal') }}</a>
            {{ __('or update an animal\'s date of birth or herd, then come back.') }}
        </div>
    </div>

    {{-- People --}}
    <div class="dash-form-field" data-md-section="person">
        <label for="recipient_type">{{ __('Recipient type') }} <span class="dash-required">*</span></label>
        <select name="recipient_type" id="recipient_type" required data-md-recipient-type>
            @foreach ($recipientTypeLabels as $value => $label)
                <option value="{{ $value }}" @selected($selectedRecipientType === $value)>{{ __($label) }}</option>
            @endforeach
        </select>
    </div>
    <div class="dash-form-field" data-md-section="person">
        <label for="recipient_name">
            {{ __('Recipient name') }}
            <span class="dash-required" data-md-name-required>*</span>
        </label>
        <input type="text" name="recipient_name" id="recipient_name" maxlength="255" autocomplete="off" value="{{ old('recipient_name', $milkDistribution?->recipient_name) }}" placeholder="{{ __('e.g. Mukamana Jeanne') }}">
        <p class="dash-field-hint">{{ __('No system account needed.') }}</p>
    </div>
    <div class="dash-form-field" data-md-section="person">
        <label for="recipient_phone">{{ __('Phone number') }}</label>
        <input type="tel" name="recipient_phone" id="recipient_phone" maxlength="30" autocomplete="off" value="{{ old('recipient_phone', $milkDistribution?->recipient_phone) }}" placeholder="078 000 0000">
    </div>

    <div class="dash-form-field dash-form-field--full">
        <label for="notes">{{ __('Notes') }}</label>
        <textarea name="notes" id="notes" rows="2" maxlength="2000">{{ old('notes', $milkDistribution?->notes) }}</textarea>
    </div>
</div>
