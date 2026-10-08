@php
    $raw = trim((string) ($exception?->getMessage() ?: ''));
    $message = $raw !== '' ? __($raw) : __('You do not have permission to access this module.');

    if (auth('admin')->check()) {
        $homeUrl = url('/admin');
        $homeLabel = __('Go to home');
    } elseif (auth()->check()) {
        $homeRoute = app(\App\Services\WorkspaceAccessService::class)->homeRouteFor(auth()->user());
        $homeUrl = route($homeRoute);
        $homeLabel = __('Go to home');
    } else {
        $homeUrl = route('login');
        $homeLabel = __('Sign in');
    }

    $previous = url()->previous();
    $showBack = $previous && $previous !== url()->current() && $previous !== $homeUrl;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Access denied') }} — Orora</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --orora-ink: #002B2B;
            --orora-lime: #A4D400;
            --orora-muted: #64748b;
            --orora-bg: #f0f1f4;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(ellipse 80% 50% at 50% -10%, rgba(164, 212, 0, 0.16), transparent 55%),
                var(--orora-bg);
            color: var(--orora-ink);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .error-shell {
            width: 100%;
            max-width: 28rem;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 2.25rem 1.75rem 1.85rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 43, 43, 0.06);
        }
        .error-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 0 1.5rem;
            text-decoration: none;
            color: var(--orora-ink);
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: -0.02em;
        }
        .error-brand__mark {
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 0.45rem;
            background: var(--orora-ink);
            color: var(--orora-lime);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 800;
        }
        .error-icon {
            width: 3.75rem;
            height: 3.75rem;
            margin: 0 auto 1.15rem;
            border-radius: 9999px;
            background: var(--orora-ink);
            border: 1px solid rgba(164, 212, 0, 0.28);
            color: var(--orora-lime);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-icon svg { width: 1.65rem; height: 1.65rem; }
        .error-title {
            margin: 0 0 0.55rem;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--orora-ink);
        }
        .error-message {
            margin: 0 0 0.45rem;
            font-size: 0.9375rem;
            line-height: 1.5;
            color: #334155;
        }
        .error-hint {
            margin: 0 0 1.5rem;
            font-size: 0.8125rem;
            line-height: 1.45;
            color: var(--orora-muted);
        }
        .error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            justify-content: center;
        }
        .error-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.65rem 1.15rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 650;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        .error-btn--primary {
            background: var(--orora-lime);
            color: #111;
        }
        .error-btn--primary:hover { filter: brightness(0.96); }
        .error-btn--ghost {
            background: transparent;
            color: var(--orora-muted);
        }
        .error-btn--ghost:hover { color: var(--orora-ink); }
    </style>
</head>
<body>
    <main class="error-shell" role="main">
        <a href="{{ $homeUrl }}" class="error-brand">
            <span class="error-brand__mark" aria-hidden="true">O</span>
            Orora
        </a>

        <div class="error-icon" aria-hidden="true">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
            </svg>
        </div>

        <h1 class="error-title">{{ __('Access denied') }}</h1>
        <p class="error-message">{{ $message }}</p>
        <p class="error-hint">{{ __('Ask a workspace admin if you need access to this area.') }}</p>

        <div class="error-actions">
            <a href="{{ $homeUrl }}" class="error-btn error-btn--primary">{{ $homeLabel }}</a>
            @if ($showBack)
                <a href="{{ $previous }}" class="error-btn error-btn--ghost">{{ __('Go back') }}</a>
            @endif
        </div>
    </main>
</body>
</html>
