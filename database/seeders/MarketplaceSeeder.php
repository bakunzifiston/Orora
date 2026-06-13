<?php

namespace Database\Seeders;

use App\Models\Central\MarketplaceCategory;
use App\Models\Central\MarketplaceListing;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Live Animals', 'slug' => 'live-animals', 'icon' => '🐄', 'sort_order' => 1],
            ['name' => 'Meat Products', 'slug' => 'meat-products', 'icon' => '🥩', 'sort_order' => 2],
            ['name' => 'Milk & Dairy', 'slug' => 'milk-dairy', 'icon' => '🥛', 'sort_order' => 3],
            ['name' => 'Feed & Supplies', 'slug' => 'feed-supplies', 'icon' => '🌾', 'sort_order' => 4],
        ];

        foreach ($categories as $category) {
            MarketplaceCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                $category + ['is_active' => true]
            );
        }

        $this->call(LearningSeeder::class);

        $tenant = Tenant::query()->first();
        if (! $tenant) {
            return;
        }

        $categoryMap = MarketplaceCategory::query()->pluck('id', 'slug');

        $listings = [
            [
                'category_id' => $categoryMap['live-animals'],
                'listing_type' => 'animal',
                'title' => 'Friesian Cow for Sale',
                'slug' => 'friesian-cow-for-sale',
                'description' => 'Healthy Friesian cow, vaccinated and ready for breeding. Well maintained with full health records available.',
                'breed' => 'Friesian',
                'age' => '3 years',
                'weight_kg' => 350,
                'quantity' => 1,
                'unit' => 'head',
                'price' => 500000,
                'price_type' => 'negotiable',
                'seller_name' => 'Kagarama Prime Farm',
                'seller_phone' => '+250 788 123 456',
                'seller_email' => 'kagarama@example.com',
                'seller_type' => 'individual',
                'location_district' => 'Kigali',
                'location_sector' => 'Kicukiro',
                'is_featured' => true,
                'is_verified' => true,
                'views_count' => 1240,
            ],
            [
                'category_id' => $categoryMap['milk-dairy'],
                'listing_type' => 'milk_dairy',
                'title' => 'Fresh farm milk — daily supply',
                'slug' => 'fresh-farm-milk-daily',
                'description' => 'Fresh cow milk available for households and small businesses. Minimum order 20 litres.',
                'quantity' => 200,
                'unit' => 'liter',
                'price' => 600,
                'price_type' => 'per_liter',
                'seller_name' => 'Nyagatare Dairy Co-op',
                'seller_phone' => '+250 788 234 567',
                'seller_type' => 'cooperative',
                'location_district' => 'Nyagatare',
                'is_featured' => true,
                'is_verified' => true,
            ],
            [
                'category_id' => $categoryMap['meat-products'],
                'listing_type' => 'meat',
                'title' => 'Grass-fed beef — quarter carcass',
                'slug' => 'grass-fed-beef-quarter',
                'description' => 'Locally raised, grass-fed beef. Sold by quarter carcass with optional delivery in Kigali.',
                'quantity' => 50,
                'unit' => 'kg',
                'price' => 4500,
                'price_type' => 'per_kg',
                'seller_name' => 'Musanze Meats Ltd',
                'seller_phone' => '+250 788 345 678',
                'seller_type' => 'company',
                'location_district' => 'Musanze',
                'is_verified' => true,
            ],
            [
                'category_id' => $categoryMap['feed-supplies'],
                'listing_type' => 'feed_supply',
                'title' => 'Dairy concentrate feed — 50 kg bags',
                'slug' => 'dairy-concentrate-50kg',
                'description' => 'High-energy concentrate formulated for lactating dairy cows. Pickup or delivery available.',
                'quantity' => 120,
                'unit' => 'bag',
                'price' => 28000,
                'price_type' => 'fixed',
                'seller_name' => 'Huye Agro Supplies',
                'seller_phone' => '+250 788 456 789',
                'seller_type' => 'company',
                'location_district' => 'Huye',
                'is_featured' => true,
            ],
        ];

        foreach ($listings as $listing) {
            MarketplaceListing::query()->updateOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $listing['slug']],
                $listing + [
                    'tenant_id' => $tenant->id,
                    'listing_code' => MarketplaceListing::generateCode(),
                    'currency' => 'RWF',
                    'status' => 'active',
                    'images' => [],
                ]
            );
        }
    }
}
