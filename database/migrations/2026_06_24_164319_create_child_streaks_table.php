<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_streaks', function (Blueprint $table) {

            $table->id();

            $table->foreignId('child_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('current_streak')
                ->default(0);

            $table->unsignedInteger('longest_streak')
                ->default(0);

            $table->date('last_activity_date')
                ->nullable();

            $table->timestamps();

            $table->unique('child_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_streaks');
    }
};