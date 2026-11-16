<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('plan_price_id')->nullable()->constrained('plan_prices')->nullOnDelete();

            // Immutable purchase snapshot: the referenced plan/price may later change.
            $table->string('plan_key', 80);
            $table->unsignedSmallInteger('duration_months');
            $table->string('reference', 64)->unique();
            $table->string('idempotency_key', 100)->nullable();
            $table->string('provider', 30);
            $table->string('provider_transaction_id', 150)->nullable();
            $table->unsignedBigInteger('amount_minor');
            $table->char('currency', 3);
            $table->string('status', 24)->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('failure_code', 100)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'idempotency_key'], 'payments_user_idempotency_unique');
            $table->unique(['provider', 'provider_transaction_id'], 'payments_provider_transaction_unique');
            $table->index(['user_id', 'status', 'created_at']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
