@php
    $days = $daysUntilPregnancyCheck ?? null;
    $dueOn = $breedingRecord->pregnancy_check_due_on?->format('M j, Y');
@endphp
<div class="dash-alert dash-alert--warning" style="margin-bottom: 1rem;">
    @if ($pregnancyCheckDue ?? false)
        <strong>Pregnancy check due.</strong>
        This breeding was recorded on {{ $breedingRecord->breeding_date->format('M j, Y') }}.
        @if ($dueOn)
            The check was due on {{ $dueOn }} ({{ config('modules.breeding_pregnancy_check_due_days', 35) }} days after breeding).
        @endif
        @if ($days !== null && $days < 0)
            It is {{ abs($days) }} day(s) overdue.
        @endif
    @elseif ($days !== null && $days > 0)
        <strong>Pregnancy check scheduled.</strong>
        Due on {{ $dueOn ?? '—' }} (in {{ $days }} day(s), {{ config('modules.breeding_pregnancy_check_due_days', 35) }} days after breeding).
    @endif
    <p style="margin: 0.65rem 0 0;">
        <a href="{{ route('breeding.checks.create', ['breeding_record_id' => $breedingRecord->id]) }}" class="dash-btn-save">Record pregnancy check</a>
    </p>
</div>
