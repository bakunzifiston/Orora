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
            $table->json('herd_groups')->nullable()->after('farm_id');
            $table->string('herd_group_other')->nullable()->after('herd_groups');
        });

        $options = config('modules.herd_groups', []);

        foreach (DB::table('livestock')->get() as $row) {
            $name = trim((string) ($row->name ?? ''));
            $groups = [];
            $other = null;

            if ($name !== '') {
                if (in_array($name, $options, true)) {
                    $groups = [$name];
                } else {
                    $groups = ['Other'];
                    $other = $name;
                }
            }

            DB::table('livestock')->where('id', $row->id)->update([
                'herd_groups' => $groups !== [] ? json_encode($groups) : null,
                'herd_group_other' => $other,
            ]);
        }

        Schema::table('livestock', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
        });
    }

    public function down(): void
    {
        foreach (DB::table('livestock')->get() as $row) {
            $groups = json_decode($row->herd_groups ?? '[]', true) ?: [];
            $label = $groups[0] ?? 'Livestock group';
            if (($label === 'Other') && $row->herd_group_other) {
                $label = $row->herd_group_other;
            }

            DB::table('livestock')->where('id', $row->id)->update(['name' => $label]);
        }

        Schema::table('livestock', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->dropColumn(['herd_groups', 'herd_group_other']);
        });
    }
};
