<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('learning_posts')) {
            return;
        }

        $posts = DB::table('learning_posts')
            ->where('content_type', 'video')
            ->get(['id', 'content', 'excerpt', 'video_url']);

        foreach ($posts as $post) {
            if (! $this->isPlaceholderVideo((string) $post->video_url)) {
                continue;
            }

            $content = trim((string) $post->content);

            if ($content === '' && filled($post->excerpt)) {
                $content = '<p>'.e((string) $post->excerpt).'</p>';
            } elseif ($content !== '' && ! str_contains($content, '<')) {
                $content = '<p>'.e($content).'</p>';
            }

            DB::table('learning_posts')->where('id', $post->id)->update([
                'content_type' => 'article',
                'content' => $content ?: null,
                'video_url' => null,
                'video_duration' => null,
            ]);
        }
    }

    public function down(): void
    {
        // Placeholder videos were replaced with articles; no safe rollback.
    }

    private function isPlaceholderVideo(string $url): bool
    {
        if ($url === '') {
            return true;
        }

        return str_contains($url, 'dQw4w9WgXcQ')
            || str_contains($url, 'youtube.com/watch')
            || str_contains($url, 'youtu.be/');
    }
};
