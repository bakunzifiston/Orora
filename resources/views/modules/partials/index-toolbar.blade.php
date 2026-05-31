<div class="dash-index-toolbar dash-panel">
    <div class="dash-index-toolbar__stats">
        {{ $stats }}
    </div>
    @if (isset($filters))
        <div @class(['dash-index-toolbar__filters', 'dash-index-toolbar__filters--wide' => $filtersWide ?? false])>
            {{ $filters }}
        </div>
    @endif
</div>
