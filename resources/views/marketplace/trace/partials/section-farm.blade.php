<section class="tr-block">
    <h2 class="tr-block__title">Farm Information</h2>
    <dl class="tr-kv">
        <div class="tr-kv__row">
            <dt>Farm Name</dt>
            <dd>{{ $animal->farm?->name ?: '—' }}</dd>
        </div>
        <div class="tr-kv__row">
            <dt>Location</dt>
            <dd>{{ $farmLocation }}</dd>
        </div>
        <div class="tr-kv__row">
            <dt>Livestock Group</dt>
            <dd>{{ $livestockGroup }}</dd>
        </div>
        <div class="tr-kv__row">
            <dt>Registration</dt>
            <dd>{{ $animal->farm?->registration_number ?: '—' }}</dd>
        </div>
    </dl>
</section>
