@extends('layouts.milk-module')

@section('title', 'Edit milk storage')

@section('milk-content')
    @include('modules.partials.header', ['title' => 'Edit storage', 'backRoute' => 'milk.storage'])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('milk.storage.update', $milkStorage) }}" class="dash-farm-form">
        @csrf
        @method('PUT')
        @include('modules.milk.storage._form', ['milkStorage' => $milkStorage])
        <div class="dash-form-actions">
            <button type="submit" class="dash-btn-save">Save</button>
            <a href="{{ route('milk.storage') }}" class="dash-btn-cancel">Cancel</a>
        </div>
    </form>

    @if ($milkStorage->movements->isNotEmpty())
        <div class="dash-panel" style="margin-top: 1.25rem;">
            <div class="dash-panel-title">Recent movements</div>
            <ul class="dash-health-activity">
                @foreach ($milkStorage->movements as $movement)
                    <li>
                        <div>
                            <strong>{{ $movementLabels[$movement->movement_type] ?? $movement->movement_type }}</strong>
                            <span style="color: #808080;">{{ $movement->moved_at->format('M j, Y H:i') }}</span>
                        </div>
                        <span>{{ number_format($movement->quantity_liters, 2) }} L → {{ number_format($movement->balance_after, 2) }} L</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
