<?php

namespace Database\Seeders\Tenant\Concerns;

use App\Models\AbattoirDispatch;
use App\Models\AbattoirDispatchAnimal;
use App\Models\AbattoirReturn;
use App\Models\Animal;
use App\Models\Certificate;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\EmployeeAddress;
use App\Models\EmployeeEmergencyContact;
use App\Models\EmployeeFarmAssignment;
use App\Models\EmployeePayroll;
use App\Models\EmployeeProfile;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseVendor;
use App\Models\Farm;
use App\Models\FarmMember;
use App\Models\FeedingSchedule;
use App\Models\FeedInventory;
use App\Models\FeedInventoryMovement;
use App\Models\FeedSupplier;
use App\Models\FeedType;
use App\Models\HealthRecord;
use App\Models\Livestock;
use App\Models\Movement;
use App\Models\SalePayment;
use App\Models\SaleTransaction;
use App\Models\Treatment;
use App\Models\Vaccination;
use App\Models\VetVisit;
use App\Services\BreedingService;
use App\Services\CustomerService;
use App\Services\MilkSessionService;
use App\Services\MilkStorageService;
use App\Services\SaleTransactionService;
use Carbon\Carbon;
use Database\Seeders\Support\DemoSeedContext;
use Database\Seeders\Support\DemoTimeline;
use Database\Seeders\Support\RwandaDemoData;
use Illuminate\Support\Collection;

trait SeedsHistoricalDemo
{
    protected DemoSeedContext $ctx;

    protected DemoTimeline $timeline;

    protected int $minRecords;

    abstract protected function customerService(): CustomerService;

    abstract protected function saleService(): SaleTransactionService;

    abstract protected function breedingService(): BreedingService;

    abstract protected function milkStorageService(): MilkStorageService;

    abstract protected function milkSessionService(): MilkSessionService;

    protected function initHistoricalDemo(): void
    {
        $this->timeline = DemoTimeline::make();
        $this->minRecords = config('demo.min_records_per_module', 25);
        $this->ctx = new DemoSeedContext;
    }

    protected function seedHistoricalFoundation(): void
    {
        $farmCount = config('demo.farm_count', 4);
        $locationKeys = array_column(RwandaDemoData::LOCATIONS, 'key');

        for ($f = 0; $f < $farmCount; $f++) {
            $loc = RwandaDemoData::location($locationKeys[$f % count($locationKeys)]);
            $person = RwandaDemoData::person($f);
            $regDate = $this->timeline->dateAtProgress(0.05 + ($f * 0.04));
            $isCoop = $f % 3 === 1;

            $farm = Farm::create([
                'name' => RwandaDemoData::FARM_NAMES[$f] ?? ('Rwanda Farm '.($f + 1)),
                'registration_number' => sprintf('RWA-FARM-%s-%03d', $regDate->format('Y'), $f + 1),
                'country' => 'Rwanda',
                ...$this->demoLocationFields($loc),
                'farm_size_hectares' => 3.5 + ($f * 2.5),
                'registration_date' => $regDate->toDateString(),
                'status' => 'active',
                'ownership_type' => $isCoop ? 'cooperative' : 'sole_proprietor',
                'owner_first_name' => $person['first_name'],
                'owner_last_name' => $person['last_name'],
                'owner_national_id' => RwandaDemoData::nationalId(100 + $f),
                'contact_phone' => RwandaDemoData::phone(100 + $f),
                'contact_email' => 'farm'.($f + 1).'@ororafarm.rw',
                'owner_gender' => $person['gender'],
                'organization_name' => $isCoop ? (RwandaDemoData::FARM_NAMES[$f] ?? 'Farm '.($f + 1)).' Cooperative' : null,
            ]);

            if ($isCoop) {
                $m = RwandaDemoData::person($f + 3);
                FarmMember::create([
                    'farm_id' => $farm->id,
                    'first_name' => $m['first_name'],
                    'last_name' => $m['last_name'],
                    'date_of_birth' => '1985-04-20',
                    'phone' => RwandaDemoData::phone(110 + $f),
                    'gender' => $m['gender'],
                ]);
            }

            $key = $locationKeys[$f % count($locationKeys)];
            $this->ctx->farmsByKey[$key] = $farm;
            $this->ctx->farms->push($farm);

            $dairyGroups = ['Cows (lactating)'];
            $youngGroups = ['Heifer', 'Calves Group'];

            $dairyHerd = $this->demoCreateHerd($farm, $this->demoHerdNameFromGroups($dairyGroups), $dairyGroups, 30 + $f);
            $youngHerd = $this->demoCreateHerd($farm, $this->demoHerdNameFromGroups($youngGroups), $youngGroups, 15 + $f);
            $this->ctx->herds->push($dairyHerd, $youngHerd);

            if ($f % 2 === 0) {
                $bullGroups = ['Bulls'];
                $bullHerd = $this->demoCreateHerd($farm, $this->demoHerdNameFromGroups($bullGroups), $bullGroups, 8);
                $this->ctx->herds->push($bullHerd);
            }
        }

        $this->seedHistoricalAnimals();
        $this->seedHistoricalEmployees();
        $this->seedHistoricalCustomers();
        $this->seedHistoricalFeedBase();
        $this->seedHistoricalMilkTanks();
    }

    protected function seedHistoricalAnimals(): void
    {
        $target = config('demo.animal_count', 120);
        $seq = 1;
        $prefixes = ['GH', 'KN', 'TV', 'LK'];

        while ($this->ctx->animals->count() < $target) {
            foreach ($this->ctx->farms as $farmIndex => $farm) {
                if ($this->ctx->animals->count() >= $target) {
                    break 2;
                }

                $prefix = $prefixes[$farmIndex % count($prefixes)];
                $herds = $this->ctx->herds->where('farm_id', $farm->id);
                $dairy = $herds->first(fn (Livestock $h) => in_array('Cows (lactating)', $h->herd_groups ?? [], true))
                    ?? $herds->first();
                $bullHerd = $herds->first(fn (Livestock $h) => in_array('Bulls', $h->herd_groups ?? [], true));

                $roll = $seq % 10;
                if ($roll < 6) {
                    $herd = $dairy;
                    $gender = 'female';
                    $production = 'Lactating';
                    $lifecycle = 'Active';
                } elseif ($roll < 8) {
                    $herd = $dairy;
                    $gender = 'female';
                    $production = null;
                    $lifecycle = 'Active';
                } else {
                    $herd = $bullHerd ?? $dairy;
                    $gender = 'male';
                    $production = null;
                    $lifecycle = 'Active';
                }

                $born = $this->timeline->randomDate($seq)->subYears(random_int(2, 6));

                $this->ctx->animals->push(Animal::create([
                    'farm_id' => $farm->id,
                    'livestock_id' => $herd->id,
                    'tag_number' => sprintf('%s-%04d', $prefix, $seq),
                    'name' => RwandaDemoData::animalName($seq),
                    'gender' => $gender,
                    'species' => 'Cattle',
                    'breed' => 'Ankole-Friesian cross',
                    'date_of_birth' => $born->toDateString(),
                    'weight_kg' => $gender === 'male' ? random_int(400, 550) : random_int(320, 480),
                    'health_status' => 'Healthy',
                    'production_status' => $production,
                    'lifecycle_status' => $lifecycle,
                    'acquisition_type' => 'Born on farm',
                ]));
                $seq++;
            }
        }
    }

    protected function seedHistoricalEmployees(): void
    {
        $roles = array_keys(config('modules.employee_job_roles'));
        $types = ['full_time', 'part_time', 'seasonal'];

        for ($i = 0; $i < $this->minRecords; $i++) {
            $farm = $this->ctx->farms[$i % $this->ctx->farms->count()];
            $person = RwandaDemoData::person($i + 2);
            $hireDate = $this->timeline->spreadDates($this->minRecords)[$i];

            $employee = Employee::create([
                'employee_code' => sprintf('EMP-%04d', $i + 1),
                'display_name' => $person['full_name'],
                'status' => 'active',
                'employment_type' => $types[$i % count($types)],
                'job_role' => $roles[$i % count($roles)],
                'primary_farm_id' => $farm->id,
                'hire_date' => $hireDate->toDateString(),
                'created_by' => $this->ctx->user->id,
            ]);

            EmployeeProfile::create([
                'employee_id' => $employee->id,
                'first_name' => $person['first_name'],
                'last_name' => $person['last_name'],
                'national_id' => RwandaDemoData::nationalId(200 + $i),
                'gender' => $person['gender'],
                'phone' => RwandaDemoData::phone(200 + $i),
            ]);

            EmployeeEmergencyContact::create([
                'employee_id' => $employee->id,
                'contact_name' => RwandaDemoData::person($i + 5)['full_name'],
                'relationship' => 'family',
                'phone' => RwandaDemoData::phone(250 + $i),
                'is_primary' => true,
            ]);

            EmployeeAddress::create([
                'employee_id' => $employee->id,
                'address_type' => 'physical',
                'country' => 'Rwanda',
                'province' => $farm->province,
                'district' => $farm->district,
                'is_default' => true,
            ]);

            EmployeeFarmAssignment::create([
                'employee_id' => $employee->id,
                'farm_id' => $farm->id,
                'is_primary' => true,
                'assigned_from' => $hireDate->toDateString(),
            ]);

            EmployeePayroll::create([
                'employee_id' => $employee->id,
                'base_salary' => RwandaDemoData::rwf(150_000 + ($i * 25_000)),
                'currency' => 'RWF',
                'pay_frequency' => 'monthly',
            ]);

            $this->ctx->employees->push($employee);
        }
    }

    protected function seedHistoricalCustomers(): void
    {
        $types = array_keys(config('modules.customer_types'));
        $trustLevels = array_keys(config('modules.customer_trust_levels'));

        for ($i = 0; $i < $this->minRecords; $i++) {
            $name = RwandaDemoData::BUSINESS_NAMES[$i % count(RwandaDemoData::BUSINESS_NAMES)]
                .($i >= count(RwandaDemoData::BUSINESS_NAMES) ? ' #'.($i + 1) : '');
            $type = $types[$i % count($types)];
            $profile = $type === 'individual'
                ? ['first_name' => explode(' ', $name)[0], 'last_name' => 'Customer']
                : ['organization_name' => $name];

            $customer = $this->customerService()->create(
                [
                    'customer_type' => $type,
                    'display_name' => $name,
                    'status' => 'active',
                    'trust_level' => $trustLevels[$i % count($trustLevels)],
                    'currency' => 'RWF',
                    'created_by' => $this->ctx->user->id,
                ],
                $profile,
                [
                    'contact_name' => $name,
                    'phone' => RwandaDemoData::phone(300 + $i),
                ],
            );

            $customer->credit()->update([
                'credit_limit' => RwandaDemoData::rwf(500_000 + ($i * 100_000)),
            ]);

            $this->ctx->customers->push($customer);
        }
    }

    protected function seedHistoricalFeedBase(): void
    {
        foreach ([
            ['AgroFeed Rwanda Ltd', 'feed'],
            ['Rwanda Vet Services', 'vet'],
            ['Kigali Transport Co-op', 'transport'],
        ] as $idx => [$vendorName, $type]) {
            if ($type === 'feed') {
                $this->ctx->feedSuppliers->push(FeedSupplier::create([
                    'name' => $vendorName,
                    'contact_person' => RwandaDemoData::person(8)['full_name'],
                    'phone' => RwandaDemoData::phone(400 + $idx),
                    'is_active' => true,
                ]));
            }
            $this->ctx->vendors->push(ExpenseVendor::create([
                'name' => $vendorName,
                'phone' => RwandaDemoData::phone(410 + $idx),
                'is_active' => true,
            ]));
        }

        foreach ($this->ctx->farms as $farmIndex => $farm) {
            $supplier = $this->ctx->feedSuppliers->first();
            foreach (['Dairy concentrate', 'Napier silage', 'Mineral lick'] as $tIdx => $typeName) {
                $feedType = FeedType::create([
                    'feed_supplier_id' => $supplier->id,
                    'name' => $typeName.' — '.$farm->district,
                    'unit' => 'kg',
                    'category' => $tIdx === 1 ? 'roughage' : 'concentrate',
                    'is_active' => true,
                ]);
                $this->ctx->feedTypes->push($feedType);

                FeedInventory::create([
                    'farm_id' => $farm->id,
                    'feed_type_id' => $feedType->id,
                    'quantity_on_hand' => 500,
                    'reorder_level' => 200,
                    'unit' => 'kg',
                ]);
            }
        }
    }

    protected function seedHistoricalMilkTanks(): void
    {
        foreach ($this->ctx->farms as $i => $farm) {
            $this->ctx->milkTanks->push($this->milkStorageService()->create([
                'farm_id' => $farm->id,
                'container_name' => 'Bulk tank — '.$farm->name,
                'container_type' => 'bulk_tank',
                'capacity_liters' => 15000,
                'storage_temperature' => 4,
                'storage_location' => 'Milk room '.chr(65 + $i),
            ]));
        }
    }

    protected function seedHistoricalFeedingVolume(): void
    {
        $dates = $this->timeline->spreadDates($this->minRecords);
        $movementTypes = config('modules.feed_movement_types', ['purchase', 'adjustment_in', 'adjustment_out', 'consumption']);

        for ($i = 0; $i < $this->minRecords; $i++) {
            $farm = $this->ctx->farms[$i % $this->ctx->farms->count()];
            $inventory = FeedInventory::query()->where('farm_id', $farm->id)->inRandomOrder()->first();
            if (! $inventory) {
                continue;
            }

            $qty = random_int(50, 800);
            $balance = max(0, (float) $inventory->quantity_on_hand + $qty);

            FeedInventoryMovement::create([
                'feed_inventory_id' => $inventory->id,
                'movement_type' => $movementTypes[$i % count($movementTypes)],
                'quantity' => $qty,
                'unit' => 'kg',
                'balance_after' => $balance,
                'moved_at' => $dates[$i],
                'notes' => 'Historical stock movement #'.($i + 1),
            ]);
            $inventory->update(['quantity_on_hand' => $balance]);

            $herd = $this->ctx->herds->first(fn (Livestock $h) => (int) $h->farm_id === (int) $farm->id);
            $feedType = FeedType::query()->find($inventory->feed_type_id);

            if ($herd && $feedType) {
                FeedingSchedule::create([
                    'farm_id' => $farm->id,
                    'livestock_id' => $herd->id,
                    'feed_type_id' => $feedType->id,
                    'feed_inventory_id' => $inventory->id,
                    'quantity' => random_int(3, 8),
                    'unit' => 'kg',
                    'frequency' => 'daily',
                    'scheduled_time' => sprintf('%02d:30:00', 5 + ($i % 4)),
                    'start_date' => $dates[$i]->toDateString(),
                    'status' => 'active',
                ]);
            }
        }
    }

    protected function seedHistoricalHealthVolume(): void
    {
        $dates = $this->timeline->spreadDates($this->minRecords);
        $vet = 'Dr. Aimable Mugabo';
        $activeAnimals = $this->ctx->animals->filter(fn (Animal $a) => $a->lifecycle_status === 'Active')->values();
        $animalCount = max(1, $activeAnimals->count());

        for ($i = 0; $i < $this->minRecords; $i++) {
            $animal = $activeAnimals[$i % $animalCount];
            $date = $dates[$i];

            $vaccination = Vaccination::create([
                'farm_id' => $animal->farm_id,
                'animal_id' => $animal->id,
                'vaccine_name' => RwandaDemoData::VACCINES[$i % count(RwandaDemoData::VACCINES)],
                'vaccine_type' => 'FMD',
                'vaccination_date' => $date->toDateString(),
                'next_due_date' => $date->copy()->addMonths(6)->toDateString(),
                'status' => 'Completed',
                'veterinarian_name' => $vet,
            ]);
            $this->demoLinkVaccinationHealthRecord($vaccination);
        }

        for ($i = 0; $i < $this->minRecords; $i++) {
            $animal = $activeAnimals[($i + 3) % $animalCount];
            $date = $dates[$i];

            $treatment = Treatment::create([
                'farm_id' => $animal->farm_id,
                'animal_id' => $animal->id,
                'disease_name' => RwandaDemoData::DISEASES[$i % count(RwandaDemoData::DISEASES)],
                'medicine_name' => 'Antibiotic course',
                'treatment_method' => 'Intramuscular',
                'start_date' => $date->copy()->addDays(3)->toDateString(),
                'status' => 'Completed',
                'veterinarian_name' => $vet,
            ]);
            $this->demoLinkTreatmentHealthRecord($treatment);
        }

        for ($i = 0; $i < $this->minRecords; $i++) {
            $animal = $activeAnimals[($i + 11) % $animalCount];

            VetVisit::create([
                'farm_id' => $animal->farm_id,
                'animal_id' => $animal->id,
                'disease_name' => 'Routine check',
                'medicine_name' => 'N/A',
                'start_date' => $dates[$i]->toDateString(),
                'status' => 'Completed',
                'veterinarian_name' => $vet,
            ]);
        }

        for ($i = 0; $i < $this->minRecords; $i++) {
            $animal = $activeAnimals[($i + 7) % $animalCount];
            HealthRecord::create([
                'farm_id' => $animal->farm_id,
                'animal_id' => $animal->id,
                'record_type' => 'Observation',
                'recorded_on' => $dates[$i]->toDateString(),
                'health_status' => 'Healthy',
                'title' => 'Routine observation',
                'notes' => 'Body condition score recorded during walk-through.',
            ]);
        }
    }

    protected function seedHistoricalMilkVolume(): void
    {
        $dates = $this->timeline->spreadDates($this->minRecords);
        $shifts = ['morning', 'afternoon', 'evening'];
        $employee = $this->ctx->employees->first();

        for ($i = 0; $i < $this->minRecords; $i++) {
            $farm = $this->ctx->farms[$i % $this->ctx->farms->count()];
            $herd = $this->ctx->herds->first(fn (Livestock $h) => (int) $h->farm_id === (int) $farm->id
                && in_array('Cows (lactating)', $h->herd_groups ?? [], true));
            $tank = $this->ctx->milkTanks->first(fn ($t) => (int) $t->farm_id === (int) $farm->id);
            $cows = collect($this->ctx->lactatingCows($farm))->take(6);

            if (! $herd || $cows->isEmpty()) {
                continue;
            }

            $sessionDate = $dates[$i];

            $session = $this->milkSessionService()->create([
                'farm_id' => $farm->id,
                'livestock_id' => $herd->id,
                'session_date' => $sessionDate->toDateString(),
                'session_shift' => $shifts[$i % 3],
                'milked_by' => $employee?->display_name ?? 'Milker',
                'milking_method' => $i % 2 === 0 ? 'machine' : 'hand',
                'destination_storage_id' => $tank?->id,
            ]);

            foreach ($cows as $cowIndex => $cow) {
                $this->milkSessionService()->addRecord($session, [
                    'animal_id' => $cow->id,
                    'yield_liters' => 10 + random_int(2, 10) + ($cowIndex * 0.5),
                    'lactation_stage' => ['early', 'mid', 'late'][$cowIndex % 3],
                    'udder_condition' => 'normal',
                    'abnormal_milk' => false,
                ]);
            }

            if ($tank) {
                $this->milkSessionService()->complete($session->fresh(), $tank->id);
            }
        }
    }

    protected function seedHistoricalBreedingVolume(): void
    {
        $dates = $this->timeline->spreadDates($this->minRecords);
        $types = config('modules.breeding_types');
        $females = $this->ctx->animals->filter(fn (Animal $a) => $a->gender === 'female' && $a->lifecycle_status === 'Active')->values();

        $femaleCursor = 0;
        $femaleCount = max(1, $females->count());
        $created = 0;

        for ($i = 0; $created < $this->minRecords && $i < $this->minRecords * 3; $i++) {
            $female = $females[($femaleCursor + $i) % $femaleCount];
            $farm = $this->ctx->farms->firstWhere('id', $female->farm_id);
            $bull = $this->ctx->animals->first(fn (Animal $a) => (int) $a->farm_id === (int) $farm->id && $a->gender === 'male');
            $bredOn = $dates[$created];

            try {
                $record = $this->breedingService()->createBreedingRecord([
                    'farm_id' => $farm->id,
                    'female_animal_id' => $female->id,
                    'male_animal_id' => $bull?->id,
                    'external_sire_name' => $bull ? null : 'External sire '.($i + 1),
                    'breeding_date' => $bredOn->toDateString(),
                    'breeding_type' => $types[$i % count($types)],
                    'animal_type' => 'cattle',
                    'breeding_status' => 'pending',
                ]);

                if ($created % 2 === 0 && $bredOn->lt($this->timeline->end->copy()->subDays(40))) {
                    $checkDate = $bredOn->copy()->addDays(35);
                    if ($checkDate->lte($this->timeline->end)) {
                        $this->breedingService()->createPregnancyCheck([
                            'breeding_record_id' => $record->id,
                            'animal_id' => $female->id,
                            'check_date' => $checkDate->toDateString(),
                            'check_method' => 'ultrasound',
                            'result' => $created % 4 === 0 ? 'not_pregnant' : 'confirmed_pregnant',
                            'checked_by' => 'Dr. Aimable Mugabo',
                        ]);
                    }
                }

                if ($created % 4 === 0 && $bredOn->lt($this->timeline->end->copy()->subMonths(3))) {
                    $birthDate = $bredOn->copy()->addMonths(9);
                    if ($birthDate->lte($this->timeline->end) && $record->fresh()->breeding_status === 'confirmed_pregnant') {
                        $this->breedingService()->createBirthRecord([
                            'breeding_record_id' => $record->id,
                            'mother_animal_id' => $female->id,
                            'birth_date' => $birthDate->toDateString(),
                            'birth_type' => 'single',
                            'birth_difficulty' => 'easy',
                            'total_offspring' => 1,
                            'alive_offspring' => 1,
                            'stillborn_offspring' => 0,
                            'mother_condition_after' => 'good',
                        ]);
                    }
                }

                $femaleCursor++;
                $created++;
            } catch (\Throwable) {
                // Skip invalid combinations (same female twice, etc.)
            }
        }
    }

    protected function seedHistoricalSalesVolume(): void
    {
        $dates = $this->timeline->spreadDates($this->minRecords);
        $saleTypes = ['animal_sale', 'milk_sale', 'animal_sale', 'milk_sale', 'meat_sale'];
        $activeAnimals = $this->ctx->animals->filter(fn (Animal $a) => $a->lifecycle_status === 'Active')->values();

        $recentMonthSlots = min(10, $this->minRecords);

        for ($i = 0; $i < $this->minRecords; $i++) {
            $farm = $this->ctx->farms[$i % $this->ctx->farms->count()];
            $customer = $this->ctx->customers[$i % $this->ctx->customers->count()];
            $inCurrentMonth = $i >= $this->minRecords - $recentMonthSlots;
            $saleDate = $inCurrentMonth
                ? now()->subDays(random_int(0, max(0, now()->day - 1)))
                : $dates[$i];
            $type = $saleTypes[$i % count($saleTypes)];
            $complete = $inCurrentMonth || $i < (int) ($this->minRecords * 0.85);

            try {
                $transaction = $this->saleService()->createDraft([
                    'farm_id' => $farm->id,
                    'sale_type' => $type,
                    'sale_date' => $saleDate->toDateString(),
                    'customer_id' => $customer->id,
                    'pricing_method' => match ($type) {
                        'milk_sale' => 'per_liter',
                        'animal_sale' => 'per_kg',
                        default => 'per_animal',
                    },
                    'currency' => 'RWF',
                ]);

                if ($type === 'animal_sale') {
                    $animal = $activeAnimals->first(fn (Animal $a) => (int) $a->farm_id === (int) $farm->id
                        && $a->lifecycle_status === 'Active'
                        && ($a->gender === 'male' || $i % 3 === 0));

                    if (! $animal) {
                        continue;
                    }

                    $this->saleService()->addItem($transaction, [
                        'item_type' => 'animal',
                        'animal_id' => $animal->id,
                        'description' => "Sale of {$animal->tag_number}",
                        'live_weight_kg' => (float) ($animal->weight_kg ?? 400),
                        'price_per_kg' => random_int(2400, 3200),
                        'quantity' => 1,
                        'unit' => 'head',
                    ]);
                    $activeAnimals = $activeAnimals->reject(fn (Animal $a) => $a->id === $animal->id)->values();
                } elseif ($type === 'milk_sale') {
                    $tank = $this->ctx->milkTanks->first(fn ($t) => (int) $t->farm_id === (int) $farm->id);
                    $liters = min(40, max(10, (int) (($tank?->current_quantity_liters ?? 100) * 0.05)));

                    $this->saleService()->addItem($transaction, [
                        'item_type' => 'milk',
                        'milk_storage_id' => $tank?->id,
                        'description' => 'Bulk milk sale',
                        'quantity' => $liters,
                        'unit' => 'L',
                        'unit_price' => random_int(400, 550),
                    ]);
                } else {
                    $this->saleService()->addItem($transaction, [
                        'item_type' => 'meat_cut',
                        'description' => 'Beef cuts — batch '.($i + 1),
                        'quantity' => random_int(20, 80),
                        'unit' => 'kg',
                        'unit_price' => random_int(3000, 3800),
                    ]);
                }

                if ($complete) {
                    $transaction = $this->saleService()->complete($transaction->fresh());
                    $paid = (float) $transaction->total_amount;
                    if ($paid > 0 && $i % 3 !== 0) {
                        $this->saleService()->addPayment($transaction, [
                            'amount_paid' => $paid * ($i % 5 === 0 ? 0.5 : 1),
                            'payment_method' => ['Mobile Money', 'Cash', 'Bank Transfer'][$i % 3],
                            'payment_date' => $saleDate->copy()->addDays(random_int(0, 14))->toDateString(),
                            'transaction_reference' => 'PAY-'.$transaction->id.'-'.$i,
                        ]);
                    }
                }
            } catch (\Throwable) {
                continue;
            }
        }
    }

    protected function seedHistoricalAbattoirVolume(): void
    {
        $dates = $this->timeline->spreadDates($this->minRecords);
        $males = $this->ctx->animals->filter(fn (Animal $a) => $a->gender === 'male' && $a->lifecycle_status === 'Active')->values();

        for ($i = 0; $i < $this->minRecords; $i++) {
            $farm = $this->ctx->farms[$i % $this->ctx->farms->count()];
            $animal = $males[$i % max(1, $males->count())] ?? null;
            if (! $animal || (int) $animal->farm_id !== (int) $farm->id) {
                $animal = $this->ctx->animals->first(fn (Animal $a) => (int) $a->farm_id === (int) $farm->id && $a->gender === 'male');
            }
            if (! $animal) {
                continue;
            }

            $dispatchDate = $dates[$i];

            $dispatch = AbattoirDispatch::create([
                'farm_id' => $farm->id,
                'dispatch_code' => $this->saleService()->generateDispatchCode($dispatchDate),
                'dispatch_date' => $dispatchDate->toDateString(),
                'abattoir_name' => $farm->district.' Municipal Abattoir',
                'abattoir_location' => $farm->district,
                'transport_method' => 'truck',
                'vehicle_plate' => 'RAD '.random_int(100, 999).' '.chr(65 + ($i % 26)),
                'total_animals_dispatched' => 1,
                'dispatch_status' => 'returned',
                'created_by' => $this->ctx->user->id,
            ]);

            AbattoirDispatchAnimal::create([
                'abattoir_dispatch_id' => $dispatch->id,
                'animal_id' => $animal->id,
                'live_weight_kg' => $animal->weight_kg ?? 380,
            ]);

            AbattoirReturn::create([
                'abattoir_dispatch_id' => $dispatch->id,
                'animal_id' => $animal->id,
                'return_date' => $dispatchDate->copy()->addDay()->toDateString(),
                'carcass_weight_kg' => ($animal->weight_kg ?? 380) * 0.52,
                'cut_type' => 'Mixed cuts',
                'cut_weight_kg' => ($animal->weight_kg ?? 380) * 0.52,
                'grade' => ['A', 'B', 'C'][$i % 3],
                'price_per_kg' => random_int(2800, 3600),
                'is_sold' => $i % 2 === 0,
            ]);
        }
    }

    protected function seedHistoricalExpenseVolume(): void
    {
        $dates = $this->timeline->spreadDates($this->minRecords);
        $categories = ExpenseCategory::query()->where('is_active', true)->pluck('id', 'code');
        $titles = [
            'feed.purchase' => 'Feed delivery',
            'health.vaccination' => 'Vaccination supplies',
            'farm.labor' => 'Farm labor',
            'general.transport' => 'Transport',
            'farm.equipment' => 'Equipment repair',
        ];

        for ($i = 0; $i < $this->minRecords; $i++) {
            $farm = $this->ctx->farms[$i % $this->ctx->farms->count()];
            $code = array_keys($titles)[$i % count($titles)];
            $categoryId = $categories[$code] ?? $categories->first();

            Expense::create([
                'farm_id' => $farm->id,
                'livestock_id' => $this->ctx->herds->firstWhere('farm_id', $farm->id)?->id,
                'expense_category_id' => $categoryId,
                'expense_vendor_id' => $this->ctx->vendors[$i % $this->ctx->vendors->count()]->id,
                'expense_date' => $dates[$i]->toDateString(),
                'amount' => RwandaDemoData::rwf(random_int(25_000, 1_200_000)),
                'currency' => 'RWF',
                'payment_method' => ['Cash', 'Mobile Money', 'Bank Transfer'][$i % 3],
                'paid_by' => $this->ctx->user->name,
                'title' => $titles[$code].' #'.($i + 1),
                'status' => 'paid',
            ]);
        }
    }

    protected function seedHistoricalComplianceVolume(): void
    {
        $dates = $this->timeline->spreadDates($this->minRecords);
        $types = config('modules.certificate_types');
        $activeAnimals = $this->ctx->animals->filter(fn (Animal $a) => $a->lifecycle_status === 'Active')->values();

        for ($i = 0; $i < $this->minRecords; $i++) {
            $farm = $this->ctx->farms[$i % $this->ctx->farms->count()];
            $animal = $activeAnimals[$i % max(1, $activeAnimals->count())];
            $issued = $dates[$i];

            Certificate::create([
                'farm_id' => $farm->id,
                'animal_id' => $animal->id,
                'certificate_type' => $types[$i % count($types)],
                'certificate_number' => sprintf('CERT-%s-%04d', $issued->format('Y'), $i + 1),
                'issuing_authority' => 'RAB — Rwanda Agriculture Board',
                'issued_on' => $issued->toDateString(),
                'expires_on' => $issued->copy()->addYear()->toDateString(),
                'status' => $issued->copy()->addYear()->isPast() ? 'expired' : 'valid',
            ]);

            Movement::create([
                'animal_id' => $animal->id,
                'from_farm_id' => $farm->id,
                'to_farm_id' => $i % 7 === 0 ? $this->ctx->farms[($i + 1) % $this->ctx->farms->count()]->id : null,
                'movement_type' => config('modules.movement_types')[$i % count(config('modules.movement_types'))],
                'moved_on' => $issued->toDateString(),
                'reference' => 'MV-'.($i + 1),
            ]);
        }
    }

    protected function syncHistoricalCustomerBalances(): void
    {
        $this->ctx->customers->each(fn (Customer $c) => $this->customerService()->syncOutstandingBalance($c));
    }

    protected function demoCreateHerd(Farm $farm, string $name, array $herdGroups, int $headCount): Livestock
    {
        return Livestock::create([
            'farm_id' => $farm->id,
            'name' => $name,
            'herd_groups' => $herdGroups,
            'livestock_types' => ['Cattle'],
            'production_purposes' => ['Dairy Production'],
            'farming_methods' => ['Zero Grazing'],
            'feeding_methods' => ['Stall Feeding'],
            'breed' => 'Ankole-Friesian cross',
            'head_count' => $headCount,
            'status' => 'active',
        ]);
    }

    protected function demoHerdNameFromGroups(array $herdGroups): string
    {
        if ($herdGroups === []) {
            return 'Herd';
        }

        return implode(' / ', $herdGroups);
    }

    /** @param  array<string, mixed>  $location */
    protected function demoLocationFields(array $location): array
    {
        return [
            'province_code' => $location['province_code'],
            'province' => $location['province'],
            'district_code' => $location['district_code'],
            'district' => $location['district'],
            'sector_code' => $location['sector_code'],
            'sector' => $location['sector'],
            'cell_code' => $location['cell_code'],
            'cell' => $location['cell'],
            'village_code' => $location['village_code'],
            'village' => $location['village'],
        ];
    }

    protected function demoLinkVaccinationHealthRecord(Vaccination $vaccination): void
    {
        $record = HealthRecord::create([
            'farm_id' => $vaccination->farm_id,
            'animal_id' => $vaccination->animal_id,
            'record_type' => 'Vaccination',
            'recorded_on' => $vaccination->vaccination_date,
            'health_status' => 'Healthy',
            'title' => $vaccination->vaccine_name,
            'medication' => $vaccination->vaccine_name,
            'veterinarian' => $vaccination->veterinarian_name,
            'next_follow_up' => $vaccination->next_due_date,
        ]);
        $vaccination->update(['health_record_id' => $record->id]);
    }

    protected function demoLinkTreatmentHealthRecord(Treatment $treatment): void
    {
        $record = HealthRecord::create([
            'farm_id' => $treatment->farm_id,
            'animal_id' => $treatment->animal_id,
            'record_type' => 'Treatment',
            'recorded_on' => $treatment->start_date,
            'health_status' => 'Under treatment',
            'title' => $treatment->disease_name,
            'medication' => $treatment->medicine_name,
            'veterinarian' => $treatment->veterinarian_name,
        ]);
        $treatment->update(['health_record_id' => $record->id]);
    }
}
