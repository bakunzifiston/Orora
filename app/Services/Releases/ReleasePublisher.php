<?php

namespace App\Services\Releases;

use App\Models\Central\AdminUser;
use App\Models\Central\PlatformRelease;
use App\Models\User;
use App\Notifications\ReleasePublished;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReleasePublisher
{
    /**
     * Publish a draft and notify its audience. Safe to call more than once.
     */
    public function publish(PlatformRelease $release, AdminUser $admin): int
    {
        if ($release->audience === 'selected' && $release->tenants()->doesntExist()) {
            throw new InvalidArgumentException(__('Choose at least one tenant before publishing to selected tenants.'));
        }

        return DB::connection('central')->transaction(function () use ($release, $admin) {
            if (! $release->isPublished()) {
                $release->update([
                    'status' => 'published',
                    'published_at' => now(),
                    'published_by' => $admin->getKey(),
                ]);
            }

            return $this->notifyAudience($release->fresh());
        });
    }

    /**
     * Create one in-app notification per intended tenant user who has not had one
     * for this release yet. Returns the number of new notifications.
     *
     * Called on publish, and again when a published release's audience is widened,
     * so newly targeted tenants are reached without re-notifying anyone.
     */
    public function notifyAudience(PlatformRelease $release): int
    {
        if (! $release->isPublished()) {
            return 0;
        }

        $payload = json_encode(ReleasePublished::payload($release));
        $morphType = (new User)->getMorphClass();
        $created = 0;

        $this->recipients($release)
            ->select('users.id')
            ->chunkById(500, function ($users) use ($release, $payload, $morphType, &$created) {
                $now = now();

                $rows = $users->map(fn ($user) => [
                    'id' => $this->notificationId($release->id, $user->id),
                    'type' => ReleasePublished::class,
                    'notifiable_type' => $morphType,
                    'notifiable_id' => $user->id,
                    'data' => $payload,
                    'platform_release_id' => $release->id,
                    'read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                // The unique (release, recipient) index makes repeats a no-op.
                $created += DB::connection('central')->table('notifications')->insertOrIgnore($rows);
            }, 'users.id', 'id');

        return $created;
    }

    /**
     * Active tenant users in the release's audience.
     */
    public function recipients(PlatformRelease $release): Builder
    {
        return User::query()
            ->withoutGlobalScopes()
            ->whereNotNull('users.tenant_id')
            ->where(fn (Builder $q) => $q->whereNull('users.is_active')->orWhere('users.is_active', true))
            ->when($release->audience === 'selected', function (Builder $q) use ($release) {
                $q->whereIn('users.tenant_id', $release->tenants()->pluck('tenants.id'));
            });
    }

    /** Remove a release's notifications, e.g. when the release is deleted. */
    public function retract(PlatformRelease $release): int
    {
        return DB::connection('central')->table('notifications')
            ->where('platform_release_id', $release->id)
            ->delete();
    }

    /** Deterministic UUID per (release, user), a second guard against duplicates. */
    private function notificationId(int $releaseId, int $userId): string
    {
        $hash = md5("orora-release:{$releaseId}:user:{$userId}");

        return sprintf(
            '%s-%s-5%s-%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 13, 3),
            dechex((hexdec(substr($hash, 16, 2)) & 0x3F) | 0x80).substr($hash, 18, 2),
            substr($hash, 20, 12),
        );
    }
}
