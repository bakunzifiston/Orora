@extends('layouts.marketplace')

@section('title', $post->title)

@section('content')
    <section class="learn-detail">
        <div class="mp-container">
            <nav class="learn-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('marketplace.home') }}">Home</a>
                <span>/</span>
                <a href="{{ route('marketplace.learning') }}">Learning</a>
                @if ($post->category)
                    <span>/</span>
                    <a href="{{ route('marketplace.learning.category', $post->category) }}">{{ $post->category->name }}</a>
                @endif
                <span>/</span>
                <span aria-current="page">{{ $post->title }}</span>
            </nav>

            <div class="learn-detail__grid">
                <article class="learn-detail__main">
                    <p class="learn-detail__meta-top">
                        @if ($post->category)
                            {{ $post->category->icon }} {{ $post->category->name }}
                        @endif
                        · {{ $post->typeBadge() }}
                    </p>

                    <h1 class="learn-detail__title">{{ $post->title }}</h1>

                    <div class="learn-detail__meta">
                        @if ($post->author_name)
                            <span>👤 {{ $post->author_name }}</span>
                        @endif
                        @if ($post->readableDate())
                            <span>📅 {{ $post->readableDate() }}</span>
                        @endif
                        @if ($post->typeMeta())
                            <span>· {{ $post->typeMeta() }}</span>
                        @endif
                        <span>👁 {{ number_format($post->views_count) }} views</span>
                        <span class="learn-card__difficulty learn-card__difficulty--{{ $post->difficulty_level }}">
                            {{ $post->difficultyLabel() }}
                        </span>
                    </div>

                    @if ($post->content_type === 'video' && $post->embedVideoUrl())
                        <div class="learn-detail__embed">
                            <iframe
                                src="{{ $post->embedVideoUrl() }}"
                                title="{{ $post->title }}"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                                loading="lazy"
                            ></iframe>
                        </div>
                    @elseif ($post->content_type === 'pdf')
                        <div class="learn-detail__pdf">
                            @if ($post->thumbnailUrl())
                                <img src="{{ $post->thumbnailUrl() }}" alt="PDF preview" class="learn-detail__pdf-preview">
                            @endif
                            <div class="learn-detail__pdf-actions">
                                @if ($post->pdfUrl())
                                    <a href="{{ $post->pdfUrl() }}" class="lp-btn lp-btn--primary" download>Download PDF</a>
                                    <a href="{{ $post->pdfUrl() }}" class="lp-btn lp-btn--outline" target="_blank" rel="noopener noreferrer">Preview in browser</a>
                                @endif
                                @if ($post->pdf_pages)
                                    <p class="learn-detail__pdf-pages">{{ $post->pdf_pages }} pages</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($post->content)
                        <div class="learn-detail__content mp-prose">
                            @if ($post->content_type === 'article')
                                {!! $post->content !!}
                            @else
                                {!! nl2br(e(strip_tags($post->content))) !!}
                            @endif
                        </div>
                    @elseif ($post->excerpt)
                        <div class="learn-detail__content mp-prose"><p>{{ $post->excerpt }}</p></div>
                    @endif

                    @if ($post->tags)
                        <div class="learn-detail__tags">
                            @foreach ($post->tags as $tag)
                                <span class="learn-tag">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if ($post->author_name)
                        <footer class="learn-detail__author-bio">
                            <h2>About the author</h2>
                            <div class="learn-detail__author-card">
                                @if ($post->authorPhotoUrl())
                                    <img src="{{ $post->authorPhotoUrl() }}" alt="" class="learn-detail__author-photo">
                                @else
                                    <div class="learn-detail__author-avatar" aria-hidden="true">{{ strtoupper(substr($post->author_name, 0, 1)) }}</div>
                                @endif
                                <div>
                                    <p class="learn-detail__author-name">{{ $post->author_name }}</p>
                                    @if ($post->author_title)
                                        <p class="learn-detail__author-title">{{ $post->author_title }}</p>
                                    @endif
                                </div>
                            </div>
                        </footer>
                    @endif
                </article>

                <aside class="learn-detail__sidebar">
                    @if ($post->author_name)
                        <section class="learn-sidebar-block">
                            <h3>Author</h3>
                            <div class="learn-sidebar-author">
                                @if ($post->authorPhotoUrl())
                                    <img src="{{ $post->authorPhotoUrl() }}" alt="">
                                @else
                                    <div class="learn-detail__author-avatar">{{ strtoupper(substr($post->author_name, 0, 1)) }}</div>
                                @endif
                                <div>
                                    <p class="learn-sidebar-author__name">{{ $post->author_name }}</p>
                                    @if ($post->author_title)
                                        <p class="learn-sidebar-author__title">{{ $post->author_title }}</p>
                                    @endif
                                </div>
                            </div>
                        </section>
                    @endif

                    @if ($post->tableOfContents())
                        <section class="learn-sidebar-block">
                            <h3>In this article</h3>
                            <ul class="learn-sidebar-toc">
                                @foreach ($post->tableOfContents() as $heading)
                                    <li>{{ $heading }}</li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    <section class="learn-sidebar-block">
                        <h3>Share</h3>
                        <div class="learn-share" data-share-url="{{ url()->current() }}" data-share-title="{{ $post->title }}">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener noreferrer">Facebook</a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener noreferrer">Twitter</a>
                            <a href="https://wa.me/?text={{ urlencode($post->title.' '.url()->current()) }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                            <button type="button" data-copy-link>Copy Link</button>
                        </div>
                    </section>

                    @if ($alsoRead->isNotEmpty())
                        <section class="learn-sidebar-block">
                            <h3>Also read</h3>
                            <ul class="learn-sidebar-links">
                                @foreach ($alsoRead as $item)
                                    <li><a href="{{ route('marketplace.learning.show', $item) }}">{{ $item->title }}</a></li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                </aside>
            </div>

            @include('marketplace.learning.partials.related-content', [
                'posts' => $relatedPosts,
                'title' => 'More in '.($post->category?->name ?? 'this topic'),
            ])
        </div>
    </section>
@endsection

@push('scripts')
    @vite('resources/js/marketplace-learning.js')
@endpush
