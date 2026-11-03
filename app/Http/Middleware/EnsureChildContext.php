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
   * 2. The authenticated child resolves to their own Child, or a parent
   *    has selected one of their own children for preview.
   * 3. The child account is active.
   *
   * Guests are redirected to the login page. Parents with no child
   * selected are sent to pick one. Anyone else without a valid child
   * context receives 403.
   */
  public function handle(Request $request, Closure $next): Response
  {
    if (!$request->user()) {
      return redirect()->route("login");
    }

    $child = app(CurrentChildService::class)->current();

    if (!$child) {
      if ($request->user()->isParent()) {
        return redirect()
          ->route("parent.children.index")
          ->with(
            "parent_child_status",
            "Choose a child to preview their learning space."
          );
      }

      abort(403);
    }

    if (!$child->is_active) {
      abort(403);
    }

    return $next($request);
  }
}