<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_grants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subscription_id')
                ->constrained()
                ->cascadeOnDelete();

            // The parent who received the time.
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // The admin who granted it. Kept if the admin account is deleted.
            $table->foreignId('granted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('plan_id')->constrained();

            $table->unsignedSmallInteger('days');

            // null when the grant created a brand-new subscription.
            $table->timestamp('previous_ends_at')->nullable();
            $table->timestamp('new_ends_at');

            $table->text('reason');

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_grants');
    }
};