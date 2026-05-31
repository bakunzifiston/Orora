<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique();
            $table->string('display_name');
            $table->string('status')->default('active');
            $table->string('employment_type')->default('full_time');
            $table->string('job_role')->default('farm_worker');
            $table->foreignId('primary_farm_id')->nullable()->constrained('farms')->nullOnDelete();
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'job_role']);
        });

        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('national_id')->nullable();
            $table->string('passport_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('nationality')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('education_level')->nullable();
            $table->text('skills')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('contact_name');
            $table->string('relationship')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('address_type')->default('physical');
            $table->string('address_label')->nullable();
            $table->string('country')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('sector')->nullable();
            $table->string('cell')->nullable();
            $table->string('village')->nullable();
            $table->text('street_address')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('employee_farm_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('assignment_role')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->date('assigned_from')->nullable();
            $table->date('assigned_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'farm_id']);
        });

        Schema::create('employee_payroll', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('contract_type')->default('permanent');
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            $table->decimal('base_salary', 15, 2)->nullable();
            $table->string('currency')->default('RWF');
            $table->string('pay_frequency')->default('monthly');
            $table->string('payment_method')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('mobile_money_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('document_type');
            $table->string('document_name');
            $table->string('file_path')->nullable();
            $table->date('issued_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('employee_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('action_type');
            $table->foreignId('action_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('action_at');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_logs');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('employee_payroll');
        Schema::dropIfExists('employee_farm_assignments');
        Schema::dropIfExists('employee_addresses');
        Schema::dropIfExists('employee_emergency_contacts');
        Schema::dropIfExists('employee_profiles');
        Schema::dropIfExists('employees');
    }
};
