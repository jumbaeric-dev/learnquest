<?php

namespace App\Support\Lqds;

use Illuminate\Support\Collection;

class ComponentRegistry
{
    /**
     * Return all registered components.
     */
    public static function all(): Collection
    {
        return collect(config('lqds.components', []));
    }

    /**
     * Find a component by its unique ID.
     */
    public static function find(string $id): ?array
    {
        return static::all()
            ->firstWhere('id', $id);
    }

    /**
     * Group components by category.
     */
    public static function grouped(): Collection
    {
        return static::all()
            ->groupBy('category')
            ->sortKeys();
    }

    /**
     * Return only stable components.
     */
    public static function stable(): Collection
    {
        return static::all()
            ->where('status', 'Stable')
            ->values();
    }

    /**
     * Return components in a category.
     */
    public static function category(string $category): Collection
    {
        return static::all()
            ->where('category', $category)
            ->values();
    }
}