<?php

namespace App\Http\Middleware;

use App\Services\WorkspaceAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceAdmin
{
    public function __construct(private readonly WorkspaceAccessService $access) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if (! $user || ! $this->access->isActive($user) || ! $this->access->isWorkspaceAdmin($user)) {
            abort(403, __('Only workspace admins can manage users.'));
        }

        return $next($request);
    }
}
