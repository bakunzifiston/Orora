<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('milk_sessions')) {
            Schema::create('milk_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
                $table->foreignId('livestock_id')->constrained('livestock')->cascadeOnDelete();
                $table->string('session_code')->unique();
                $table->date('session_date');
                $table->string('session_shift');
                $table->decimal('total_yield_liters', 10, 2)->default(0);
                $table->unsignedInteger('number_of_animals_milked')->default(0);
                $table->decimal('average_yield_per_animal', 10, 2)->default(0);
                $table->string('milked_by');
                $table->string('milking_method');
                $table->string('status')->default('open');
                $table->text('notes')->nullable();
                $table->foreignId('destination_storage_id')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('completed_at')->nullable();
                $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['livestock_id', 'session_date', 'session_shift']);
                $table->index(['session_date', 'farm_id', 'status']);
            });
        }

        if (! Schema::hasTable('milk_storage')) {
            Schema::create('milk_storage', function (Blueprint $table) {
                $table->id();
                $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
                $table->string('storage_code')->unique();
                $table->string('container_name');
                $table->string('container_type')->default('bulk_tank');
                $table->decimal('capacity_liters', 10, 2);
                $table->decimal('current_quantity_liters', 10, 2)->default(0);
                $table->decimal('storage_temperature', 5, 2)->nullable();
                $table->string('storage_location')->nullable();
                $table->string('status')->default('available');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['farm_id', 'status']);
            });
        }

        if (Schema::hasTable('milk_sessions') && ! $this->hasForeignKey('milk_sessions', 'milk_sessions_destination_storage_id_foreign')) {
            Schema::table('milk_sessions', function (Blueprint $table) {
                $table->foreign('destination_storage_id')->references('id')->on('milk_storage')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('milk_storage_movements')) {
            Schema::create('milk_storage_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('milk_storage_id')->constrained('milk_storage')->cascadeOnDelete();
                $table->string('movement_type');
                $table->decimal('quantity_liters', 10, 2);
                $table->decimal('balance_after', 10, 2);
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('moved_at');
                $table->timestamps();

                $table->index(['reference_type', 'reference_id']);
            });
        }

        $this->migrateLegacyMilkRecords();

        if (! Schema::hasTable('milk_sales')) {
            Schema::create('milk_sales', function (Blueprint $table) {
                $table->id();
                $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
                $table->string('sale_code')->unique();
                $table->string('buyer_name');
                $table->string('buyer_contact')->nullable();
                $table->date('sold_on');
                $table->decimal('total_amount', 14, 2)->default(0);
                $table->string('currency')->default('RWF');
                $table->string('status')->default('draft');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['sold_on', 'farm_id']);
            });
        }

        if (! Schema::hasTable('milk_sale_items')) {
            Schema::create('milk_sale_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('milk_sale_id')->constrained()->cascadeOnDelete();
                $table->foreignId('milk_storage_id')->nullable()->constrained('milk_storage')->nullOnDelete();
                $table->decimal('quantity_liters', 10, 2);
                $table->decimal('unit_price', 12, 2)->nullable();
                $table->decimal('line_total', 14, 2)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('milk_sale_payments')) {
            Schema::create('milk_sale_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('milk_sale_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 14, 2);
                $table->string('payment_method')->nullable();
                $table->date('paid_on');
                $table->text('notes')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('milk_sale_logs')) {
            Schema::create('milk_sale_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('milk_sale_id')->constrained()->cascadeOnDelete();
                $table->string('event');
                $table->json('meta')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    private function hasForeignKey(string $table, string $name): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $result = $connection->selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ?',
            [$database, $table, $name, 'FOREIGN KEY']
        );

        return $result !== null;
    }

    public function down(): void
    {
        Schema::dropIfExists('milk_sale_logs');
        Schema::dropIfExists('milk_sale_payments');
        Schema::dropIfExists('milk_sale_items');
        Schema::dropIfExists('milk_sales');
        Schema::dropIfExists('milk_storage_movements');
        Schema::table('milk_sessions', function (Blueprint $table) {
            $table->dropForeign(['destination_storage_id']);
        });
        Schema::dropIfExists('milk_records');
        Schema::dropIfExists('milk_sessions');
        Schema::dropIfExists('milk_storage');

        Schema::create('milk_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('animal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('livestock_id')->nullable()->constrained('livestock')->nullOnDelete();
            $table->date('recorded_on');
            $table->string('session')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->default('L');
            $table->decimal('fat_percentage', 5, 2)->nullable();
            $table->string('quality_grade')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['recorded_on', 'farm_id']);
        });
    }

    private function migrateLegacyMilkRecords(): void
    {
        if (Schema::hasTable('milk_records') && Schema::hasColumn('milk_records', 'milk_session_id')) {
            return;
        }

        if (Schema::hasTable('milk_records_legacy')) {
            Schema::drop('milk_records_legacy');
        }

        $legacyRows = collect();

        if (Schema::hasTable('milk_records')) {
            $legacyRows = DB::table('milk_records')->orderBy('id')->get();
            Schema::drop('milk_records');
        }

        $this->createMilkRecordsTable();

        if ($legacyRows->isEmpty()) {
            return;
        }

        $sessionSeq = 0;
        $recordSeq = 0;
        $sessionMap = [];

        foreach ($legacyRows->groupBy(fn ($row) => implode('|', [
            $row->farm_id,
            $row->livestock_id ?? 'none',
            $row->recorded_on,
            $this->normalizeShift($row->session),
        ])) as $key => $rows) {
            $first = $rows->first();
            $sessionSeq++;
            $sessionCode = sprintf('MS-%s-%04d', str_replace('-', '', $first->recorded_on), $sessionSeq);

            $livestockId = $first->livestock_id;
            if (! $livestockId) {
                $livestockId = DB::table('livestock')
                    ->where('farm_id', $first->farm_id)
                    ->orderBy('id')
                    ->value('id');
            }

            if (! $livestockId) {
                continue;
            }

            $sessionId = DB::table('milk_sessions')->insertGetId([
                'farm_id' => $first->farm_id,
                'livestock_id' => $livestockId,
                'session_code' => $sessionCode,
                'session_date' => $first->recorded_on,
                'session_shift' => $this->normalizeShift($first->session),
                'total_yield_liters' => 0,
                'number_of_animals_milked' => 0,
                'average_yield_per_animal' => 0,
                'milked_by' => 'Legacy import',
                'milking_method' => 'manual',
                'status' => 'completed',
                'completed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sessionMap[$key] = $sessionId;
            $total = 0;
            $count = 0;

            foreach ($rows as $row) {
                $recordSeq++;
                $liters = strtolower((string) $row->unit) === 'ml'
                    ? (float) $row->quantity / 1000
                    : (float) $row->quantity;

                DB::table('milk_records')->insert([
                    'milk_session_id' => $sessionId,
                    'animal_id' => $row->animal_id,
                    'record_code' => sprintf('MR-%s-%04d', str_replace('-', '', $row->recorded_on), $recordSeq),
                    'yield_liters' => $liters,
                    'notes' => $row->notes,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]);

                $total += $liters;
                $count++;
            }

            DB::table('milk_sessions')->where('id', $sessionId)->update([
                'total_yield_liters' => $total,
                'number_of_animals_milked' => $count,
                'average_yield_per_animal' => $count > 0 ? round($total / $count, 2) : 0,
            ]);
        }
    }

    private function createMilkRecordsTable(): void
    {
        Schema::create('milk_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milk_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('animal_id')->constrained()->cascadeOnDelete();
            $table->string('record_code')->unique();
            $table->decimal('yield_liters', 10, 2);
            $table->unsignedInteger('milking_duration_minutes')->nullable();
            $table->string('lactation_stage')->nullable();
            $table->unsignedInteger('lactation_number')->nullable();
            $table->string('udder_condition')->nullable();
            $table->boolean('abnormal_milk')->default(false);
            $table->text('abnormal_notes')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['milk_session_id', 'animal_id']);
            $table->index('animal_id');
        });
    }

    private function normalizeShift(?string $session): string
    {
        $map = [
            'morning' => 'morning',
            'Morning' => 'morning',
            'afternoon' => 'afternoon',
            'Afternoon' => 'afternoon',
            'evening' => 'evening',
            'Evening' => 'evening',
        ];

        return $map[trim((string) $session)] ?? 'morning';
    }
};
