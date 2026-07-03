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
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();

            $table->foreignId('child_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('lesson_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('completed_activities')
                ->default(0);

            $table->unsignedInteger('total_activities')
                ->default(0);

            $table->decimal('progress_percentage', 5, 2)
                ->default(0);

            $table->boolean('completed')
                ->default(false);

            $table->timestamp('completed_at')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'child_id',
                'lesson_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_progress');
    }
};
