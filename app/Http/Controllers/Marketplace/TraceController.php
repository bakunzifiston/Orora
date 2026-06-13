<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marketplace\AnimalTraceLookupRequest;
use App\Services\Marketplace\AnimalTraceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class TraceController extends Controller
{
    public function __construct(private readonly AnimalTraceService $trace) {}

    public function index(): View
    {
        return view('marketplace.trace.index', [
            'activePage' => 'trace',
        ]);
    }

    public function lookup(AnimalTraceLookupRequest $request): RedirectResponse|View
    {
        $tagNumber = $request->validated('tag_number');
        $matches = $this->trace->findByTag($tagNumber);

        if ($matches->isEmpty()) {
            return view('marketplace.trace.not-found', [
                'activePage' => 'trace',
                'tagNumber' => $tagNumber,
            ]);
        }

        if ($matches->count() === 1) {
            return redirect()->route('marketplace.trace.show', $matches->first());
        }

        return view('marketplace.trace.results', [
            'activePage' => 'trace',
            'tagNumber' => $tagNumber,
            'matches' => $matches,
        ]);
    }

    public function show(int $animal): View|RedirectResponse
    {
        $profile = $this->trace->resolve($animal);

        if (! $profile) {
            abort(404);
        }

        return view('marketplace.trace.show', $this->trace->reportContext($profile) + [
            'activePage' => 'trace',
        ]);
    }

    public function pdf(int $animal): Response
    {
        $profile = $this->trace->resolve($animal);

        if (! $profile) {
            abort(404);
        }

        return $this->trace->pdfResponse($profile);
    }
}
