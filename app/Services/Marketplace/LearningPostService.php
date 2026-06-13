<?php

namespace App\Services\Marketplace;

use App\Models\Central\LearningPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class LearningPostService
{
    public function filter(Request $request, int $perPage = 12): LengthAwarePaginator
    {
        $query = LearningPost::query()
            ->published()
            ->with('category');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->byType($type);
        }

        if ($category = $request->input('category')) {
            $query->byCategory($category);
        }

        $difficulties = array_filter((array) $request->input('difficulty', []));
        if ($difficulties) {
            $query->whereIn('difficulty_level', $difficulties);
        }

        $languages = array_filter((array) $request->input('language', []));
        if ($languages) {
            $query->whereIn('language', $languages);
        }

        $types = array_filter((array) $request->input('content_type', []));
        if ($types) {
            $query->whereIn('content_type', $types);
        }

        match ($request->input('sort', 'newest')) {
            'views' => $query->orderByDesc('views_count'),
            'title' => $query->orderBy('title'),
            default => $query->orderByDesc('published_at'),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    public function featured(): ?LearningPost
    {
        return LearningPost::query()
            ->published()
            ->featured()
            ->with('category')
            ->orderByDesc('published_at')
            ->first()
            ?? LearningPost::query()->published()->with('category')->orderByDesc('published_at')->first();
    }

    public function related(LearningPost $post, int $limit = 3)
    {
        return LearningPost::query()
            ->published()
            ->with('category')
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function incrementViews(LearningPost $post): void
    {
        $post->increment('views_count');
    }
}
