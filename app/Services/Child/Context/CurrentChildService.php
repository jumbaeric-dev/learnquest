<?php

namespace App\Services\Child\Context;

use App\Models\Child;

class CurrentChildService
{
    /**
     * Return the currently active child.
     *
     * Temporary implementation.
     *
     * Later this will use authentication,
     * parent child switching,
     * or classroom context.
     */
    public function current(): ?Child
    {
        return Child::query()->first();
    }
}