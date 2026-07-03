<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lesson_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');

            $table->enum('activity_type', [
                'video',
                'story',
                'quiz',
                'audio',
                'drag_drop',
                'drawing',
                'flashcard',
                'ai_chat',
                'coding_challenge',
                'project'
            ]);

            $table->jsonb('content');

            $table->unsignedInteger('position')->default(1);

            $table->integer('xp_reward')->default(5);

            $table->boolean('is_published')
                ->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
