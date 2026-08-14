<?php

namespace App\Services;

class DesignSystemService
{
    public function name(): string
    {
        return config('lqds.name');
    }

    public function version(): string
    {
        return config('lqds.version');
    }

    public function components(): array
    {
        return config('lqds.components', []);
    }

    public function totalComponents(): int
    {
        return count($this->components());
    }
}