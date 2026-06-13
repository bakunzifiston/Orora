@props(['post'])

<article class="learn-card">
    <a href="{{ route('marketplace.learning.show', $post) }}" class="learn-card__media">
        @if ($post->thumbnailUrl())
            <img src="{{ $post->thumbnailUrl() }}" alt="" loading="lazy">
        @else
            <div class="learn-card__placeholder" aria-hidden="true">
                {{ $post->content_type === 'video' ? '▶' : ($post->content_type === 'pdf' ? '📄' : '📰') }}
            </div>
        @endif
        <span class="learn-card__badge">
            {{ $post->typeBadge() }}
            @if ($post->typeMeta())
                · {{ $post->typeMeta() }}
            @endif
        </span>
    </a>

    <div class="learn-card__body">
        @if ($post->category)
            <p class="learn-card__category">{{ $post->category->icon }} {{ $post->category->name }}</p>
        @endif

        <h3 class="learn-card__title">
            <a href="{{ route('marketplace.learning.show', $post) }}">{{ $post->title }}</a>
        </h3>

        @if ($post->excerpt)
            <p class="learn-card__excerpt">{{ \Illuminate\Support\Str::limit($post->excerpt, 120) }}</p>
        @endif

        <div class="learn-card__divider"></div>

        <div class="learn-card__meta">
            @if ($post->author_name)
                <span>👤 {{ $post->author_name }}</span>
            @endif
            @if ($post->readableDate())
                <span>📅 {{ $post->readableDate() }}</span>
            @endif
            <span>👁 {{ number_format($post->views_count) }} views</span>
            <span class="learn-card__difficulty learn-card__difficulty--{{ $post->difficulty_level }}">
                {{ $post->difficultyLabel() }}
            </span>
        </div>

        <a href="{{ route('marketplace.learning.show', $post) }}" class="learn-card__link">
            {{ $post->ctaLabel() }} →
        </a>
    </div>
</article>
