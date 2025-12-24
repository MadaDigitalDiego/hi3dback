<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the subscription system with Stripe Billing
    |
    */

    'stripe' => [
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    // Devise par défaut utilisée pour les prix Stripe (ex: EUR, USD)
    // Utilisée par le StripeService pour créer les prix (en minuscule côté Stripe)
    'currency' => env('SUBSCRIPTION_CURRENCY', 'EUR'),

    'plans' => [
        'free' => [
            'name' => 'Free',
            'price' => 0,
            'currency' => 'EUR',
            'interval' => 'month',
            // Free plan has 0 quota on all main resources; users must upgrade
            // to create service offers, open offers, or upload portfolio files.
            'limits' => [
                'service_offers' => 0,
                'open_offers' => 0,
	                'applications' => 0,
	                'messages' => 0,
                'portfolio_files' => 0,
                'analytics_retention_days' => 30,
            ],
            'features' => [
                'basic_profile',
                'service_offers',
                'open_offers',
                'messaging',
            ],
        ],

        'basic' => [
            'name' => 'Basic',
            'price' => 29.99,
            'currency' => 'EUR',
            'interval' => 'month',
            'stripe_price_id_monthly' => env('STRIPE_PRICE_BASIC_MONTHLY'),
            'stripe_price_id_yearly' => env('STRIPE_PRICE_BASIC_YEARLY'),
            'limits' => [
                'service_offers' => 10,
                'open_offers' => 20,
                'portfolio_files' => 50,
                'analytics_retention_days' => 90,
            ],
            'features' => [
                'basic_profile',
                'service_offers',
                'open_offers',
                'messaging',
                'analytics',
                'priority_support',
            ],
        ],

        'pro' => [
            'name' => 'Pro',
            'price' => 49.99,
            'currency' => 'EUR',
            'interval' => 'month',
            'stripe_price_id_monthly' => env('STRIPE_PRICE_PRO_MONTHLY'),
            'stripe_price_id_yearly' => env('STRIPE_PRICE_PRO_YEARLY'),
            'limits' => [
                'service_offers' => 50,
                'open_offers' => 100,
                'portfolio_files' => 200,
                'analytics_retention_days' => 365,
            ],
            'features' => [
                'basic_profile',
                'service_offers',
                'open_offers',
                'messaging',
                'analytics',
                'priority_support',
                'advanced_analytics',
                'custom_branding',
            ],
        ],

        'enterprise' => [
            'name' => 'Enterprise',
            'price' => 'custom',
            'currency' => 'EUR',
            'interval' => 'month',
            'stripe_price_id_monthly' => env('STRIPE_PRICE_ENTERPRISE_MONTHLY'),
            'stripe_price_id_yearly' => env('STRIPE_PRICE_ENTERPRISE_YEARLY'),
            'limits' => [
                'service_offers' => 999,
                'open_offers' => 999,
                'portfolio_files' => 999,
                'analytics_retention_days' => 999,
            ],
            'features' => [
                'all_features',
                'dedicated_support',
                'api_access',
                'custom_integration',
            ],
        ],
    ],

    'trial' => [
        'days' => 14,
        'enabled' => true,
    ],

    'coupon' => [
        'max_discount_percentage' => 100,
        'max_discount_fixed' => 1000,
    ],
];

