<?php

namespace Database\Seeders;

use App\Models\Central\LearningCategory;
use App\Models\Central\LearningPost;
use Illuminate\Database\Seeder;

class LearningSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Animal Health', 'slug' => 'animal-health', 'icon' => '🏥', 'description' => 'Everything you need to keep your animals healthy', 'sort_order' => 1],
            ['name' => 'Feeding & Nutrition', 'slug' => 'feeding-nutrition', 'icon' => '🌾', 'description' => 'Feed planning, rations, and nutrition for livestock', 'sort_order' => 2],
            ['name' => 'Milk Production', 'slug' => 'milk-production', 'icon' => '🥛', 'description' => 'Milk recording, hygiene, and yield improvement', 'sort_order' => 3],
            ['name' => 'Breeding', 'slug' => 'breeding', 'icon' => '🐄', 'description' => 'Reproduction, pregnancy, and offspring management', 'sort_order' => 4],
            ['name' => 'Farm Management', 'slug' => 'farm-management', 'icon' => '🏠', 'description' => 'Daily operations, records, and team management', 'sort_order' => 5],
            ['name' => 'Finance & Business', 'slug' => 'finance-business', 'icon' => '💰', 'description' => 'Profitability, pricing, and farm business skills', 'sort_order' => 6],
            ['name' => 'Disease Control', 'slug' => 'disease-control', 'icon' => '💊', 'description' => 'Prevention, biosecurity, and outbreak response', 'sort_order' => 7],
            ['name' => 'Movement & Certificates', 'slug' => 'movement-certificates', 'icon' => '📋', 'description' => 'Permits, traceability, and compliance documents', 'sort_order' => 8],
        ];

        foreach ($categories as $category) {
            LearningCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                $category + ['is_active' => true]
            );
        }

        $map = LearningCategory::query()->pluck('id', 'slug');

        $posts = [
            [
                'category_id' => $map['animal-health'],
                'title' => '5 signs your cow needs a vet visit',
                'slug' => '5-signs-cow-needs-vet',
                'excerpt' => 'Early warning signs that help you act before a small issue becomes costly.',
                'content_type' => 'article',
                'content' => '<h2>Changes in appetite</h2><p>A sudden drop in feed intake is often the first sign of illness.</p><h2>Reduced milk yield</h2><p>Track daily yields to spot problems early.</p><h2>Body temperature</h2><p>Fever above 39.5°C warrants immediate attention.</p><h2>Mobility issues</h2><p>Limping or reluctance to stand may indicate foot problems or injury.</p><h2>Behaviour changes</h2><p>Isolation from the herd or unusual aggression can signal distress.</p>',
                'author_name' => 'Dr. Marie Uwimana',
                'author_title' => 'Livestock Health Advisor',
                'read_time' => 6,
                'difficulty_level' => 'beginner',
                'language' => 'en',
                'is_featured' => true,
                'is_published' => true,
                'published_at' => now()->subDays(5),
                'views_count' => 890,
                'tags' => ['cattle', 'health', 'veterinary'],
            ],
            [
                'category_id' => $map['feeding-nutrition'],
                'title' => 'How to calculate daily feed for lactating cows',
                'slug' => 'daily-feed-lactating-cows',
                'excerpt' => 'A practical guide to meeting energy needs based on body weight and milk yield.',
                'content_type' => 'article',
                'content' => '<h2>Start with body weight</h2><p>Maintenance needs depend on live weight. Weigh cows regularly or use a weight tape for consistent estimates.</p><h2>Adjust for milk yield</h2><p>Higher-producing cows need more energy and protein. Add concentrates gradually as yield increases.</p><h2>Balance roughage and concentrates</h2><p>Quality hay, silage, or pasture should form the base ration. Supplement with concentrates to close energy gaps.</p><h2>Track in Orora Farm</h2><p>Record feed purchases and link feeding plans to livestock groups so you can review cost per litre over time.</p>',
                'author_name' => 'Patrick Habimana',
                'author_title' => 'Feeding Specialist',
                'read_time' => 9,
                'difficulty_level' => 'intermediate',
                'language' => 'en',
                'is_published' => true,
                'published_at' => now()->subDays(12),
                'views_count' => 756,
                'tags' => ['feeding', 'dairy', 'nutrition'],
            ],
            [
                'category_id' => $map['milk-production'],
                'title' => 'Setting up a daily milk recording routine',
                'slug' => 'daily-milk-recording-routine',
                'excerpt' => 'Why consistent milk records help you track profit per litre and spot sick cows faster.',
                'content_type' => 'article',
                'content' => '<h2>Why record daily?</h2><p>Daily records reveal trends that weekly totals hide.</p><h2>Morning and evening sessions</h2><p>Record each milking separately for better analysis.</p><h2>Using Orora Farm</h2><p>Log sessions in the milk module and review cost per litre on your dashboard.</p>',
                'author_name' => 'Grace Mukamana',
                'author_title' => 'Dairy Farm Manager',
                'read_time' => 7,
                'difficulty_level' => 'beginner',
                'language' => 'en',
                'is_published' => true,
                'published_at' => now()->subDays(8),
                'views_count' => 542,
                'tags' => ['milk', 'records', 'dairy'],
            ],
            [
                'category_id' => $map['finance-business'],
                'title' => 'Farm record-keeping checklist (PDF)',
                'slug' => 'farm-record-keeping-checklist',
                'excerpt' => 'Download a simple checklist for daily, weekly, and monthly farm records.',
                'content_type' => 'pdf',
                'content' => 'A comprehensive checklist covering animals, milk, health, sales, and expenses.',
                'pdf_path' => null,
                'pdf_pages' => 8,
                'author_name' => 'Orora Farm Team',
                'author_title' => 'Farm Management',
                'read_time' => 10,
                'difficulty_level' => 'beginner',
                'language' => 'en',
                'is_published' => true,
                'published_at' => now()->subDays(20),
                'views_count' => 1105,
                'tags' => ['records', 'checklist', 'management'],
            ],
            [
                'category_id' => $map['disease-control'],
                'title' => 'Biosecurity basics for smallholder farms',
                'slug' => 'biosecurity-basics-smallholder',
                'excerpt' => 'Simple steps to prevent disease spread between herds and visitors.',
                'content_type' => 'article',
                'content' => '<h2>Control farm access</h2><p>Limit visitor movement between animal areas.</p><h2>Quarantine new animals</h2><p>Isolate newcomers for at least 14 days.</p><h2>Clean equipment</h2><p>Disinfect tools shared between groups.</p>',
                'author_name' => 'Dr. Jean Mugabo',
                'author_title' => 'Veterinarian, Kigali',
                'read_time' => 5,
                'difficulty_level' => 'intermediate',
                'language' => 'rw',
                'is_published' => true,
                'published_at' => now()->subDays(15),
                'views_count' => 423,
                'tags' => ['biosecurity', 'disease', 'prevention'],
            ],
            [
                'category_id' => $map['movement-certificates'],
                'title' => 'How to prepare movement permits in Rwanda',
                'slug' => 'movement-permits-rwanda',
                'excerpt' => 'Step-by-step guide to digital permits and certificates for livestock movement.',
                'content_type' => 'article',
                'content' => '<h2>When you need a permit</h2><p>Any cross-district movement of livestock requires documentation.</p><h2>Health certificate</h2><p>Ensure vaccinations are up to date before applying.</p><h2>Digital records in Orora</h2><p>Store certificates in the app for quick access during inspections.</p>',
                'author_name' => 'Eric Nshimiyimana',
                'author_title' => 'Compliance Officer',
                'read_time' => 8,
                'difficulty_level' => 'advanced',
                'language' => 'en',
                'is_published' => true,
                'published_at' => now()->subDays(18),
                'views_count' => 312,
                'tags' => ['movement', 'certificates', 'compliance'],
            ],
            [
                'category_id' => $map['breeding'],
                'title' => 'Tracking pregnancy and expected calving dates',
                'slug' => 'pregnancy-calving-tracking',
                'excerpt' => 'Use breeding records to plan feed, housing, and vet visits before calving.',
                'content_type' => 'article',
                'content' => '<h2>Record breeding dates</h2><p>Log every insemination or natural service.</p><h2>Pregnancy checks</h2><p>Confirm pregnancy at 30–45 days.</p><h2>Prepare for calving</h2><p>Adjust nutrition and prepare a clean calving area two weeks before due date.</p>',
                'author_name' => 'Grace Mukamana',
                'author_title' => 'Dairy Farm Manager',
                'read_time' => 6,
                'difficulty_level' => 'intermediate',
                'language' => 'en',
                'is_published' => true,
                'published_at' => now()->subDays(6),
                'views_count' => 478,
                'tags' => ['breeding', 'pregnancy', 'calving'],
            ],
        ];

        foreach ($posts as $post) {
            LearningPost::query()->updateOrCreate(
                ['slug' => $post['slug']],
                $post
            );
        }
    }
}
