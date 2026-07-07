<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupLearningContentCommand extends Command
{
    protected $signature = 'orora:learning-cleanup';

    protected $description = 'Remove placeholder learning videos and deleted posts from the Learning Hub';

    public function handle(): int
    {
        if (! Schema::hasTable('learning_posts')) {
            $this->components->error('learning_posts table is missing. Run: php artisan migrate --force');

            return self::FAILURE;
        }

        $removedSlugs = ['prevent-mastitis-dairy-cows'];

        $deleted = DB::table('learning_posts')->whereIn('slug', $removedSlugs)->delete();

        if ($deleted > 0) {
            $this->components->info("Removed {$deleted} learning post(s).");
        }

        $videos = DB::table('learning_posts')
            ->where('content_type', 'video')
            ->get(['id', 'content', 'excerpt', 'video_url']);

        $converted = 0;

        foreach ($videos as $post) {
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

            $converted++;
        }

        if ($converted > 0) {
            $this->components->info("Converted {$converted} video post(s) to articles.");
        }

        if (DB::table('learning_posts')->where('is_featured', true)->doesntExist()) {
            DB::table('learning_posts')
                ->where('slug', '5-signs-cow-needs-vet')
                ->update(['is_featured' => true]);

            $this->components->info('Set featured post to “5 signs your cow needs a vet visit”.');
        }

        $remainingVideos = DB::table('learning_posts')->where('content_type', 'video')->count();

        if ($remainingVideos === 0) {
            $this->components->info('No learning videos remain in the database.');
        } else {
            $this->components->warn("{$remainingVideos} video post(s) still remain.");
        }

        $this->line('Clear caches: php artisan config:clear && php artisan cache:clear && php artisan view:clear');

        return self::SUCCESS;
    }
}
