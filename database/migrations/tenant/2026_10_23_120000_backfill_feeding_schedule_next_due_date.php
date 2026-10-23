<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Schedules created without a next due date start from their start date,
     * the same default new schedules get.
     */
    public function up(): void
    {
        DB::table('feeding_schedules')
            ->whereNull('next_due_date')
            ->update(['next_due_date' => DB::raw('start_date')]);
    }

    public function down(): void
    {
        // Data backfill only; nothing to undo safely.
    }
};
