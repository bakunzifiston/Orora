<?php

namespace Tests\Feature;

use App\Models\Central\MarketplaceCategory;
use App\Models\Central\MarketplaceInquiry;
use App\Models\Central\MarketplaceListing;
use App\Models\Tenant;
use App\Models\TenantAccount;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Support\FarmTestFixtures;
use Tests\TenantTestCase;

class AdminPlatformUsersTest extends TenantTestCase
{
    public function test_guest_is_redirected_from_platform_users(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
    }

    public function test_admin_can_view_animal_marketplace_dashboard(): void
    {
        $this->actingAsAdmin();

        $this->get(route('central.ecommerce.index'))
            ->assertOk()
            ->assertSee(route('central.ecommerce.index'))
            ->assertSee('Animal marketplace')
            ->assertSee('Pending review')
            ->assertSee('Buyer inquiries')
            ->assertSee('Create listing')
            ->assertSee('No animal listings match these filters.');
    }

    public function test_non_super_admin_cannot_access_animal_marketplace_management(): void
    {
        $this->actingAsAdmin(['is_super_admin' => false]);

        $this->get(route('central.ecommerce.index'))->assertForbidden();
    }

    public function test_admin_can_create_a_linked_animal_listing_as_a_draft(): void
    {
        $this->actingAsAdmin();
        $farm = FarmTestFixtures::farm();
        $livestock = FarmTestFixtures::livestock($farm);
        $animal = FarmTestFixtures::animal($farm, $livestock, overrides: [
            'species' => 'cattle',
            'breed' => 'Friesian',
        ]);
        $category = $this->liveAnimalCategory();

        $this->post(route('central.ecommerce.listings.store'), [
            'farm_id' => $farm->id,
            'animal_id' => $animal->id,
            'category_id' => $category->id,
            'title' => 'Friesian for sale',
            'description' => 'Healthy animal ready for sale.',
            'animal_species' => 'cattle',
            'animal_sex' => 'female',
            'breed' => 'Friesian',
            'age' => '2 years',
            'quantity' => 1,
            'unit' => 'head',
            'price' => 450000,
            'price_type' => 'fixed',
            'seller_name' => 'Farm owner',
            'seller_phone' => '0780000000',
            'seller_type' => 'individual',
            'location_district' => 'Gasabo',
        ])->assertRedirect();

        $listing = MarketplaceListing::query()->where('title', 'Friesian for sale')->firstOrFail();
        $this->assertSame('draft', $listing->status);
        $this->assertSame((int) $animal->id, (int) $listing->animal_id);
        $this->assertSame((int) $farm->id, (int) $listing->farm_id);
        $this->assertDatabaseHas('marketplace_listing_status_changes', [
            'listing_id' => $listing->id,
            'to_status' => 'draft',
        ]);
    }

    public function test_admin_can_change_listing_status_and_status_is_audited(): void
    {
        $this->actingAsAdmin();
        $farm = FarmTestFixtures::farm();
        $category = $this->liveAnimalCategory();
        $listing = MarketplaceListing::query()->create([
            'tenant_id' => $farm->tenant_id,
            'farm_id' => $farm->id,
            'category_id' => $category->id,
            'listing_code' => 'LST-TEST-'.uniqid(),
            'listing_type' => 'animal',
            'title' => 'Pending cow',
            'slug' => 'pending-cow-'.uniqid(),
            'description' => 'Cow listing for review.',
            'quantity' => 1,
            'unit' => 'head',
            'price' => 250000,
            'price_type' => 'fixed',
            'currency' => 'RWF',
            'seller_name' => 'Farm owner',
            'seller_phone' => '0780000000',
            'seller_type' => 'individual',
            'location_district' => 'Gasabo',
            'status' => 'pending_review',
        ]);

        $this->patch(route('central.ecommerce.listings.status', $listing), [
            'status' => 'approved',
            'note' => 'Farm details checked.',
        ])->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Listing status updated.');

        $this->assertSame('approved', $listing->fresh()->status);
        $this->assertDatabaseHas('marketplace_listing_status_changes', [
            'listing_id' => $listing->id,
            'from_status' => 'pending_review',
            'to_status' => 'approved',
            'note' => 'Farm details checked.',
        ]);
    }

    public function test_farmer_submission_reuses_animal_and_waits_for_review(): void
    {
        $user = $this->createTenantUser([
            'name' => 'Kigali Farmer',
            'email' => 'kigali.farmer@example.com',
        ]);
        $farm = FarmTestFixtures::farm();
        $livestock = FarmTestFixtures::livestock($farm);
        $animal = FarmTestFixtures::animal($farm, $livestock, overrides: [
            'species' => 'cattle',
            'breed' => 'Friesian',
        ]);
        $category = $this->liveAnimalCategory();

        $this->actingAs($user)->post(route('marketplace.shop.store'), [
            'farm_id' => $farm->id,
            'animal_id' => $animal->id,
            'category_id' => $category->id,
            'listing_type' => 'animal',
            'title' => 'Friesian for sale',
            'description' => 'Healthy animal ready for sale.',
            'quantity' => 1,
            'unit' => 'head',
            'price' => 450000,
            'price_type' => 'fixed',
            'seller_name' => 'Farm owner',
            'seller_phone' => '0780000000',
            'seller_type' => 'individual',
            'location_district' => 'Gasabo',
        ])->assertRedirect(route('marketplace.shop.mine'));

        $listing = MarketplaceListing::query()->where('title', 'Friesian for sale')->firstOrFail();
        $this->assertSame('pending_review', $listing->status);
        $this->assertSame((int) $animal->id, (int) $listing->animal_id);
        $this->assertSame((int) $farm->id, (int) $listing->farm_id);
        $this->assertDatabaseHas('marketplace_listing_status_changes', [
            'listing_id' => $listing->id,
            'to_status' => 'pending_review',
            'user_id' => $user->id,
        ]);
    }

    public function test_farmer_cannot_submit_another_workspace_animal(): void
    {
        $user = $this->createTenantUser([
            'name' => 'Kigali Farmer',
            'email' => 'kigali.farmer@example.com',
        ]);
        $farm = FarmTestFixtures::farm();
        $livestock = FarmTestFixtures::livestock($farm);
        $otherTenant = Tenant::query()->create([
            'id' => 'other_'.Str::lower(Str::random(8)),
            'name' => 'Second workspace',
        ]);
        $otherFarm = TenantContext::run($otherTenant, fn () => FarmTestFixtures::farm());
        $otherLivestock = TenantContext::run($otherTenant, fn () => FarmTestFixtures::livestock($otherFarm));
        $otherAnimal = TenantContext::run($otherTenant, fn () => FarmTestFixtures::animal($otherFarm, $otherLivestock));
        $category = $this->liveAnimalCategory();

        $this->actingAs($user)
            ->from(route('marketplace.shop.create'))
            ->post(route('marketplace.shop.store'), [
                'farm_id' => $farm->id,
                'animal_id' => $otherAnimal->id,
                'category_id' => $category->id,
                'listing_type' => 'animal',
                'title' => 'Unauthorized animal listing',
                'description' => 'This must not be submitted.',
                'quantity' => 1,
                'unit' => 'head',
                'price' => 450000,
                'price_type' => 'fixed',
                'seller_name' => 'Farm owner',
                'seller_phone' => '0780000000',
                'seller_type' => 'individual',
                'location_district' => 'Gasabo',
            ])
            ->assertRedirect(route('marketplace.shop.create'))
            ->assertSessionHasErrors('animal_id');

        $this->assertDatabaseMissing('marketplace_listings', ['title' => 'Unauthorized animal listing']);
    }

    public function test_farmer_marketplace_workflow_upload_rejection_resubmission_publication_and_sold_status(): void
    {
        Storage::fake('public');

        $farmer = $this->createTenantUser([
            'name' => 'Kigali Farmer',
            'email' => 'market-farmer-'.Str::lower(Str::random(10)).'@example.com',
        ]);
        $farm = FarmTestFixtures::farm();
        $livestock = FarmTestFixtures::livestock($farm);
        $animal = FarmTestFixtures::animal($farm, $livestock, overrides: [
            'species' => 'cattle',
            'breed' => 'Friesian',
        ]);
        $category = $this->liveAnimalCategory();
        $payload = $this->farmerAnimalListingPayload($farm->id, $animal->id, $category->id);
        $payload['images'] = [UploadedFile::fake()->image('cow.png')];

        $this->loginAsFarmer($farmer);
        $this->post(route('marketplace.shop.store'), $payload)
            ->assertRedirect(route('marketplace.shop.mine'));

        $listing = MarketplaceListing::query()->where('title', $payload['title'])->firstOrFail();
        $this->assertSame('pending_review', $listing->status);
        $this->assertSame((int) $animal->id, (int) $listing->animal_id);
        $this->assertCount(1, $listing->images);
        Storage::disk('public')->assertExists(Str::after($listing->images[0], '/storage/'));

        $this->get(route('marketplace.shop'))->assertDontSee($listing->title);
        $this->get(route('marketplace.shop.mine'))->assertOk()->assertSee($listing->title);

        $admin = $this->actingAsAdmin();
        $this->patch(route('central.ecommerce.listings.status', $listing), ['status' => 'active'])
            ->assertSessionHasErrors('status');
        $this->assertSame('pending_review', $listing->fresh()->status);

        $this->patch(route('central.ecommerce.listings.status', $listing), [
            'status' => 'rejected',
            'note' => 'Please add more animal details.',
        ])->assertSessionHasNoErrors();
        $this->assertSame('rejected', $listing->fresh()->status);
        $this->get(route('marketplace.shop.mine'))->assertOk()->assertSee('Rejected');

        $this->app['auth']->shouldUse('web');
        $this->assertAuthenticatedAs($farmer, 'web');
        $payload['images'] = [];
        $payload['keep_images'] = $listing->images;
        $this->put(route('marketplace.shop.update', $listing), $payload)
            ->assertRedirect(route('marketplace.shop.mine'));

        $listing->refresh();
        $this->assertSame('pending_review', $listing->status);
        $this->assertSame($payload['title'], $listing->title);
        $this->assertSame(1, count($listing->images));
        $this->assertDatabaseHas('marketplace_listing_status_changes', [
            'listing_id' => $listing->id,
            'from_status' => 'rejected',
            'to_status' => 'pending_review',
            'note' => 'Resubmitted by farmer after editing',
        ]);
        $this->get(route('marketplace.shop'))->assertDontSee($listing->title);

        $this->actingAs($admin, 'admin');
        $this->patch(route('central.ecommerce.listings.status', $listing), ['status' => 'approved'])
            ->assertSessionHasNoErrors();
        $this->get(route('marketplace.shop'))->assertDontSee($listing->title);

        $this->patch(route('central.ecommerce.listings.status', $listing), ['status' => 'active'])
            ->assertSessionHasNoErrors();
        $this->get(route('marketplace.shop'))->assertOk()->assertSee($listing->title);
        $this->get(route('marketplace.shop.show', $listing))->assertOk()->assertSee($listing->title);

        $this->patch(route('central.ecommerce.listings.status', $listing), ['status' => 'sold'])
            ->assertSessionHasNoErrors();
        $this->get(route('marketplace.shop'))->assertDontSee($listing->title);
        $this->get(route('marketplace.shop.show', $listing))->assertNotFound();
        $this->assertDatabaseHas('marketplace_listing_status_changes', [
            'listing_id' => $listing->id,
            'from_status' => 'active',
            'to_status' => 'sold',
        ]);
    }

    public function test_farmer_cannot_view_or_manage_another_farmers_listings_animals_or_inquiries(): void
    {
        $owner = $this->createTenantUser([
            'name' => 'Listing Owner',
            'email' => 'owner@example.com',
        ]);
        $ownerFarm = FarmTestFixtures::farm();
        $ownerLivestock = FarmTestFixtures::livestock($ownerFarm);
        $ownerAnimal = FarmTestFixtures::animal($ownerFarm, $ownerLivestock);
        $otherTenant = Tenant::query()->create([
            'id' => 'other_'.Str::lower(Str::random(8)),
            'name' => 'Other farmer workspace',
        ]);
        $otherFarmer = TenantContext::run($otherTenant, fn () => User::factory()->create([
            'name' => 'Other Farmer',
            'email' => 'other-farmer-'.Str::lower(Str::random(10)).'@example.com',
        ]));
        TenantAccount::query()->create([
            'tenant_id' => $otherTenant->id,
            'email' => $otherFarmer->email,
        ]);
        $otherFarm = TenantContext::run($otherTenant, fn () => FarmTestFixtures::farm());
        $otherLivestock = TenantContext::run($otherTenant, fn () => FarmTestFixtures::livestock($otherFarm));
        $otherAnimal = TenantContext::run($otherTenant, fn () => FarmTestFixtures::animal($otherFarm, $otherLivestock));
        $category = $this->liveAnimalCategory();
        $listing = MarketplaceListing::query()->create([
            'tenant_id' => $this->tenant->id,
            'farm_id' => $ownerFarm->id,
            'animal_id' => $ownerAnimal->id,
            'category_id' => $category->id,
            'listing_code' => 'LST-OWNER-'.uniqid(),
            'listing_type' => 'animal',
            'title' => 'Private owner listing',
            'slug' => 'private-owner-listing-'.uniqid(),
            'description' => 'Listing visible to its owner and platform admins.',
            'quantity' => 1,
            'unit' => 'head',
            'price' => 250000,
            'price_type' => 'fixed',
            'currency' => 'RWF',
            'seller_name' => 'Listing Owner',
            'seller_phone' => '0780000000',
            'seller_type' => 'individual',
            'location_district' => 'Gasabo',
            'status' => 'pending_review',
        ]);
        $inquiry = MarketplaceInquiry::query()->create([
            'listing_id' => $listing->id,
            'buyer_name' => 'Interested buyer',
            'buyer_phone' => '0781111111',
            'buyer_email' => 'buyer@example.com',
            'message' => 'I would like to discuss this animal.',
            'status' => 'new',
        ]);

        TenantContext::set($otherTenant);
        $this->loginAsFarmer($otherFarmer);

        $this->get(route('marketplace.shop.mine'))
            ->assertOk()
            ->assertDontSee($listing->title);
        $this->get(route('marketplace.shop.create'))
            ->assertOk()
            ->assertSee($otherAnimal->tag_number)
            ->assertDontSee($ownerAnimal->tag_number);
        $this->get(route('animals.show', $ownerAnimal))->assertNotFound();
        $this->get(route('marketplace.shop.edit', $listing))->assertForbidden();
        $this->put(route('marketplace.shop.update', $listing), $this->farmerAnimalListingPayload(
            (int) $otherFarm->id,
            (int) $otherAnimal->id,
            (int) $category->id,
        ))->assertForbidden();
        $this->delete(route('marketplace.shop.destroy', $listing))->assertForbidden();
        $this->get(route('marketplace.shop.show', $listing))->assertNotFound();
        $this->get(route('central.ecommerce.listings.show', $listing))->assertRedirect();
        $this->patch(route('central.ecommerce.inquiries.update', $inquiry), ['status' => 'closed'])
            ->assertRedirect();
        $this->assertDatabaseHas('marketplace_inquiries', [
            'id' => $inquiry->id,
            'status' => 'new',
        ]);
        $this->assertDatabaseHas('marketplace_listings', [
            'id' => $listing->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'pending_review',
        ]);
        TenantContext::set($this->tenant);
        $otherTenant->delete();
    }

    public function test_admin_can_list_all_platform_users(): void
    {
        $this->actingAsAdmin();

        $this->createTenantUser([
            'name' => 'Kigali Farmer',
            'email' => 'kigali.farmer@example.com',
        ]);

        $otherTenant = Tenant::query()->create([
            'id' => 'other_'.Str::lower(Str::random(8)),
            'name' => 'Second workspace',
        ]);

        TenantContext::run($otherTenant, function () {
            return User::factory()->create([
                'name' => 'Musanze Farmer',
                'email' => 'musanze.farmer@example.com',
            ]);
        });

        FarmTestFixtures::farm(['name' => 'Kigali Dairy']);

        $this->get(route('central.accounts.index'))
            ->assertOk()
            ->assertSee('Kigali Farmer')
            ->assertSee('kigali.farmer@example.com')
            ->assertSee('Musanze Farmer')
            ->assertSee('musanze.farmer@example.com')
            ->assertSee('Has farm')
            ->assertSee('View')
            ->assertSee('Delete');
    }

    public function test_admin_can_search_platform_users(): void
    {
        $this->actingAsAdmin();
        $this->createTenantUser([
            'name' => 'Kigali Farmer',
            'email' => 'kigali.farmer@example.com',
        ]);

        $this->get(route('central.accounts.index', ['q' => 'Musanze']))
            ->assertOk()
            ->assertDontSee('Kigali Farmer');

        $this->get(route('central.accounts.index', ['q' => 'Kigali']))
            ->assertOk()
            ->assertSee('Kigali Farmer');
    }

    public function test_admin_can_view_a_platform_user(): void
    {
        $this->actingAsAdmin();
        $user = $this->createTenantUser([
            'name' => 'Kigali Farmer',
            'email' => 'kigali.farmer@example.com',
        ]);
        $farm = FarmTestFixtures::farm(['name' => 'Kigali Dairy']);
        FarmTestFixtures::livestock($farm);

        $this->get(route('central.accounts.show', $user))
            ->assertOk()
            ->assertSee('Kigali Farmer')
            ->assertSee('kigali.farmer@example.com')
            ->assertSee('Kigali Dairy')
            ->assertSee('Related records')
            ->assertSee('Livestock groups')
            ->assertSee('Delete user');
    }

    public function test_admin_can_delete_a_user_and_related_workspace_data(): void
    {
        $this->actingAsAdmin();

        $user = $this->createTenantUser([
            'name' => 'Kigali Farmer',
            'email' => 'kigali.farmer@example.com',
        ]);
        $farm = FarmTestFixtures::farm(['name' => 'Kigali Dairy']);
        $livestock = FarmTestFixtures::livestock($farm);
        $animal = FarmTestFixtures::animal($farm, $livestock);
        $tenantId = $this->tenant->id;

        $otherTenant = Tenant::query()->create([
            'id' => 'keep_'.Str::lower(Str::random(8)),
            'name' => 'Keep workspace',
        ]);
        $keptUser = TenantContext::run($otherTenant, function () {
            return User::factory()->create([
                'name' => 'Musanze Farmer',
                'email' => 'musanze.farmer@example.com',
            ]);
        });

        $this->from(route('central.accounts.index'))
            ->delete(route('central.accounts.destroy', $user))
            ->assertRedirect(route('central.accounts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('farms', ['id' => $farm->id]);
        $this->assertDatabaseMissing('livestock', ['id' => $livestock->id]);
        $this->assertDatabaseMissing('animals', ['id' => $animal->id]);
        $this->assertDatabaseMissing('tenants', ['id' => $tenantId]);
        $this->assertDatabaseMissing('tenant_accounts', [
            'tenant_id' => $tenantId,
            'email' => 'kigali.farmer@example.com',
        ]);
        $this->assertDatabaseHas('users', ['id' => $keptUser->id]);
        $this->assertDatabaseHas('tenants', ['id' => $otherTenant->id]);
    }

    private function liveAnimalCategory(): MarketplaceCategory
    {
        return MarketplaceCategory::query()->firstOrCreate(
            ['slug' => 'live-animals'],
            ['name' => 'Live Animals', 'is_active' => true],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function farmerAnimalListingPayload(int $farmId, int $animalId, int $categoryId): array
    {
        return [
            'farm_id' => $farmId,
            'animal_id' => $animalId,
            'category_id' => $categoryId,
            'listing_type' => 'animal',
            'title' => 'Test Friesian for sale',
            'description' => 'Healthy animal ready for sale.',
            'animal_species' => 'cattle',
            'animal_sex' => 'female',
            'breed' => 'Friesian',
            'age' => '2 years',
            'quantity' => 1,
            'unit' => 'head',
            'price' => 450000,
            'price_type' => 'fixed',
            'seller_name' => 'Farm owner',
            'seller_phone' => '0780000000',
            'seller_type' => 'individual',
            'location_district' => 'Gasabo',
        ];
    }

    private function loginAsFarmer(User $farmer): void
    {
        auth('web')->logout();
        auth('admin')->logout();
        $this->app['auth']->forgetGuards();
        $this->flushSession();
        TenantContext::set($farmer->tenant_id);

        $this->post(route('login.store'), [
            'email' => $farmer->email,
            'password' => 'password',
        ])->assertRedirect();
        $this->assertAuthenticatedAs($farmer, 'web');
    }
}
