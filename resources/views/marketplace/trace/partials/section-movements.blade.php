<section class="tr-block">
    <h2 class="tr-block__title">Movement History</h2>
    @if ($animal->movements->isEmpty())
        <p class="tr-empty">No movement records on file.</p>
    @else
        <div class="tr-table-wrap">
            <table class="tr-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Purpose</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($animal->movements as $movement)
                        <tr>
                            <td>{{ $movement->moved_on?->format('M Y') ?: '—' }}</td>
                            <td>{{ $movement->fromFarm?->name ?: '—' }}</td>
                            <td>{{ $movement->toFarm?->name ?: '—' }}</td>
                            <td>{{ $movement->movement_type ?: $movement->notes ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
