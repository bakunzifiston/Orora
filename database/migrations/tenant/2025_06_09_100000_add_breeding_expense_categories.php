<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $rows = [
            ['breeding', 'Breeding / insemination', 'breeding.insemination', 'AI, semen, and breeding service costs'],
            ['breeding', 'Pregnancy check', 'breeding.pregnancy_check', 'Ultrasound, palpation, and pregnancy diagnosis fees'],
            ['breeding', 'Birth / calving', 'breeding.birth', 'Veterinary assistance, supplies, and costs at birth'],
        ];

        foreach ($rows as [$group, $name, $code, $description]) {
            if (DB::table('expense_categories')->where('code', $code)->exists()) {
                continue;
            }

            DB::table('expense_categories')->insert([
                'expense_group' => $group,
                'name' => $name,
                'code' => $code,
                'description' => $description,
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('expense_categories')->whereIn('code', [
            'breeding.insemination',
            'breeding.pregnancy_check',
            'breeding.birth',
        ])->delete();
    }
};
