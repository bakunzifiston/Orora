<?php

/**
 * Read-only detail pages ("View") for module records that have no dedicated show page.
 *
 * Each entry registers GET {uri}/{record} named "{route}.show" inside the module-access
 * group, so workspace permissions and species rules apply as for the record's other pages.
 *
 * Keys: route (name prefix), uri, model, label (singular), list (plural), nav (sidebar key),
 * hidden (attributes not shown), labels (attribute => custom label),
 * title (relation.attribute paths tried in order for the page title), title_prefix.
 */
return [
    'breeding-records' => [
        'title' => ['femaleAnimal.tag_number'], 'title_prefix' => 'Breeding',
        'route' => 'breeding.records', 'uri' => 'breeding/records', 'model' => App\Models\BreedingRecord::class,
        'label' => 'Breeding record', 'list' => 'Breeding records', 'nav' => 'breeding',
        'labels' => ['female_animal_id' => 'Female', 'male_animal_id' => 'Male'],
    ],
    'birth-records' => [
        'title' => ['motherAnimal.tag_number'], 'title_prefix' => 'Birth',
        'route' => 'breeding.births', 'uri' => 'breeding/births', 'model' => App\Models\BirthRecord::class,
        'label' => 'Birth record', 'list' => 'Births', 'nav' => 'breeding',
    ],
    'certificates' => [
        'route' => 'certificates', 'uri' => 'certificates', 'model' => App\Models\Certificate::class,
        'label' => 'Certificate', 'list' => 'Certificates', 'nav' => 'certificates',
    ],
    'egg-collections' => [
        'title' => ['flock.name'], 'title_prefix' => 'Eggs',
        'route' => 'eggs.collections', 'uri' => 'eggs/collections', 'model' => App\Models\EggCollection::class,
        'label' => 'Egg collection', 'list' => 'Collections', 'nav' => 'eggs',
    ],
    'expense-categories' => [
        'route' => 'expenses.categories', 'uri' => 'expenses/categories', 'model' => App\Models\ExpenseCategory::class,
        'label' => 'Expense category', 'list' => 'Categories', 'nav' => 'expenses',
    ],
    'expenses' => [
        'route' => 'expenses.records', 'uri' => 'expenses/records', 'model' => App\Models\Expense::class,
        'label' => 'Expense', 'list' => 'Expenses', 'nav' => 'expenses',
        'hidden' => ['source_type', 'source_id'],
    ],
    'expense-vendors' => [
        'route' => 'expenses.vendors', 'uri' => 'expenses/vendors', 'model' => App\Models\ExpenseVendor::class,
        'label' => 'Vendor', 'list' => 'Vendors', 'nav' => 'expenses',
    ],
    'feed-types' => [
        'route' => 'feeding.feed-types', 'uri' => 'feeding/feed-types', 'model' => App\Models\FeedType::class,
        'label' => 'Feed type', 'list' => 'Feed types', 'nav' => 'feeding',
        'hidden' => ['feed_supplier_id'],
    ],
    'feed-inventory' => [
        'title' => ['feedType.name'],
        'route' => 'feeding.inventory', 'uri' => 'feeding/inventory', 'model' => App\Models\FeedInventory::class,
        'label' => 'Feed inventory', 'list' => 'Inventory', 'nav' => 'feeding',
        'labels' => ['quantity_on_hand' => 'Quantity in stock'],
    ],
    'feeding-schedules' => [
        'title' => ['feedType.name'], 'title_prefix' => 'Schedule',
        'route' => 'feeding.schedules', 'uri' => 'feeding/schedules', 'model' => App\Models\FeedingSchedule::class,
        'label' => 'Feeding schedule', 'list' => 'Schedules', 'nav' => 'feeding',
        'hidden' => ['days_of_week'],
    ],
    'feedings' => [
        'title' => ['feedType.name'], 'title_prefix' => 'Feeding',
        'route' => 'feeding.records', 'uri' => 'feeding/records', 'model' => App\Models\Feeding::class,
        'label' => 'Feeding record', 'list' => 'Feeding records', 'nav' => 'feeding',
    ],
    'dewormings' => [
        'route' => 'health.dewormings', 'uri' => 'health/dewormings', 'model' => App\Models\Deworming::class,
        'label' => 'Deworming', 'list' => 'Dewormings', 'nav' => 'health',
    ],
    'disease-records' => [
        'route' => 'health.disease', 'uri' => 'health/disease', 'model' => App\Models\DiseaseRecord::class,
        'label' => 'Disease record', 'list' => 'Disease records', 'nav' => 'health',
    ],
    'mortalities' => [
        'title' => ['animal.tag_number', 'flock.name'], 'title_prefix' => 'Mortality',
        'route' => 'health.mortalities', 'uri' => 'health/mortalities', 'model' => App\Models\Mortality::class,
        'label' => 'Mortality', 'list' => 'Mortalities', 'nav' => 'health',
    ],
    'treatments' => [
        'route' => 'health.treatments', 'uri' => 'health/treatments', 'model' => App\Models\Treatment::class,
        'label' => 'Treatment', 'list' => 'Treatments', 'nav' => 'health',
    ],
    'vaccinations' => [
        'route' => 'health.vaccinations', 'uri' => 'health/vaccinations', 'model' => App\Models\Vaccination::class,
        'label' => 'Vaccination', 'list' => 'Vaccinations', 'nav' => 'health',
    ],
    'vet-visits' => [
        'route' => 'health.vet-visits', 'uri' => 'health/vet-visits', 'model' => App\Models\VetVisit::class,
        'label' => 'Vet visit', 'list' => 'Vet visits', 'nav' => 'health',
    ],
    'milk-distributions' => [
        'route' => 'milk.distribution', 'uri' => 'milk/distribution', 'model' => App\Models\MilkDistribution::class,
        'label' => 'Milk distribution', 'list' => 'Distribution', 'nav' => 'milk',
        'hidden' => ['storage_movement_id', 'sale_item_id'],
        'labels' => ['milk_storage_id' => 'Storage tank', 'sale_transaction_id' => 'Sale'],
    ],
    'milk-sessions' => [
        'route' => 'milk.sessions', 'uri' => 'milk/sessions', 'model' => App\Models\MilkSession::class,
        'label' => 'Milking session', 'list' => 'Milking sessions', 'nav' => 'milk',
    ],
    'milk-storage' => [
        'route' => 'milk.storage', 'uri' => 'milk/storage', 'model' => App\Models\MilkStorage::class,
        'label' => 'Storage tank', 'list' => 'Storage', 'nav' => 'milk',
    ],
    'movements' => [
        'title' => ['animal.tag_number', 'flock.name'], 'title_prefix' => 'Movement',
        'route' => 'movements', 'uri' => 'movements', 'model' => App\Models\Movement::class,
        'label' => 'Movement', 'list' => 'Movements', 'nav' => 'movement',
    ],
];
