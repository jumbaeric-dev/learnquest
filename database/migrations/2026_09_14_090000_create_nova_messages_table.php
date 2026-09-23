<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nova_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('child_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
             * 'user' or 'assistant'.
             */
            $table->string('role');

            $table->text('content');

            $table->string('provider')->nullable();

            $table->timestamps();

            $table->index(['child_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nova_messages');
    }
};
