<div class="farm-dash farms-page health-page">
    <div class="dash-page-header">
        <div>
            <h1 class="dash-welcome" style="margin-bottom: 0.35rem;">{{ $heading }}</h1>
            <p style="color: #808080; font-size: 0.875rem;">{{ $intro }}</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem;">
        @foreach ($documents as $key => $doc)
            <article class="dash-panel" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem;">
                <div>
                    <h2 class="whatsnew-item__title" style="margin: 0 0 0.35rem;">{{ __($doc['title']) }}</h2>
                    <p style="color: #808080; font-size: 0.875rem; margin: 0;">{{ __($doc['summary']) }}</p>
                </div>
                <dl style="display: grid; grid-template-columns: auto 1fr; gap: 0.25rem 0.75rem; font-size: 0.8125rem; margin: 0;">
                    <dt style="color: #808080;">{{ __('Version') }}</dt>
                    <dd style="margin: 0;">{{ $doc['version'] ?? '—' }}</dd>
                    <dt style="color: #808080;">{{ __('Last updated') }}</dt>
                    <dd style="margin: 0;">{{ $doc['updated'] ? \Illuminate\Support\Carbon::parse($doc['updated'])->format('M j, Y') : '—' }}</dd>
                    <dt style="color: #808080;">{{ __('Format') }}</dt>
                    <dd style="margin: 0;">PDF{{ $doc['pages'] ? ' · '.$doc['pages'].' '.__('pages') : '' }}{{ $doc['size'] ? ' · '.number_format($doc['size'] / 1048576, 1).' MB' : '' }}</dd>
                </dl>
                <div style="margin-top: auto;">
                    @if ($doc['available'])
                        <a href="{{ route($downloadRoute, $key) }}" class="dash-btn-save">{{ __('Download PDF') }}</a>
                    @else
                        <span style="color: #808080; font-size: 0.875rem;">{{ __('Not available yet.') }}</span>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
</div>
