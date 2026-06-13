<section class="tr-block">
    <h2 class="tr-block__title">Breeding History</h2>
    <dl class="tr-kv">
        <div class="tr-kv__row">
            <dt>Mother Tag</dt>
            <dd>{{ $breeding['mother_label'] ?: '—' }}</dd>
        </div>
        <div class="tr-kv__row">
            <dt>Father Tag</dt>
            <dd>{{ $breeding['father_label'] ?: '—' }}</dd>
        </div>
        <div class="tr-kv__row">
            <dt>Breeding Type</dt>
            <dd>{{ $breeding['breeding_type'] ?: '—' }}</dd>
        </div>
        <div class="tr-kv__row">
            <dt>Birth Type</dt>
            <dd>{{ $breeding['birth_type'] ?: '—' }}</dd>
        </div>
    </dl>
</section>
