<?php

namespace Tests\Feature;

use App\Models\Central\MarketplaceCategory;
use App\Models\Central\MarketplaceInquiry;
use App\Models\Central\MarketplaceListing;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\MarketplaceInquiryReceived;
use App\Services\TenantContext;
use Illuminate\Support\Str;
use Tests\Support\FarmTestFixtures;
use Tests\TenantTestCase;

class MarketplaceAdminConnectionTest extends TenantTestCase
{
    public function test_admin_sees_and_filters_listings_from_every_category(): void
    {
        $this->actingAsAdmin();
        $milk = $this->listing('milk_dairy', 'milk-dairy', 'Fresh milk from Musanze');
        $animal = $this->listing('animal', 'live-animals', 'Friesian heifer');

        $this->get(route('central.ecommerce.index'))
            ->assertOk()
            ->assertSee($milk->title)
            ->assertSee($animal->title)
            ->assertSee('Milk &amp; Dairy', false);

        $this->get(route('central.ecommerce.index', ['type' => 'milk_dairy']))
            ->assertOk()
            ->assertSee($milk->title)
            ->assertDontSee($animal->title);
    }

    public function test_admin_can_open_and_publish_a_non_animal_listing(): void
    {
        $this->actingAsAdmin();
        $feed = $this->listing('feed_supply', 'feed-supplies', 'Dairy meal 50 kg', 'pending_review');

        $this->get(route('central.ecommerce.listings.show', $feed))
            ->assertOk()
            ->assertSee('Product details')
            ->assertDontSee(route('central.ecommerce.listings.edit', $feed));

        $this->patch(route('central.ecommerce.listings.status', $feed), ['status' => 'approved'])->assertSessionHasNoErrors();
        $this->patch(route('central.ecommerce.listings.status', $feed), ['status' => 'active'])->assertSessionHasNoErrors();

        $this->assertSame('active', $feed->fresh()->status);
    }

    public function test_listing_rows_show_the_next_status_actions(): void
    {
        $this->actingAsAdmin();
        $pending = $this->listing('meat', 'meat-products', 'Pending goat meat', 'pending_review');
        $published = $this->listing('animal', 'live-animals', 'Published heifer');

        $this->get(route('central.ecommerce.index', ['q' => 'Pending goat meat']))
            ->assertOk()
            ->assertSeeInOrder(['Approve', 'Reject', 'Manage'])
            ->assertDontSee('Mark sold');

        $this->get(route('central.ecommerce.index', ['q' => 'Published heifer']))
            ->assertOk()
            ->assertSeeInOrder(['Mark sold', 'Unpublish', 'View in shop', 'Edit', 'Manage'])
            ->assertSee(route('marketplace.shop.show', $published))
            ->assertDontSee('>Approve<', false);

        $this->patch(route('central.ecommerce.listings.status', $pending), ['status' => 'rejected'])->assertSessionHasNoErrors();
        $this->assertSame('rejected', $pending->fresh()->status);
    }

    public function test_admin_menu_shows_listings_waiting_for_review(): void
    {
        $this->actingAsAdmin();
        $before = MarketplaceListing::query()->where('status', 'pending_review')->count();
        $this->listing('meat', 'meat-products', 'Beef quarter', 'pending_review');
        $this->listing('meat', 'meat-products', 'Goat meat', 'pending_review');

        $this->get(route('central.ecommerce.index'))
            ->assertOk()
            ->assertSee('<span class="dash-nav__count"', false)
            ->assertSee(($before + 2).' listings waiting for review');
    }

    public function test_buyer_inquiry_notifies_seller_and_shows_on_my_listings(): void
    {
        $seller = $this->createTenantUser(['tenant_id' => $this->tenant->id]);
        $listing = $this->listing('milk_dairy', 'milk-dairy', 'Daily milk supply');

        $this->post(route('marketplace.shop.inquiry', $listing), [
            'buyer_name' => 'Aline Buyer',
            'buyer_phone' => '0781234567',
            'message' => 'Can you deliver 20 litres every morning?',
        ])->assertRedirect(route('marketplace.shop.show', $listing));

        $notification = $seller->fresh()->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame(MarketplaceInquiryReceived::class, $notification->type);
        $this->assertSame('New buyer inquiry: Daily milk supply', $notification->data['title']);
        $this->assertStringContainsString('Aline Buyer', $notification->data['message']);

        $this->actingAsTenantUser($seller);

        $this->get(route('marketplace.shop.mine'))
            ->assertOk()
            ->assertSee('Buyer inquiries')
            ->assertSee('Aline Buyer')
            ->assertSee('Can you deliver 20 litres every morning?')
            ->assertSee('1 new');
    }

    public function test_seller_can_update_own_inquiry_but_not_another_workspaces(): void
    {
        $seller = $this->createTenantUser(['tenant_id' => $this->tenant->id]);
        $own = $this->inquiry($this->listing('animal', 'live-animals', 'Own cow'));

        $otherTenant = Tenant::query()->create(['id' => 'other_'.Str::lower(Str::random(8)), 'name' => 'Other workspace']);
        $foreignListing = TenantContext::run($otherTenant, fn () => $this->listing('animal', 'live-animals', 'Foreign cow', 'active', $otherTenant->id));
        $foreign = $this->inquiry($foreignListing);

        $this->actingAsTenantUser($seller);

        $this->patch(route('marketplace.shop.inquiries.update', $own), ['status' => 'contacted'])
            ->assertRedirect(route('marketplace.shop.mine').'#inquiries');
        $this->assertSame('contacted', $own->fresh()->status);

        $this->patch(route('marketplace.shop.inquiries.update', $foreign), ['status' => 'closed'])->assertForbidden();
        $this->assertSame('new', $foreign->fresh()->status);

        $otherTenant->delete();
    }

    private function listing(string $type, string $categorySlug, string $title, string $status = 'active', ?string $tenantId = null): MarketplaceListing
    {
        $farm = FarmTestFixtures::farm();
        $category = MarketplaceCategory::query()->firstOrCreate(
            ['slug' => $categorySlug],
            ['name' => Str::headline($categorySlug), 'is_active' => true],
        );

        return MarketplaceListing::query()->create([
            'tenant_id' => $tenantId ?? $this->tenant->id,
            'farm_id' => $farm->id,
            'category_id' => $category->id,
            'listing_code' => 'LST-'.Str::upper(Str::random(10)),
            'listing_type' => $type,
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'description' => $title.' description.',
            'quantity' => 1,
            'unit' => $type === 'animal' ? 'head' : 'kg',
            'price' => 1000,
            'price_type' => 'fixed',
            'currency' => 'RWF',
            'seller_name' => 'Farm owner',
            'seller_phone' => '0780000000',
            'seller_type' => 'individual',
            'location_district' => 'Gasabo',
            'status' => $status,
        ]);
    }

    private function inquiry(MarketplaceListing $listing): MarketplaceInquiry
    {
        return MarketplaceInquiry::query()->create([
            'listing_id' => $listing->id,
            'buyer_name' => 'Buyer',
            'buyer_phone' => '0781111111',
            'message' => 'Interested.',
            'status' => 'new',
        ]);
    }
}
