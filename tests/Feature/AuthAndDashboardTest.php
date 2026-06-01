<?php

namespace Tests\Feature;

use Tests\TenantTestCase;

class AuthAndDashboardTest extends TenantTestCase
{
    public function test_guest_sees_login_page(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/');
    }

    public function test_tenant_user_can_view_dashboard(): void
    {
        $this->actingAsTenantUser();

        $this->get('/dashboard')->assertOk();
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
