<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Existing accounts are grandfathered. New registrations explicitly
            // set this based on LEARNQUEST_FREE_PLAN_ENABLED.
            $table->boolean('free_plan_grandfathered')->default(true)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('free_plan_grandfathered');
        });
    }
};
