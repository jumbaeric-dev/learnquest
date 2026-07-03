<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_progress', function (Blueprint $table) {

            $table->id();

            $table->foreignId('child_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('activity_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->boolean('completed')
                ->default(false);

            $table->unsignedInteger('score')
                ->nullable();

            $table->unsignedInteger('xp_earned')
                ->default(0);

            $table->timestamp('completed_at')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'child_id',
                'activity_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_progress');
    }
};