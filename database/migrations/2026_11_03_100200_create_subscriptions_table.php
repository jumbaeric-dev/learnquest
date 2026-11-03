<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('plan_id')->constrained();

            $table->foreignId('plan_price_id')
                ->nullable()
                ->constrained('plan_prices')
                ->nullOnDelete();

            // trialing, active or canceled. Expiry is derived from ends_at,
            // so no scheduled job is needed to flip statuses.
            $table->string('status', 20);

            $table->timestamp('starts_at');

            // "Access valid until". Every payment method moves this date.
            $table->timestamp('ends_at');

            $table->timestamp('canceled_at')->nullable();

            $table->boolean('auto_renews')->default(false);

            $table->string('gateway', 30)->nullable();
            $table->string('gateway_reference')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};