<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('animals') || ! Schema::hasColumn('animals', 'production_status')) {
            return;
        }

        DB::table('animals')
            ->where('production_status', 'Gestating')
            ->update(['production_status' => 'Pregnancy']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('animals') || ! Schema::hasColumn('animals', 'production_status')) {
            return;
        }

        DB::table('animals')
            ->where('production_status', 'Pregnancy')
            ->update(['production_status' => 'Gestating']);
    }
};
