<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            if (! Schema::hasColumn('farms', 'primary_species')) {
                $table->string('primary_species', 32)->default('cattle')->after('status');
                $table->index('primary_species');
            }
        });

        Schema::create('flocks', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('livestock_id')->nullable()->constrained('livestock')->nullOnDelete();
            $table->string('flock_code');
            $table->string('name');
            $table->string('production_type');
            $table->string('breed')->nullable();
            $table->string('source')->nullable();
            $table->date('placed_on');
            $table->unsignedInteger('placed_count');
            $table->unsignedInteger('current_count');
            $table->unsignedInteger('male_count')->nullable();
            $table->unsignedInteger('female_count')->nullable();
            $table->string('house_or_pen')->nullable();
            $table->date('expected_end_on')->nullable();
            $table->string('lifecycle_status')->default('Active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'flock_code']);
            $table->index(['farm_id', 'lifecycle_status']);
        });

        Schema::create('flock_events', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('flock_id')->constrained('flocks')->cascadeOnDelete();
            $table->string('event_type');
            $table->integer('quantity');
            $table->unsignedInteger('balance_after');
            $table->date('occurred_on');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['flock_id', 'occurred_on']);
        });

        Schema::create('egg_collections', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flock_id')->constrained('flocks')->cascadeOnDelete();
            $table->string('collection_code');
            $table->date('collected_on');
            $table->string('shift')->nullable();
            $table->unsignedInteger('eggs_count');
            $table->unsignedInteger('cracked_count')->default(0);
            $table->decimal('weight_kg', 10, 2)->nullable();
            $table->string('collected_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'collection_code']);
            $table->unique(['flock_id', 'collected_on', 'shift'], 'egg_collections_flock_day_shift_unique');
        });

        $this->addFlockSupportTo('health_records', makeAnimalNullable: true);
        $this->addFlockSupportTo('vaccinations', makeAnimalNullable: true);
        $this->addFlockSupportTo('treatments', makeAnimalNullable: true);
        $this->addFlockSupportTo('vet_visits', makeAnimalNullable: true);
        $this->addFlockSupportTo('mortalities', makeAnimalNullable: true);
        $this->addFlockSupportTo('disease_records', makeAnimalNullable: true, makeLivestockNullable: true);
        $this->addFlockSupportTo('feedings');
        $this->addFlockSupportTo('feeding_schedules');
        $this->addFlockSupportTo('expenses');
        $this->addFlockSupportTo('certificates');
        $this->addFlockSupportTo('sale_items');
        $this->addFlockSupportTo('movements', makeAnimalNullable: true);

        if (Schema::hasTable('mortalities') && ! Schema::hasColumn('mortalities', 'deaths_count')) {
            Schema::table('mortalities', function (Blueprint $table) {
                $table->unsignedInteger('deaths_count')->default(1)->after('animal_id');
            });
        }

        if (Schema::hasTable('movements') && ! Schema::hasColumn('movements', 'quantity')) {
            Schema::table('movements', function (Blueprint $table) {
                $table->unsignedInteger('quantity')->default(1)->after('flock_id');
            });
        }

        if (Schema::hasTable('finance_accounts')) {
            $exists = DB::table('finance_accounts')->where('account_code', '4250')->exists();

            if (! $exists) {
                $now = now();
                $row = [
                    'account_code' => '4250',
                    'account_name' => 'Egg Sales Revenue',
                    'account_type' => 'income',
                    'account_subtype' => 'egg_revenue',
                    'normal_balance' => 'credit',
                    'source_module' => 'sales',
                    'is_system' => true,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (Schema::hasColumn('finance_accounts', 'tenant_id')) {
                    $tenantIds = DB::table('tenants')->pluck('id');

                    if ($tenantIds->isEmpty()) {
                        DB::table('finance_accounts')->insert($row);
                    } else {
                        foreach ($tenantIds as $tenantId) {
                            DB::table('finance_accounts')->insert($row + ['tenant_id' => $tenantId]);
                        }
                    }
                } else {
                    DB::table('finance_accounts')->insert($row);
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('finance_accounts')->where('account_code', '4250')->delete();

        $tables = [
            'health_records',
            'vaccinations',
            'treatments',
            'vet_visits',
            'mortalities',
            'disease_records',
            'feedings',
            'feeding_schedules',
            'expenses',
            'certificates',
            'sale_items',
            'movements',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'flock_id')) {
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    $blueprint->dropForeign(['flock_id']);
                    $blueprint->dropColumn('flock_id');
                });
            }
        }

        Schema::dropIfExists('egg_collections');
        Schema::dropIfExists('flock_events');
        Schema::dropIfExists('flocks');

        if (Schema::hasColumn('farms', 'primary_species')) {
            Schema::table('farms', function (Blueprint $table) {
                $table->dropIndex(['primary_species']);
                $table->dropColumn('primary_species');
            });
        }
    }

    private function addFlockSupportTo(string $table, bool $makeAnimalNullable = false, bool $makeLivestockNullable = false): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if ($makeAnimalNullable && Schema::hasColumn($table, 'animal_id')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['animal_id']);
            });

            DB::statement("ALTER TABLE `{$table}` MODIFY `animal_id` BIGINT UNSIGNED NULL");

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreign('animal_id')->references('id')->on('animals')->nullOnDelete();
            });
        }

        if ($makeLivestockNullable && Schema::hasColumn($table, 'livestock_id')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['livestock_id']);
            });

            DB::statement("ALTER TABLE `{$table}` MODIFY `livestock_id` BIGINT UNSIGNED NULL");

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreign('livestock_id')->references('id')->on('livestock')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn($table, 'flock_id')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('flock_id')->nullable()->after('animal_id')->constrained('flocks')->nullOnDelete();
            });
        }
    }
};
