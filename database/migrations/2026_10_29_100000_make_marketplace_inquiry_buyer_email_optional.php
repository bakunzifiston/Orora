<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The inquiry form marks email as optional, but the column was left NOT NULL,
 * so inquiries without an email failed with a database error.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_inquiries', function (Blueprint $table) {
            $table->string('buyer_email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_inquiries', function (Blueprint $table) {
            $table->string('buyer_email')->nullable(false)->change();
        });
    }
};
