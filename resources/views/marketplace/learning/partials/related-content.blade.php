@props(['posts', 'title' => 'Related content'])

@if ($posts->isNotEmpty())
    <section class="learn-related">
        <h2 class="learn-related__title">{{ $title }}</h2>
        <div class="learn-grid">
            @foreach ($posts as $post)
                @include('marketplace.learning.partials.content-card', ['post' => $post])
            @endforeach
        </div>
    </section>
@endif
