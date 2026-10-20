<?php

namespace Tests\Feature;

use App\Models\Central\PlatformRelease;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TenantTestCase;

class PlatformReleaseNotificationsTest extends TenantTestCase
{
    private Tenant $otherTenant;

    protected function tearDown(): void
    {
        if (isset($this->otherTenant)) {
            $this->otherTenant->delete();
        }

        parent::tearDown();
    }

    public function test_drafts_send_no_notifications_and_stay_hidden(): void
    {
        $user = $this->createTenantUser();
        $this->actingAsAdmin();

        $this->post(route('central.releases.store'), $this->payload(['title' => 'Draft only '.Str::random(6)]))
            ->assertRedirect(route('central.releases.index'));

        $release = PlatformRelease::query()->latest('id')->firstOrFail();
        $this->assertSame('draft', $release->status);

        $this->get(route('central.dashboard'))->assertOk()
            ->assertSee('href="'.route('central.releases.index').'"', false)
            ->assertSee('Releases');
        $this->get(route('central.releases.index'))->assertOk()->assertSee($release->title)->assertSee('Publish');
        $this->get(route('central.releases.create'))->assertOk()->assertSee('Target audience');
        $this->get(route('central.releases.edit', $release))->assertOk()->assertSee('Save &amp; publish', false);
        $this->assertSame(0, $this->notificationsFor($user));

        $this->signOutAdmin();
        $this->actingAsTenantUser($user);
        $this->get(route('whats-new.index'))->assertOk()->assertDontSee($release->title);
    }

    public function test_publishing_notifies_every_tenant_user_once(): void
    {
        $user = $this->createTenantUser();
        $teammate = $this->createTenantUser();
        $otherUser = $this->userInOtherTenant();

        $this->actingAsAdmin();
        $release = $this->draft(['audience' => 'all']);

        $this->post(route('central.releases.publish', $release))->assertRedirect(route('central.releases.index'));
        $this->post(route('central.releases.publish', $release))->assertRedirect(route('central.releases.index'));

        $this->assertSame('published', $release->fresh()->status);
        $this->assertSame(1, $this->notificationsFor($user, $release));
        $this->assertSame(1, $this->notificationsFor($teammate, $release));
        $this->assertSame(1, $this->notificationsFor($otherUser, $release));
    }

    public function test_selected_audience_respects_tenant_isolation(): void
    {
        $user = $this->createTenantUser();
        $otherUser = $this->userInOtherTenant();

        $this->actingAsAdmin();
        $release = $this->draft(['audience' => 'selected', 'tenant_ids' => [$this->otherTenant->id]]);
        $this->post(route('central.releases.publish', $release));

        $this->assertSame(0, $this->notificationsFor($user, $release));
        $this->assertSame(1, $this->notificationsFor($otherUser, $release));

        $this->signOutAdmin();
        $this->actingAsTenantUser($user);
        $this->get(route('whats-new.index'))->assertOk()->assertDontSee($release->title);
    }

    public function test_widening_audience_only_notifies_new_tenants(): void
    {
        $user = $this->createTenantUser();
        $otherUser = $this->userInOtherTenant();

        $this->actingAsAdmin();
        $release = $this->draft(['audience' => 'selected', 'tenant_ids' => [$this->otherTenant->id]]);
        $this->post(route('central.releases.publish', $release));

        $this->put(route('central.releases.update', $release), $this->payload([
            'title' => $release->title,
            'audience' => 'selected',
            'tenant_ids' => [$this->otherTenant->id, $this->tenant->id],
        ]))->assertRedirect(route('central.releases.index'));

        $this->assertSame(1, $this->notificationsFor($user, $release));
        $this->assertSame(1, $this->notificationsFor($otherUser, $release));
    }

    public function test_only_super_admins_can_publish_and_tenants_cannot_manage_releases(): void
    {
        $user = $this->createTenantUser();

        $this->actingAsAdmin(['is_super_admin' => false]);
        $release = $this->draft();
        $this->post(route('central.releases.publish', $release))->assertForbidden();
        $this->assertSame('draft', $release->fresh()->status);

        $this->signOutAdmin();
        $this->actingAsTenantUser($user);
        $this->get(route('central.releases.index'))->assertRedirect();
        $this->post(route('central.releases.publish', $release))->assertRedirect();
        $this->assertSame('draft', $release->fresh()->status);
    }

    public function test_external_links_are_rejected(): void
    {
        $this->actingAsAdmin();

        $this->post(route('central.releases.store'), $this->payload(['link_path' => 'https://evil.example']))
            ->assertSessionHasErrors('link_path');
        $this->post(route('central.releases.store'), $this->payload(['link_path' => '//evil.example']))
            ->assertSessionHasErrors('link_path');
    }

    public function test_bell_open_and_mark_all_read(): void
    {
        $user = $this->createTenantUser();
        $otherUser = $this->userInOtherTenant();

        $this->actingAsAdmin();
        $first = $this->draft(['title' => 'Bell one '.Str::random(5)]);
        $second = $this->draft(['title' => 'Bell two '.Str::random(5), 'module' => 'milk']);
        $this->post(route('central.releases.publish', $first));
        $this->post(route('central.releases.publish', $second));
        $this->signOutAdmin();

        $this->actingAsTenantUser($user);
        $unreadBefore = $user->unreadNotifications()->count();
        $this->assertGreaterThanOrEqual(2, $unreadBefore);

        $this->get(route('dashboard'))->assertOk()->assertSee($second->title)->assertSee('data-bell-count', false);

        $notification = $user->notifications()->where('platform_release_id', $first->id)->firstOrFail();
        $this->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('whats-new.index', [], false).'#release-'.$first->id);
        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertSame($unreadBefore - 1, $user->unreadNotifications()->count());

        $foreign = $otherUser->notifications()->firstOrFail();
        $this->get(route('notifications.open', $foreign->id))->assertNotFound();
        $this->assertNull($foreign->fresh()->read_at);

        $this->postJson(route('notifications.read-all'))->assertOk()->assertJson(['unread' => 0]);
        $this->assertSame(0, $user->unreadNotifications()->count());
        $this->assertGreaterThan(0, $otherUser->unreadNotifications()->count());

        $this->get(route('whats-new.index'))->assertOk()->assertSee($second->title)->assertSee('Explore feature');
        $this->get(route('notifications.index'))->assertOk()->assertSee($first->title);
    }

    public function test_deleting_a_release_removes_its_notifications(): void
    {
        $user = $this->createTenantUser();

        $this->actingAsAdmin();
        $release = $this->draft();
        $this->post(route('central.releases.publish', $release));
        $this->assertSame(1, $this->notificationsFor($user, $release));

        $this->delete(route('central.releases.destroy', $release))->assertRedirect(route('central.releases.index'));

        $this->assertDatabaseMissing('platform_releases', ['id' => $release->id], 'central');
        $this->assertSame(0, $this->notificationsFor($user, $release));
    }

    /** End the admin session and make "web" the default guard again for tenant requests. */
    private function signOutAdmin(): void
    {
        auth('admin')->logout();
        $this->app['auth']->shouldUse('web');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'version' => 'v'.random_int(1, 9).'.'.random_int(0, 99),
            'title' => 'Release '.Str::random(8),
            'description' => "Line one.\nLine two.",
            'category' => 'new_feature',
            'released_on' => now()->toDateString(),
            'module' => '',
            'link_path' => '',
            'audience' => 'all',
            'action' => 'draft',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function draft(array $overrides = []): PlatformRelease
    {
        $payload = $this->payload($overrides);
        $this->post(route('central.releases.store'), $payload)->assertSessionHasNoErrors();

        return PlatformRelease::query()->where('title', $payload['title'])->latest('id')->firstOrFail();
    }

    private function userInOtherTenant(): User
    {
        $this->otherTenant ??= Tenant::query()->create([
            'id' => 'test_'.Str::lower(Str::random(10)),
            'name' => 'Other tenant',
        ]);

        TenantContext::set($this->otherTenant);
        $user = User::factory()->create();
        TenantContext::set($this->tenant);

        $this->assertSame($this->otherTenant->id, $user->tenant_id);

        return $user;
    }

    private function notificationsFor(User $user, ?PlatformRelease $release = null): int
    {
        return DB::connection('central')->table('notifications')
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->id)
            ->when($release, fn ($q) => $q->where('platform_release_id', $release->id))
            ->count();
    }
}
