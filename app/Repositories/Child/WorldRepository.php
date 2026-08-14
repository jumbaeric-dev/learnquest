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
            ->withCount('courses')
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
            ->with([
                'courses' => function ($query) {
                    $query->orderBy('title');
                },

                'courses.modules' => function ($query) {
                    $query->orderBy('position');
                },

                'courses.modules.lessons' => function ($query) {
                    $query->orderBy('position');
                },
            ])
            ->withCount('courses')
            ->find($id);
    }
}