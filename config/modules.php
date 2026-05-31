<?php

return [

    'navigation' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'grid'],
        ['key' => 'farms', 'label' => 'Farms', 'route' => 'farms.index', 'icon' => 'farm'],
        ['key' => 'livestock', 'label' => 'Livestock', 'route' => 'livestock.index', 'icon' => 'livestock'],
        ['key' => 'animals', 'label' => 'Animals', 'route' => 'animals.index', 'icon' => 'animal'],
        ['key' => 'health', 'label' => 'Health', 'route' => 'health.overview', 'icon' => 'health'],
        ['key' => 'feeding', 'label' => 'Feeding', 'route' => 'feeding.overview', 'icon' => 'feeding'],
        ['key' => 'expenses', 'label' => 'Expenses', 'route' => 'expenses.overview', 'icon' => 'expense'],
        ['key' => 'milk', 'label' => 'Milk', 'route' => 'milk.overview', 'icon' => 'milk'],
        ['key' => 'certificates', 'label' => 'Certificates', 'route' => 'certificates.index', 'icon' => 'certificate'],
        ['key' => 'movement', 'label' => 'Movement', 'route' => 'movements.index', 'icon' => 'movement'],
        ['key' => 'sales', 'label' => 'Sales', 'route' => 'sales.index', 'icon' => 'sale'],
        ['key' => 'settings', 'label' => 'Profile', 'route' => 'profile.edit', 'icon' => 'gear'],
    ],

    'species' => ['cattle', 'goat', 'sheep', 'pig', 'poultry', 'rabbit', 'fish', 'other'],

    'herd_groups' => [
        'Calves Group',
        'Heifer',
        'Cows (lactating)',
        'Pregnant Cows',
        'Dry Cows',
        'Bulls',
        'Steer (castrated male)',
        'Other',
    ],

    'livestock_types' => [
        'Cattle',
        'Goat',
        'Sheep',
        'Pig',
        'Poultry',
        'Rabbit',
        'Fish',
        'Camel',
        'Donkey',
        'Horse',
        'Bee Colony',
        'Mixed Livestock',
        'Other',
    ],

    'production_purposes' => [
        'Meat Production',
        'Dairy Production',
        'Egg Production',
        'Breeding',
        'Other',
    ],

    'farming_methods' => [
        'Intensive',
        'Semi-Intensive',
        'Extensive',
        'Free Range',
        'Zero Grazing',
        'Pasture-Based',
        'Organic',
        'Commercial',
        'Backyard',
        'Mixed System',
        'Nomadic / Pastoral',
        'Feedlot',
        'Cage System',
        'Deep Litter',
        'Free Run',
        'Hydroponic Integrated',
        'Other',
    ],

    'feeding_methods' => [
        'Stall Feeding',
        'Grazing',
        'Rotational Grazing',
        'Zero Grazing (Cut-and-Carry)',
        'Semi-Intensive Feeding',
        'Intensive Feeding',
        'Free Feeding (Ad libitum)',
        'Restricted Feeding (Controlled rationing)',
        'Hand Feeding',
        'Mechanical Feeding',
        'TMR (Total Mixed Ration)',
        'Pasture Grazing',
        'Supplementary Feeding',
        'Mixed Feeding System',
        'Automatic Feeding System',
        'Bucket Feeding (common in dairy calves)',
        'Creep Feeding (young animals)',
        'Other',
    ],

    'animal_statuses' => ['active', 'sold', 'deceased', 'transferred'],

    'animal_genders' => [
        'male' => 'Male',
        'female' => 'Female',
        'unknown' => 'Unknown',
    ],

    'acquisition_types' => [
        'Purchased',
        'Born on farm',
        'Gift',
        'Transfer',
        'Loan',
        'Auction',
        'Other',
    ],

    'health_statuses' => [
        'Healthy',
        'Sick',
        'Under treatment',
        'Quarantined',
        'Recovering',
        'Deceased',
    ],

    'expense_groups' => [
        'feed' => ['label' => 'Feed expenses', 'module' => 'feeding'],
        'health' => ['label' => 'Health expenses', 'module' => 'health'],
        'farm_operations' => ['label' => 'Farm operations', 'module' => 'farms'],
        'general' => ['label' => 'General / other', 'module' => null],
    ],

    'expense_sections' => [
        ['key' => 'overview', 'label' => 'Overview', 'route' => 'expenses.overview'],
        ['key' => 'categories', 'label' => 'Categories', 'route' => 'expenses.categories'],
        ['key' => 'vendors', 'label' => 'Vendors', 'route' => 'expenses.vendors'],
        ['key' => 'records', 'label' => 'Expense records', 'route' => 'expenses.records'],
    ],

    'milk_sections' => [
        ['key' => 'overview', 'label' => 'Overview', 'route' => 'milk.overview'],
        ['key' => 'records', 'label' => 'Milk records', 'route' => 'milk.records'],
    ],

    'milk_sessions' => ['Morning', 'Afternoon', 'Evening'],

    'milk_units' => ['L', 'ml'],

    'milk_quality_grades' => ['A', 'B', 'C', 'Rejected'],

    'expense_payment_methods' => ['Cash', 'Mobile money', 'Bank transfer', 'Cheque', 'Other'],

    'expense_statuses' => ['paid', 'draft', 'void'],

    'feeding_sections' => [
        ['key' => 'overview', 'label' => 'Overview', 'route' => 'feeding.overview'],
        ['key' => 'feed-types', 'label' => 'Feed types', 'route' => 'feeding.feed-types'],
        ['key' => 'inventory', 'label' => 'Inventory', 'route' => 'feeding.inventory'],
        ['key' => 'records', 'label' => 'Feeding records', 'route' => 'feeding.records'],
        ['key' => 'suppliers', 'label' => 'Suppliers', 'route' => 'feeding.suppliers'],
        ['key' => 'schedules', 'label' => 'Schedules', 'route' => 'feeding.schedules'],
    ],

    'feed_units' => ['kg', 'g', 'lb', 'bale', 'bag', 'liter'],

    'feed_movement_types' => [
        'purchase',
        'adjustment_in',
        'adjustment_out',
        'consumption',
    ],

    'feed_movement_labels' => [
        'purchase' => 'Purchase / stock in',
        'adjustment_in' => 'Adjustment (add)',
        'adjustment_out' => 'Adjustment (remove)',
        'consumption' => 'Consumption',
    ],

    'schedule_frequencies' => ['daily', 'weekly', 'biweekly', 'monthly'],

    'schedule_statuses' => ['active', 'paused', 'completed'],

    'feed_type_categories' => ['Forage', 'Concentrate', 'Supplement', 'Mineral', 'Other'],

    'health_sections' => [
        ['key' => 'overview', 'label' => 'Overview', 'route' => 'health.overview'],
        ['key' => 'vaccinations', 'label' => 'Vaccinations', 'route' => 'health.vaccinations'],
        ['key' => 'treatments', 'label' => 'Treatments', 'route' => 'health.treatments'],
        ['key' => 'vet-visits', 'label' => 'Vet visits', 'route' => 'health.vet-visits'],
        ['key' => 'mortality', 'label' => 'Mortality', 'route' => 'health.mortality'],
        ['key' => 'timeline', 'label' => 'Timeline', 'route' => 'health.timeline'],
    ],

    'health_record_types' => [
        'Vaccination',
        'Treatment',
        'Vet visit',
        'Illness',
        'Injury',
        'Deworming',
        'Quarantine',
        'Mortality',
        'Other',
    ],

    'vaccination_statuses' => [
        'Scheduled',
        'Completed',
        'Overdue',
        'Cancelled',
    ],

    'vaccine_types' => [
        'Live attenuated',
        'Inactivated',
        'Subunit',
        'Toxoid',
        'mRNA',
        'Vector',
        'Other',
    ],

    'administration_methods' => [
        'Injection',
        'Subcutaneous',
        'Intramuscular',
        'Intranasal',
        'Oral',
        'Pour-on',
        'Other',
    ],

    'treatment_statuses' => [
        'Ongoing',
        'Completed',
        'Discontinued',
        'Cancelled',
    ],

    'treatment_methods' => [
        'Oral',
        'Injection',
        'Topical',
        'Intravenous',
        'Intramuscular',
        'Subcutaneous',
        'Supportive care',
        'Other',
    ],

    'disposal_methods' => [
        'Burial',
        'Rendering',
        'Incineration',
        'Composting',
        'Other',
    ],

    'health_section_record_types' => [
        'vaccinations' => ['Vaccination'],
        'treatments' => ['Treatment'],
        'vet-visits' => ['Vet visit', 'Health checkup'],
        'mortality' => ['Mortality'],
    ],

    'production_statuses' => [
        'Lactating',
        'Dry',
        'Growing',
        'Breeding',
        'Fattening',
        'Gestating',
        'Not applicable',
    ],

    'lifecycle_statuses' => [
        'Active',
        'Sold',
        'Deceased',
        'Transferred',
        'Missing',
        'Retired',
    ],

    'current_conditions' => [
        'Good',
        'Fair',
        'Poor',
        'Critical',
        'Under observation',
    ],

    'record_statuses' => ['active', 'inactive'],

    'farm_statuses' => ['pending', 'active', 'inactive', 'suspended'],

    'ownership_types' => [
        'sole_proprietor' => 'Sole proprietor',
        'cooperative' => 'Cooperative',
        'company' => 'Company',
    ],

    'genders' => ['male', 'female', 'other'],

    'certificate_types' => ['health', 'vaccination', 'export', 'ownership', 'transport', 'other'],

    'certificate_statuses' => ['valid', 'expired', 'revoked'],

    'movement_types' => ['transfer', 'sale', 'death', 'export', 'other'],

    'payment_statuses' => ['pending', 'paid', 'partial'],

    'sexes' => ['male', 'female', 'unknown'],

];
