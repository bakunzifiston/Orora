@extends('layouts.dashboard')

@section('title', __('Add user'))

@section('content')
    <div class="farm-dash farms-page workspace-users-page">
        @include('modules.partials.header', [
            'title' => __('Add user'),
            'backRoute' => 'workspace.users.index',
        ])
        @include('modules.partials.flash')

        <form method="POST" action="{{ route('workspace.users.store') }}" class="dash-farm-form">
            @csrf

            <section class="farm-dash__section">
                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Account details') }}</h2>
                            <p class="farm-panel__desc">{{ __('This person will sign in with the email and password you set.') }}</p>
                        </div>
                    </header>

                    <div class="dash-form-grid">
                        <div class="dash-form-field">
                            <label for="name">{{ __('Name') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required autocomplete="name">
                            @error('name')<p class="dash-field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="dash-form-field">
                            <label for="email">{{ __('Email') }}</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email">
                            @error('email')<p class="dash-field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="dash-form-field">
                            <label for="password">{{ __('Password') }}</label>
                            <input type="password" name="password" id="password" required autocomplete="new-password">
                            @error('password')<p class="dash-field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="dash-form-field">
                            <label for="password_confirmation">{{ __('Confirm password') }}</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password">
                        </div>
                        <div class="dash-form-field dash-form-field--full">
                            <label for="role">{{ __('Role') }}</label>
                            <select name="role" id="role" data-role-select>
                                @foreach ($roles as $value => $label)
                                    <option value="{{ $value }}" @selected(old('role', 'member') === $value)>{{ __($label) }}</option>
                                @endforeach
                            </select>
                            <p class="dash-field-hint">{{ __('Admins get full access. Members only get modules you select below.') }}</p>
                            @error('role')<p class="dash-field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </article>
            </section>

            <section class="farm-dash__section">
                @include('workspace.users.partials.permissions-matrix')
            </section>

            <div class="dash-form-actions">
                <a href="{{ route('workspace.users.index') }}" class="dash-btn-cancel">{{ __('Cancel') }}</a>
                <button type="submit" class="dash-btn-save">{{ __('Create user') }}</button>
            </div>
        </form>
    </div>
@endsection
