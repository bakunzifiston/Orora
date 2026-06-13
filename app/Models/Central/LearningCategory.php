<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LearningCategory extends CentralModel
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(LearningPost::class, 'category_id');
    }

    public function publishedPosts(): HasMany
    {
        return $this->posts()->published();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function contentCounts(): array
    {
        $posts = $this->publishedPosts()->get(['content_type']);

        return [
            'articles' => $posts->where('content_type', 'article')->count(),
            'videos' => $posts->where('content_type', 'video')->count(),
            'pdfs' => $posts->where('content_type', 'pdf')->count(),
            'total' => $posts->count(),
        ];
    }
}
