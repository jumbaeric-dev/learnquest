<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('children', function (Blueprint $table) {

            $table->id();

            $table->foreignId('parent_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('first_name');

            $table->string('last_name')
                ->nullable();

            $table->date('date_of_birth')
                ->nullable();

            $table->string('avatar')
                ->nullable();

            $table->unsignedInteger('xp')
                ->default(0);

            $table->unsignedInteger('level')
                ->default(1);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('children');
    }
};