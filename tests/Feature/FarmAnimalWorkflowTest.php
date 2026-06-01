<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Farm;
use App\Models\Livestock;
use Tests\Support\FarmTestFixtures;
use Tests\TenantTestCase;

class FarmAnimalWorkflowTest extends TenantTestCase
{
    public function test_user_can_register_farm_livestock_and_animal(): void
    {
        $this->actingAsTenantUser();

        $farm = FarmTestFixtures::farm();
        $livestock = FarmTestFixtures::livestock($farm);
        $animal = FarmTestFixtures::animal($farm, $livestock, 'female', [
            'tag_number' => 'E2E-001',
            'name' => 'Bella',
        ]);

        $this->assertDatabaseHas('farms', ['id' => $farm->id, 'name' => $farm->name]);
        $this->assertDatabaseHas('livestock', ['id' => $livestock->id, 'farm_id' => $farm->id]);
        $this->assertDatabaseHas('animals', [
            'id' => $animal->id,
            'farm_id' => $farm->id,
            'tag_number' => 'E2E-001',
        ]);

        $this->get(route('animals.show', $animal))->assertOk();
        $this->get(route('farms.index'))->assertOk();
    }

    public function test_animals_index_lists_registered_animal(): void
    {
        $this->actingAsTenantUser();

        $farm = Farm::query()->find(FarmTestFixtures::farm()->id);
        $livestock = Livestock::query()->find(FarmTestFixtures::livestock($farm)->id);
        $animal = FarmTestFixtures::animal($farm, $livestock, 'female', [
            'tag_number' => 'LIST-001',
        ]);

        $this->get(route('animals.index', ['farm_id' => $farm->id]))
            ->assertOk()
            ->assertSee('LIST-001');
    }
}
