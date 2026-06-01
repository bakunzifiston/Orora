<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('expense_categories')->where('code', 'breeding.birth')->exists()) {
            return;
        }

        $now = now();

        DB::table('expense_categories')->insert([
            'expense_group' => 'breeding',
            'name' => 'Birth / calving',
            'code' => 'breeding.birth',
            'description' => 'Veterinary assistance, supplies, and costs at birth',
            'is_system' => true,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('expense_categories')->where('code', 'breeding.birth')->delete();
    }
};
