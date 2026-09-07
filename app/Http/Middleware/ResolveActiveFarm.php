<?php

namespace App\Http\Middleware;

use App\Services\Species\ActiveFarmContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveActiveFarm
{
    public function __construct(private readonly ActiveFarmContext $farms) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && ! $request->is('admin', 'admin/*')) {
            $this->farms->rememberFromRequest();
        }

        return $next($request);
    }
}
