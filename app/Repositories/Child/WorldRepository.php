<?php

namespace App\Repositories\Child;

use App\Models\Subject;

class WorldRepository
{
    /**
     * Get all learning worlds.
     */
    public function all()
    {
        return Subject::query()
            ->where('is_active', true)
            ->withCount([
                'courses' => function ($query) {
                    $query->where('is_published', true);
                },
            ])
            ->orderBy('position')
            ->orderBy('name')
            ->get();
    }

    /**
     * Find a learning world with everything required
     * to render the World page.
     */
    public function find(int $id): ?Subject
    {
        return Subject::query()
            ->where('is_active', true)
            ->with([
                'courses' => function ($query) {
                    $query
                        ->where('is_published', true)
                        ->orderBy('title');
                },

                'courses.modules' => function ($query) {
                    $query->orderBy('position');
                },

                'courses.modules.lessons' => function ($query) {
                    $query->orderBy('position');
                },
            ])
            ->withCount([
                'courses' => function ($query) {
                    $query->where('is_published', true);
                },
            ])
            ->find($id);
    }
}
