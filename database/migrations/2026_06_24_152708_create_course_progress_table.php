<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_progress', function (Blueprint $table) {

            $table->id();

            $table->foreignId('child_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('course_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('completed_lessons')
                ->default(0);

            $table->unsignedInteger('total_lessons')
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
                'course_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_progress');
    }
};