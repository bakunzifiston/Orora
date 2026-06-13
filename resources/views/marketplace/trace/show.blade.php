@extends('layouts.marketplace')

@section('title', $animal->tag_number)

@section('content')
    <section class="tr-report">
        <div class="mp-container">
            <a href="{{ route('marketplace.trace') }}" class="tr-back">← Trace another animal</a>

            @include('marketplace.trace.partials.section-identity')
            @include('marketplace.trace.partials.section-farm')
            @include('marketplace.trace.partials.section-health')
            @include('marketplace.trace.partials.section-movements')
            @include('marketplace.trace.partials.section-certificates')

            @if ($breeding['has_history'])
                @include('marketplace.trace.partials.section-breeding')
            @endif
        </div>
    </section>

    @include('marketplace.trace.partials.verified-footer')
@endsection
