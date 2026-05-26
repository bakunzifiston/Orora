<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropUnique(['tag_number']);
        });

        Schema::table('animals', function (Blueprint $table) {
            $table->string('gender')->default('male')->after('name');
            $table->string('photo_path')->nullable()->after('gender');
            $table->string('color_markings')->nullable()->after('weight_kg');
            $table->string('acquisition_type')->nullable()->after('color_markings');
            $table->date('acquisition_date')->nullable()->after('acquisition_type');
            $table->string('source')->nullable()->after('acquisition_date');
            $table->string('mother_tag')->nullable()->after('source');
            $table->string('father_tag')->nullable()->after('mother_tag');
            $table->string('health_status')->default('Healthy')->after('father_tag');
            $table->string('production_status')->nullable()->after('health_status');
            $table->string('lifecycle_status')->default('Active')->after('production_status');
            $table->string('current_condition')->nullable()->after('lifecycle_status');
        });

        foreach (DB::table('animals')->get() as $row) {
            DB::table('animals')->where('id', $row->id)->update([
                'gender' => match ($row->sex ?? 'unknown') {
                    'male' => 'male',
                    'female' => 'female',
                    default => 'unknown',
                },
                'lifecycle_status' => match ($row->status ?? 'active') {
                    'sold' => 'Sold',
                    'deceased' => 'Deceased',
                    'transferred' => 'Transferred',
                    default => 'Active',
                },
            ]);
        }

        foreach (DB::table('animals')->whereNull('livestock_id')->get() as $row) {
            $livestockId = DB::table('livestock')->where('farm_id', $row->farm_id)->orderBy('id')->value('id');

            if ($livestockId) {
                DB::table('animals')->where('id', $row->id)->update(['livestock_id' => $livestockId]);
            }
        }

        Schema::table('animals', function (Blueprint $table) {
            $table->string('species')->nullable()->change();
            $table->dropColumn(['sex', 'status']);
        });

        Schema::table('animals', function (Blueprint $table) {
            $table->unique(['livestock_id', 'tag_number']);
        });
    }

    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropUnique(['livestock_id', 'tag_number']);
            $table->string('sex')->default('unknown');
            $table->string('status')->default('active');
        });

        foreach (DB::table('animals')->get() as $row) {
            DB::table('animals')->where('id', $row->id)->update([
                'sex' => $row->gender ?? 'unknown',
                'status' => strtolower($row->lifecycle_status ?? 'active'),
            ]);
        }

        Schema::table('animals', function (Blueprint $table) {
            $table->dropColumn([
                'gender',
                'photo_path',
                'color_markings',
                'acquisition_type',
                'acquisition_date',
                'source',
                'mother_tag',
                'father_tag',
                'health_status',
                'production_status',
                'lifecycle_status',
                'current_condition',
            ]);
            $table->unique('tag_number');
        });
    }
};
