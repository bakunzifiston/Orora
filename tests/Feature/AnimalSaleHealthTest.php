<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\SaleTransaction;
use Illuminate\Support\Str;
use Tests\Support\FarmTestFixtures;
use Tests\TenantTestCase;

class AnimalSaleHealthTest extends TenantTestCase
{
    public function test_sick_pregnant_and_quarantined_animals_can_be_sold_with_a_warning(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();
        $herd = FarmTestFixtures::livestock($farm);

        foreach (['Sick' => true, 'Pregnant' => false, 'Quarantined' => true, 'Under treatment' => true] as $status => $warns) {
            $animal = FarmTestFixtures::animal($farm, $herd, 'female', ['health_status' => $status]);
            $sale = $this->draftSale($farm->id);

            $page = $this->get(route('sales.transactions.show', $sale))->assertOk()->assertSee($animal->tag_number);
            $warns
                ? $page->assertSee('— ⚠ '.$status, false)
                : $page->assertDontSee('— ⚠ '.$status, false);

            $this->post(route('sales.transactions.items.store', $sale), $this->animalItem($animal))
                ->assertSessionHasNoErrors();

            $html = $this->get(route('sales.transactions.show', $sale))->assertOk()->getContent();
            $this->assertSame($warns, str_contains($html, ': tell the buyer</span>'), "warning label for {$status}");

            $this->post(route('sales.transactions.complete', $sale))->assertSessionHasNoErrors();
            $this->assertSame('Sold', $animal->fresh()->lifecycle_status, "{$status} animal should be sold");
        }
    }

    public function test_sold_animal_cannot_be_sold_again(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();
        $herd = FarmTestFixtures::livestock($farm);
        $animal = FarmTestFixtures::animal($farm, $herd, 'female', ['lifecycle_status' => 'Sold']);
        $sale = $this->draftSale($farm->id);

        $this->get(route('sales.transactions.show', $sale))->assertOk()->assertDontSee($animal->tag_number);

        $this->post(route('sales.transactions.items.store', $sale), $this->animalItem($animal));
        $this->assertSame(0, $sale->items()->count());
    }

    private function draftSale(int $farmId): SaleTransaction
    {
        $this->post(route('sales.transactions.store'), [
            'farm_id' => $farmId,
            'sale_type' => 'animal_sale',
            'sale_date' => now()->toDateString(),
            'customer_mode' => 'none',
            'pricing_method' => array_key_first(config('modules.sale_pricing_methods')),
            'currency' => 'RWF',
        ])->assertSessionHasNoErrors();

        return SaleTransaction::query()->where('farm_id', $farmId)->latest('id')->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function animalItem(Animal $animal): array
    {
        return [
            'item_type' => 'animal',
            'animal_id' => $animal->id,
            'description' => 'Sale of '.$animal->tag_number,
            'quantity' => 1,
            'unit' => 'head',
            'unit_price' => 500000,
        ];
    }
}
