<section class="tr-block">
    <h2 class="tr-block__title">Certificates</h2>
    @if ($animal->certificates->isEmpty())
        <p class="tr-empty">No certificates on file.</p>
    @else
        <div class="tr-table-wrap">
            <table class="tr-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Number</th>
                        <th>Issued</th>
                        <th>Expires</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($animal->certificates as $certificate)
                        <tr>
                            <td>{{ $certificate->certificate_type ?: '—' }}</td>
                            <td>{{ $certificate->certificate_number ?: '—' }}</td>
                            <td>{{ $certificate->issued_on?->format('M Y') ?: '—' }}</td>
                            <td>{{ $certificate->expires_on?->format('M Y') ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
