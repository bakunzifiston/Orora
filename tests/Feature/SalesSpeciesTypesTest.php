<?php

namespace Tests\Feature;

use Tests\Support\FarmTestFixtures;
use Tests\TenantTestCase;

class SalesSpeciesTypesTest extends TenantTestCase
{
    public function test_cattle_only_workspace_has_no_egg_sales(): void
    {
        $this->actingAsTenantUser();
        FarmTestFixtures::farm(['primary_species' => 'cattle']);

        $this->get(route('sales.overview'))->assertOk()
            ->assertSee('Milk sales')
            ->assertDontSee('Egg sales');

        $this->get(route('sales.transactions'))->assertOk()
            ->assertSee('<option value="milk_sale"', false)
            ->assertDontSee('<option value="egg_sale"', false);
    }

    public function test_workspace_with_a_poultry_farm_shows_egg_sales(): void
    {
        $this->actingAsTenantUser();
        FarmTestFixtures::farm(['primary_species' => 'cattle']);
        FarmTestFixtures::farm(['primary_species' => 'poultry']);

        $this->get(route('sales.overview'))->assertOk()
            ->assertSee('Milk sales')
            ->assertSee('Egg sales');

        $this->get(route('sales.transactions'))->assertOk()->assertSee('<option value="egg_sale"', false);
    }

    public function test_poultry_only_workspace_has_no_milk_sales(): void
    {
        $this->actingAsTenantUser();
        FarmTestFixtures::farm(['primary_species' => 'poultry']);

        $this->get(route('sales.overview'))->assertOk()
            ->assertSee('Egg sales')
            ->assertDontSee('Milk sales');
    }
}
