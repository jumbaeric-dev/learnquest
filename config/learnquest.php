<?php

return [
    // Where parents can reach you. Used as the Reply-To on parent emails.
    'support_email' => env('LEARNQUEST_SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS')),

    'marketing' => [
        'premium_price'  => env('LQ_PREMIUM_PRICE', '$4.99'),
        'premium_period' => 'per month',
    ],

    /*
    |--------------------------------------------------------------------------
    | Child Authentication
    |--------------------------------------------------------------------------
    */

    'auth' => [
        'child_login' => [
            'max_attempts' => (int) env('LEARNQUEST_CHILD_LOGIN_MAX_ATTEMPTS', 5),
            'decay_seconds' => (int) env('LEARNQUEST_CHILD_LOGIN_DECAY_SECONDS', 60),
        ],
        'parent_login' => [
            'max_attempts' => (int) env('LEARNQUEST_PARENT_LOGIN_MAX_ATTEMPTS', 5),
            'decay_seconds' => (int) env('LEARNQUEST_PARENT_LOGIN_DECAY_SECONDS', 60),
        ],
        'verification_minutes' => (int) env('LEARNQUEST_VERIFICATION_MINUTES', 1440),
    ],

    /*
    |--------------------------------------------------------------------------
    | Learning
    |--------------------------------------------------------------------------
    */

    'activity_pass_score' => 70,

    /*
    |--------------------------------------------------------------------------
    | XP
    |--------------------------------------------------------------------------
    */

    'xp_per_level' => 100,

    'max_daily_xp' => 500,

    'activity_completion_xp' => 25,

    /*
    |--------------------------------------------------------------------------
    | Levels
    |--------------------------------------------------------------------------
    */

    'levels' => [
        1 => 0,
        2 => 100,
        3 => 250,
        4 => 500,
        5 => 900,
        6 => 1400,
        7 => 2000,
        8 => 2700,
        9 => 3500,
        10 => 4500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Gamification
    |--------------------------------------------------------------------------
    */

    'daily_streak_bonus' => 10,

    /*
    |--------------------------------------------------------------------------
    | Future Readiness
    |--------------------------------------------------------------------------
    */

    'max_skill_xp' => 1000,

    'future_readiness_max_score' => 100,

    'future_readiness' => [

        'levels' => [
            [
                'min_score' => 90,
                'title' => 'Future Ready',
            ],
            [
                'min_score' => 75,
                'title' => 'Visionary',
            ],
            [
                'min_score' => 60,
                'title' => 'Creator',
            ],
            [
                'min_score' => 45,
                'title' => 'Builder',
            ],
            [
                'min_score' => 25,
                'title' => 'Innovator',
            ],
            [
                'min_score' => 0,
                'title' => 'Explorer',
            ],
        ],

        'strongest_skills_limit' => 5,

        'weakest_skills_limit' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Tutor
    |--------------------------------------------------------------------------
    */

    'ai_feedback_enabled' => true,

    'adaptive_learning' => true,

    'nova' => [

        /*
         * Which provider powers Nova. See App\Services\Nova\NovaProviderFactory
         * for the available keys: anthropic (claude), openai (chatgpt), gemini,
         * openrouter, deepseek, mistral. Swapping providers is a single env
         * change — no other code needs to change.
         */
        'provider' => env('NOVA_AI_PROVIDER', 'gemini'),

        'fallback_providers' => array_values(array_filter(
            array_map(
                'trim',
                explode(',', env(
                    'NOVA_AI_FALLBACK_PROVIDERS',
                    'openrouter'
                ))
            )
        )),

        // How many prior turns are sent back to the AI provider as context.
        'history_limit' => 20,

        // Per-child, per-day cap on messages sent to Nova.
        'daily_message_limit' => 60,

        // Max tokens requested per Nova reply.
        'max_tokens' => 1024,

    ],

    /*
    |--------------------------------------------------------------------------
    | Daily Challenges
    |--------------------------------------------------------------------------
    |
    | Completion is derived from real activity (see DailyChallengeService);
    | only the display text and XP reward are configured here.
    |
    | "timezone" decides when a child's day starts and ends.
    */
    'daily_challenges' => [

        'timezone' => env('LEARNQUEST_TIMEZONE', 'UTC'),

        'challenges' => [

            'complete_lesson' => [
                'icon' => '🔥',
                'title' => 'Complete one lesson',
                'description' => 'Finish every activity in a lesson.',
                'xp' => 20,
            ],

            'create_something' => [
                'icon' => '🎨',
                'title' => 'Create something',
                'description' => 'Finish a project activity.',
                'xp' => 30,
            ],

            'ask_nova' => [
                'icon' => '🤖',
                'title' => 'Ask Nova AI',
                'description' => 'Send Nova a message.',
                'xp' => 10,
            ],

        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Parent Dashboard
    |--------------------------------------------------------------------------
    */

    'weekly_report_day' => 'Sunday',

    /*
    |--------------------------------------------------------------------------
    | Billing
    |--------------------------------------------------------------------------
    |
    | While "enforced" is false nobody is restricted and no trials start.
    | Flip it on at launch, then run: php artisan learnquest:start-trials
    |
    | A null limit means unlimited. Plan names and prices live in the
    | plans / plan_prices tables, not here.
    */
    'gateways' => [
        // Paystack is the default checkout provider. It remains fail-closed until
        // its sandbox credentials and webhook settings are configured.
        'default_provider' => env('LEARNQUEST_DEFAULT_PAYMENT_PROVIDER', 'paystack'),
        // Which provider handles each currency at checkout. Currencies not listed
        // here use default_provider. USD goes to PayPal; KES stays on Paystack.
        'currency_providers' => [
            'USD' => env('LEARNQUEST_USD_PROVIDER', 'paypal'),
        ],
        'providers' => [
            'intasend' => [
                'enabled' => (bool) env('LEARNQUEST_INTASEND_ENABLED', false),
                'driver' => \App\Services\Billing\Gateways\IntaSendGateway::class,
                'currencies' => ['KES'],
                'public_key' => env('INTASEND_PUBLIC_KEY'),
                'secret_key' => env('INTASEND_SECRET_KEY'),
                'webhook_secret' => env('INTASEND_WEBHOOK_CHALLENGE', env('INTASEND_WEBHOOK_SECRET')),
                'base_url' => env('INTASEND_BASE_URL', 'https://api.intasend.com'),
            ],
            'paystack' => [
                'enabled' => (bool) env('LEARNQUEST_PAYSTACK_ENABLED', false),
                'driver' => \App\Services\Billing\Gateways\PaystackGateway::class,
                'currencies' => ['KES'],
                'secret_key' => env('PAYSTACK_SECRET_KEY'),
                'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET'),
            ],
            'paypal' => [
                'enabled' => (bool) env('LEARNQUEST_PAYPAL_ENABLED', false),
                'driver' => \App\Services\Billing\Gateways\PayPalGateway::class,
                'currencies' => ['USD'],
                'client_id' => env('PAYPAL_CLIENT_ID'),
                'client_secret' => env('PAYPAL_CLIENT_SECRET'),
                'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
                'base_url' => env('PAYPAL_BASE_URL', 'https://api-m.sandbox.paypal.com'),
            ],
        ],
    ],

    'billing' => [
        'enforced' => (bool) env('LEARNQUEST_BILLING_ENFORCED', false),

        // Controls Free-plan availability for newly registered families.
        // Existing families are grandfathered in the users table.
        'free_plan_enabled' => (bool) env('LEARNQUEST_FREE_PLAN_ENABLED', true),

        'default_plan' => 'free',

        // Shown as the plan while billing is not enforced.
        'top_plan' => 'premium',

        'trial' => [
            'plan' => 'premium',
            'days' => (int) env('LEARNQUEST_TRIAL_DAYS', 14),        // Inside the 'trial' array:
            'requires_verified_email' => (bool) env('LEARNQUEST_TRIAL_REQUIRES_VERIFIED_EMAIL', false),
        ],

        // Reminders before access ends. M-Pesa and card payments are prepaid
        // terms with no automatic renewal, so parents need a nudge.
        'reminders' => [
            'trial_days_before' => [3, 1],
            'paid_days_before' => [7, 3, 1],
            'expired_window_days' => 3,

            // In-app banner thresholds.
            'banner' => [
                'trial_days' => 5,
                'paid_days' => 7,
                'expired_days' => 14,
            ],
        ],
        // Complimentary time an admin may grant in one go.
        'grants' => [
            'max_days' => 365,
        ],

        // Days a lapsed PAID plan keeps working while a late payment confirms.
        // Trials and complimentary time never get grace. 0 disables it.
        'grace_days' => (int) env('LEARNQUEST_GRACE_DAYS', 2),

        'currencies' => ['KES', 'USD'],

        'default_currency' => env('LEARNQUEST_DEFAULT_CURRENCY', 'KES'),

        'plans' => [
            'free' => [
                'limits' => [
                    'children' => 1,
                    'modules_per_course' => 1,
                    'nova_messages_per_day' => 5,
                ],
                'features' => [
                    'skill_reports' => false,
                ],
                'highlights' => [
                    '1 child profile',
                    'The first module of every course',
                    'XP, streaks and badges',
                    '5 Nova messages a day',
                    'Weekly activity and streak overview',
                ],
            ],

            'premium' => [
                'limits' => [
                    'children' => 4,
                    'modules_per_course' => null,
                    'nova_messages_per_day' => null,
                ],
                'features' => [
                    'skill_reports' => true,
                ],
                'highlights' => [
                    'Up to 4 child profiles',
                    'Every module of every course',
                    'XP, streaks and badges',
                    'Much more time with Nova',
                    'Full skill reports: strengths and growth areas',
                ],
            ],
        ],
    ],

    'sms' => [
        'enabled' => env('LEARNQUEST_SMS_ENABLED', false),

        'default_provider' => env(
            'LEARNQUEST_SMS_PROVIDER',
            'africastalking'
        ),

        'providers' => [
            'africastalking' => [
                'enabled' => env('LEARNQUEST_SMS_AT_ENABLED', false),
            ],

            'infobip' => [
                'enabled' => env('LEARNQUEST_SMS_INFOBIP_ENABLED', false),
            ],
        ],
    ],

    'require_verified_email_for_checkout' => (bool) env('LEARNQUEST_CHECKOUT_REQUIRES_VERIFIED_EMAIL', true),
];
