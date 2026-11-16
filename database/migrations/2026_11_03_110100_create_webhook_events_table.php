<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 30);
            $table->string('event_id', 180);
            $table->string('event_type', 120);
            $table->string('payment_reference', 64)->nullable();
            $table->string('status', 24)->default('received');
            $table->timestamp('signature_verified_at')->nullable();
            // Only normalized, allow-listed metadata is stored; the encrypted cast protects it at rest.
            $table->longText('payload')->nullable();
            $table->unsignedSmallInteger('attempts')->default(1);
            $table->timestamp('processed_at')->nullable();
            $table->string('failure_reason', 500)->nullable();
            $table->timestamps();

            $table->unique(['provider', 'event_id'], 'webhook_provider_event_unique');
            $table->index(['status', 'created_at']);
            $table->index(['payment_reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};
