@extends('layouts.auth')

@section('title', 'Sign in')

@section('hero-quote')
    One platform for every part of your farm operation.
@endsection

@section('hero-cite-name')
    Orora Farm
@endsection

@section('hero-cite-role')
    Smart farm management, simplified.
@endsection

@section('content')
    <header class="auth-form-header">
        <h1 class="auth-form-title">Welcome back</h1>
        <p class="auth-form-subtitle">Sign in to your farm workspace</p>
    </header>

    @if (session('error'))
        <div class="auth-alert" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="auth-alert" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form class="auth-form" method="POST" action="{{ route('login.store') }}">
        @csrf

        @include('auth.partials.labeled-field', [
            'label' => 'Email',
            'type' => 'email',
            'name' => 'email',
            'id' => 'email',
            'value' => old('email'),
            'placeholder' => 'you@example.com',
            'required' => true,
            'autofocus' => true,
            'autocomplete' => 'username',
        ])

        @include('auth.partials.labeled-field', [
            'label' => 'Password',
            'type' => 'password',
            'name' => 'password',
            'id' => 'password',
            'placeholder' => '••••••••',
            'required' => true,
            'toggle' => true,
            'autocomplete' => 'current-password',
        ])

        <div class="auth-form-meta">
            <label class="auth-toggle">
                <input type="checkbox" name="remember" value="1" class="auth-toggle__input" @checked(old('remember'))>
                <span class="auth-toggle__track" aria-hidden="true"></span>
                <span class="auth-toggle__label">Remember me</span>
            </label>
            <span class="auth-forgot-link auth-forgot-link--disabled" title="Coming soon">Forgot password?</span>
        </div>

        <button type="submit" class="auth-btn-primary">Log in</button>
    </form>
@endsection

@section('footer')
    <p>Don't have an account? <a href="{{ route('register') }}">Sign up</a></p>
@endsection
