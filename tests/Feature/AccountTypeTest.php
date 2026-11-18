<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\Tenant;
use App\Models\TenantAccount;
use App\Models\User;
use App\Services\AccountTypeStatsService;
use App\Services\TenantContext;
use App\Support\AccountType;
use Illuminate\Support\Str;
use Tests\Support\FarmTestFixtures;
use Tests\TenantTestCase;

class AccountTypeTest extends TenantTestCase
{
    protected function tearDown(): void
    {
        AccountType::useView(null);
        AccountType::flush();

        parent::tearDown();
    }

    public function test_new_and_existing_accounts_default_to_real(): void
    {
        $this->assertSame('real', $this->tenant->fresh()->account_type);
        $this->assertFalse($this->tenant->fresh()->isTesting());
    }

    public function test_super_admin_can_classify_a_workspace_as_testing_and_back(): void
    {
        $this->actingAsAdmin();
        $user = $this->createTenantUser(['tenant_id' => $this->tenant->id]);

        $this->patch(route('central.accounts.account-type', $user), ['account_type' => 'testing'])
            ->assertRedirect();
        $this->assertTrue($this->tenant->fresh()->isTesting());

        $this->patch(route('central.accounts.account-type', $user), ['account_type' => 'real'])
            ->assertRedirect();
        $this->assertFalse($this->tenant->fresh()->isTesting());

        $this->patch(route('central.accounts.account-type', $user), ['account_type' => 'bogus'])
            ->assertSessionHasErrors('account_type');
    }

    public function test_non_admin_cannot_classify_accounts(): void
    {
        $user = $this->createTenantUser(['tenant_id' => $this->tenant->id]);
        $this->actingAs($user);

        $this->patch(route('central.accounts.account-type', $user), ['account_type' => 'testing'])
            ->assertRedirect();
        $this->assertFalse($this->tenant->fresh()->isTesting());
    }

    public function test_testing_accounts_are_excluded_from_production_views_but_not_deleted(): void
    {
        $realFarm = FarmTestFixtures::farm();

        $testingTenant = Tenant::query()->create([
            'id' => 'test_'.Str::lower(Str::random(10)),
            'name' => 'Demo workspace',
            'account_type' => 'testing',
        ]);
        $testingFarm = TenantContext::run($testingTenant, fn () => FarmTestFixtures::farm());
        AccountType::flush();

        TenantContext::forget();

        // No view selected: nothing is filtered (application default).
        $this->assertTrue(Farm::query()->whereKey([$realFarm->id, $testingFarm->id])->count() === 2);

        AccountType::useView('real');
        $this->assertTrue(Farm::query()->whereKey($realFarm->id)->exists());
        $this->assertFalse(Farm::query()->whereKey($testingFarm->id)->exists());

        AccountType::useView('testing');
        $this->assertFalse(Farm::query()->whereKey($realFarm->id)->exists());
        $this->assertTrue(Farm::query()->whereKey($testingFarm->id)->exists());

        // Inside a workspace the type never hides that workspace's own data.
        AccountType::useView('real');
        TenantContext::set($testingTenant);
        $this->assertTrue(Farm::query()->whereKey($testingFarm->id)->exists());

        TenantContext::forget();
        AccountType::useView(null);
        $stats = app(AccountTypeStatsService::class)->compare();
        $this->assertGreaterThanOrEqual(1, $stats['real']['farms']);
        $this->assertGreaterThanOrEqual(1, $stats['testing']['farms']);

        $testingTenant->delete();
    }

    public function test_accounts_list_filters_by_type_and_shows_badges(): void
    {
        $this->actingAsAdmin();
        $realUser = $this->createTenantUser(['name' => 'Real Farmer', 'tenant_id' => $this->tenant->id]);

        $testingTenant = Tenant::query()->create([
            'id' => 'test_'.Str::lower(Str::random(10)),
            'name' => 'Demo workspace',
            'account_type' => 'testing',
        ]);
        $demoUser = User::factory()->create(['name' => 'Demo Person', 'tenant_id' => $testingTenant->id]);
        TenantAccount::query()->create(['tenant_id' => $testingTenant->id, 'email' => strtolower($demoUser->email)]);
        AccountType::flush();

        $this->get(route('central.accounts.index'))
            ->assertOk()->assertSee('Real Farmer')->assertSee('Demo Person')->assertSee('Testing');

        $this->get(route('central.accounts.index', ['account_type' => 'testing']))
            ->assertOk()->assertSee('Demo Person')->assertDontSee('Real Farmer');

        $this->get(route('central.accounts.index', ['account_type' => 'real']))
            ->assertOk()->assertSee('Real Farmer')->assertDontSee('Demo Person');

        // A testing account can still be opened directly while the dashboard defaults to real.
        $this->get(route('central.accounts.show', $demoUser))->assertOk()->assertSee('Testing');

        $testingTenant->delete();
    }

    public function test_admin_dashboard_defaults_to_real_accounts_and_can_switch(): void
    {
        $this->actingAsAdmin();

        $this->get(route('central.dashboard'))->assertOk()->assertSee('Active')->assertDontSee('Real vs testing accounts');
        $this->get(route('central.dashboard', ['account_type' => 'testing']))
            ->assertOk()->assertSee('Showing testing accounts only');
        $this->get(route('central.farms.index'))->assertOk();
    }

    public function test_testing_workspace_shows_a_banner_to_its_users(): void
    {
        $this->tenant->forceFill(['account_type' => 'testing'])->save();
        AccountType::flush();

        $user = $this->createTenantUser(['tenant_id' => $this->tenant->id]);
        $this->actingAsTenantUser($user);

        $this->get(route('dashboard'))->assertOk()->assertSee('TESTING ACCOUNT');
    }
}
