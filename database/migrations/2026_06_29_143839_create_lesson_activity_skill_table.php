<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_activity_skill', function (Blueprint $table) {

            $table->id();

            $table->foreignId('activity_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('skill_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('weight')
                ->default(100);

            $table->timestamps();

            $table->unique([
                'activity_id',
                'skill_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'lesson_activity_skill'
        );
    }
};
