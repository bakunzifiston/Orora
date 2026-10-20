@extends('layouts.admin')

@section('title', $release->exists ? __('Edit release') : __('New release'))

@section('content')
    <div class="farm-dash farms-page health-page admin-dash admin-release">
        <div class="dash-ops-toolbar farms-page__toolbar admin-dash__toolbar">
            <div class="admin-dash__header-text dash-ops-toolbar__brand">
                <h1 class="admin-dash__title">{{ $release->exists ? __('Edit release') : __('New release') }}</h1>
                <p class="admin-dash__period">
                    @if ($release->isPublished())
                        {{ __('Published :date. Edits update What\'s New; widening the audience notifies only the newly added tenants.', ['date' => $release->published_at?->format('M j, Y H:i')]) }}
                    @else
                        {{ __('Drafts are not visible to tenants and send no notifications.') }}
                    @endif
                </p>
            </div>
            <a href="{{ route('central.releases.index') }}" class="dash-btn-cancel">{{ __('Back to releases') }}</a>
        </div>

        @if ($errors->any())
            <div class="dash-alert dash-alert--error">{{ __('Please fix the errors below.') }}</div>
        @endif

        <form
            method="POST"
            action="{{ $release->exists ? route('central.releases.update', $release) : route('central.releases.store') }}"
            class="farm-panel admin-release__form"
            data-release-form
        >
            @csrf
            @if ($release->exists) @method('PUT') @endif

            <div class="admin-release__grid">
                <label>{{ __('Version or identifier') }} *
                    <input name="version" value="{{ old('version', $release->version) }}" required maxlength="50" placeholder="v2.4.0">
                    @error('version')<span class="admin-release__error">{{ $message }}</span>@enderror
                </label>

                <label>{{ __('Release date') }} *
                    <input type="date" name="released_on" value="{{ old('released_on', $release->released_on?->toDateString()) }}" required>
                    @error('released_on')<span class="admin-release__error">{{ $message }}</span>@enderror
                </label>

                <label class="admin-release__full">{{ __('Title') }} *
                    <input name="title" value="{{ old('title', $release->title) }}" required maxlength="255" placeholder="{{ __('Track where your milk goes') }}">
                    @error('title')<span class="admin-release__error">{{ $message }}</span>@enderror
                </label>

                <label class="admin-release__full">{{ __('Description of the changes') }} *
                    <textarea name="description" rows="6" required maxlength="5000" placeholder="{{ __('What changed and how tenants can use it. Line breaks are kept.') }}">{{ old('description', $release->description) }}</textarea>
                    @error('description')<span class="admin-release__error">{{ $message }}</span>@enderror
                </label>

                <label>{{ __('Category') }} *
                    <select name="category" required>
                        @foreach (config('releases.categories') as $key => $label)
                            <option value="{{ $key }}" @selected(old('category', $release->category) === $key)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                    @error('category')<span class="admin-release__error">{{ $message }}</span>@enderror
                </label>

                <label>{{ __('Affected module') }}
                    <select name="module">
                        <option value="">{{ __('None / whole platform') }}</option>
                        @foreach ($modules as $key => $label)
                            <option value="{{ $key }}" @selected(old('module', $release->module) === $key)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                    @error('module')<span class="admin-release__error">{{ $message }}</span>@enderror
                </label>

                <label class="admin-release__full">{{ __('Link to dashboard page') }}
                    <input name="link_path" value="{{ old('link_path', $release->link_path) }}" maxlength="255" placeholder="/milk/distribution">
                    <span class="admin-release__hint">{{ __('Optional. Shown as "Explore feature". Leave empty to link to the affected module\'s main page.') }}</span>
                    @error('link_path')<span class="admin-release__error">{{ $message }}</span>@enderror
                </label>

                <fieldset class="admin-release__full admin-release__audience">
                    <legend>{{ __('Target audience') }} *</legend>
                    @foreach (config('releases.audiences') as $key => $label)
                        <label class="admin-release__radio">
                            <input type="radio" name="audience" value="{{ $key }}" data-release-audience @checked(old('audience', $release->audience ?? 'all') === $key)>
                            {{ __($label) }}
                        </label>
                    @endforeach

                    <div class="admin-release__tenants" data-release-tenants>
                        @forelse ($tenants as $tenant)
                            <label class="admin-release__check">
                                <input type="checkbox" name="tenant_ids[]" value="{{ $tenant->id }}" @checked(in_array($tenant->id, $selectedTenants, true))>
                                <span>{{ $tenant->name ?: $tenant->id }} <small>{{ $tenant->id }}</small></span>
                            </label>
                        @empty
                            <p class="admin-release__hint">{{ __('No tenants exist yet.') }}</p>
                        @endforelse
                    </div>
                    @error('tenant_ids')<span class="admin-release__error">{{ $message }}</span>@enderror
                    @error('tenant_ids.*')<span class="admin-release__error">{{ $message }}</span>@enderror
                </fieldset>
            </div>

            <div class="admin-release__actions">
                @if ($release->isPublished())
                    <button type="submit" class="dash-btn-save">{{ __('Save changes') }}</button>
                @else
                    <button type="submit" name="action" value="draft" class="dash-btn-cancel">{{ __('Save as draft') }}</button>
                    @if ($canPublish)
                        <button type="submit" name="action" value="publish" class="dash-btn-save" onclick="return confirm(@js(__('Publish this release and notify its tenants?')));">{{ __('Save & publish') }}</button>
                    @endif
                @endif
                <a href="{{ route('central.releases.index') }}" class="dash-btn-cancel">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    @include('central.dashboard.partials.styles')
    <style>
        .admin-release__grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
        .admin-release__form label { display: grid; align-content: start; gap: 0.35rem; color: #334155; font-size: 0.875rem; font-weight: 600; }
        .admin-release__form input:not([type="checkbox"]):not([type="radio"]), .admin-release__form select, .admin-release__form textarea {
            width: 100%; min-width: 0; padding: 0.6rem 0.7rem; border: 1px solid #cbd5e1; border-radius: 0.5rem;
            background: #fff; color: #0f172a; font: inherit; font-weight: 400;
        }
        .admin-release__full { grid-column: 1 / -1; }
        .admin-release__hint { color: #64748b; font-size: 0.78rem; font-weight: 400; }
        .admin-release__error { color: #b91c1c; font-size: 0.8rem; font-weight: 500; }
        .admin-release__audience { border: 1px solid #e2e8f0; border-radius: 0.6rem; padding: 0.85rem 1rem; display: grid; gap: 0.5rem; }
        .admin-release__audience legend { padding: 0 0.35rem; color: #334155; font-size: 0.875rem; font-weight: 600; }
        .admin-release__form .admin-release__radio, .admin-release__form .admin-release__check { display: flex; align-items: center; gap: 0.5rem; font-weight: 500; }
        .admin-release__tenants {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(14rem, 1fr)); gap: 0.35rem 1rem;
            max-height: 16rem; overflow-y: auto; padding: 0.6rem; border-radius: 0.5rem; background: #f8fafc;
        }
        .admin-release__tenants[hidden] { display: none; }
        .admin-release__check small { color: #94a3b8; font-weight: 400; }
        .admin-release__actions { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1.25rem; }
        @media (max-width: 640px) { .admin-release__grid { grid-template-columns: 1fr; } }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-release-form]');
            const tenants = form?.querySelector('[data-release-tenants]');
            if (!form || !tenants) return;
            const sync = () => {
                tenants.hidden = form.querySelector('[data-release-audience]:checked')?.value !== 'selected';
            };
            form.querySelectorAll('[data-release-audience]').forEach((radio) => radio.addEventListener('change', sync));
            sync();
        });
    </script>
@endpush
