<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('breeding_records', function (Blueprint $table) {
            $table->date('pregnancy_check_due_on')->nullable()->after('breeding_date');
            $table->index(['breeding_status', 'pregnancy_check_due_on']);
        });

        $days = (int) config('modules.breeding_pregnancy_check_due_days', 35);

        DB::table('breeding_records')->orderBy('id')->chunkById(100, function ($records) use ($days) {
            foreach ($records as $record) {
                DB::table('breeding_records')
                    ->where('id', $record->id)
                    ->update([
                        'pregnancy_check_due_on' => \Carbon\Carbon::parse($record->breeding_date)->addDays($days)->toDateString(),
                    ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('breeding_records', function (Blueprint $table) {
            $table->dropIndex(['breeding_status', 'pregnancy_check_due_on']);
            $table->dropColumn('pregnancy_check_due_on');
        });
    }
};
