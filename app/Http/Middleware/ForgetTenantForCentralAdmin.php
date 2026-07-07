<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForgetTenantForCentralAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        TenantContext::forget();

        return $next($request);
    }
}
