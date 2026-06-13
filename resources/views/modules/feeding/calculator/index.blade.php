@extends('layouts.feeding-module')

@section('title', 'Feed calculator')

@section('feeding-content')
    @include('modules.partials.header', [
        'title' => 'Feed calculator',
        'subtitle' => 'Daily feed recommendations by animal weight, type, age, and production status.',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel dash-profile-panel">
        <form
            method="POST"
            action="{{ route('feeding.calculator.calculate') }}"
            class="dash-profile-form"
            id="feed-calculator-form"
            data-livestock-url="{{ route('feeding.calculator.livestock') }}"
            data-animals-url="{{ route('feeding.calculator.animals') }}"
        >
            @csrf

            @component('modules.farms._form-section', [
                'number' => '1',
                'title' => 'Calculation scope',
                'description' => 'Choose an individual animal or an entire herd, then select farm and livestock.',
            ])
                <div class="dash-form-grid">
                    <div class="dash-form-field dash-form-field--full">
                        <label>Calculate for <span class="dash-required">*</span></label>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <label class="dash-checkbox">
                                <input type="radio" name="level" value="individual" @checked(old('level', 'individual') === 'individual') required>
                                <span>Individual animal</span>
                            </label>
                            <label class="dash-checkbox">
                                <input type="radio" name="level" value="herd" @checked(old('level') === 'herd')>
                                <span>Herd / livestock group</span>
                            </label>
                        </div>
                    </div>
                    <div class="dash-form-field">
                        <label for="farm_id">Farm <span class="dash-required">*</span></label>
                        <select name="farm_id" id="farm_id" required>
                            <option value="">Select farm</option>
                            @foreach ($farms as $farm)
                                <option value="{{ $farm->id }}" @selected(old('farm_id') == $farm->id)>{{ $farm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="dash-form-field">
                        <label for="livestock_id">Livestock group <span class="dash-required">*</span></label>
                        <select name="livestock_id" id="livestock_id" required data-selected="{{ old('livestock_id') }}">
                            <option value="">Select livestock</option>
                        </select>
                    </div>
                    <div class="dash-form-field dash-form-field--full" id="animal-row" @if(old('level') === 'herd') hidden @endif>
                        <label for="animal_id">Animal <span class="dash-required">*</span></label>
                        <select name="animal_id" id="animal_id" data-selected="{{ old('animal_id') }}">
                            <option value="">Select animal</option>
                        </select>
                    </div>
                </div>
            @endcomponent

            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Calculate feed requirements</button>
            </div>
        </form>
    </div>

    @include('modules.feeding.calculator._rules-reference')
@endsection

@push('scripts')
    @vite(['resources/js/feed-calculator-form.js'])
@endpush
