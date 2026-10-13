<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vet_visits', function (Blueprint $table) {
            if (! Schema::hasColumn('vet_visits', 'livestock_id')) {
                $table->foreignId('livestock_id')
                    ->nullable()
                    ->after('farm_id')
                    ->constrained('livestock')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('vet_visits', function (Blueprint $table) {
            if (Schema::hasColumn('vet_visits', 'livestock_id')) {
                $table->dropConstrainedForeignId('livestock_id');
            }
        });
    }
};
