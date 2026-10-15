<?php

namespace Tests\Unit;

use App\Models\Deworming;
use App\Models\Medicine;
use App\Models\TenantModel;
use App\Services\MedicineInventoryService;
use App\Services\TenantContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BelongsToTenantTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('tenant_scope_without_tenant_id', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });

        Schema::create('tenant_scope_with_tenant_id', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->string('name');
        });

        Schema::create('medicines', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->string('name');
            $table->string('unit')->default('ml');
            $table->decimal('quantity_on_hand', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });

        Schema::create('dewormings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('medicine_id')->nullable()->constrained('medicines');
            $table->decimal('quantity_used', 12, 2)->nullable();
            $table->string('product_name');
            $table->date('deworming_date');
            $table->string('status')->default('Scheduled');
            $table->timestamps();
        });

        Schema::create('medicine_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines');
            $table->string('movement_type');
            $table->decimal('quantity', 12, 2);
            $table->string('unit')->nullable();
            $table->decimal('balance_after', 12, 2);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('moved_at');
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();
        });

        $tenantId = new \ReflectionProperty(TenantContext::class, 'tenantId');
        $tenantId->setValue(null, 'demo');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('tenant_scope_without_tenant_id');
        Schema::dropIfExists('tenant_scope_with_tenant_id');
        Schema::dropIfExists('dewormings');
        Schema::dropIfExists('medicine_movements');
        Schema::dropIfExists('medicines');

        $tenantId = new \ReflectionProperty(TenantContext::class, 'tenantId');
        $tenantId->setValue(null, null);

        parent::tearDown();
    }

    public function test_global_scope_is_skipped_for_tables_missing_tenant_id(): void
    {
        $model = new class extends TenantModel
        {
            protected $table = 'tenant_scope_without_tenant_id';

            public $timestamps = false;
        };

        $sql = $model->newQuery()->toSql();

        $this->assertStringNotContainsString('where', strtolower($sql));
    }

    public function test_global_scope_is_applied_for_tables_with_tenant_id(): void
    {
        $model = new class extends TenantModel
        {
            protected $table = 'tenant_scope_with_tenant_id';

            public $timestamps = false;
        };

        $sql = $model->newQuery()->toSql();

        $this->assertStringContainsString('where', strtolower($sql));
    }

    public function test_deworming_deducts_medicine_inventory_on_consumption(): void
    {
        $medicine = Medicine::create([
            'name' => 'Dewormer X',
            'unit' => 'ml',
            'quantity_on_hand' => 10,
            'is_active' => true,
        ]);

        $deworming = Deworming::create([
            'medicine_id' => $medicine->id,
            'quantity_used' => 2.5,
            'product_name' => 'Dewormer X',
            'deworming_date' => now()->toDateString(),
            'status' => 'Completed',
        ]);

        (new MedicineInventoryService())->deductForDeworming($deworming);

        $medicine->refresh();

        $this->assertSame('7.50', (string) $medicine->quantity_on_hand);
    }
}
