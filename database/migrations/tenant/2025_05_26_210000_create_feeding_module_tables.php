<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('feed_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('unit')->default('kg');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('feed_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feed_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_on_hand', 12, 2)->default(0);
            $table->string('unit')->default('kg');
            $table->decimal('reorder_level', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['farm_id', 'feed_type_id']);
        });

        Schema::create('feed_inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_inventory_id')->constrained()->cascadeOnDelete();
            $table->string('movement_type');
            $table->decimal('quantity', 12, 2);
            $table->string('unit');
            $table->decimal('balance_after', 12, 2);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('moved_at');
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('feeding_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feed_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feed_inventory_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('livestock_id')->nullable()->constrained('livestock')->nullOnDelete();
            $table->foreignId('animal_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->default('kg');
            $table->string('frequency')->default('daily');
            $table->json('days_of_week')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_due_date')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('feedings', function (Blueprint $table) {
            $table->foreignId('feed_type_id')->nullable()->after('animal_id')->constrained()->nullOnDelete();
            $table->foreignId('feed_inventory_id')->nullable()->after('feed_type_id')->constrained()->nullOnDelete();
            $table->foreignId('feeding_schedule_id')->nullable()->after('feed_inventory_id')->constrained()->nullOnDelete();
        });

        $this->migrateLegacyFeedings();

        Schema::table('feedings', function (Blueprint $table) {
            $table->dropColumn('feed_type');
        });
    }

    public function down(): void
    {
        Schema::table('feedings', function (Blueprint $table) {
            $table->string('feed_type')->nullable();
        });

        Schema::table('feedings', function (Blueprint $table) {
            $table->dropForeign(['feed_type_id']);
            $table->dropForeign(['feed_inventory_id']);
            $table->dropForeign(['feeding_schedule_id']);
            $table->dropColumn(['feed_type_id', 'feed_inventory_id', 'feeding_schedule_id']);
        });

        Schema::dropIfExists('feeding_schedules');
        Schema::dropIfExists('feed_inventory_movements');
        Schema::dropIfExists('feed_inventories');
        Schema::dropIfExists('feed_types');
        Schema::dropIfExists('feed_suppliers');
    }

    private function migrateLegacyFeedings(): void
    {
        if (! Schema::hasColumn('feedings', 'feed_type')) {
            return;
        }

        $rows = DB::table('feedings')->get();

        foreach ($rows as $row) {
            $typeId = DB::table('feed_types')->where('name', $row->feed_type)->value('id');

            if (! $typeId) {
                $typeId = DB::table('feed_types')->insertGetId([
                    'name' => $row->feed_type,
                    'unit' => $row->unit ?? 'kg',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $inventoryId = DB::table('feed_inventories')->where('farm_id', $row->farm_id)
                ->where('feed_type_id', $typeId)
                ->value('id');

            if (! $inventoryId) {
                $inventoryId = DB::table('feed_inventories')->insertGetId([
                    'farm_id' => $row->farm_id,
                    'feed_type_id' => $typeId,
                    'quantity_on_hand' => 0,
                    'unit' => $row->unit ?? 'kg',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('feedings')->where('id', $row->id)->update([
                'feed_type_id' => $typeId,
                'feed_inventory_id' => $inventoryId,
            ]);
        }
    }
};
