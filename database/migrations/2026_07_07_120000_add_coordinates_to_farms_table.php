<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('farms')) {
            return;
        }

        Schema::table('farms', function (Blueprint $table) {
            if (! Schema::hasColumn('farms', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('village_code');
            }
            if (! Schema::hasColumn('farms', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('farms')) {
            return;
        }

        Schema::table('farms', function (Blueprint $table) {
            if (Schema::hasColumn('farms', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::hasColumn('farms', 'latitude')) {
                $table->dropColumn('latitude');
            }
        });
    }
};
