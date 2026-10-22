<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_challenge_claims', function (Blueprint $table) {
            $table->id();

            $table->foreignId('child_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('challenge_key');

            $table->date('challenge_date');

            $table->unsignedInteger('xp_awarded');

            $table->timestamps();

            $table->unique([
                'child_id',
                'challenge_key',
                'challenge_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_challenge_claims');
    }
};