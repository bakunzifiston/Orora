<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Services\Marketplace\MarketplaceHomeService;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __construct(private readonly MarketplaceHomeService $home) {}

    public function index(): View
    {
        return view('marketplace.about.index', [
            'activePage' => 'about',
            'stats' => $this->home->aboutStats(),
            'about' => config('marketplace.about'),
            'testimonials' => config('marketplace.testimonials'),
        ]);
    }
}
