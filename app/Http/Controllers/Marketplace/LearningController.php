<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marketplace\LearningSubscribeRequest;
use App\Models\Central\LearningCategory;
use App\Models\Central\LearningPost;
use App\Services\Marketplace\LearningPostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LearningController extends Controller
{
    public function __construct(private readonly LearningPostService $learning) {}

    public function index(Request $request): View
    {
        $posts = $this->learning->filter($request);

        return view('marketplace.learning.index', [
            'activePage' => 'learning',
            'posts' => $posts,
            'featuredPost' => $this->learning->featured(),
            'categories' => LearningCategory::query()->active()->orderBy('sort_order')->get(),
            'filters' => $request->only(['q', 'type', 'category', 'difficulty', 'language', 'content_type', 'sort']),
            'contentTypes' => config('marketplace.learning.content_types', []),
            'difficultyLevels' => config('marketplace.learning.difficulty_levels', []),
            'languages' => config('marketplace.learning.languages', []),
            'sortOptions' => config('marketplace.learning.sort_options', []),
        ]);
    }

    public function category(Request $request, LearningCategory $category): View
    {
        abort_unless($category->is_active, 404);

        $request->merge(['category' => $category->slug]);

        return view('marketplace.learning.category', [
            'activePage' => 'learning',
            'category' => $category,
            'posts' => $this->learning->filter($request),
            'counts' => $category->contentCounts(),
            'categories' => LearningCategory::query()->active()->orderBy('sort_order')->get(),
            'filters' => $request->only(['q', 'type', 'difficulty', 'language', 'content_type', 'sort']),
            'contentTypes' => config('marketplace.learning.content_types', []),
            'difficultyLevels' => config('marketplace.learning.difficulty_levels', []),
            'languages' => config('marketplace.learning.languages', []),
            'sortOptions' => config('marketplace.learning.sort_options', []),
        ]);
    }

    public function show(LearningPost $post): View
    {
        abort_unless($post->is_published && $post->published_at?->isPast(), 404);

        $this->learning->incrementViews($post);
        $post->load('category');

        return view('marketplace.learning.show', [
            'activePage' => 'learning',
            'post' => $post->fresh(),
            'relatedPosts' => $this->learning->related($post),
            'alsoRead' => $this->learning->related($post, 3),
        ]);
    }

    public function subscribe(LearningSubscribeRequest $request): RedirectResponse
    {
        return redirect()
            ->route('marketplace.learning')
            ->with('learning_subscribed', true)
            ->withFragment('newsletter');
    }
}
