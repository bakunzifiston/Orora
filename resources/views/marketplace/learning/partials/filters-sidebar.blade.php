@props([
    'filters' => [],
    'categories' => [],
    'difficultyLevels' => [],
    'languages' => [],
    'contentTypes' => [],
    'baseRoute' => 'marketplace.learning',
    'routeParams' => [],
])

<aside class="learn-filters" id="learn-filters">
    <form method="GET" action="{{ route($baseRoute, $routeParams) }}" class="learn-filters__form">
        @if ($filters['q'] ?? false)
            <input type="hidden" name="q" value="{{ $filters['q'] }}">
        @endif
        @if ($filters['type'] ?? false)
            <input type="hidden" name="type" value="{{ $filters['type'] }}">
        @endif

        <h3 class="learn-filters__title">Filters</h3>

        <div class="learn-filters__group">
            <label for="learn-category">Category</label>
            <select name="category" id="learn-category">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>
                        {{ $category->icon }} {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="learn-filters__group">
            <span class="learn-filters__label">Difficulty</span>
            @foreach ($difficultyLevels as $value => $label)
                <label class="learn-filters__check">
                    <input type="checkbox" name="difficulty[]" value="{{ $value }}" @checked(in_array($value, (array) ($filters['difficulty'] ?? []), true))>
                    {{ $label }}
                </label>
            @endforeach
        </div>

        <div class="learn-filters__group">
            <span class="learn-filters__label">Language</span>
            @foreach ($languages as $value => $label)
                <label class="learn-filters__check">
                    <input type="checkbox" name="language[]" value="{{ $value }}" @checked(in_array($value, (array) ($filters['language'] ?? []), true))>
                    {{ $label }}
                </label>
            @endforeach
        </div>

        <div class="learn-filters__group">
            <span class="learn-filters__label">Type</span>
            @foreach ($contentTypes as $value => $label)
                <label class="learn-filters__check">
                    <input type="checkbox" name="content_type[]" value="{{ $value }}" @checked(in_array($value, (array) ($filters['content_type'] ?? []), true))>
                    {{ $label }}
                </label>
            @endforeach
        </div>

        <div class="learn-filters__actions">
            <button type="submit" class="lp-btn lp-btn--primary lp-btn--block">Apply</button>
            <a href="{{ route($baseRoute, $routeParams) }}" class="lp-btn lp-btn--outline lp-btn--block">Reset</a>
        </div>
    </form>
</aside>
