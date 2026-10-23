<?php

namespace Tests\Feature;

use App\Models\ExpenseVendor;
use App\Models\FeedInventory;
use App\Models\FeedType;
use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Support\Str;
use Tests\Support\FarmTestFixtures;
use Tests\TenantTestCase;

class RecordViewPagesTest extends TenantTestCase
{
    public function test_every_view_route_answers_cleanly_for_missing_records(): void
    {
        $this->actingAsTenantUser();

        foreach (config('record_views') as $recordType => $config) {
            $status = $this->get(route($config['route'].'.show', 999999))->status();
            $this->assertContains($status, [404], "{$recordType} returned {$status}");
        }
    }

    public function test_view_page_shows_fields_related_links_and_edit(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();
        $type = FeedType::create(['name' => 'Maize bran '.Str::random(4), 'unit' => 'kg', 'category' => 'Concentrate', 'is_active' => true]);
        $inventory = FeedInventory::create(['farm_id' => $farm->id, 'feed_type_id' => $type->id, 'quantity_on_hand' => 125.5, 'unit' => 'kg', 'reorder_level' => 20]);

        $this->get(route('feeding.inventory'))->assertOk()->assertSee(route('feeding.inventory.show', $inventory));

        $this->get(route('feeding.inventory.show', $inventory))
            ->assertOk()
            ->assertSee('Quantity in stock')
            ->assertSee('125.5')
            ->assertSee($type->name)
            ->assertSee(route('farms.show', $farm))
            ->assertSee(route('feeding.inventory.edit', $inventory));

        $this->get(route('feeding.feed-types.show', $type))->assertOk()->assertSee($type->name)->assertSee('Yes');
    }

    public function test_records_from_another_workspace_are_not_found(): void
    {
        $other = Tenant::query()->create(['id' => 'test_'.Str::lower(Str::random(10)), 'name' => 'Other']);
        TenantContext::set($other);
        $foreignType = FeedType::create(['name' => 'Foreign feed', 'unit' => 'kg', 'is_active' => true]);
        $foreignVendor = ExpenseVendor::create(['name' => 'Foreign vendor '.Str::random(4), 'is_active' => true]);
        TenantContext::set($this->tenant);

        $this->actingAsTenantUser();

        $this->get(route('feeding.feed-types.show', $foreignType))->assertNotFound();
        $this->get(route('expenses.vendors.show', $foreignVendor))->assertNotFound();

        $other->delete();
    }

    public function test_feeding_suppliers_section_is_gone(): void
    {
        $this->actingAsTenantUser();

        $this->assertFalse(\Illuminate\Support\Facades\Route::has('feeding.suppliers'));
        $this->get('/feeding/suppliers')->assertNotFound();
        $this->get(route('feeding.overview'))->assertOk()->assertSee('Overdue schedules')->assertDontSee('Suppliers');
    }

    public function test_member_without_module_permission_is_blocked(): void
    {
        $member = $this->createTenantUser(['role' => 'member']);
        $type = FeedType::create(['name' => 'Agro '.Str::random(4), 'unit' => 'kg', 'is_active' => true]);

        $this->actingAsTenantUser($member);

        $this->get(route('feeding.feed-types.show', $type))->assertForbidden();
    }
}
