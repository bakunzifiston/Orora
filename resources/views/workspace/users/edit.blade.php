@extends('layouts.dashboard')

@section('title', __('Edit user'))

@section('content')
    <div class="farm-dash farms-page workspace-users-page">
        @include('modules.partials.header', [
            'title' => __('Edit user'),
            'subtitle' => $workspaceUser->name,
            'backRoute' => 'workspace.users.index',
        ])
        @include('modules.partials.flash')

        <form method="POST" action="{{ route('workspace.users.update', $workspaceUser) }}" class="dash-farm-form">
            @csrf
            @method('PUT')

            <section class="farm-dash__section">
                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Account details') }}</h2>
                            <p class="farm-panel__desc">{{ __('Update profile, role, and sign-in status.') }}</p>
                        </div>
                    </header>

                    <div class="dash-form-grid">
                        <div class="dash-form-field">
                            <label for="name">{{ __('Name') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $workspaceUser->name) }}" required autocomplete="name">
                            @error('name')<p class="dash-field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="dash-form-field">
                            <label for="email">{{ __('Email') }}</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $workspaceUser->email) }}" required autocomplete="email">
                            @error('email')<p class="dash-field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="dash-form-field">
                            <label for="password">{{ __('New password') }}</label>
                            <input type="password" name="password" id="password" placeholder="{{ __('Leave blank to keep current') }}" autocomplete="new-password">
                            @error('password')<p class="dash-field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="dash-form-field">
                            <label for="password_confirmation">{{ __('Confirm password') }}</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password">
                        </div>
                        <div class="dash-form-field">
                            <label for="role">{{ __('Role') }}</label>
                            @php
                                $currentRole = old('role', $workspaceUser->isWorkspaceAdmin() ? 'workspace_admin' : $workspaceUser->role);
                            @endphp
                            <select name="role" id="role" data-role-select @disabled($workspaceUser->id === auth()->id())>
                                @foreach ($roles as $value => $label)
                                    <option value="{{ $value }}" @selected($currentRole === $value)>{{ __($label) }}</option>
                                @endforeach
                            </select>
                            @if ($workspaceUser->id === auth()->id())
                                <input type="hidden" name="role" value="workspace_admin">
                                <p class="dash-field-hint">{{ __('You cannot change your own role away from workspace admin.') }}</p>
                            @endif
                            @error('role')<p class="dash-field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="dash-form-field">
                            <label>{{ __('Status') }}</label>
                            @if ($workspaceUser->id === auth()->id())
                                <input type="hidden" name="is_active" value="1">
                                <p class="dash-field-hint">{{ __('Active') }} — {{ __('You cannot deactivate your own account.') }}</p>
                            @else
                                <label class="workspace-users-toggle">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $workspaceUser->is_active))>
                                    <span>{{ __('Active (can sign in)') }}</span>
                                </label>
                            @endif
                        </div>
                    </div>
                </article>
            </section>

            <section class="farm-dash__section">
                @include('workspace.users.partials.permissions-matrix', ['workspaceUser' => $workspaceUser])
            </section>

            <div class="dash-form-actions">
                <a href="{{ route('workspace.users.index') }}" class="dash-btn-cancel">{{ __('Cancel') }}</a>
                <button type="submit" class="dash-btn-save">{{ __('Save changes') }}</button>
            </div>
        </form>
    </div>
@endsection
