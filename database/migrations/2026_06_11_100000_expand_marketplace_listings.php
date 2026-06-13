<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->string('listing_code')->nullable()->unique()->after('category_id');
            $table->string('listing_type', 30)->nullable()->after('listing_code');
            $table->string('breed')->nullable()->after('description');
            $table->string('age')->nullable()->after('breed');
            $table->decimal('weight_kg', 10, 2)->nullable()->after('age');
            $table->decimal('quantity', 10, 3)->nullable()->after('weight_kg');
            $table->string('unit', 20)->nullable()->after('quantity');
            $table->string('price_type', 20)->default('fixed')->after('price');
            $table->json('images')->nullable()->after('currency');
            $table->string('seller_name')->nullable()->after('images');
            $table->string('seller_phone')->nullable()->after('seller_name');
            $table->string('seller_email')->nullable()->after('seller_phone');
            $table->string('seller_type', 30)->nullable()->after('seller_email');
            $table->string('location_district')->nullable()->after('seller_type');
            $table->string('location_sector')->nullable()->after('location_district');
            $table->boolean('is_featured')->default(false)->after('location_sector');
            $table->boolean('is_verified')->default(false)->after('is_featured');
            $table->timestamp('expires_at')->nullable()->after('status');
            $table->unsignedInteger('views_count')->default(0)->after('expires_at');
        });

        if (Schema::hasColumn('marketplace_listings', 'featured')) {
            DB::table('marketplace_listings')->update([
                'is_featured' => DB::raw('featured'),
            ]);
        }

        DB::table('marketplace_listings')->where('status', 'published')->update(['status' => 'active']);
        DB::table('marketplace_listings')->where('status', 'draft')->update(['status' => 'draft']);

        $rows = DB::table('marketplace_listings')->orderBy('id')->get();

        foreach ($rows as $row) {
            $updates = [];

            if ($row->location && ! $row->location_district) {
                $updates['location_district'] = $row->location;
            }

            if ($row->image_path) {
                $updates['images'] = json_encode([$row->image_path]);
            }

            if ($row->price_unit && ! $row->unit) {
                $unitMap = ['head' => 'head', 'kg' => 'kg', 'litre' => 'liter', 'liter' => 'liter', 'bag' => 'bag', 'ton' => 'ton'];
                $updates['unit'] = $unitMap[strtolower($row->price_unit)] ?? 'head';
                $updates['price_type'] = in_array(strtolower($row->price_unit), ['kg', 'litre', 'liter'], true) ? 'per_kg' : 'fixed';
            }

            if ($row->quantity_available && ! $row->quantity) {
                if (preg_match('/([\d.]+)/', $row->quantity_available, $m)) {
                    $updates['quantity'] = $m[1];
                }
            }

            if (! $row->listing_code) {
                $dateKey = date('Ymd', strtotime($row->created_at ?? 'now'));
                $updates['listing_code'] = sprintf('LST-%s-%04d', $dateKey, $row->id);
            }

            if (! $row->listing_type) {
                $updates['listing_type'] = 'animal';
            }

            if (! $row->seller_name) {
                $updates['seller_name'] = 'Farm Seller';
                $updates['seller_phone'] = '+250 788 000 000';
                $updates['seller_type'] = 'individual';
            }

            if ($updates) {
                DB::table('marketplace_listings')->where('id', $row->id)->update($updates);
            }
        }

        Schema::table('marketplace_listings', function (Blueprint $table) {
            if (Schema::hasColumn('marketplace_listings', 'price_unit')) {
                $table->dropColumn('price_unit');
            }
            if (Schema::hasColumn('marketplace_listings', 'location')) {
                $table->dropColumn('location');
            }
            if (Schema::hasColumn('marketplace_listings', 'quantity_available')) {
                $table->dropColumn('quantity_available');
            }
            if (Schema::hasColumn('marketplace_listings', 'image_path')) {
                $table->dropColumn('image_path');
            }
            if (Schema::hasColumn('marketplace_listings', 'featured')) {
                $table->dropColumn('featured');
            }
            if (Schema::hasColumn('marketplace_listings', 'published_at')) {
                $table->dropColumn('published_at');
            }
        });

        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
            $table->index('location_district');
            $table->index('listing_type');
        });

        Schema::table('marketplace_inquiries', function (Blueprint $table) {
            if (Schema::hasColumn('marketplace_inquiries', 'name')) {
                $table->renameColumn('name', 'buyer_name');
            }
            if (Schema::hasColumn('marketplace_inquiries', 'email')) {
                $table->renameColumn('email', 'buyer_email');
            }
            if (Schema::hasColumn('marketplace_inquiries', 'phone')) {
                $table->renameColumn('phone', 'buyer_phone');
            }
        });

        Schema::table('marketplace_inquiries', function (Blueprint $table) {
            $table->string('buyer_location')->nullable()->after('buyer_email');
        });

        DB::table('marketplace_categories')->where('slug', 'meat')->update(['slug' => 'meat-products', 'name' => 'Meat Products']);
        DB::table('marketplace_categories')->where('slug', 'feed')->update(['slug' => 'feed-supplies', 'name' => 'Feed & Supplies']);
    }

    public function down(): void
    {
        Schema::table('marketplace_inquiries', function (Blueprint $table) {
            $table->dropColumn('buyer_location');
        });

        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->dropColumn([
                'listing_code', 'listing_type', 'breed', 'age', 'weight_kg', 'quantity', 'unit',
                'price_type', 'images', 'seller_name', 'seller_phone', 'seller_email', 'seller_type',
                'location_district', 'location_sector', 'is_featured', 'is_verified', 'expires_at', 'views_count',
            ]);
        });
    }
};
