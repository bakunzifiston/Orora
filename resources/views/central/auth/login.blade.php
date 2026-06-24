<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin Sign in — Orora</title>
    <style>
        :root { --bg: #0b1220; --card: #1f2937; --text: #f9fafb; --muted: #9ca3af; --accent: #a4d400; --border: #374151; --danger-bg: #450a0a; --danger-text: #fecaca; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: ui-sans-serif, system-ui, sans-serif; background: var(--bg); color: var(--text); padding: 1.5rem; }
        .login-card { width: min(100%, 420px); background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 2rem; }
        h1 { margin: 0 0 0.35rem; font-size: 1.5rem; }
        p { margin: 0 0 1.5rem; color: var(--muted); font-size: 0.9rem; }
        label { display: block; margin-bottom: 0.35rem; color: var(--muted); font-size: 0.875rem; }
        input[type="email"], input[type="password"] {
            width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border);
            background: var(--bg); color: var(--text); margin-bottom: 1rem;
        }
        .btn { width: 100%; padding: 0.7rem 1rem; border: none; border-radius: 8px; background: var(--accent); color: #111; font-weight: 700; cursor: pointer; }
        .alert { padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; background: var(--danger-bg); color: var(--danger-text); font-size: 0.875rem; }
        .remember { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; color: var(--muted); font-size: 0.875rem; }
        .footer { margin-top: 1.25rem; text-align: center; font-size: 0.8125rem; }
        .footer a { color: var(--muted); text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>Super Admin</h1>
        <p>Sign in to manage tenants, marketplace, and platform settings.</p>

        @if (empty($adminReady))
            <div class="alert" style="background:#422006;color:#fde68a;">
                Super admin is not installed yet. On the server run:
                <code style="display:block;margin-top:0.5rem;">php artisan migrate --force</code>
                <code style="display:block;margin-top:0.35rem;">php artisan orora:super-admin</code>
            </div>
        @elseif (empty($hasAdminAccounts))
            <div class="alert" style="background:#422006;color:#fde68a;">
                No super admin account exists yet. On the server run:
                <code style="display:block;margin-top:0.5rem;">php artisan orora:super-admin --email=you@example.com --password=your-password</code>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('central.login.store') }}">
            @csrf
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">

            <label class="remember">
                <input type="checkbox" name="remember" value="1">
                Remember me
            </label>

            <button type="submit" class="btn">Sign in</button>
        </form>

        <div class="footer">
            <a href="{{ url('/') }}">← Back to public site</a>
        </div>
    </div>
</body>
</html>
