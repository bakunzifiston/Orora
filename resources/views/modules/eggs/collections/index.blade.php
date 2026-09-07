@extends('layouts.eggs-module')

@section('title', 'Egg collections')

@section('eggs-content')
    @include('modules.partials.header', [
        'title' => __('Collections'),
        'subtitle' => __('Daily egg collections by flock.'),
        'createRoute' => 'eggs.collections.create',
        'createLabel' => '+ '. __('Record collection'),
    ])
    @include('modules.partials.flash')

    @if ($collections->isEmpty())
        <div class="dash-panel dash-entity-empty">
            <p class="dash-empty">{{ __('No collections recorded yet.') }}</p>
            <a href="{{ route('eggs.collections.create') }}" class="dash-btn-save">{{ __('Record collection') }}</a>
        </div>
    @else
        <div class="dash-panel">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Flock') }}</th>
                        <th>{{ __('Eggs') }}</th>
                        <th>{{ __('Cracked') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($collections as $collection)
                        <tr>
                            <td>{{ $collection->collection_code }}</td>
                            <td>{{ $collection->collected_on->format('M j, Y') }}</td>
                            <td>{{ $collection->flock?->name }}</td>
                            <td>{{ number_format($collection->eggs_count) }}</td>
                            <td>{{ number_format($collection->cracked_count) }}</td>
                            <td>
                                <a href="{{ route('eggs.collections.edit', $collection) }}">{{ __('Edit') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="dash-pagination">{{ $collections->links() }}</div>
    @endif
@endsection
