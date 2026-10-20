<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\MilkDistribution;
use App\Models\MilkStorage;
use App\Models\SaleItem;
use App\Models\SaleTransaction;
use App\Services\SaleTransactionService;
use Tests\Support\FarmTestFixtures;
use Tests\TenantTestCase;

class MilkDistributionTest extends TenantTestCase
{
    public function test_distribution_pages_load(): void
    {
        $this->actingAsTenantUser();
        FarmTestFixtures::farm();

        $this->get(route('milk.distribution'))->assertOk()->assertSee('Milk distribution');
        $this->get(route('milk.distribution.create'))->assertOk()->assertSee('What happened to the milk?');
    }

    public function test_overview_shows_distribution_kpis(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();

        $this->post(route('milk.distribution.store'), $this->payload($farm, [
            'category' => 'household',
            'recipient_type' => 'household',
            'quantity_liters' => 7,
        ]))->assertSessionHasNoErrors();

        $this->get(route('milk.overview', ['period' => 'all']))
            ->assertOk()
            ->assertSee('Fed to calves')
            ->assertSee('Given away')
            ->assertSee(route('milk.distribution', ['period' => 'all', 'category' => 'household']));
    }

    public function test_household_use_is_recorded_with_the_user(): void
    {
        $user = $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();

        $this->post(route('milk.distribution.store'), $this->payload($farm, [
            'category' => 'household',
            'recipient_type' => 'household',
            'quantity_liters' => 3.5,
        ]))->assertRedirect(route('milk.distribution'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('milk_distributions', [
            'farm_id' => $farm->id,
            'category' => 'household',
            'quantity_liters' => 3.5,
            'recorded_by' => $user->id,
            'milk_storage_id' => null,
        ]);
    }

    public function test_neighbor_needs_a_name_but_no_account(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();

        $this->post(route('milk.distribution.store'), $this->payload($farm, [
            'category' => 'neighbor',
            'recipient_type' => 'neighbor',
        ]))->assertSessionHasErrors('recipient_name');

        $this->post(route('milk.distribution.store'), $this->payload($farm, [
            'category' => 'neighbor',
            'recipient_type' => 'neighbor',
            'recipient_name' => 'Mukamana Jeanne',
            'recipient_phone' => '078 123 4567',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('milk_distributions', [
            'recipient_name' => 'Mukamana Jeanne',
            'recipient_phone' => '078 123 4567',
        ]);
    }

    public function test_other_use_requires_a_description_but_no_recipient_name(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();

        $this->post(route('milk.distribution.store'), $this->payload($farm, [
            'category' => 'other',
            'recipient_type' => 'other',
        ]))->assertSessionHasErrors('other_use');

        $this->post(route('milk.distribution.store'), $this->payload($farm, [
            'category' => 'other',
            'recipient_type' => 'other',
            'other_use' => 'Made into yoghurt',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('milk_distributions', [
            'category' => 'other',
            'other_use' => 'Made into yoghurt',
            'recipient_name' => null,
        ]);
        $this->get(route('milk.distribution', ['period' => 'all']))->assertSee('Made into yoghurt');
    }

    public function test_description_is_dropped_for_other_categories(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();

        $this->post(route('milk.distribution.store'), $this->payload($farm, [
            'category' => 'household',
            'recipient_type' => 'household',
            'other_use' => 'Should not be stored',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('milk_distributions', ['category' => 'household', 'other_use' => null]);
    }

    public function test_young_calf_on_the_same_farm_can_be_fed(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();
        $calf = FarmTestFixtures::animal($farm, FarmTestFixtures::livestock($farm), 'female', [
            'date_of_birth' => now()->subMonths(2)->toDateString(),
        ]);

        $this->post(route('milk.distribution.store'), $this->payload($farm, [
            'category' => 'fed_to_calf',
            'recipient_type' => 'calf',
            'animal_id' => $calf->id,
            'recipient_name' => 'ignored for calves',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('milk_distributions', [
            'category' => 'fed_to_calf',
            'animal_id' => $calf->id,
            'recipient_name' => null,
        ]);
    }

    public function test_calf_must_belong_to_the_selected_farm(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();
        $otherFarm = FarmTestFixtures::farm();
        $calf = FarmTestFixtures::animal($otherFarm, FarmTestFixtures::livestock($otherFarm), 'female', [
            'date_of_birth' => now()->subMonths(2)->toDateString(),
        ]);

        $this->post(route('milk.distribution.store'), $this->payload($farm, [
            'category' => 'fed_to_calf',
            'recipient_type' => 'calf',
            'animal_id' => $calf->id,
        ]))->assertSessionHasErrors('animal_id');
    }

    public function test_adult_or_inactive_animals_are_not_eligible_calves(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();
        $herd = FarmTestFixtures::livestock($farm);
        $cow = FarmTestFixtures::animal($farm, $herd, 'female', ['date_of_birth' => now()->subYears(4)->toDateString()]);
        $soldCalf = FarmTestFixtures::animal($farm, $herd, 'female', [
            'date_of_birth' => now()->subMonths(3)->toDateString(),
            'lifecycle_status' => 'Sold',
        ]);

        foreach ([$cow, $soldCalf] as $animal) {
            $this->post(route('milk.distribution.store'), $this->payload($farm, [
                'category' => 'fed_to_calf',
                'recipient_type' => 'calf',
                'animal_id' => $animal->id,
            ]))->assertSessionHasErrors('animal_id');
        }
    }

    public function test_animal_in_calves_herd_group_is_eligible_without_birth_date(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();
        $calfHerd = FarmTestFixtures::livestock($farm, ['herd_groups' => ['Calves Group']]);
        $calf = FarmTestFixtures::animal($farm, $calfHerd, 'male');

        $this->post(route('milk.distribution.store'), $this->payload($farm, [
            'category' => 'fed_to_calf',
            'recipient_type' => 'calf',
            'animal_id' => $calf->id,
        ]))->assertSessionHasNoErrors();
    }

    public function test_sold_cannot_be_entered_manually(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();

        $this->post(route('milk.distribution.store'), $this->payload($farm, [
            'category' => 'sold',
            'recipient_type' => 'customer',
            'recipient_name' => 'Buyer',
        ]))->assertSessionHasErrors('category');

        $this->assertDatabaseCount('milk_distributions', 0);
    }

    public function test_tank_is_deducted_and_restored_when_the_record_is_deleted(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();
        $tank = $this->tank($farm, 50);

        $this->post(route('milk.distribution.store'), $this->payload($farm, [
            'category' => 'household',
            'recipient_type' => 'household',
            'quantity_liters' => 10,
            'milk_storage_id' => $tank->id,
        ]))->assertSessionHasNoErrors();

        $this->assertSame(40.0, (float) $tank->fresh()->current_quantity_liters);
        $distribution = MilkDistribution::query()->firstOrFail();
        $this->assertNotNull($distribution->storage_movement_id);

        $this->put(route('milk.distribution.update', $distribution), $this->payload($farm, [
            'category' => 'household',
            'recipient_type' => 'household',
            'quantity_liters' => 15,
            'milk_storage_id' => $tank->id,
        ]))->assertSessionHasNoErrors();

        $this->assertSame(35.0, (float) $tank->fresh()->current_quantity_liters);

        $this->delete(route('milk.distribution.destroy', $distribution))->assertRedirect(route('milk.distribution'));

        $this->assertSame(50.0, (float) $tank->fresh()->current_quantity_liters);
        $this->assertDatabaseCount('milk_distributions', 0);
    }

    public function test_cannot_take_more_than_the_tank_holds_or_use_another_farms_tank(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();
        $otherFarm = FarmTestFixtures::farm();
        $tank = $this->tank($farm, 5);
        $otherTank = $this->tank($otherFarm, 100);

        $this->post(route('milk.distribution.store'), $this->payload($farm, [
            'category' => 'household',
            'recipient_type' => 'household',
            'quantity_liters' => 8,
            'milk_storage_id' => $tank->id,
        ]))->assertSessionHasErrors('quantity_liters');

        $this->post(route('milk.distribution.store'), $this->payload($farm, [
            'category' => 'household',
            'recipient_type' => 'household',
            'milk_storage_id' => $otherTank->id,
        ]))->assertSessionHasErrors('milk_storage_id');

        $this->assertSame(5.0, (float) $tank->fresh()->current_quantity_liters);
    }

    public function test_confirmed_milk_sale_appears_as_sold_and_cancel_removes_it(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();
        $tank = $this->tank($farm, 30);

        $transaction = SaleTransaction::query()->create([
            'farm_id' => $farm->id,
            'sale_number' => 'MLS-TEST-'.uniqid(),
            'sale_type' => 'milk_sale',
            'sale_date' => now()->toDateString(),
            'sale_status' => 'draft',
            'payment_status' => 'unpaid',
            'currency' => 'RWF',
        ]);
        SaleItem::query()->create([
            'sale_transaction_id' => $transaction->id,
            'item_type' => 'milk',
            'milk_storage_id' => $tank->id,
            'description' => 'Fresh milk',
            'quantity' => 12,
            'unit' => 'L',
            'unit_price' => 500,
            'total_price' => 6000,
        ]);

        $service = app(SaleTransactionService::class);
        $service->confirm($transaction->fresh());

        $this->assertDatabaseHas('milk_distributions', [
            'category' => 'sold',
            'sale_transaction_id' => $transaction->id,
            'quantity_liters' => 12,
        ]);
        $this->assertSame(18.0, (float) $tank->fresh()->current_quantity_liters);

        $sold = MilkDistribution::query()->where('category', 'sold')->firstOrFail();
        $this->get(route('milk.distribution.edit', $sold))->assertRedirect();

        $service->cancel($transaction->fresh());
        $this->assertDatabaseMissing('milk_distributions', ['sale_transaction_id' => $transaction->id]);
    }

    private function tank(Farm $farm, float $litres): MilkStorage
    {
        return MilkStorage::query()->create([
            'farm_id' => $farm->id,
            'storage_code' => 'MK-TEST-'.uniqid(),
            'container_name' => 'Tank '.uniqid(),
            'container_type' => 'bulk_tank',
            'capacity_liters' => 500,
            'current_quantity_liters' => $litres,
            'status' => 'in_use',
        ]);
    }

    private function payload(Farm $farm, array $overrides = []): array
    {
        return array_merge([
            'farm_id' => $farm->id,
            'distributed_at' => now()->subHour()->format('Y-m-d\TH:i'),
            'quantity_liters' => 2,
            'category' => 'household',
            'recipient_type' => 'household',
            'notes' => null,
        ], $overrides);
    }
}
