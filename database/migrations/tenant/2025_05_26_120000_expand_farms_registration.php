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
            $table->string('registration_number')->nullable()->after('name');
            $table->string('country')->default('Rwanda')->after('registration_number');
            $table->unsignedTinyInteger('province_code')->nullable()->after('country');
            $table->string('province')->nullable()->after('province_code');
            $table->unsignedSmallInteger('district_code')->nullable()->after('province');
            $table->string('sector')->nullable()->after('district');
            $table->string('sector_code', 20)->nullable()->after('sector');
            $table->string('cell')->nullable()->after('sector_code');
            $table->unsignedInteger('cell_code')->nullable()->after('cell');
            $table->string('village')->nullable()->after('cell_code');
            $table->unsignedInteger('village_code')->nullable()->after('village');
            $table->decimal('farm_size_hectares', 10, 2)->nullable()->after('village_code');
            $table->date('registration_date')->nullable()->after('farm_size_hectares');

            $table->string('ownership_type')->default('sole_proprietor')->after('status');
            $table->string('owner_first_name')->nullable()->after('ownership_type');
            $table->string('owner_last_name')->nullable()->after('owner_first_name');
            $table->string('owner_national_id')->nullable()->after('owner_last_name');
            $table->string('contact_phone')->nullable()->after('owner_national_id');
            $table->string('contact_email')->nullable()->after('contact_phone');
            $table->string('owner_emergency_contact')->nullable()->after('contact_email');
            $table->string('organization_name')->nullable()->after('owner_emergency_contact');
            $table->string('tax_id')->nullable()->after('organization_name');
            $table->date('owner_dob')->nullable()->after('tax_id');
            $table->string('owner_gender')->nullable()->after('owner_dob');
        });

        if (Schema::hasColumn('farms', 'code')) {
            DB::table('farms')->whereNotNull('code')->update([
                'registration_number' => DB::raw('code'),
            ]);
            Schema::table('farms', function (Blueprint $table) {
                $table->dropColumn(['code', 'location', 'capacity']);
            });
        }

        Schema::table('farms', function (Blueprint $table) {
            $table->unique('registration_number');
        });

        Schema::create('farm_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->string('phone');
            $table->string('gender');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_members');

        Schema::table('farms', function (Blueprint $table) {
            $table->dropUnique(['registration_number']);
            $table->dropColumn([
                'registration_number',
                'country',
                'province_code',
                'province',
                'district_code',
                'sector',
                'sector_code',
                'cell',
                'cell_code',
                'village',
                'village_code',
                'farm_size_hectares',
                'registration_date',
                'ownership_type',
                'owner_first_name',
                'owner_last_name',
                'owner_national_id',
                'contact_phone',
                'contact_email',
                'owner_emergency_contact',
                'organization_name',
                'tax_id',
                'owner_dob',
                'owner_gender',
            ]);
            $table->string('code')->nullable()->unique();
            $table->string('location')->nullable();
            $table->unsignedInteger('capacity')->nullable();
        });
    }
};
