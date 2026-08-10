@extends('layouts.breeding-module')

@section('title', __('Breeding — Birth records'))

@section('breeding-content')
    @include('modules.partials.header', [
        'title' => __('Birth records'),
        'subtitle' => __('Calving and kidding events.'),
        'createRoute' => 'breeding.births.create',
        'createLabel' => '+ '. __('Record birth'),
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($births->isEmpty())
            <p class="dash-empty">{{ __('No births recorded yet.') }}</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Mother') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Alive') }}</th>
                            <th>{{ __('Stillborn') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($births as $birth)
                            <tr>
                                <td><strong>{{ $birth->birth_code }}</strong></td>
                                <td>{{ $birth->birth_date->format('M j, Y') }}</td>
                                <td>{{ $birth->motherAnimal->tag_number }}</td>
                                <td>{{ ucfirst($birth->birth_type) }}</td>
                                <td>{{ $birth->alive_offspring }}</td>
                                <td>{{ $birth->stillborn_offspring }}</td>
                                <td>
                                    <a href="{{ route('breeding.births.edit', $birth) }}" class="dash-btn-link">{{ __('Manage offspring') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $births->links() }}</div>
        @endif
    </div>
@endsection
