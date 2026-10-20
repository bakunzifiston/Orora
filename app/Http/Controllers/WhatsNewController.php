<?php

namespace App\Http\Controllers;

use App\Models\Central\PlatformRelease;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsNewController extends Controller
{
    public function index(Request $request): View
    {
        $category = array_key_exists((string) $request->input('category'), config('releases.categories'))
            ? $request->input('category')
            : null;

        $releases = PlatformRelease::query()
            ->visibleToTenant($request->user()->tenant_id)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderByDesc('released_on')
            ->orderByDesc('published_at')
            ->paginate(15)
            ->withQueryString();

        // Viewing the page counts as reading the release notices it lists.
        $request->user()->unreadNotifications()
            ->whereIn('platform_release_id', $releases->pluck('id'))
            ->update(['read_at' => now()]);

        return view('whats-new.index', [
            'activeNav' => 'whats-new',
            'releases' => $releases,
            'selectedCategory' => $category,
        ]);
    }
}
