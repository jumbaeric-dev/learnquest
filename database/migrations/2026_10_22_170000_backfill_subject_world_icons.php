<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fill missing icons for the built-in LearnQuest worlds.
     * Existing non-empty icons chosen by an admin are preserved.
     */
    public function up(): void
    {
        $icons = [
            'math-galaxy' => 'heroicon-o-calculator',
            'reading-kingdom' => 'heroicon-o-book-open',
            'science-lab' => 'heroicon-o-beaker',
            'code-city' => 'heroicon-o-code-bracket',
            'creative-studio' => 'heroicon-o-paint-brush',
            'bible-adventures' => 'heroicon-o-book-open',
            'life-skills-village' => 'heroicon-o-heart',
            'ai-academy' => 'heroicon-o-sparkles',
            'ai-for-kids' => 'heroicon-o-cpu-chip',
        ];

        foreach ($icons as $slug => $icon) {
            DB::table('subjects')
                ->where('slug', $slug)
                ->where(function ($query): void {
                    $query->whereNull('icon')->orWhere('icon', '');
                })
                ->update([
                    'icon' => $icon,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Keep icon data on rollback. These values are editable curriculum
     * content, and clearing them could erase intentional admin changes.
     */
    public function down(): void
    {
        // Intentionally left blank to preserve content configuration.
    }
};
