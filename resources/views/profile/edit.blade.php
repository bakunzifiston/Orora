@extends('layouts.dashboard')

@section('title', 'Profile settings')

@section('content')
    <div class="dash-page-header">
        <div>
            <h1 class="dash-welcome" style="margin-bottom: 0.35rem;">Profile settings</h1>
            <p style="color: #808080; font-size: 0.875rem;">Update your account information and password.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="dash-back-link">← Back to dashboard</a>
    </div>

    @if (session('success'))
        <div class="dash-alert dash-alert--success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="dash-alert dash-alert--error">
            <ul style="margin: 0; padding-left: 1.15rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('profile.update') }}" class="dash-profile-form">
            @csrf
            @method('PUT')

            <div class="dash-profile-avatar-row">
                <div class="dash-profile-avatar">
                    {{ collect(explode(' ', $user->name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('') }}
                </div>
                <div>
                    <p class="dash-profile-avatar-label">Profile photo</p>
                    <p class="dash-profile-avatar-hint">Initials are shown until you upload a photo.</p>
                </div>
            </div>

            <div class="dash-form-grid">
                <div class="dash-form-field">
                    <label for="name">Full name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
                </div>

                <div class="dash-form-field">
                    <label for="email">Email address</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
                </div>
            </div>

            <hr class="dash-form-divider">

            <p class="dash-form-section-title">Change password</p>
            <p class="dash-form-section-hint">Leave blank to keep your current password.</p>

            <div class="dash-form-grid">
                <div class="dash-form-field dash-form-field--full">
                    <label for="current_password">Current password</label>
                    <input type="password" name="current_password" id="current_password" autocomplete="current-password">
                </div>

                <div class="dash-form-field">
                    <label for="password">New password</label>
                    <input type="password" name="password" id="password" autocomplete="new-password">
                </div>

                <div class="dash-form-field">
                    <label for="password_confirmation">Confirm new password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password">
                </div>
            </div>

            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save changes</button>
                <a href="{{ route('dashboard') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
