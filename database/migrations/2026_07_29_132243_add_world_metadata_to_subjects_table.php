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
            | World Presentation
            |--------------------------------------------------------------------------
            */

            $table->string('cover_image')
                ->nullable()
                ->after('icon');

            $table->string('theme_color', 20)
                ->default('#6366F1')
                ->after('cover_image');

            $table->string('background_music')
                ->nullable()
                ->after('theme_color');

            /*
            |--------------------------------------------------------------------------
            | World Ordering
            |--------------------------------------------------------------------------
            */

            $table->unsignedSmallInteger('sort_order')
                ->default(0)
                ->after('background_music');

            /*
            |--------------------------------------------------------------------------
            | Difficulty
            |--------------------------------------------------------------------------
            */

            $table->enum('difficulty', [
                'beginner',
                'intermediate',
                'advanced',
            ])
                ->default('beginner')
                ->after('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {

            $table->dropColumn([
                'cover_image',
                'theme_color',
                'background_music',
                'sort_order',
                'difficulty',
            ]);

        });
    }
};