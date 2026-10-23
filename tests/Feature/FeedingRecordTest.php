<?php

namespace Tests\Feature;

use App\Models\FeedInventory;
use App\Models\FeedType;
use Illuminate\Support\Str;
use Tests\Support\FarmTestFixtures;
use Tests\TenantTestCase;

class FeedingRecordTest extends TenantTestCase
{
    public function test_feeding_can_be_recorded_from_active_feed_stock(): void
    {
        $this->actingAsTenantUser();
        [$farm, $herd, $inventory] = $this->stock(active: true);

        $this->post(route('feeding.records.store'), $this->payload($farm->id, $herd->id, $inventory->id))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('feedings', ['feed_inventory_id' => $inventory->id, 'livestock_id' => $herd->id]);
        $this->assertEquals(95, (float) $inventory->fresh()->quantity_on_hand);
    }

    public function test_stock_of_an_inactive_feed_type_is_rejected(): void
    {
        $this->actingAsTenantUser();
        [$farm, $herd, $inventory] = $this->stock(active: false);

        $this->post(route('feeding.records.store'), $this->payload($farm->id, $herd->id, $inventory->id))
            ->assertSessionHasErrors('feed_inventory_id');
    }

    /**
     * @return array{0: \App\Models\Farm, 1: \App\Models\Livestock, 2: FeedInventory}
     */
    private function stock(bool $active): array
    {
        $farm = FarmTestFixtures::farm();
        $herd = FarmTestFixtures::livestock($farm);
        $type = FeedType::create(['name' => 'Silage '.Str::random(5), 'unit' => 'kg', 'is_active' => $active]);
        $inventory = FeedInventory::create([
            'farm_id' => $farm->id,
            'feed_type_id' => $type->id,
            'quantity_on_hand' => 100,
            'unit' => 'kg',
        ]);

        return [$farm, $herd, $inventory];
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(int $farmId, int $herdId, int $inventoryId): array
    {
        return [
            'farm_id' => $farmId,
            'feed_inventory_id' => $inventoryId,
            'feed_target' => 'group',
            'livestock_id' => $herdId,
            'quantity' => '5',
            'fed_on' => now()->toDateString(),
        ];
    }
}
