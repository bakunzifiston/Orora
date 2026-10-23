<?php

namespace Tests\Feature;

use App\Models\FeedInventory;
use App\Models\FeedType;
use Illuminate\Support\Str;
use Tests\Support\FarmTestFixtures;
use Tests\TenantTestCase;

class FeedInventoryQuantityTest extends TenantTestCase
{
    public function test_opening_quantity_is_recorded_as_a_movement(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();
        $type = FeedType::create(['name' => 'Maize bran '.Str::random(4), 'unit' => 'kg', 'is_active' => true]);

        $this->get(route('feeding.inventory.create'))->assertOk()->assertSee('Quantity in stock now');

        $this->post(route('feeding.inventory.store'), [
            'farm_id' => $farm->id,
            'feed_type_id' => $type->id,
            'opening_quantity' => '350.5',
            'reorder_level' => '50',
        ])->assertSessionHasNoErrors()->assertRedirect(route('feeding.inventory'));

        $inventory = FeedInventory::query()->where('feed_type_id', $type->id)->firstOrFail();
        $this->assertEquals(350.5, (float) $inventory->quantity_on_hand);
        $this->assertSame(1, $inventory->movements()->count());
        $this->assertSame('adjustment_in', $inventory->movements()->first()->movement_type);

        $this->get(route('feeding.inventory.edit', $inventory))->assertOk()->assertSee('Current stock')->assertSee('350.5');
        $this->get(route('feeding.inventory'))->assertOk()->assertSee('Quantity in stock')->assertSee('Add stock');
    }

    public function test_inventory_without_opening_quantity_starts_at_zero(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();
        $type = FeedType::create(['name' => 'Hay '.Str::random(4), 'unit' => 'bale', 'is_active' => true]);

        $this->post(route('feeding.inventory.store'), [
            'farm_id' => $farm->id,
            'feed_type_id' => $type->id,
            'opening_quantity' => '',
        ])->assertSessionHasNoErrors();

        $inventory = FeedInventory::query()->where('feed_type_id', $type->id)->firstOrFail();
        $this->assertEquals(0, (float) $inventory->quantity_on_hand);
        $this->assertSame(0, $inventory->movements()->count());
    }

    public function test_editing_settings_cannot_overwrite_quantity(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();
        $type = FeedType::create(['name' => 'Salt '.Str::random(4), 'unit' => 'kg', 'is_active' => true]);
        $this->post(route('feeding.inventory.store'), ['farm_id' => $farm->id, 'feed_type_id' => $type->id, 'opening_quantity' => '20']);
        $inventory = FeedInventory::query()->where('feed_type_id', $type->id)->firstOrFail();

        $this->put(route('feeding.inventory.update', $inventory), [
            'farm_id' => $farm->id,
            'feed_type_id' => $type->id,
            'opening_quantity' => '999',
            'reorder_level' => '5',
        ])->assertSessionHasNoErrors();

        $this->assertEquals(20, (float) $inventory->fresh()->quantity_on_hand);
    }
}
