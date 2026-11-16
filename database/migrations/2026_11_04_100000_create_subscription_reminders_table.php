<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_reminders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subscription_id')
                ->constrained()
                ->cascadeOnDelete();

            // ending_7, ending_3, ending_1 or expired.
            $table->string('kind', 20);

            // The ends_at this reminder was about. Renewing moves ends_at,
            // so each new period gets its own reminders.
            $table->timestamp('period_ends_at');

            $table->timestamp('sent_at');

            $table->timestamps();

            $table->unique(['subscription_id', 'kind', 'period_ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_reminders');
    }
};