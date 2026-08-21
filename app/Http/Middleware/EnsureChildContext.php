<?php

namespace App\Http\Middleware;

use App\Services\Child\Context\CurrentChildService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureChildContext
{
    /**
     * Ensure the authenticated request has a valid child context.
     *
     * Child routes must only be accessible when:
     *
     * 1. A user is authenticated.
     * 2. That authenticated user resolves to a Child.
     * 3. The child account is active.
     */
    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        if (! $request->user()) {
            abort(403);
        }

        $child = app(CurrentChildService::class)->current();

        if (! $child) {
            abort(403);
        }

        if (! $child->is_active) {
            abort(403);
        }

        return $next($request);
    }
}