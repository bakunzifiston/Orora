<?php

namespace App\Http\Middleware;

use App\Services\WorkspaceAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    public function __construct(private readonly WorkspaceAccessService $access) {}

    public function handle(Request $request, Closure $next, ?string $module = null, ?string $action = null): Response
    {
        $user = Auth::guard('web')->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if (! $this->access->isActive($user)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => __('Your account has been deactivated. Contact your workspace admin.')]);
        }

        if ($module) {
            $allowed = $this->access->can($user, $module, $action ?: 'view');
        } else {
            $allowed = $this->access->canAccessRoute(
                $user,
                $request->route()?->getName(),
                strtoupper($request->method())
            );
        }

        if (! $allowed) {
            abort(403, __('You do not have permission to access this module.'));
        }

        return $next($request);
    }
}
