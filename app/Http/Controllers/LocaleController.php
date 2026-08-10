<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        $supported = array_keys(config('localization.supported', []));

        if (! in_array($locale, $supported, true)) {
            abort(404);
        }

        $request->session()->put('locale', $locale);

        return back(fallback: url('/'));
    }
}
