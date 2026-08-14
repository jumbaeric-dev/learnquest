<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | World Marketing
            |--------------------------------------------------------------------------
            */

            $table->string('tagline')
                ->nullable()
                ->after('difficulty');

            $table->text('story_intro')
                ->nullable()
                ->after('tagline');

            /*
            |--------------------------------------------------------------------------
            | World Character
            |--------------------------------------------------------------------------
            */

            $table->string('hero_character')
                ->nullable()
                ->after('tagline');

            /*
            |--------------------------------------------------------------------------
            | World Visuals
            |--------------------------------------------------------------------------
            */

            $table->string('banner_animation')
                ->nullable()
                ->after('hero_character');

            /*
            |--------------------------------------------------------------------------
            | Progression
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('unlock_level')
                ->default(1)
                ->after('banner_animation');

            $table->unsignedSmallInteger('estimated_hours')
                ->default(0)
                ->after('unlock_level');

            /*
            |--------------------------------------------------------------------------
            | Rewards
            |--------------------------------------------------------------------------
            */

            $table->string('badge_icon')
                ->nullable()
                ->after('estimated_hours');

            /*
            |--------------------------------------------------------------------------
            | Discovery
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_featured')
                ->default(false)
                ->after('badge_icon');

            $table->boolean('is_seasonal')
                ->default(false)
                ->after('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {

            $table->dropColumn([
                'tagline',
                'story_intro',
                'hero_character',
                'banner_animation',
                'unlock_level',
                'estimated_hours',
                'badge_icon',
                'is_featured',
                'is_seasonal',
            ]);
        });
    }
};
