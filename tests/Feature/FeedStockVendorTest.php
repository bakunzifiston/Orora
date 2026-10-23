<?php

namespace Tests\Feature;

use App\Models\FeedInventory;
use App\Models\FeedType;
use Illuminate\Support\Str;
use Tests\Support\FarmTestFixtures;
use Tests\TenantTestCase;

class FeedStockVendorTest extends TenantTestCase
{
    public function test_feed_type_and_stock_forms_do_not_ask_for_a_supplier(): void
    {
        $this->actingAsTenantUser();
        $inventory = $this->inventory();

        $this->get(route('feeding.feed-types.create'))->assertOk()->assertDontSee('name="feed_supplier_id"', false);
        $this->get(route('feeding.inventory.edit', $inventory))->assertOk()
            ->assertDontSee('name="feed_supplier_id"', false)
            ->assertSee('Log expense');
    }

    public function test_expense_vendor_shows_in_history_list_and_next_purchase(): void
    {
        $this->actingAsTenantUser();
        $inventory = $this->inventory();
        $vendor = 'Kigali Mills '.Str::random(4);

        $this->post(route('feeding.inventory.movements.store', $inventory), [
            'movement_type' => 'purchase',
            'quantity' => '50',
            'moved_at' => now()->toDateString(),
            'log_expense' => '1',
            'expense_amount' => '45000',
            'expense_vendor_name' => $vendor,
        ])->assertSessionHasNoErrors();

        $movement = $inventory->movements()->latest('id')->firstOrFail();
        $this->assertSame($vendor, $movement->expense?->vendor?->name);
        $this->assertEquals(50, (float) $inventory->fresh()->quantity_on_hand);

        $this->get(route('feeding.inventory.edit', $inventory))->assertOk()->assertSee($vendor);
        $this->get(route('feeding.inventory'))->assertOk()->assertSee('Last bought from: '.$vendor);
    }

    public function test_purchase_without_expense_still_records_stock(): void
    {
        $this->actingAsTenantUser();
        $inventory = $this->inventory();

        $this->post(route('feeding.inventory.movements.store', $inventory), [
            'movement_type' => 'purchase',
            'quantity' => '12',
            'moved_at' => now()->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->assertEquals(12, (float) $inventory->fresh()->quantity_on_hand);
    }

    private function inventory(): FeedInventory
    {
        $farm = FarmTestFixtures::farm();
        $type = FeedType::create(['name' => 'Concentrate '.Str::random(5), 'unit' => 'kg', 'is_active' => true]);

        return FeedInventory::create([
            'farm_id' => $farm->id,
            'feed_type_id' => $type->id,
            'quantity_on_hand' => 0,
            'unit' => 'kg',
        ]);
    }
}
