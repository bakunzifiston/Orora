@extends('layouts.auth')

@section('title', 'Create account')

@section('hero-quote')
    “Your herd, your farms, your data — all in one place from day one.”
@endsection

@section('hero-cite-name')
    Orora platform
@endsection

@section('hero-cite-role')
    Farm management for modern operations
@endsection

@section('content')
    <header class="auth-form-header">
        <h1 class="auth-form-title">Create account</h1>
        <p class="auth-form-subtitle">Set up your private farm workspace</p>
    </header>

    @if ($errors->any())
        <div class="auth-alert" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="auth-form" method="POST" action="{{ route('register.store') }}">
        @csrf

        @include('auth.partials.labeled-field', [
            'label' => 'Full name',
            'type' => 'text',
            'name' => 'name',
            'id' => 'name',
            'value' => old('name'),
            'placeholder' => 'Your name',
            'required' => true,
            'autofocus' => true,
            'autocomplete' => 'name',
        ])

        @include('auth.partials.labeled-field', [
            'label' => 'Email',
            'type' => 'email',
            'name' => 'email',
            'id' => 'email',
            'value' => old('email'),
            'placeholder' => 'you@example.com',
            'required' => true,
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
            'autocomplete' => 'new-password',
        ])

        @include('auth.partials.labeled-field', [
            'label' => 'Confirm password',
            'type' => 'password',
            'name' => 'password_confirmation',
            'id' => 'password_confirmation',
            'placeholder' => '••••••••',
            'required' => true,
            'autocomplete' => 'new-password',
        ])

        <button type="submit" class="auth-btn-primary">Create account</button>
    </form>
@endsection

@section('footer')
    <p>Already have an account? <a href="{{ route('login') }}">Log in</a></p>
@endsection
