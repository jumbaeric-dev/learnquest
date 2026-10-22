<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nova AI Providers
    |--------------------------------------------------------------------------
    |
    | Nova's default engine is Claude (Anthropic). config('learnquest.nova.provider')
    | picks which of these is active; both are configured here so switching
    | providers only ever means changing NOVA_AI_PROVIDER in .env.
    |
    */

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'workspace_id' => env('ANTHROPIC_WORKSPACE_ID'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-5-5'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env(
            'GEMINI_MODEL',
            'gemini-3.5-flash-lite'
        ),
        'fallback_model' => env(
            'GEMINI_FALLBACK_MODEL',
            'gemini-3.6-flash'
        ),
    ],

    'deepseek' => [
        'api_key' => env('DEEPSEEK_API_KEY'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
    ],

    'mistral' => [
        'api_key' => env('MISTRAL_API_KEY'),
        'model' => env('MISTRAL_MODEL', 'mistral-small-latest'),
    ],

    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'model' => env(
            'OPENROUTER_MODEL',
            'thinkingmachines/inkling-small:free'
        ),
    ],

];
