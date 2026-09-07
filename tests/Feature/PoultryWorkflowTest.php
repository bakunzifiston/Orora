<?php

namespace Tests\Feature;

use App\Models\Flock;
use App\Models\Vaccination;
use Tests\Support\FarmTestFixtures;
use Tests\TenantTestCase;

class PoultryWorkflowTest extends TenantTestCase
{
    public function test_poultry_farm_can_place_flock_and_record_eggs(): void
    {
        $this->actingAsTenantUser();

        $farm = FarmTestFixtures::farm(['primary_species' => 'poultry']);
        $livestock = FarmTestFixtures::livestock($farm, ['name' => 'Layers house 1']);

        $this->post(route('flocks.store'), [
            'farm_id' => $farm->id,
            'livestock_id' => $livestock->id,
            'name' => 'Batch A',
            'production_type' => 'layer',
            'placed_on' => now()->toDateString(),
            'placed_count' => 250,
            'lifecycle_status' => 'Active',
        ])->assertRedirect();

        $flock = Flock::query()->where('name', 'Batch A')->first();
        $this->assertNotNull($flock);
        $this->assertSame(250, $flock->current_count);
        $this->assertSame($farm->id, $flock->farm_id);

        $this->post(route('eggs.collections.store'), [
            'farm_id' => $farm->id,
            'flock_id' => $flock->id,
            'collected_on' => now()->toDateString(),
            'eggs_count' => 180,
            'cracked_count' => 4,
        ])->assertRedirect(route('eggs.collections'));

        $this->assertDatabaseHas('egg_collections', [
            'flock_id' => $flock->id,
            'eggs_count' => 180,
            'cracked_count' => 4,
        ]);
    }

    public function test_poultry_farm_hides_cattle_modules_and_cattle_farm_hides_poultry_modules(): void
    {
        $this->actingAsTenantUser();

        $cattle = FarmTestFixtures::farm(['primary_species' => 'cattle']);
        $poultry = FarmTestFixtures::farm(['primary_species' => 'poultry']);

        $this->get(route('animals.index', ['farm_id' => $poultry->id]))->assertNotFound();
        $this->get(route('milk.overview', ['farm_id' => $poultry->id]))->assertNotFound();
        $this->get(route('breeding.overview', ['farm_id' => $poultry->id]))->assertNotFound();
        $this->get(route('flocks.index', ['farm_id' => $poultry->id]))->assertOk();
        $this->get(route('eggs.overview', ['farm_id' => $poultry->id]))->assertOk();

        $this->get(route('animals.index', ['farm_id' => $cattle->id]))->assertOk();
        $this->get(route('milk.overview', ['farm_id' => $cattle->id]))->assertOk();
        $this->get(route('breeding.overview', ['farm_id' => $cattle->id]))->assertOk();
        $this->get(route('flocks.index', ['farm_id' => $cattle->id]))->assertNotFound();
        $this->get(route('eggs.overview', ['farm_id' => $cattle->id]))->assertNotFound();
    }

    public function test_cattle_health_vaccination_still_requires_only_animal_id(): void
    {
        $this->actingAsTenantUser();

        $farm = FarmTestFixtures::farm(['primary_species' => 'cattle']);
        $livestock = FarmTestFixtures::livestock($farm);
        $animal = FarmTestFixtures::animal($farm, $livestock);

        $this->post(route('health.vaccinations.store'), [
            'animal_id' => $animal->id,
            'vaccine_name' => 'FMD vaccine',
            'vaccination_date' => now()->toDateString(),
            'status' => 'Completed',
        ])->assertRedirect(route('health.vaccinations'));

        $this->assertDatabaseHas('vaccinations', [
            'animal_id' => $animal->id,
            'flock_id' => null,
            'vaccine_name' => 'FMD vaccine',
        ]);

        $vaccination = Vaccination::query()->where('animal_id', $animal->id)->first();
        $this->assertNotNull($vaccination);
        $this->assertSame($farm->id, $vaccination->farm_id);
    }

    public function test_poultry_health_vaccination_targets_flock(): void
    {
        $this->actingAsTenantUser();

        $farm = FarmTestFixtures::farm(['primary_species' => 'poultry']);
        $livestock = FarmTestFixtures::livestock($farm);

        $this->post(route('flocks.store'), [
            'farm_id' => $farm->id,
            'livestock_id' => $livestock->id,
            'name' => 'Broiler 1',
            'production_type' => 'broiler',
            'placed_on' => now()->toDateString(),
            'placed_count' => 100,
            'lifecycle_status' => 'Active',
        ])->assertRedirect();

        $flock = Flock::query()->where('name', 'Broiler 1')->first();

        $this->post(route('health.vaccinations.store', ['farm_id' => $farm->id]), [
            'flock_id' => $flock->id,
            'vaccine_name' => 'NCD vaccine',
            'vaccination_date' => now()->toDateString(),
            'status' => 'Completed',
        ])->assertRedirect(route('health.vaccinations'));

        $this->assertDatabaseHas('vaccinations', [
            'flock_id' => $flock->id,
            'animal_id' => null,
            'vaccine_name' => 'NCD vaccine',
        ]);
    }

    public function test_flock_pages_render_card_actions_and_form_sections(): void
    {
        $this->actingAsTenantUser();

        $farm = FarmTestFixtures::farm(['primary_species' => 'poultry']);
        $livestock = FarmTestFixtures::livestock($farm, ['name' => 'House 2']);

        $this->post(route('flocks.store'), [
            'farm_id' => $farm->id,
            'livestock_id' => $livestock->id,
            'name' => 'Layer batch',
            'production_type' => 'layer',
            'placed_on' => now()->toDateString(),
            'placed_count' => 80,
            'lifecycle_status' => 'Active',
        ])->assertRedirect();

        $flock = Flock::query()->where('name', 'Layer batch')->first();
        $this->assertNotNull($flock);

        $this->get(route('flocks.index', ['farm_id' => $farm->id]))
            ->assertOk()
            ->assertSee('Layer batch')
            ->assertSee('View')
            ->assertSee('Edit')
            ->assertSee('Log eggs')
            ->assertSee('Delete')
            ->assertSee(__('Production type'));

        $this->get(route('flocks.create', ['farm_id' => $farm->id]))
            ->assertOk()
            ->assertSee(__('Farm & identity'))
            ->assertSee(__('Placement'))
            ->assertSee(__('Housing & status'));

        $this->get(route('flocks.show', $flock))
            ->assertOk()
            ->assertSee('Layer batch')
            ->assertSee(__('Edit flock'));

        $this->get(route('flocks.edit', $flock))
            ->assertOk()
            ->assertSee(__('Farm & identity'))
            ->assertSee(__('Current count'));

        $this->get(route('eggs.collections.create', [
            'farm_id' => $farm->id,
            'flock_id' => $flock->id,
        ]))
            ->assertOk()
            ->assertSee(__('Flock'))
            ->assertSee(__('Collection'))
            ->assertSee('Layer batch');
    }
}
