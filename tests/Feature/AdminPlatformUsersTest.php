<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Support\Str;
use Tests\Support\FarmTestFixtures;
use Tests\TenantTestCase;

class AdminPlatformUsersTest extends TenantTestCase
{
    public function test_guest_is_redirected_from_platform_users(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
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
}
