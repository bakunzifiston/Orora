<?php

namespace Tests\Feature;

use App\Models\Deworming;
use Carbon\Carbon;
use Tests\Support\FarmTestFixtures;
use Tests\TenantTestCase;

class AuthAndDashboardTest extends TenantTestCase
{
    public function test_guest_sees_login_page(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_tenant_user_can_view_dashboard(): void
    {
        $this->actingAsTenantUser();

        $this->get('/dashboard')->assertOk();
    }

    public function test_dashboard_alerts_for_scheduled_deworming_within_seven_days(): void
    {
        Carbon::setTestNow('2026-10-15 12:00:00');
        try {
            $this->actingAsTenantUser();
            $farm = FarmTestFixtures::farm();

            Deworming::create([
                'farm_id' => $farm->id,
                'product_name' => 'Scheduled soon',
                'deworming_date' => now()->addDays(7)->toDateString(),
                'status' => 'Scheduled',
            ]);
            Deworming::create([
                'farm_id' => $farm->id,
                'product_name' => 'Scheduled later',
                'deworming_date' => now()->addDays(8)->toDateString(),
                'status' => 'Scheduled',
            ]);
            Deworming::create([
                'farm_id' => $farm->id,
                'product_name' => 'Already completed',
                'deworming_date' => now()->addDays(2)->toDateString(),
                'status' => 'Completed',
            ]);

            $this->get('/dashboard')
                ->assertOk()
                ->assertSee('Deworming scheduled soon')
                ->assertSee('1 scheduled deworming(s) within 7 days.');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_tenant_user_can_log_in(): void
    {
        $user = $this->createTenantUser([
            'email' => 'farmer@example.com',
            'password' => 'secret-password',
        ]);

        $response = $this->post('/login', [
            'email' => 'farmer@example.com',
            'password' => 'secret-password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertEquals($this->tenant->id, session('tenant_id'));
    }
}
