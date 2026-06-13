@props(['categories'])

<nav class="learn-category-chips" aria-label="Browse by category">
    @foreach ($categories as $category)
        <a href="{{ route('marketplace.learning.category', $category) }}" class="learn-category-chip">
            {{ $category->icon }} {{ $category->name }}
        </a>
    @endforeach
</nav>
