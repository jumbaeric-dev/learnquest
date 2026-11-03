<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureParentContext
{
  /**
   * Parent routes require an authenticated user with the parent role.
   * Guests go to the parent login; other signed-in users get 403.
   */
  public function handle(Request $request, Closure $next): Response
  {
    $user = $request->user();

    if (!$user) {
      return redirect()->route("parent.login");
    }

    if (!$user->isParent()) {
      abort(403);
    }

    return $next($request);
  }
}