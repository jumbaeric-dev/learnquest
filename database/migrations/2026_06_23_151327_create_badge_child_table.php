<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badge_child', function (Blueprint $table) {

            $table->id();

            $table->foreignId('badge_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('child_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamp('earned_at')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'badge_id',
                'child_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_child');
    }
};