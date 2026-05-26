<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->string('location')->nullable();
            $table->string('district')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('livestock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('species');
            $table->string('breed')->nullable();
            $table->unsignedInteger('head_count')->default(0);
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('livestock_id')->nullable()->constrained('livestock')->nullOnDelete();
            $table->string('tag_number')->unique();
            $table->string('name')->nullable();
            $table->string('species');
            $table->string('breed')->nullable();
            $table->string('sex')->default('unknown');
            $table->date('date_of_birth')->nullable();
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('feedings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('livestock_id')->nullable()->constrained('livestock')->nullOnDelete();
            $table->foreignId('animal_id')->nullable()->constrained()->nullOnDelete();
            $table->string('feed_type');
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->default('kg');
            $table->date('fed_on');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('animal_id')->nullable()->constrained()->nullOnDelete();
            $table->string('certificate_type');
            $table->string('certificate_number')->nullable();
            $table->string('issuing_authority')->nullable();
            $table->date('issued_on');
            $table->date('expires_on')->nullable();
            $table->string('status')->default('valid');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_farm_id')->constrained('farms')->cascadeOnDelete();
            $table->foreignId('to_farm_id')->nullable()->constrained('farms')->nullOnDelete();
            $table->string('movement_type');
            $table->date('moved_on');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('animal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('livestock_id')->nullable()->constrained('livestock')->nullOnDelete();
            $table->string('buyer_name');
            $table->string('buyer_contact')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->string('currency')->default('RWF');
            $table->date('sold_on');
            $table->string('payment_status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
        Schema::dropIfExists('movements');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('feedings');
        Schema::dropIfExists('animals');
        Schema::dropIfExists('livestock');
        Schema::dropIfExists('farms');
    }
};
