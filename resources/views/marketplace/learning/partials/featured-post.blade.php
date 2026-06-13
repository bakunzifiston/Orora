@props(['post'])

@if ($post)
    <article class="learn-featured">
        <a href="{{ route('marketplace.learning.show', $post) }}" class="learn-featured__media">
            @if ($post->thumbnailUrl())
                <img src="{{ $post->thumbnailUrl() }}" alt="" loading="lazy">
            @else
                <div class="learn-featured__placeholder">{{ $post->content_type === 'video' ? '▶' : '📄' }}</div>
            @endif
        </a>

        <div class="learn-featured__body">
            <p class="learn-featured__meta">
                {{ $post->typeBadge() }}
                @if ($post->category)
                    · {{ $post->category->icon }} {{ $post->category->name }}
                @endif
            </p>

            <h2 class="learn-featured__title">
                <a href="{{ route('marketplace.learning.show', $post) }}">{{ $post->title }}</a>
            </h2>

            @if ($post->excerpt)
                <p class="learn-featured__excerpt">{{ $post->excerpt }}</p>
            @endif

            <div class="learn-featured__author">
                @if ($post->author_name)
                    <span>👤 {{ $post->author_name }}</span>
                @endif
                @if ($post->readableDate())
                    <span>📅 {{ $post->readableDate() }}</span>
                @endif
                @if ($post->typeMeta())
                    <span>· {{ $post->typeMeta() }}</span>
                @endif
            </div>

            <a href="{{ route('marketplace.learning.show', $post) }}" class="lp-btn lp-btn--primary">
                {{ $post->ctaLabel() }} →
            </a>
        </div>
    </article>
@endif
