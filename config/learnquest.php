<?php

return [

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

];
