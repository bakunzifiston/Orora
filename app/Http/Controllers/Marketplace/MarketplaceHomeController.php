<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Services\Marketplace\MarketplaceHomeService;
use Illuminate\View\View;

class MarketplaceHomeController extends Controller
{
    public function __construct(private readonly MarketplaceHomeService $home) {}

    public function index(): View
    {
        return view('marketplace.home', [
            'activePage' => 'home',
            'categories' => $this->home->categories(),
            'featuredListings' => $this->home->featuredListings(),
            'landingStats' => $this->home->landingStats(),
        ]);
    }
}
