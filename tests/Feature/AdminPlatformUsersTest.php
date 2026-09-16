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
            ->assertSee('Has farm');
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
        FarmTestFixtures::farm(['name' => 'Kigali Dairy']);

        $this->get(route('central.accounts.show', $user))
            ->assertOk()
            ->assertSee('Kigali Farmer')
            ->assertSee('kigali.farmer@example.com')
            ->assertSee('Kigali Dairy');
    }
}
