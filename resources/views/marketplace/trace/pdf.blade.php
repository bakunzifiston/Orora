<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Orora Trace — {{ $animal->tag_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #1a2e1a; line-height: 1.45; margin: 0; padding: 28px 32px; }
        .header { border-bottom: 2px solid #1b5e20; padding-bottom: 12px; margin-bottom: 16px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; border: none; padding: 0; }
        .logo { max-height: 42px; }
        .report-title { color: #1b5e20; font-size: 16px; font-weight: bold; text-align: right; margin: 0; }
        .report-meta { color: #5f6368; font-size: 9px; text-align: right; margin: 4px 0 0; }
        h2 { color: #1b5e20; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; margin: 16px 0 8px; border-bottom: 1px solid #c8e6c9; padding-bottom: 4px; }
        .divider { border-top: 1px solid #dde8dd; margin: 12px 0; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid td { width: 50%; vertical-align: top; border: none; padding: 0 12px 0 0; font-size: 10px; }
        .row { margin-bottom: 3px; }
        .label { color: #5f6368; }
        .value { font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 9.5px; }
        table.data th, table.data td { border: 1px solid #dde8dd; padding: 4px 5px; text-align: left; vertical-align: top; }
        table.data th { background: #f1f8e9; color: #1b5e20; }
        .health-status { color: #1b5e20; font-weight: bold; font-size: 11px; margin: 8px 0; }
        .empty { color: #5f6368; font-style: italic; font-size: 9.5px; }
        .footer-table { width: 100%; border-collapse: collapse; margin-top: 18px; border-top: 2px solid #1b5e20; padding-top: 12px; }
        .footer-table td { border: none; vertical-align: middle; padding: 10px 0 0; }
        .qr { width: 90px; height: 90px; }
        .verify { color: #1b5e20; font-weight: bold; font-size: 10px; }
        .token { font-family: DejaVu Sans Mono, monospace; font-size: 9px; color: #5f6368; }
        .url { font-size: 9px; color: #1b5e20; }
        .list-item { margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 45%;">
                    @if ($logoDataUri)
                        <img src="{{ $logoDataUri }}" alt="Orora Farm" class="logo">
                    @else
                        <strong style="color:#1b5e20;font-size:14px;">Orora Farm</strong>
                    @endif
                </td>
                <td style="width: 55%;">
                    <p class="report-title">ANIMAL TRACEABILITY REPORT</p>
                    <p class="report-meta">Generated: {{ $generatedAt->format('F j, Y') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <h2>Animal Identity</h2>
    <table class="grid">
        <tr>
            <td>
                <div class="row"><span class="label">Code:</span> <span class="value">{{ $animalCode }}</span></div>
                <div class="row"><span class="label">Tag:</span> <span class="value">{{ $animal->tag_number }}</span></div>
                <div class="row"><span class="label">Name:</span> <span class="value">{{ $animal->name ?: '—' }}</span></div>
                <div class="row"><span class="label">Type:</span> <span class="value">{{ $animal->species ?: '—' }}</span></div>
            </td>
            <td>
                <div class="row"><span class="label">Breed:</span> <span class="value">{{ $animal->breed ?: '—' }}</span></div>
                <div class="row"><span class="label">Gender:</span> <span class="value">{{ $animal->gender_label }}</span></div>
                <div class="row"><span class="label">Birth Date:</span> <span class="value">{{ $animal->date_of_birth?->format('M Y') ?: '—' }}</span></div>
                <div class="row"><span class="label">Age:</span> <span class="value">{{ $animal->age_label ?: '—' }}</span></div>
                <div class="row"><span class="label">Weight:</span> <span class="value">{{ $animal->weight_kg !== null ? number_format($animal->weight_kg, 0).' kg' : '—' }}</span></div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <h2>Farm Information</h2>
    <div class="row"><span class="label">Farm:</span> <span class="value">{{ $animal->farm?->name ?: '—' }}</span></div>
    <div class="row"><span class="label">Location:</span> <span class="value">{{ $farmLocation }}</span></div>
    <div class="row"><span class="label">Registration:</span> <span class="value">{{ $animal->farm?->registration_number ?: '—' }}</span></div>

    <div class="divider"></div>

    <h2>Health Status</h2>
    <p class="health-status">🟢 {{ strtoupper($animal->health_status) }}</p>
    <div class="row"><span class="label">Production:</span> <span class="value">{{ $animal->production_status ?: '—' }}</span></div>
    <div class="row"><span class="label">Last check:</span> <span class="value">{{ $lastHealthCheck?->format('M j, Y') ?: '—' }}</span></div>

    <p style="margin:10px 0 4px;font-weight:bold;">Vaccinations:</p>
    @if ($animal->vaccinations->isEmpty())
        <p class="empty">No vaccination records.</p>
    @else
        @foreach ($animal->vaccinations as $row)
            <div class="list-item">
                {{ $row->vaccination_date?->format('M Y') ?: '—' }}
                — {{ $row->vaccine_name }}
                — {{ $row->veterinarian_name ?: $row->administered_by ?: '—' }}
                — Next: {{ $row->next_due_date?->format('M Y') ?: '—' }}
            </div>
        @endforeach
    @endif

    <p style="margin:10px 0 4px;font-weight:bold;">Treatments:</p>
    @if ($animal->treatments->isEmpty())
        <p class="empty">No treatment records.</p>
    @else
        @foreach ($animal->treatments as $row)
            <div class="list-item">
                {{ $row->start_date?->format('M Y') ?: '—' }}
                — {{ $row->disease_name ?: $row->diagnosis ?: '—' }}
                — {{ $row->medicine_name ?: '—' }}
                — {{ $row->status ?: '—' }}
            </div>
        @endforeach
    @endif

    <div class="divider"></div>

    <h2>Movement History</h2>
    @if ($animal->movements->isEmpty())
        <p class="empty">No movement records.</p>
    @else
        @foreach ($animal->movements as $row)
            <div class="list-item">
                {{ $row->moved_on?->format('M Y') ?: '—' }}
                — {{ $row->fromFarm?->name ?: '—' }} → {{ $row->toFarm?->name ?: '—' }}
                — {{ $row->movement_type ?: '—' }}
            </div>
        @endforeach
    @endif

    <div class="divider"></div>

    <h2>Certificates</h2>
    @if ($animal->certificates->isEmpty())
        <p class="empty">No certificates.</p>
    @else
        @foreach ($animal->certificates as $row)
            <div class="list-item">
                {{ $row->certificate_type ?: 'Certificate' }}
                — {{ $row->certificate_number ?: '—' }}
                @if ($row->expires_on)
                    — Valid until {{ $row->expires_on->format('M Y') }}
                @elseif ($row->issued_on)
                    — Issued {{ $row->issued_on->format('M Y') }}
                @endif
            </div>
        @endforeach
    @endif

    @if ($breeding['has_history'])
        <div class="divider"></div>
        <h2>Breeding History</h2>
        <div class="row"><span class="label">Mother:</span> <span class="value">{{ $breeding['mother_label'] ?: '—' }}</span></div>
        <div class="row"><span class="label">Father:</span> <span class="value">{{ $breeding['father_label'] ?: '—' }}</span></div>
        <div class="row"><span class="label">Breeding type:</span> <span class="value">{{ $breeding['breeding_type'] ?: '—' }}</span></div>
        <div class="row"><span class="label">Birth type:</span> <span class="value">{{ $breeding['birth_type'] ?: '—' }}</span></div>
    @endif

    <table class="footer-table">
        <tr>
            <td style="width: 110px;">
                <img src="{{ $qrCodeUrl }}" alt="QR code" class="qr">
                <div style="font-size:8px;color:#5f6368;margin-top:4px;">Scan to verify online</div>
            </td>
            <td>
                <p class="verify">✅ Verified by Orora Farm</p>
                <p class="token">Token: {{ $verificationToken }}</p>
                <p class="url">{{ $traceUrl }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
