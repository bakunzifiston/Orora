<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Feed suppliers are no longer managed separately; who you buy from is the
     * expense vendor. Copy any supplier that has no matching vendor (same workspace,
     * same name ignoring case) so no contact is lost. The feed_suppliers table is kept.
     */
    public function up(): void
    {
        if (! Schema::hasTable('feed_suppliers') || ! Schema::hasTable('expense_vendors')) {
            return;
        }

        $now = now();

        foreach (DB::table('feed_suppliers')->orderBy('id')->get() as $supplier) {
            $exists = DB::table('expense_vendors')
                ->where('tenant_id', $supplier->tenant_id)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($supplier->name))])
                ->exists();

            if ($exists) {
                continue;
            }

            $notes = collect([$supplier->address ? 'Address: '.$supplier->address : null, $supplier->notes])
                ->filter()
                ->implode("\n");

            DB::table('expense_vendors')->insert([
                'tenant_id' => $supplier->tenant_id,
                'name' => trim($supplier->name),
                'contact_person' => $supplier->contact_person,
                'phone' => $supplier->phone,
                'email' => $supplier->email,
                'notes' => $notes !== '' ? $notes : null,
                'is_active' => $supplier->is_active,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Copied vendors may since have expenses attached; leave them in place.
    }
};
