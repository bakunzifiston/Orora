<?php

namespace App\Http\Middleware;

use App\Services\Species\SpeciesProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSpeciesModule
{
    public function __construct(private readonly SpeciesProfile $species) {}

    public function handle(Request $request, Closure $next): Response
    {
        $module = $this->species->moduleForRoute($request->route()?->getName());

        if ($module === 'abattoir') {
            if (in_array('abattoir', $this->species->profile()['hidden_sale_sections'] ?? [], true)) {
                abort(404);
            }

            return $next($request);
        }

        if ($module && ! $this->species->allows($module)) {
            abort(404);
        }

        return $next($request);
    }
}
