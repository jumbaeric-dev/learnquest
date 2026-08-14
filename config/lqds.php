<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LearnQuest Design System
    |--------------------------------------------------------------------------
    */

    'name' => 'LearnQuest Design System',

    'short_name' => 'LQDS',

    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Registered Components
    |--------------------------------------------------------------------------
    */

    'components' => [

        [
            'id' => 'avatar',
            'name' => 'Avatar',
            'tag' => 'x-lq.avatar',
            'category' => 'Foundation',
            'status' => 'Stable',
            'version' => '1.0.0',
            'description' => 'Displays a learner avatar.',
        ],

        [
            'id' => 'glass_card',
            'name' => 'Glass Card',
            'tag' => 'x-lq.glass-card',
            'category' => 'Foundation',
            'status' => 'Stable',
            'version' => '1.0.0',
            'description' => 'Reusable frosted glass container.',
        ],

        [
            'id' => 'progress_bar',
            'name' => 'Progress Bar',
            'tag' => 'x-lq.progress-bar',
            'category' => 'Foundation',
            'status' => 'Stable',
            'version' => '1.0.0',
            'description' => 'Displays progress values.',
        ],

        [
            'id' => 'icon_button',
            'name' => 'Icon Button',
            'tag' => 'x-lq.icon-button',
            'category' => 'Foundation',
            'status' => 'Stable',
            'version' => '1.0.0',
            'description' => 'Reusable icon action button.',
        ],

        [
            'id' => 'badge',
            'name' => 'Badge',
            'tag' => 'x-lq.badge',
            'category' => 'Core',
            'status' => 'Development',
            'version' => '1.0.0',
            'description' =>
            'Compact visual indicator used for achievements, status, identity, and categories.',
        ],

        [
            'id' => 'button',
            'name' => 'Button',
            'tag' => 'x-lq.button',
            'category' => 'Core',
            'status' => 'Experimental',
            'version' => '1.0.0',
            'description' => 'Reusable button component for primary and secondary actions.',
        ],

        [
            'id' => 'stat_chip',
            'name' => 'Stat Chip',
            'tag' => 'x-lq.stat-chip',
            'category' => 'Core',
            'status' => 'Experimental',
            'version' => '1.0.0',
            'description' => 'Compact component for displaying a statistic with an optional icon.',
        ],

        [
            'id' => 'section_title',
            'name' => 'Section Title',
            'tag' => 'x-lq.section-title',
            'category' => 'Core',
            'status' => 'Experimental',
            'version' => '1.0.0',
            'description' => 'Creates consistent content section hierarchy.',
        ],

    ],

    /*
|--------------------------------------------------------------------------
| Component Settings
|--------------------------------------------------------------------------
*/

    'settings' => [

        'badge' => [

            'defaults' => [

                'variant' => 'neutral',

                'size' => 'md',

                'icon_position' => 'left',

            ],

            'variants' => [

                'neutral',
                'primary',
                'success',
                'achievement',
                'locked',

            ],

            'sizes' => [

                'sm',
                'md',
                'lg',

            ],

        ],

        'button' => [

            'defaults' => [

                'variant' => 'primary',

                'size' => 'md',

                'type' => 'button',

                'full_width' => false,

            ],

            'variants' => [

                'primary',

                'secondary',

            ],

            'sizes' => [

                'sm',

                'md',

                'lg',

            ],

        ],

        'stat_chip' => [

            'defaults' => [

                'variant' => 'neutral',

                'size' => 'md',

            ],

            'variants' => [

                'neutral',
                'primary',
                'success',
                'warning',
                'danger',
                'reward',

            ],

            'sizes' => [

                'sm',
                'md',
                'lg',

            ],

        ],

        'section_title' => [

            'defaults' => [

                'size' => 'md',

                'align' => 'left',

            ],

            'sizes' => [

                'sm',
                'md',
                'lg',

            ],

            'alignments' => [

                'left',
                'center',

            ],

        ],

    ],

];
