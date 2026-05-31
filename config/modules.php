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
        ['key' => 'breeding', 'label' => 'Breeding', 'route' => 'breeding.overview', 'icon' => 'breeding'],
        ['key' => 'certificates', 'label' => 'Certificates', 'route' => 'certificates.index', 'icon' => 'certificate'],
        ['key' => 'movement', 'label' => 'Movement', 'route' => 'movements.index', 'icon' => 'movement'],
        ['key' => 'sales', 'label' => 'Sales', 'route' => 'sales.overview', 'icon' => 'sale'],
        ['key' => 'customers', 'label' => 'Customers', 'route' => 'customers.overview', 'icon' => 'customer'],
        ['key' => 'finance', 'label' => 'Finance', 'route' => 'finance.overview', 'icon' => 'finance'],
        ['key' => 'employees', 'label' => 'Employees', 'route' => 'employees.overview', 'icon' => 'employee'],
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

    /** Herd group labels that imply milking eligibility when an animal has no production status set. */
    'milking_herd_groups' => [
        'Cows (lactating)',
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
        'Pregnant',
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

    'sale_sections' => [
        ['key' => 'overview', 'label' => 'Overview', 'route' => 'sales.overview'],
        ['key' => 'transactions', 'label' => 'Transactions', 'route' => 'sales.transactions'],
        ['key' => 'abattoir', 'label' => 'Abattoir', 'route' => 'sales.abattoir'],
    ],

    'sale_types' => ['animal_sale', 'meat_sale', 'milk_sale'],

    'sale_type_labels' => [
        'animal_sale' => 'Animal sale',
        'meat_sale' => 'Meat sale',
        'milk_sale' => 'Milk sale',
    ],

    'sale_statuses' => ['draft', 'confirmed', 'completed', 'cancelled', 'refunded'],

    'sale_status_labels' => [
        'draft' => 'In progress',
        'confirmed' => 'Confirmed',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ],

    'sale_payment_statuses' => ['unpaid', 'partial', 'paid', 'overdue'],

    'sale_pricing_methods' => [
        'per_animal' => 'Per animal (head)',
        'per_kg' => 'Per kg (live weight)',
        'per_liter' => 'Per liter',
    ],

    'sale_item_types' => ['animal', 'meat_cut', 'milk'],

    'sale_delivery_methods' => ['pickup', 'delivery', 'abattoir_transfer'],

    'buyer_types' => ['individual', 'cooperative', 'company', 'abattoir', 'exporter'],

    'customer_sections' => [
        ['key' => 'overview', 'label' => 'Overview', 'route' => 'customers.overview'],
        ['key' => 'directory', 'label' => 'Directory', 'route' => 'customers.directory'],
        ['key' => 'communications', 'label' => 'Communications', 'route' => 'customers.communications'],
    ],

    'customer_types' => [
        'individual' => 'Individual',
        'company' => 'Company',
        'cooperative' => 'Cooperative',
        'abattoir' => 'Abattoir',
        'exporter' => 'Exporter',
    ],

    'customer_statuses' => ['active', 'inactive', 'blacklisted'],

    'customer_trust_levels' => [
        'new' => 'New',
        'regular' => 'Regular',
        'trusted' => 'Trusted',
        'vip' => 'VIP',
    ],

    'customer_address_types' => [
        'billing' => 'Billing',
        'delivery' => 'Delivery',
        'physical' => 'Physical',
        'other' => 'Other',
    ],

    'customer_communication_types' => [
        'call' => 'Phone call',
        'email' => 'Email',
        'visit' => 'Visit',
        'meeting' => 'Meeting',
        'whatsapp' => 'WhatsApp',
        'note' => 'Note',
    ],

    'customer_communication_directions' => [
        'inbound' => 'Inbound',
        'outbound' => 'Outbound',
    ],

    'customer_document_types' => [
        'national_id' => 'National ID',
        'contract' => 'Contract',
        'license' => 'License',
        'tax_certificate' => 'Tax certificate',
        'registration' => 'Registration',
        'other' => 'Other',
    ],

    'customer_genders' => [
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
    ],

    'employee_sections' => [
        ['key' => 'overview', 'label' => 'Overview', 'route' => 'employees.overview'],
        ['key' => 'directory', 'label' => 'Directory', 'route' => 'employees.directory'],
    ],

    'employee_statuses' => ['active', 'inactive', 'on_leave', 'terminated'],

    'employee_employment_types' => [
        'full_time' => 'Full time',
        'part_time' => 'Part time',
        'seasonal' => 'Seasonal',
        'contractor' => 'Contractor',
        'intern' => 'Intern',
    ],

    'employee_job_roles' => [
        'farm_manager' => 'Farm manager',
        'herd_manager' => 'Herd manager',
        'milker' => 'Milker',
        'feeder' => 'Feeder / stock handler',
        'vet_assistant' => 'Vet assistant',
        'driver' => 'Driver',
        'security' => 'Security',
        'admin' => 'Admin / office',
        'farm_worker' => 'General farm worker',
        'other' => 'Other',
    ],

    'employee_contract_types' => [
        'permanent' => 'Permanent',
        'fixed_term' => 'Fixed term',
        'casual' => 'Casual / daily',
    ],

    'employee_pay_frequencies' => [
        'monthly' => 'Monthly',
        'weekly' => 'Weekly',
        'biweekly' => 'Bi-weekly',
        'daily' => 'Daily',
    ],

    'employee_address_types' => [
        'physical' => 'Physical',
        'mailing' => 'Mailing',
        'other' => 'Other',
    ],

    'employee_document_types' => [
        'national_id' => 'National ID',
        'contract' => 'Employment contract',
        'certificate' => 'Training certificate',
        'medical' => 'Medical clearance',
        'license' => 'License / permit',
        'other' => 'Other',
    ],

    'employee_marital_statuses' => [
        'single' => 'Single',
        'married' => 'Married',
        'divorced' => 'Divorced',
        'widowed' => 'Widowed',
        'other' => 'Other',
    ],

    'buyer_trust_levels' => ['new_buyer', 'regular', 'trusted'],

    'buyer_statuses' => ['active', 'inactive'],

    'abattoir_dispatch_statuses' => ['pending', 'dispatched', 'processing', 'returned'],

    'abattoir_cut_types' => ['whole_carcass', 'hindquarter', 'forequarter', 'ribs', 'offal', 'mixed'],

    'sale_document_types' => ['invoice', 'receipt', 'delivery_note', 'certificate', 'permit'],

    'milk_sections' => [
        ['key' => 'overview', 'label' => 'Overview', 'route' => 'milk.overview'],
        ['key' => 'sessions', 'label' => 'Milking sessions', 'route' => 'milk.sessions'],
        ['key' => 'storage', 'label' => 'Storage', 'route' => 'milk.storage'],
    ],

    'breeding_sections' => [
        ['key' => 'overview', 'label' => 'Overview', 'route' => 'breeding.overview'],
        ['key' => 'records', 'label' => 'Breeding records', 'route' => 'breeding.records'],
        ['key' => 'checks', 'label' => 'Pregnancy checks', 'route' => 'breeding.checks'],
        ['key' => 'births', 'label' => 'Birth records', 'route' => 'breeding.births'],
    ],

    'breeding_animal_types' => ['cattle', 'goat', 'pig', 'poultry'],

    'breeding_gestation_days' => [
        'cattle' => 283,
        'goat' => 150,
        'pig' => 114,
        'poultry' => 21,
    ],

    'breeding_types' => ['natural_mating', 'artificial_insemination'],

    'breeding_type_labels' => [
        'natural_mating' => 'Natural mating',
        'artificial_insemination' => 'Artificial insemination (AI)',
    ],

    'heat_detection_methods' => ['visual', 'tail_paint', 'detector', 'hormone'],

    'heat_detection_method_labels' => [
        'visual' => 'Visual',
        'tail_paint' => 'Tail paint',
        'detector' => 'Heat detector',
        'hormone' => 'Hormone / progesterone test',
    ],

    'breeding_statuses' => ['pending', 'confirmed_pregnant', 'failed', 'aborted', 'calved'],

    'breeding_status_labels' => [
        'pending' => 'Pending',
        'confirmed_pregnant' => 'Confirmed pregnant',
        'failed' => 'Failed',
        'aborted' => 'Aborted',
        'calved' => 'Calved',
    ],

    'pregnancy_check_methods' => ['rectal_palpation', 'ultrasound', 'blood_test', 'visual_observation'],

    'pregnancy_check_method_labels' => [
        'rectal_palpation' => 'Rectal palpation',
        'ultrasound' => 'Ultrasound',
        'blood_test' => 'Blood test',
        'visual_observation' => 'Visual observation',
    ],

    'pregnancy_check_results' => ['confirmed_pregnant', 'not_pregnant', 'inconclusive'],

    'pregnancy_check_result_labels' => [
        'confirmed_pregnant' => 'Confirmed pregnant',
        'not_pregnant' => 'Not pregnant',
        'inconclusive' => 'Inconclusive',
    ],

    'birth_types' => ['single', 'twins', 'triplets', 'multiple'],

    'birth_difficulties' => ['easy', 'assisted', 'difficult', 'caesarean'],

    'birth_difficulty_labels' => [
        'easy' => 'Easy',
        'assisted' => 'Assisted',
        'difficult' => 'Difficult',
        'caesarean' => 'Caesarean',
    ],

    'mother_conditions_after_birth' => ['good', 'weak', 'critical', 'died'],

    'mother_condition_after_labels' => [
        'good' => 'Good',
        'weak' => 'Weak',
        'critical' => 'Critical',
        'died' => 'Died',
    ],

    'offspring_health_at_birth' => ['healthy', 'weak', 'sick', 'stillborn'],

    'breeding_log_actions' => [
        'created',
        'pregnancy_checked',
        'confirmed_pregnant',
        'failed',
        'aborted',
        'calved',
        'offspring_registered',
    ],

    'breeding_log_action_labels' => [
        'created' => 'Breeding recorded',
        'pregnancy_checked' => 'Pregnancy check',
        'confirmed_pregnant' => 'Confirmed pregnant',
        'failed' => 'Not pregnant',
        'aborted' => 'Aborted',
        'calved' => 'Calved',
        'offspring_registered' => 'Offspring registered',
    ],

    'lactating_after_birth_types' => ['cattle', 'goat'],

    'milk_session_shifts' => ['morning', 'afternoon', 'evening'],

    'milk_session_shift_labels' => [
        'morning' => 'Morning',
        'afternoon' => 'Afternoon',
        'evening' => 'Evening',
    ],

    'milking_methods' => ['manual', 'machine', 'semi_automated'],

    'milking_method_labels' => [
        'manual' => 'Manual',
        'machine' => 'Machine',
        'semi_automated' => 'Semi-automated',
    ],

    'milk_session_statuses' => ['open', 'completed', 'cancelled'],

    'lactation_stages' => ['early', 'mid', 'late', 'dry'],

    'udder_conditions' => ['normal', 'inflamed', 'mastitis_suspected'],

    'milk_storage_container_types' => ['bulk_tank', 'chiller', 'can', 'bucket', 'other'],

    'milk_storage_statuses' => ['available', 'in_use', 'full', 'maintenance'],

    'milk_storage_movement_types' => ['intake', 'sale', 'adjustment_in', 'adjustment_out', 'spoilage'],

    'milk_storage_movement_labels' => [
        'intake' => 'Intake (milking)',
        'sale' => 'Sale',
        'adjustment_in' => 'Adjustment in',
        'adjustment_out' => 'Adjustment out',
        'spoilage' => 'Spoilage / loss',
    ],

    'milk_sale_statuses' => ['draft', 'confirmed', 'cancelled'],

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
