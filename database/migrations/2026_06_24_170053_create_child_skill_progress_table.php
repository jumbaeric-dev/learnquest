<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_skill_progress', function (Blueprint $table) {

            $table->id();

            $table->foreignId('child_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('skill_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('xp')
                ->default(0);

            $table->unsignedInteger('level')
                ->default(1);

            $table->decimal('progress_percentage', 5, 2)
                ->default(0);

            $table->timestamps();

            $table->unique([
                'child_id',
                'skill_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_skill_progress');
    }
};