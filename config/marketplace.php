<?php

return [

    'site_name' => 'Orora Farm',

    'tagline' => 'Your entire farm. One system.',

    'colors' => [
        'primary' => '#1B5E20',
        'primary_dark' => '#144717',
        'accent' => '#F9A825',
        'accent_dark' => '#E09200',
        'light_green' => '#F1F8E9',
        'light_grey' => '#F5F5F5',
        'text' => '#1A2E1A',
        'text_muted' => '#5F6368',
    ],

    'contact' => [
        'email' => 'hello@ororafarm.com',
        'phone' => '+250788000000',
        'phone_display' => '+250 788 000 000',
        'address' => 'Kigali, Rwanda',
        'full_address' => 'KG 7 Ave, Nyarutarama, Kigali City, Rwanda',
        'whatsapp' => '+250788000000',
        'whatsapp_display' => '+250 788 000 000',
        'maps_url' => 'https://maps.google.com/?q=Kigali,Rwanda',
        'map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d255079.07408376668!2d30.0117096!3d-1.9440727!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x19dca4258cb5b3b3%3A0x50c2b5ad737716f9!2sKigali%2C%20Rwanda!5e0!3m2!1sen!2s!4v1717000000000!5m2!1sen!2s',

        'hours' => [
            ['label' => 'Monday – Friday', 'time' => '8:00 AM – 6:00 PM', 'open' => true],
            ['label' => 'Saturday', 'time' => '9:00 AM – 1:00 PM', 'open' => true],
            ['label' => 'Sunday', 'time' => 'Closed', 'open' => false],
        ],

        'response_times' => [
            ['icon' => '📧', 'channel' => 'Email', 'time' => 'Within 24 hours'],
            ['icon' => '📞', 'channel' => 'Phone', 'time' => 'Same day'],
            ['icon' => '💬', 'channel' => 'WhatsApp', 'time' => 'Within 2 hours'],
        ],

        'inquiry_types' => [
            'general' => 'General Inquiry',
            'support' => 'Technical Support',
            'partnership' => 'Partnership',
            'marketplace' => 'Marketplace',
            'demo' => 'Request a Demo',
        ],

        'faq' => [
            [
                'question' => 'How do I register my farm on Orora Farm?',
                'answer' => 'Click "Register" on our homepage and fill in your farm details. You will receive your own subdomain (e.g. yourfarm.ororafarm.com) and can start adding animals and records within minutes. No credit card is required for the free plan.',
            ],
            [
                'question' => 'Is there a free trial available?',
                'answer' => 'Yes. Orora Farm offers a free plan with one farm and up to 50 animals. You can upgrade to Pro or Enterprise at any time as your operation grows.',
            ],
            [
                'question' => 'How does the marketplace work?',
                'answer' => 'Registered farmers can list live animals, meat, milk, and feed on the Orora Farm marketplace. Buyers browse listings, send inquiries, and connect directly with sellers — no middlemen required.',
            ],
            [
                'question' => 'Can I manage multiple farms in one account?',
                'answer' => 'Yes. Pro and Enterprise plans support multiple farms under a single account, with separate records and reports for each farm.',
            ],
            [
                'question' => 'What support do you offer after registration?',
                'answer' => 'All users receive email support. Pro plan users get priority support, and Enterprise customers receive dedicated onboarding, training, and a named account contact.',
            ],
            [
                'question' => 'Is my farm data safe and private?',
                'answer' => 'Absolutely. Each farm operates in an isolated workspace. Your data is encrypted in transit, never sold to third parties, and you retain full ownership and export rights at all times.',
            ],
            [
                'question' => 'Do you support Kinyarwanda language?',
                'answer' => 'The platform interface is currently in English, with Kinyarwanda support on our roadmap. Our Learning Hub already includes resources in both English and Kinyarwanda.',
            ],
            [
                'question' => 'How do I post a listing on the marketplace?',
                'answer' => 'Log in to your farm account, go to the Marketplace section, and click "Create Listing". Add photos, description, price, and location — your listing goes live once submitted.',
            ],
        ],
    ],

    'social' => [
        ['label' => 'Facebook', 'url' => 'https://facebook.com/ororafarm', 'icon' => 'facebook'],
        ['label' => 'Twitter', 'url' => 'https://twitter.com/ororafarm', 'icon' => 'twitter'],
        ['label' => 'LinkedIn', 'url' => 'https://linkedin.com/company/ororafarm', 'icon' => 'linkedin'],
        ['label' => 'Instagram', 'url' => 'https://instagram.com/ororafarm', 'icon' => 'instagram'],
        ['label' => 'WhatsApp', 'url' => 'https://wa.me/250788000000', 'icon' => 'whatsapp'],
    ],

    'hero' => [
        'trust_badges' => [
            'No credit card required',
            'Set up in minutes',
            'Your own subdomain',
        ],
    ],

    'landing_stats' => [
        ['value' => 50, 'suffix' => '+', 'label' => 'Farms Registered', 'animate' => true],
        ['value' => 1200, 'suffix' => '+', 'label' => 'Animals Tracked', 'animate' => true],
        ['value' => null, 'display' => 'Rwanda & Africa', 'label' => 'Serving farmers', 'animate' => false],
        ['value' => null, 'display' => 'Trusted', 'label' => 'By Farmers', 'animate' => false],
    ],

    'problem_solution' => [
        'title' => 'Running a farm is hard.',
        'subtitle' => 'We make it simpler.',
        'before_label' => 'Before Orora Farm',
        'after_label' => 'After Orora Farm',
        'rows' => [
            ['before' => 'Paper records', 'after' => 'Digital records'],
            ['before' => 'Lost animal history', 'after' => 'Full animal profiles'],
            ['before' => 'No financial overview', 'after' => 'Real-time P&L'],
            ['before' => 'Manual milk tracking', 'after' => 'Automated milk records'],
            ['before' => 'Missed vaccinations', 'after' => 'Health alerts & reminders'],
            ['before' => 'No movement permits', 'after' => 'Digital permits & certificates'],
        ],
    ],

    'features' => [
        [
            'icon' => '🐄',
            'title' => 'Farm & Animals',
            'description' => 'Track every animal from birth to sale with full lifecycle records.',
        ],
        [
            'icon' => '🏥',
            'title' => 'Health Management',
            'description' => 'Vaccinations, treatments, vet visits, and disease records.',
        ],
        [
            'icon' => '🥛',
            'title' => 'Milk Production',
            'description' => 'Daily sessions, yield tracking, and cost per litre analysis.',
        ],
        [
            'icon' => '🌾',
            'title' => 'Feeding Module',
            'description' => 'Feed types, inventory, schedules, and cost calculator.',
        ],
        [
            'icon' => '🐄',
            'title' => 'Breeding Records',
            'description' => 'Pregnancy tracking, birth records, and offspring registration.',
        ],
        [
            'icon' => '📋',
            'title' => 'Certificates & Movement',
            'description' => 'Digital permits, health certificates, and traceability.',
        ],
        [
            'icon' => '💰',
            'title' => 'Sales & Customers',
            'description' => 'Animal, meat, and milk sales with full payment tracking.',
        ],
        [
            'icon' => '📊',
            'title' => 'Finance & Reports',
            'description' => 'P&L, cash flow, budget vs actual, and tax reporting.',
        ],
    ],

    'how_it_works' => [
        [
            'step' => 1,
            'title' => 'Register your account',
            'description' => 'Get your own subdomain and create your farm workspace in minutes.',
        ],
        [
            'step' => 2,
            'title' => 'Set up your farm',
            'description' => 'Add livestock, animals, and team members to get started quickly.',
        ],
        [
            'step' => 3,
            'title' => 'Start managing',
            'description' => 'Track animals, milk, health, sales, and finance from one dashboard.',
        ],
    ],

    'testimonials' => [
        [
            'quote' => 'Orora Farm changed how I manage my dairy farm. I now know my cost per litre every single day and can track every animal from birth to sale.',
            'name' => 'Jean Baptiste Nkurunziza',
            'role' => 'Dairy Farmer',
            'location' => 'Kigali',
            'initials' => 'JN',
        ],
        [
            'quote' => 'We finally have one place for health records, movement permits, and sales. Our team saves hours every week.',
            'name' => 'Marie Claire Uwimana',
            'role' => 'Farm Manager',
            'location' => 'Nyagatare',
            'initials' => 'MU',
        ],
        [
            'quote' => 'The marketplace helped us find buyers for our heifers without middlemen. Registration was simple and fast.',
            'name' => 'Patrick Habimana',
            'role' => 'Livestock Trader',
            'location' => 'Musanze',
            'initials' => 'PH',
        ],
    ],

    'pricing' => [
        [
            'name' => 'Free',
            'price' => '0',
            'period' => 'RWF/mo',
            'popular' => false,
            'features' => ['1 Farm', '50 Animals', 'Basic reports', 'Marketplace listing'],
            'cta' => 'Start Free',
            'cta_route' => 'register',
        ],
        [
            'name' => 'Pro',
            'price' => '25,000',
            'period' => 'RWF/mo',
            'popular' => true,
            'features' => ['5 Farms', 'Unlimited animals', 'All modules', 'Finance & reports', 'Priority support'],
            'cta' => 'Start Pro',
            'cta_route' => 'register',
        ],
        [
            'name' => 'Enterprise',
            'price' => 'Contact us',
            'period' => '',
            'popular' => false,
            'features' => ['Unlimited farms', 'Custom integrations', 'Dedicated support', 'Training & onboarding'],
            'cta' => 'Contact Sales',
            'cta_route' => 'marketplace.contact',
        ],
    ],

    'about' => [
        'hero' => [
            'title' => ['Built for farmers.', 'Designed for results.'],
            'subtitle' => 'We believe every farmer deserves modern tools to run a profitable, traceable, and healthy farm.',
            'background' => '/images/auth-cow.jpg',
            'primary_cta' => ['label' => 'Explore the Platform', 'route' => 'register'],
            'secondary_cta' => ['label' => 'Browse Marketplace', 'route' => 'marketplace.shop'],
        ],

        'story' => [
            'title' => 'Our Story',
            'image' => '/images/auth-cow.jpg',
            'image_alt' => 'Farmers working with livestock in Rwanda',
            'paragraphs' => [
                'Orora Farm was born from a simple observation: farmers in Rwanda were managing their entire operations on paper — losing animal records, missing vaccinations, and struggling to track their finances.',
                'We built Orora Farm to change that — giving every farmer the same digital tools that large agribusinesses use, but simple enough for daily farm life.',
            ],
        ],

        'mission' => [
            'quote' => 'To empower every farmer in Rwanda and Africa with modern tools to run a profitable, traceable, and healthy livestock farm.',
            'pillars' => [
                ['icon' => '🎯', 'title' => 'Simple', 'description' => 'Tools built for everyday farm life'],
                ['icon' => '🌍', 'title' => 'Accessible', 'description' => 'Works on any device, anywhere'],
                ['icon' => '🤝', 'title' => 'Trusted', 'description' => 'Used by farmers across Rwanda'],
            ],
        ],

        'offerings' => [
            [
                'icon' => '🖥',
                'title' => 'Farm Management',
                'description' => 'Track animals, health, milk, breeding, feeding, sales and finance all in one system.',
            ],
            [
                'icon' => '🛒',
                'title' => 'Marketplace',
                'description' => 'Buy and sell live animals, meat, milk, and feed directly with verified farmers.',
            ],
            [
                'icon' => '📚',
                'title' => 'Learning Hub',
                'description' => 'Expert articles, videos and guides on livestock farming topics.',
                'route' => 'marketplace.learning',
            ],
            [
                'icon' => '🌍',
                'title' => 'Community',
                'description' => 'Connect with other farmers, share knowledge and grow together.',
            ],
        ],

        'values' => [
            ['icon' => '🌱', 'title' => 'Simplicity', 'description' => 'We build tools that are easy for every farmer to use daily.'],
            ['icon' => '🔒', 'title' => 'Trust', 'description' => 'We protect your data and give you full control of your farm.'],
            ['icon' => '🌍', 'title' => 'Impact', 'description' => 'We exist to improve lives of farmers and their families.'],
            ['icon' => '📱', 'title' => 'Accessibility', 'description' => 'Works on any device — phone, tablet, desktop.'],
            ['icon' => '🤝', 'title' => 'Partnership', 'description' => 'We grow with our farmers — your success is ours too.'],
            ['icon' => '🏆', 'title' => 'Excellence', 'description' => 'We never stop improving our platform.'],
        ],

        'modules_count' => 14,

        'team' => [
            [
                'name' => 'Jean Baptiste Nkurunziza',
                'role' => 'Founder & CEO',
                'initials' => 'JN',
                'photo' => null,
                'linkedin' => 'https://linkedin.com/in/jeanbaptiste-nkurunziza',
            ],
            [
                'name' => 'Marie Claire Uwimana',
                'role' => 'Head of Product',
                'initials' => 'MU',
                'photo' => null,
                'linkedin' => 'https://linkedin.com/in/marieclaire-uwimana',
            ],
            [
                'name' => 'Patrick Habimana',
                'role' => 'Lead Engineer',
                'initials' => 'PH',
                'photo' => null,
                'linkedin' => 'https://linkedin.com/in/patrick-habimana',
            ],
        ],

        'why_rwanda' => [
            'title' => 'Why We Started in Rwanda',
            'image' => '/images/auth-cow.jpg',
            'image_alt' => 'Rwanda agricultural landscape',
            'paragraphs' => [
                'Rwanda has one of the fastest growing agricultural sectors in Africa. With over 3 million cattle and a growing dairy industry, farmers needed modern tools built for their reality.',
                'Orora Farm is built in Rwanda, for Rwanda — with RWF currency, Rwanda admin divisions, and local farming practices at its core.',
            ],
        ],

        'partners' => [
            ['name' => 'MINAGRI', 'initials' => 'MA'],
            ['name' => 'RAB', 'initials' => 'RB'],
            ['name' => 'RDB', 'initials' => 'RD'],
            ['name' => 'HEIFER', 'initials' => 'HF'],
            ['name' => 'TechnoServe', 'initials' => 'TS'],
        ],

        'cta' => [
            'title' => 'Ready to transform your farm?',
            'subtitle' => 'Join hundreds of farmers already using Orora Farm',
            'primary_cta' => ['label' => 'Start Free Trial', 'route' => 'register'],
            'secondary_cta' => ['label' => 'Contact Us', 'route' => 'marketplace.contact'],
        ],
    ],

    // Legacy flat stats for about page fallback
    'stats' => [
        ['icon' => '🐄', 'value' => '1,200+', 'label' => 'Animals Tracked'],
        ['icon' => '🏠', 'value' => '50+', 'label' => 'Farms'],
        ['icon' => '🥛', 'value' => '10K+', 'label' => 'Milk Records'],
        ['icon' => '💰', 'value' => '5M+', 'label' => 'Sales Recorded'],
        ['icon' => '🌍', 'value' => 'Rwanda', 'label' => 'Serving farmers'],
    ],

    'shop' => [
        'listing_types' => [
            'animal' => 'Live Animals',
            'meat' => 'Meat Products',
            'milk_dairy' => 'Milk & Dairy',
            'feed_supply' => 'Feed & Supplies',
        ],

        'category_type_map' => [
            'live-animals' => 'animal',
            'meat-products' => 'meat',
            'milk-dairy' => 'milk_dairy',
            'feed-supplies' => 'feed_supply',
        ],

        'units' => [
            'head' => 'head',
            'kg' => 'kg',
            'liter' => 'litre',
            'bag' => 'bag',
            'ton' => 'ton',
        ],

        'price_types' => [
            'fixed' => 'Fixed',
            'negotiable' => 'Negotiable',
            'per_kg' => 'Per kg',
            'per_liter' => 'Per litre',
        ],

        'seller_types' => [
            'individual' => 'Individual Farmer',
            'cooperative' => 'Cooperative',
            'company' => 'Company',
            'abattoir' => 'Abattoir',
        ],

        'sort_options' => [
            'newest' => 'Newest',
            'price_asc' => 'Price: Low to High',
            'price_desc' => 'Price: High to Low',
            'most_viewed' => 'Most Viewed',
        ],

        'districts' => [
            'Bugesera', 'Burera', 'Gakenke', 'Gasabo', 'Gatsibo', 'Kayonza', 'Kicukiro',
            'Kirehe', 'Muhanga', 'Musanze', 'Ngoma', 'Ngororero', 'Nyagatare', 'Nyamagabe',
            'Nyamasheke', 'Nyanza', 'Nyarugenge', 'Rubavu', 'Ruhango', 'Rulindo', 'Rusizi',
            'Rutsiro', 'Rwamagana', 'Karongi', 'Gisagara', 'Huye', 'Kamonyi', 'Muhanga',
        ],
    ],

    'learning' => [
        'content_types' => [
            'article' => '📄 Articles',
            'video' => '🎥 Videos',
            'pdf' => '📥 PDFs',
        ],

        'difficulty_levels' => [
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate',
            'advanced' => 'Advanced',
        ],

        'languages' => [
            'en' => 'English',
            'rw' => 'Kinyarwanda',
        ],

        'sort_options' => [
            'newest' => 'Newest',
            'views' => 'Most Viewed',
            'title' => 'A–Z',
        ],
    ],

];
