<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livestock', function (Blueprint $table) {
            $table->json('livestock_types')->nullable()->after('farm_id');
            $table->string('livestock_type_other')->nullable()->after('livestock_types');
            $table->json('production_purposes')->nullable()->after('livestock_type_other');
            $table->string('production_purpose_other')->nullable()->after('production_purposes');
            $table->json('farming_methods')->nullable()->after('production_purpose_other');
            $table->string('farming_method_other')->nullable()->after('farming_methods');
            $table->json('feeding_methods')->nullable()->after('farming_method_other');
            $table->string('feeding_method_other')->nullable()->after('feeding_methods');
        });

        foreach (DB::table('livestock')->whereNotNull('species')->get() as $row) {
            $label = ucfirst((string) $row->species);
            $map = [
                'Cattle' => 'Cattle',
                'Goat' => 'Goat',
                'Sheep' => 'Sheep',
                'Pig' => 'Pig',
                'Poultry' => 'Poultry',
                'Rabbit' => 'Rabbit',
                'Other' => 'Other',
            ];

            DB::table('livestock')->where('id', $row->id)->update([
                'livestock_types' => json_encode([$map[$label] ?? 'Other']),
                'livestock_type_other' => isset($map[$label]) ? null : $label,
            ]);
        }

        Schema::table('livestock', function (Blueprint $table) {
            $table->dropColumn('species');
        });
    }

    public function down(): void
    {
        Schema::table('livestock', function (Blueprint $table) {
            $table->string('species')->nullable()->after('farm_id');
        });

        foreach (DB::table('livestock')->get() as $row) {
            $types = json_decode($row->livestock_types ?? '[]', true) ?: [];
            $species = strtolower($types[0] ?? 'other');

            DB::table('livestock')->where('id', $row->id)->update(['species' => $species]);
        }

        Schema::table('livestock', function (Blueprint $table) {
            $table->dropColumn([
                'livestock_types',
                'livestock_type_other',
                'production_purposes',
                'production_purpose_other',
                'farming_methods',
                'farming_method_other',
                'feeding_methods',
                'feeding_method_other',
            ]);
        });
    }
};
