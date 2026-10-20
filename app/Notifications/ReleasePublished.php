<?php

namespace App\Notifications;

use App\Models\Central\PlatformRelease;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * In-app notice that a new OroraFarm release was published.
 *
 * Rows are written in bulk by ReleasePublisher (see toArray for the payload shape),
 * so they appear in $user->notifications like any database notification.
 */
class ReleasePublished extends Notification
{
    public function __construct(public readonly PlatformRelease $release) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return static::payload($this->release);
    }

    /**
     * @return array<string, mixed>
     */
    public static function payload(PlatformRelease $release): array
    {
        return [
            'kind' => 'release',
            'release_id' => $release->id,
            'version' => $release->version,
            'title' => $release->title,
            'message' => Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($release->description))), 140),
            'category' => $release->category,
            'category_label' => $release->categoryLabel(),
            'url' => route('whats-new.index', [], false).'#release-'.$release->id,
        ];
    }
}
