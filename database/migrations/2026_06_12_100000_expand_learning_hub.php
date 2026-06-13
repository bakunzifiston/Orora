<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_categories', function (Blueprint $table) {
            $table->string('icon')->nullable()->after('slug');
            $table->text('description')->nullable()->after('icon');
            $table->boolean('is_active')->default(true)->after('sort_order');
        });

        Schema::table('learning_posts', function (Blueprint $table) {
            $table->string('content_type', 20)->nullable()->after('excerpt');
            $table->string('video_url')->nullable()->after('content');
            $table->string('video_duration')->nullable()->after('video_url');
            $table->string('pdf_path')->nullable()->after('video_duration');
            $table->unsignedInteger('pdf_pages')->nullable()->after('pdf_path');
            $table->string('author_name')->nullable()->after('thumbnail_path');
            $table->string('author_title')->nullable()->after('author_name');
            $table->string('author_photo')->nullable()->after('author_title');
            $table->unsignedSmallInteger('read_time')->nullable()->after('author_photo');
            $table->string('difficulty_level', 20)->default('beginner')->after('read_time');
            $table->string('language', 5)->default('en')->after('difficulty_level');
            $table->boolean('is_featured')->default(false)->after('language');
            $table->unsignedInteger('views_count')->default(0)->after('is_featured');
            $table->json('tags')->nullable()->after('views_count');
        });

        $rows = DB::table('learning_posts')->orderBy('id')->get();

        foreach ($rows as $row) {
            $updates = [
                'content_type' => $row->type ?? 'article',
                'author_name' => 'Orora Farm Team',
            ];

            if ($row->external_url) {
                $updates['video_url'] = $row->external_url;
            }

            if ($row->file_path) {
                $updates['pdf_path'] = $row->file_path;
            }

            if ($row->type === 'video') {
                $updates['read_time'] = 12;
            } elseif ($row->type === 'article') {
                $updates['read_time'] = 5;
            }

            DB::table('learning_posts')->where('id', $row->id)->update($updates);
        }

        Schema::table('learning_posts', function (Blueprint $table) {
            if (Schema::hasColumn('learning_posts', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('learning_posts', 'external_url')) {
                $table->dropColumn('external_url');
            }
            if (Schema::hasColumn('learning_posts', 'file_path')) {
                $table->dropColumn('file_path');
            }
        });

        Schema::table('learning_posts', function (Blueprint $table) {
            $table->index(['content_type', 'is_published']);
            $table->index('difficulty_level');
            $table->index('language');
        });
    }

    public function down(): void
    {
        Schema::table('learning_posts', function (Blueprint $table) {
            $table->dropColumn([
                'content_type', 'video_url', 'video_duration', 'pdf_path', 'pdf_pages',
                'author_name', 'author_title', 'author_photo', 'read_time',
                'difficulty_level', 'language', 'is_featured', 'views_count', 'tags',
            ]);
        });

        Schema::table('learning_categories', function (Blueprint $table) {
            $table->dropColumn(['icon', 'description', 'is_active']);
        });
    }
};
