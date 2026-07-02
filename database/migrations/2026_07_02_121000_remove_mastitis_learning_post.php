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

        DB::table('learning_posts')->where('slug', 'prevent-mastitis-dairy-cows')->delete();

        if (DB::table('learning_posts')->where('is_featured', true)->doesntExist()) {
            DB::table('learning_posts')
                ->where('slug', '5-signs-cow-needs-vet')
                ->update(['is_featured' => true]);
        }
    }

    public function down(): void
    {
        // Removed content is not restored.
    }
};
