@extends('layouts.dashboard')

@section('title', $title)

@section('content')
    <div class="farm-dash farms-page health-page">
        <div class="dash-page-header">
            <div>
                @if ($listUrl)
                    <a href="{{ $listUrl }}" class="record-view__crumb">← {{ __($listLabel) }}</a>
                @endif
                <h1 class="dash-welcome" style="margin-bottom: 0.35rem;">{{ $title }}</h1>
                <p style="color: #808080; font-size: 0.875rem;">{{ __($label) }}</p>
            </div>
            <div class="dash-page-header__actions">
                @if ($listUrl)
                    <a href="{{ $listUrl }}" class="dash-btn-cancel">{{ __('Back') }}</a>
                @endif
                @if ($editUrl)
                    <a href="{{ $editUrl }}" class="dash-btn-save">{{ __('Edit') }}</a>
                @endif
            </div>
        </div>

        <div class="dash-panel record-view">
            <dl class="record-view__grid">
                @foreach ($fields as $field)
                    <div class="record-view__item {{ $field['long'] ? 'record-view__item--full' : '' }}">
                        <dt>{{ __($field['label']) }}</dt>
                        <dd>
                            @if ($field['value'] === null)
                                <span class="record-view__empty">—</span>
                            @elseif ($field['url'])
                                <a href="{{ $field['url'] }}" @if (! str_starts_with($field['url'], url('/'))) target="_blank" rel="noopener" @endif>{{ $field['value'] }}</a>
                            @elseif ($field['long'])
                                {!! nl2br(e($field['value'])) !!}
                            @else
                                {{ $field['value'] }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>

            @if ($createdAt || $updatedAt)
                <p class="record-view__stamps">
                    @if ($createdAt){{ __('Recorded :date', ['date' => $createdAt->format('M j, Y H:i')]) }}@endif
                    @if ($updatedAt && $createdAt && $updatedAt->ne($createdAt)) · {{ __('Last updated :date', ['date' => $updatedAt->format('M j, Y H:i')]) }}@endif
                </p>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <style>
        .record-view__crumb { display: inline-block; margin-bottom: 0.35rem; font-size: 0.8rem; font-weight: 600; color: #6b7280; text-decoration: none; }
        .record-view__crumb:hover { color: #002b2b; text-decoration: underline; }
        .record-view { padding: 1.25rem 1.4rem; }
        .record-view__grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0; margin: 0; }
        .record-view__item { padding: 0.8rem 0.25rem; border-bottom: 1px solid #f1f3f4; min-width: 0; }
        .record-view__item--full { grid-column: 1 / -1; }
        .record-view__item dt { font-size: 0.75rem; font-weight: 600; color: #6b7280; margin-bottom: 0.2rem; }
        .record-view__item dd { margin: 0; font-size: 0.95rem; font-weight: 600; color: #111; overflow-wrap: anywhere; }
        .record-view__item--full dd { font-weight: 400; line-height: 1.6; }
        .record-view__item dd a { color: #002b2b; text-decoration: underline; text-underline-offset: 2px; }
        .record-view__empty { color: #cbd5e1; font-weight: 400; }
        .record-view__stamps { margin: 1rem 0 0; font-size: 0.78rem; color: #9ca3af; }
        @media (max-width: 1000px) { .record-view__grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 560px) { .record-view__grid { grid-template-columns: 1fr; } }
    </style>
@endpush
