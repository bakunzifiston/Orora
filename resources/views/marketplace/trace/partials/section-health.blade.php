<section class="tr-block">
    <h2 class="tr-block__title">Health Status</h2>

    <dl class="tr-kv tr-kv--inline">
        <div class="tr-kv__row">
            <dt>Current Health Status</dt>
            <dd>
                <span class="tr-status tr-status--healthy">
                    <span class="tr-status__dot" aria-hidden="true"></span>
                    {{ $animal->health_status }}
                </span>
            </dd>
        </div>
        <div class="tr-kv__row">
            <dt>Production Status</dt>
            <dd>{{ $animal->production_status ?: '—' }}</dd>
        </div>
        <div class="tr-kv__row">
            <dt>Last Health Check</dt>
            <dd>{{ $lastHealthCheck?->format('M j, Y') ?: '—' }}</dd>
        </div>
    </dl>

    <h3 class="tr-subtitle">Vaccination History</h3>
    @if ($animal->vaccinations->isEmpty())
        <p class="tr-empty">No vaccination records on file.</p>
    @else
        <div class="tr-table-wrap">
            <table class="tr-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Vaccine</th>
                        <th>Vet</th>
                        <th>Next Due</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($animal->vaccinations as $vaccination)
                        <tr>
                            <td>{{ $vaccination->vaccination_date?->format('M Y') ?: '—' }}</td>
                            <td>{{ $vaccination->vaccine_name }}</td>
                            <td>{{ $vaccination->veterinarian_name ?: $vaccination->administered_by ?: '—' }}</td>
                            <td>{{ $vaccination->next_due_date?->format('M Y') ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <h3 class="tr-subtitle">Treatment History</h3>
    @if ($animal->treatments->isEmpty())
        <p class="tr-empty">No treatment records on file.</p>
    @else
        <div class="tr-table-wrap">
            <table class="tr-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Disease</th>
                        <th>Medicine</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($animal->treatments as $treatment)
                        <tr>
                            <td>{{ $treatment->start_date?->format('M Y') ?: '—' }}</td>
                            <td>{{ $treatment->disease_name ?: $treatment->diagnosis ?: '—' }}</td>
                            <td>{{ $treatment->medicine_name ?: '—' }}</td>
                            <td>{{ $treatment->status ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
