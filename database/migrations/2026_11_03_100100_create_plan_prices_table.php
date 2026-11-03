<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plan_id')
                ->constrained()
                ->cascadeOnDelete();

            // ISO currency code, e.g. KES or USD.
            $table->string('currency', 3);

            // Length of access this price buys: 1, 3 or 12.
            $table->unsignedTinyInteger('months');

            // Minor units (cents), so KES 499.00 is stored as 49900.
            $table->unsignedInteger('amount_minor');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['plan_id', 'currency', 'months']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_prices');
    }
};