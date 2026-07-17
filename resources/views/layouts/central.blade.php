<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Orora Central') — {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <style>
        :root { color-scheme: light dark; --bg: #0f172a; --card: #1e293b; --text: #f1f5f9; --muted: #94a3b8; --accent: #38bdf8; --danger: #f87171; --border: #334155; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: ui-sans-serif, system-ui, sans-serif; background: var(--bg); color: var(--text); line-height: 1.5; }
        .wrap { max-width: 960px; margin: 0 auto; padding: 2rem 1.25rem; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        h1 { margin: 0; font-size: 1.5rem; }
        a { color: var(--accent); text-decoration: none; }
        a:hover { text-decoration: underline; }
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .btn { display: inline-block; padding: 0.5rem 1rem; border-radius: 8px; border: none; cursor: pointer; font-size: 0.875rem; text-decoration: none; }
        .btn-primary { background: var(--accent); color: #0f172a; font-weight: 600; }
        .btn-danger { background: transparent; color: var(--danger); border: 1px solid var(--danger); }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 0.75rem; border-bottom: 1px solid var(--border); }
        th { color: var(--muted); font-weight: 500; font-size: 0.75rem; text-transform: uppercase; }
        label { display: block; margin-bottom: 0.25rem; color: var(--muted); font-size: 0.875rem; }
        input { width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--border); background: var(--bg); color: var(--text); margin-bottom: 1rem; }
        .flash { padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; background: #14532d; color: #bbf7d0; }
        .errors { background: #450a0a; color: #fecaca; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .muted { color: var(--muted); font-size: 0.875rem; }
        code { background: var(--bg); padding: 0.125rem 0.375rem; border-radius: 4px; font-size: 0.8125rem; }
    </style>
</head>
<body>
    <div class="wrap">
        <header>
            <h1><a href="{{ route('central.tenants.index') }}">Orora Admin</a></h1>
            <nav>
                <a href="{{ url('/') }}">App home</a>
                &nbsp;·&nbsp;
                <a href="{{ route('central.tenants.index') }}">Tenants</a>
                &nbsp;·&nbsp;
                <a href="{{ route('central.tenants.create') }}">New tenant</a>
            </nav>
        </header>

        @if (session('success'))
            <div class="flash">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>
