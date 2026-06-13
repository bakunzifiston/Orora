<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class LearningPost extends CentralModel
{
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content_type',
        'content',
        'video_url',
        'video_duration',
        'pdf_path',
        'pdf_pages',
        'thumbnail_path',
        'author_name',
        'author_title',
        'author_photo',
        'read_time',
        'difficulty_level',
        'language',
        'is_featured',
        'is_published',
        'views_count',
        'published_at',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'views_count' => 'integer',
            'read_time' => 'integer',
            'pdf_pages' => 'integer',
            'tags' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LearningCategory::class, 'category_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('content_type', $type);
    }

    public function scopeByCategory(Builder $query, int|string $category): Builder
    {
        if (is_numeric($category)) {
            return $query->where('category_id', $category);
        }

        return $query->whereHas('category', fn (Builder $q) => $q->where('slug', $category));
    }

    public function scopeByLanguage(Builder $query, string $language): Builder
    {
        return $query->where('language', $language);
    }

    public function scopeByDifficulty(Builder $query, string $level): Builder
    {
        return $query->where('difficulty_level', $level);
    }

    public function typeLabel(): string
    {
        return match ($this->content_type) {
            'video' => 'Video',
            'pdf' => 'PDF',
            default => 'Article',
        };
    }

    public function typeBadge(): string
    {
        return match ($this->content_type) {
            'video' => '🎥 Video',
            'pdf' => '📥 PDF',
            default => '📄 Article',
        };
    }

    public function typeMeta(): ?string
    {
        return match ($this->content_type) {
            'video' => $this->video_duration,
            'pdf' => $this->pdf_pages ? $this->pdf_pages.' pages' : null,
            default => $this->read_time ? $this->read_time.' min read' : null,
        };
    }

    public function difficultyLabel(): string
    {
        return config('marketplace.learning.difficulty_levels.'.$this->difficulty_level, ucfirst($this->difficulty_level));
    }

    public function languageLabel(): string
    {
        return config('marketplace.learning.languages.'.$this->language, strtoupper($this->language));
    }

    public function thumbnailUrl(): ?string
    {
        return $this->thumbnail_path ? asset($this->thumbnail_path) : null;
    }

    public function pdfUrl(): ?string
    {
        return $this->pdf_path ? asset($this->pdf_path) : null;
    }

    public function authorPhotoUrl(): ?string
    {
        return $this->author_photo ? asset($this->author_photo) : null;
    }

    public function embedVideoUrl(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        $url = $this->video_url;

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return str_contains($url, 'embed') ? $url : null;
    }

    public function readableDate(): ?string
    {
        return $this->published_at instanceof Carbon
            ? $this->published_at->format('M j, Y')
            : null;
    }

    /**
     * @return array<int, string>
     */
    public function tableOfContents(): array
    {
        if (! $this->content) {
            return [];
        }

        preg_match_all('/<h2[^>]*>(.*?)<\/h2>/i', $this->content, $matches);

        return array_values(array_filter(array_map(
            static fn (string $heading) => trim(strip_tags($heading)),
            $matches[1] ?? []
        )));
    }

    public function ctaLabel(): string
    {
        return match ($this->content_type) {
            'video' => 'Watch Now',
            'pdf' => 'Download PDF',
            default => 'Read More',
        };
    }
}
