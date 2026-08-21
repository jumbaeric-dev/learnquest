<?php

namespace App\Http\Middleware;

use App\Services\Child\Context\CurrentChildService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureChildContext
{
  /**
   * Ensure the request has a valid active child context.
   *
   * Child routes must only be accessible when:
   *
   * 1. A user is authenticated.
   * 2. That authenticated user resolves to a Child.
   * 3. The child account is active.
   *
   * Guests are redirected to the login page.
   * Authenticated users without a valid child context receive 403.
   */
  public function handle(Request $request, Closure $next): Response
  {
    if (!$request->user()) {
      return redirect()->route("login");
    }

    $child = app(CurrentChildService::class)->current();

    if (!$child) {
      abort(403);
    }

    if (!$child->is_active) {
      abort(403);
    }

    return $next($request);
  }
}
